<?php

namespace Tests\Spark;

use PHPUnit\Framework\TestCase;
use Spark\Model\MLP;
use Spark\ModelTrainer;

class ModelTrainerTest extends TestCase
{
    public function testConstruct(): void
    {
        $model = new ModelTrainer("mlp");
        $this->assertInstanceOf(MLP::class, $model->getEstimator());
        //$this->assertInstanceOf(MultilayerPerceptron::class, $model->getModel());
    }
}