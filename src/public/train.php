<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\ML\DigitClassifier;
use Mjrmb\Sae501ia\ML\ModelSerializer;

ini_set('memory_limit', '10G');
ini_set('display_errors', 1);

try {
  $modelType = $argc > 1 ? $argv[1] : 'tree';
  if (!in_array($modelType, ['tree', 'mlp'])) {
    throw new \RuntimeException("Type de modèle invalide. Utilisez 'tree' ou 'mlp'");
  }

  echo "=== Entraînement du classificateur de chiffres ($modelType) ===\n\n";

  $classifier = new DigitClassifier($modelType);
  $serializer = new ModelSerializer($modelType);

  $datasetPath = __DIR__ . '/../../image/training';
  if (!is_dir($datasetPath)) {
    throw new \RuntimeException("Le dossier 'image/training' n'existe pas");
  }

  $classifier->train($datasetPath);
  $serializer->saveModel($classifier->getClassifier(), []);

  echo "\n✅ Entraînement terminé avec succès !\n";
} catch (\Exception $e) {
  echo "\n❌ Erreur : " . $e->getMessage() . "\n";
  exit(1);
}
