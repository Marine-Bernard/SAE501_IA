<?php

namespace  Mjrmb\Sae501ia\Model;

use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Estimator;


class Tree extends Estimator {
    public function createModelTree(): ClassificationTree
    {
        return new ClassificationTree();
    }
}