<?php

namespace Spark\Model;

use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\RandomForest;

class Tree extends ClassificationTree
{
    public function createModelTree(): RandomForest
    {
        parent::__construct();
        return new RandomForest(new ClassificationTree());
    }
}