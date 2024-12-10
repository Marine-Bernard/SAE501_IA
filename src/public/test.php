<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\ML\DigitClassifier;
use Mjrmb\Sae501ia\ML\ModelSerializer;
use Rubix\ML\CrossValidation\Metrics\Accuracy;

ini_set('memory_limit', '10G');
ini_set('display_errors', 1);

try {
    $modelType = $argc > 1 ? $argv[1] : 'tree';
    if (!in_array($modelType, ['tree', 'mlp'])) {
        throw new \RuntimeException("Type de modèle invalide. Utilisez 'tree' ou 'mlp'");
    }

    echo "=== Évaluation du modèle ($modelType) sur le jeu de test ===\n\n";

    $serializer = new ModelSerializer($modelType);
    [$model, $metadata] = $serializer->loadModel();

    $classifier = new DigitClassifier($modelType);
    $classifier->setModel($model);

    echo "Modèle chargé (entraîné le {$metadata['training_date']})\n";

    $testingPath = __DIR__ . '/../../image/testing';
    if (!is_dir($testingPath)) {
        throw new \RuntimeException("Le dossier 'image/testing' n'existe pas");
    }

    // Initialisation des métriques
    $totalImages = 0;
    $correctPredictions = 0;
    $confusionMatrix = array_fill(0, 10, array_fill(0, 10, 0));
    $startTime = microtime(true);

    // Parcours des dossiers de test
    foreach (new \DirectoryIterator($testingPath) as $categoryInfo) {
        if ($categoryInfo->isDot() || !$categoryInfo->isDir()) continue;

        $expectedDigit = (int)$categoryInfo->getFilename();
        $categoryPath = $categoryInfo->getPathname();

        foreach (new \DirectoryIterator($categoryPath) as $imageInfo) {
            if ($imageInfo->isDot() || !$imageInfo->isFile()) continue;

            $imagePath = $imageInfo->getPathname();
            $result = $classifier->predict($imagePath);
            $predictedDigit = $result['prediction'];

            // Mise à jour des métriques
            $totalImages++;
            if ($predictedDigit === $expectedDigit) {
                $correctPredictions++;
            }
            $confusionMatrix[$expectedDigit][$predictedDigit]++;

            // Affichage de la progression
            printf("\rTraitement des images : %d", $totalImages);
        }
    }

    $accuracy = ($correctPredictions / $totalImages) * 100;
    $duration = microtime(true) - $startTime;

    // Affichage des résultats
    echo "\n\n=== Résultats de l'évaluation ===\n";
    echo "Images testées : $totalImages\n";
    echo "Prédictions correctes : $correctPredictions\n";
    echo "Précision : " . round($accuracy, 2) . "%\n";
    echo "Durée : " . round($duration, 2) . " secondes\n\n";

    // Affichage de la matrice de confusion
    echo "Matrice de confusion :\n";
    echo str_repeat('-', 45) . "\n";
    echo "   | " . implode(' ', range(0, 9)) . "\n";
    echo str_repeat('-', 45) . "\n";

    for ($i = 0; $i < 10; $i++) {
        printf(" %d | ", $i);
        for ($j = 0; $j < 10; $j++) {
            printf("%2d ", $confusionMatrix[$i][$j]);
        }
        echo "\n";
    }
    echo str_repeat('-', 45) . "\n";
} catch (\Exception $e) {
    echo "\n❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
