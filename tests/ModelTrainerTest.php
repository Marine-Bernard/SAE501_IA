<?php

namespace Mjrmb\Sae501ia\tests;

use PHPUnit\Framework\TestCase;
use Rubix\ML\Estimator;

class ModelTrainerTest extends TestCase
{
    public function testConstruct(): void
    {
        $model = new ModelTrainerFake("mlp");
        $this->assertInstanceOf(Estimator::class, $model->estimator);
       
    }
}