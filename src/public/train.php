<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\ML\AnimalClassifier;
use Mjrmb\Sae501ia\ML\ModelSerializer;
use Rubix\ML\Persistable;

// Configuration
ini_set('display_errors', 1);
ini_set('memory_limit', '10G');

try {
  echo "=== Démarrage de l'entraînement du classificateur d'animaux ===\n\n";

  $classifier = new AnimalClassifier();
  $serializer = new ModelSerializer();

  $datasetPath = __DIR__ . '/../../animals';

  echo "Chargement des images depuis : $datasetPath\n";

  // Vérifie si le dossier existe
  if (!is_dir($datasetPath)) {
    throw new \RuntimeException("Le dossier 'animals' n'existe pas à la racine du projet");
  }

  // Liste les catégories (dossiers)
  $categories = array_filter(scandir($datasetPath), function ($item) use ($datasetPath) {
    return is_dir($datasetPath . '/' . $item) && !in_array($item, ['.', '..']);
  });

  echo "Catégories trouvées : " . implode(', ', $categories) . "\n\n";

  // Entraînement
  $results = $classifier->train($datasetPath);

  echo "\nRésultats de l'entraînement :\n";
  echo "- Images traitées : {$results['samples_count']}\n";
  echo "- Précision : {$results['accuracy']}%\n";
  echo "- Durée : {$results['training_time']} secondes\n";

  // Sauvegarde
  echo "\nSauvegarde du modèle...\n";
  $model = $classifier->getModel();
  if ($model instanceof Persistable) {
    $serializer->saveModel($model, [
      'training_date' => date('Y-m-d H:i:s'),
      'metrics' => $results,
      'categories' => $categories
    ]);
  } else {
    throw new \RuntimeException("Le modèle doit implémenter l'interface Persistable");
  }

  echo "\n✅ Entraînement terminé avec succès !\n";
} catch (\Exception $e) {
  echo "\n❌ Erreur : " . $e->getMessage() . "\n";
  exit(1);
}
