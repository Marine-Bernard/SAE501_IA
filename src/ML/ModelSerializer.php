<?php

namespace Mjrmb\Sae501ia\ML;

use Rubix\ML\Persistable;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Serializers\RBX;
use Rubix\ML\Classifiers\KNearestNeighbors;

class ModelSerializer
{
    private const MODEL_PATH = __DIR__ . '/../../models/model.rbx';
    private const METADATA_PATH = __DIR__ . '/../../models/metadata.json';

    public function saveModel(Persistable $model, array $metadata = []): void
    {
        // Vérifie que le modèle est bien une instance de KNearestNeighbors
        if (!($model instanceof KNearestNeighbors)) {
            throw new \RuntimeException("Le modèle doit être une instance de KNearestNeighbors");
        }

        // Crée le répertoire si nécessaire
        if (!is_dir(dirname(self::MODEL_PATH))) {
            mkdir(dirname(self::MODEL_PATH), 0777, true);
        }

        // Utilise le sérialiseur RBX pour convertir le modèle
        $serializer = new RBX();
        $encoding = $serializer->serialize($model);

        // Sauvegarde le modèle
        $persister = new Filesystem(self::MODEL_PATH);
        $persister->save($encoding);

        // Sauvegarde les métadonnées
        if (!empty($metadata)) {
            file_put_contents(
                self::METADATA_PATH,
                json_encode($metadata, JSON_PRETTY_PRINT)
            );
        }
    }

    public function loadModel(): array
    {
        if (!file_exists(self::MODEL_PATH)) {
            throw new \RuntimeException('Le modèle n\'a pas encore été entraîné');
        }

        // Charge le modèle encodé
        $persister = new Filesystem(self::MODEL_PATH);
        $encoding = $persister->load();

        // Désérialise le modèle
        $serializer = new RBX();
        $model = $serializer->deserialize($encoding);  // Correction ici : unserialize -> deserialize

        // Vérifie que le modèle est du bon type
        if (!($model instanceof KNearestNeighbors)) {
            throw new \RuntimeException('Le modèle chargé n\'est pas un KNearestNeighbors');
        }

        // Charge les métadonnées si elles existent
        $metadata = [];
        if (file_exists(self::METADATA_PATH)) {
            $metadata = json_decode(file_get_contents(self::METADATA_PATH), true);
        }

        return [$model, $metadata];
    }
}