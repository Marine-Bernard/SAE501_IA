<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\DatasetLoader;
use Mjrmb\Sae501ia\ModelTester;
use Mjrmb\Sae501ia\Service\vectorizedService;

$algorithm = $argv[1] ?? 'tree';

$loader = new DatasetLoader();
$tester = new ModelTester($algorithm);
$vector = new vectorizedService();

echo "Loading testing dataset...\n";
$testingDataset = $loader->loadDataset(__DIR__ . '/../../image/testing');
$vector->vectorizedImage($testingDataset);
echo "Testing dataset loaded.\n";

// Load the trained model
$tester->loadModel(__DIR__ . '/../../models/model_' . $algorithm . '.rbx');

echo "Testing the model...\n";
$results = $tester->test($testingDataset);
$accuracy = $results['accuracy'];
$confusionMatrix = $results['confusion_matrix'];

echo 'Accuracy: ' . ($accuracy * 100) . "%\n";
echo "Confusion Matrix:\n";
foreach ($confusionMatrix as $actual => $predictions) {
    echo $actual . ': ' . implode(', ', $predictions) . "\n";
}