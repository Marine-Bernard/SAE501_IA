<?php

namespace Mjrmb\Sae501ia\ML;

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

    public function saveModel(Persistable $model, array $metadata = []): void
    {
        // Vérifie que le modèle est d'un type valide
        if (
            !($model instanceof ClassificationTree) &&
            !($model instanceof MultilayerPerceptron)
        ) {
            throw new \RuntimeException("Le modèle doit être une instance de ClassificationTree ou MultilayerPerceptron");
        }

        // Crée le répertoire si nécessaire
        if (!is_dir(dirname($this->modelPath))) {
            mkdir(dirname($this->modelPath), 0777, true);
        }

        // Utilise le sérialiseur RBX pour convertir le modèle
        $serializer = new RBX();
        $encoding = $serializer->serialize($model);

        // Sauvegarde le modèle
        $persister = new Filesystem($this->modelPath);
        $persister->save($encoding);

        // Ajoute des informations aux métadonnées
        $metadata['model_type'] = get_class($model);
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
        $model = $serializer->deserialize($encoding);

        // Vérifie que le modèle est d'un type valide
        if (
            !($model instanceof ClassificationTree) &&
            !($model instanceof MultilayerPerceptron)
        ) {
            throw new \RuntimeException('Le modèle chargé n\'est pas du bon type');
        }

        // Charge les métadonnées
        $metadata = [];
        if (file_exists($this->metadataPath)) {
            $metadata = json_decode(file_get_contents($this->metadataPath), true);
        }

        return [$model, $metadata];
    }
}
