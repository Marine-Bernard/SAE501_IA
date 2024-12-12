<?php 

namespace Tests\SparkModel;

use Spark\Model\MLP;
use PHPUnit\Framework\TestCase;
use Rubix\ML\Classifiers\MultilayerPerceptron;

class MLPTest extends TestCase{
    public function testCreateModelMLP(): void
    {
        $model = (new MLP())->createModelMLP();
        $this->assertInstanceOf(MultilayerPerceptron::class,$model);
    }
}