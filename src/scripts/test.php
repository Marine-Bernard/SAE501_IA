<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\RubixPhp\DatasetLoader;
use Mjrmb\Sae501ia\RubixPhp\ModelTrainer;

$algorithm = $argv[1] ?? 'tree'; // Récupère l'argument de ligne de commande ou utilise 'tree' par défaut

$loader = new DatasetLoader();
$trainer = new ModelTrainer($algorithm);

echo "Loading testing dataset...\n";
$testingDataset = $loader->loadDataset(__DIR__ . '/../../image/testing');
echo "Testing dataset loaded.\n";

// Load the trained model
$trainer->loadModel(__DIR__ . '/../../models/model_' . $algorithm . '.rbx');

echo "Testing the model...\n";
$results = $trainer->test($testingDataset);
$accuracy = $results['accuracy'];
$confusionMatrix = $results['confusion_matrix'];

echo 'Accuracy: ' . ($accuracy * 100) . "%\n";
echo "Confusion Matrix:\n";
foreach ($confusionMatrix as $actual => $predictions) {
    echo $actual . ': ' . implode(', ', $predictions) . "\n";
}