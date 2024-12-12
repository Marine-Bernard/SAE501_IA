<?php

namespace Spark;


use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Estimator;
use Spark\Fabric\ModelFabric;

class ModelTester
{
    private Estimator $estimator;

    public function __construct(string $algorithm = 'tree')
    {
        $this->estimator = (new ModelFabric())->createModel($algorithm);
    }

    public function test(Labeled $testingDataset): array
    {
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