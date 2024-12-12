<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Mjrmb\Sae501ia\DatasetLoader;
use Mjrmb\Sae501ia\ModelTrainer;
use Mjrmb\Sae501ia\Service\vectorizedService;

$algorithm = $argv[1] ?? 'tree';

$loader = new DatasetLoader();
$trainer = new ModelTrainer($algorithm);
$vector = new vectorizedService();

echo "Loading training dataset...\n";
$trainingDataset = $loader->loadDataset(__DIR__ . '/../../image/training');
$vector->vectorizedImage($trainingDataset);
echo "Training dataset loaded.\n";

echo "Training the model...\n";
$trainer->train($trainingDataset);
echo "Model trained.\n";

// Create models directory if it doesn't exist
$modelsDir = __DIR__ . '/../../models';
if (!is_dir($modelsDir)) {
    mkdir($modelsDir, 0777, true);
}

// Save the trained model
$trainer->saveModel($modelsDir . '/model_' . $algorithm . '.rbx');