<?php

namespace Mjrmb\Sae501ia\Fabrique;

use Mjrmb\Sae501ia\Exception\NoModelException;
use Mjrmb\Sae501ia\Model\MLP;
use Mjrmb\Sae501ia\Model\Tree;
use Rubix\ML\Estimator;

class ModelFabric {
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