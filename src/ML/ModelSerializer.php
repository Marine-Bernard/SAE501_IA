<?php

namespace Mjrmb\Sae501ia\ML;

use Mjrmb\Sae501ia\ML\Models\Tree;
use Mjrmb\Sae501ia\ML\Models\MLP;
use Mjrmb\Sae501ia\ML\Models\ModelInterface;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\Persistable;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;

class ModelSerializer
{
    private string $modelType;
    private string $modelPath;
    private string $metadataPath;

    public function __construct(string $modelType = 'tree')
    {
        $this->modelType = $modelType;
        $this->modelPath = __DIR__ . "/../../models/model_{$modelType}.rbx";
        $this->metadataPath = __DIR__ . "/../../models/metadata_{$modelType}.json";
    }

    public function saveModel(ModelInterface $model, array $metadata = []): void
    {
        $baseModel = $model->getModel();

        // Vérifie que le modèle est d'un type valide
        if (!($baseModel instanceof Persistable)) {
            throw new \RuntimeException("Le modèle doit être persistable");
        }

        // Vérifie la correspondance du type
        if (
            ($this->modelType === 'tree' && !($model instanceof Tree)) ||
            ($this->modelType === 'mlp' && !($model instanceof MLP))
        ) {
            throw new \RuntimeException("Le type du modèle ne correspond pas au type attendu");
        }

        // Crée le répertoire si nécessaire
        if (!is_dir(dirname($this->modelPath))) {
            mkdir(dirname($this->modelPath), 0777, true);
        }

        // Utilise le sérialiseur RBX pour convertir le modèle
        $serializer = new RBX();
        $encoding = $serializer->serialize($baseModel);

        // Sauvegarde le modèle
        $persister = new Filesystem($this->modelPath);
        $persister->save($encoding);

        // Ajoute des informations aux métadonnées
        $metadata['model_type'] = get_class($baseModel);
        $metadata['training_date'] = date('Y-m-d H:i:s');

        // Sauvegarde les métadonnées
        file_put_contents(
            $this->metadataPath,
            json_encode($metadata, JSON_PRETTY_PRINT)
        );
    }

    public function loadModel(): array
    {
        if (!file_exists($this->modelPath)) {
            throw new \RuntimeException("Le modèle {$this->modelType} n'a pas encore été entraîné");
        }

        // Charge le modèle encodé
        $persister = new Filesystem($this->modelPath);
        $encoding = $persister->load();

        // Désérialise le modèle
        $serializer = new RBX();
        $baseModel = $serializer->deserialize($encoding);

        // Crée l'instance du wrapper approprié
        $model = match ($this->modelType) {
            'tree' => new Tree(),
            'mlp' => new MLP(),
            default => throw new \RuntimeException("Type de modèle non supporté")
        };

        // Vérifie et configure le modèle chargé
        if ($this->modelType === 'tree' && !($baseModel instanceof ClassificationTree)) {
            throw new \RuntimeException('Le modèle chargé n\'est pas un ClassificationTree');
        } elseif ($this->modelType === 'mlp' && !($baseModel instanceof MultilayerPerceptron)) {
            throw new \RuntimeException('Le modèle chargé n\'est pas un MultilayerPerceptron');
        }

        // Configure le modèle de base dans le wrapper
        if ($this->modelType === 'tree') {
            $model->setModel($baseModel);
        } else {
            $model->setModel($baseModel);
        }

        // Charge les métadonnées
        $metadata = [];
        if (file_exists($this->metadataPath)) {
            $metadata = json_decode(file_get_contents($this->metadataPath), true);
        }

        return [$model, $metadata];
    }
}
