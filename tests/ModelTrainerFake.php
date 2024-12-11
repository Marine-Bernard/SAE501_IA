<?php

namespace Mjrmb\Sae501ia\tests;

use Mjrmb\Sae501ia\interface\InterfaceModeltrainer;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\CrossValidation\Reports\ConfusionMatrix;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Estimator;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Transformers\ImageVectorizer;

class ModelTrainerFake implements InterfaceModeltrainer
{

    public Estimator $estimator;
    
    public function __construct(string $algorithm = 'tree')
    {
        if ($algorithm === 'mlp') {
            $this->estimator = new MultilayerPerceptron([
                new Dense(200),
                new Activation(new ReLU()),
                new Dense(50),
                new Activation(new ReLU()),
            ], 128, new Adam(0.001), 0, 10);
        } else {
            $this->estimator = new ClassificationTree();
        }
    }

    public function train(Labeled $trainingDataset): void
    {
        $vectorizer = new ImageVectorizer();
        $trainingDataset->apply($vectorizer);
        $this->estimator->train($trainingDataset);
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

    public function saveModel(string $filePath): void
    {
        file_put_contents($filePath, serialize($this->estimator));
    }

    public function loadModel(string $filePath): void
    {
        $this->estimator = unserialize(file_get_contents($filePath));
    }
    
}