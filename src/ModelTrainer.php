<?php

namespace  Mjrmb\Sae501ia;

use Mjrmb\Sae501ia\Fabrique\ModelFabric;
use Mjrmb\Sae501ia\interface\InterfaceModeltrainer;
use Rubix\ML\Transformers\ImageVectorizer;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Estimator;

class ModelTrainer implements InterfaceModeltrainer
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