<?php

namespace Spark\Fabric;

use Spark\Exception\NoModelException;
use Spark\Model\MLP;
use Spark\Model\Tree;
use Rubix\ML\Estimator;

class ModelFabric
{
    public function createModel(string $model): Estimator
    {
        switch ($model) {
            case 'mlp':
                return new MLP();
            case 'tree':
                return new Tree();
            default:
                throw new NoModelException("Bad model name, please enter 'mlp' or 'tree'");
        }
    }
}