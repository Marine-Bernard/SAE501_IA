<?php

namespace Mjrmb\Sae501ia;

use Mjrmb\Sae501ia\Fabric\ModelFabric;
use Mjrmb\Sae501ia\interface\InterfaceModelTrainer;
use Rubix\ML\Transformers\ImageVectorizer;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Estimator;

class ModelTester implements InterfaceModelTrainer
{
    private Estimator $estimator;

    public function __construct(string $algorithm = 'tree')
    {
        $this->estimator = (new ModelFabric())->createModel($algorithm);
    }

    public function test(Labeled $testingDataset): array
    {
        $vectorizer = new ImageVectorizer();
        $testingDataset->apply($vectorizer);
        $predictions = $this->estimator->predict($testingDataset);

        $accuracy = new Accuracy();
        $confusionMatrix = new ConfusionMatrix();

        return [
            'accuracy' => $accuracy->score($predictions, $testingDataset->labels()),
            'confusion_matrix' => $confusionMatrix->generate($predictions, $testingDataset->labels()),
        ];
    }

    public function loadModel(string $filePath): void
    {
        $this->estimator = unserialize(file_get_contents($filePath));
    }
}