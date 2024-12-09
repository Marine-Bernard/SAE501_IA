<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\ML\AnimalClassifier;
use Mjrmb\Sae501ia\ML\ModelSerializer;

try {
  if ($argc < 2) {
    echo "Usage: php test.php <chemin_vers_image>\n";
    exit(1);
  }

  $imagePath = $argv[1];

  if (!file_exists($imagePath)) {
    throw new \RuntimeException("L'image $imagePath n'existe pas");
  }

  echo "=== Classification d'image ===\n\n";

  // Charge le modèle pré-entraîné
  $serializer = new ModelSerializer();
  [$model, $metadata] = $serializer->loadModel();

  $classifier = new AnimalClassifier();
  $classifier->setModel($model);

  echo "Modèle chargé (entraîné le {$metadata['training_date']})\n";
  echo "Analyse de l'image : $imagePath\n\n";

  // Prédiction
  $startTime = microtime(true);
  $result = $classifier->predict($imagePath);
  $predictionTime = round((microtime(true) - $startTime) * 1000);

  // Affichage des résultats
  echo "=== Résultats ===\n";
  echo "Prédiction : {$result['prediction']}\n";
  echo "Temps d'analyse : {$predictionTime}ms\n\n";

   // SVC ne fournit pas de probabilités
   if (!empty($result['probabilities'])) {
    echo "\nProbabilités par classe :\n";
    foreach ($result['probabilities'] as $class => $probability) {
      $percentage = round($probability * 100, 2);
      $bar = str_repeat('█', (int)($percentage / 5));
      echo sprintf("%-10s : %s %.2f%%\n", $class, $bar, $percentage);
    }
  }
} catch (\Exception $e) {
  echo "\n❌ Erreur : " . $e->getMessage() . "\n";
  exit(1);
}
