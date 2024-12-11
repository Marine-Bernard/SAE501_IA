<?php

namespace Mjrmb\Sae501ia;

use Mjrmb\Sae501ia\Fabrique\ModelFabric;
use Mjrmb\Sae501ia\interface\InterfaceModeltrainer;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\Classifiers\RandomForest;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Transformers\ImageVectorizer;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Estimator;

class ModelTrainer
{
    private Estimator $estimator;

    public function __construct(string $algorithm = 'tree')
    {
       $this->estimator = (new ModelFabric())->createModel($algorithm); 
    }

    public function train(Labeled $trainingDataset): void
    {
       
        $this->estimator->train($trainingDataset);
    }
    public function saveModel(string $filePath): void
    {
        file_put_contents($filePath, serialize($this->estimator));
    }

}