<?php 

namespace Mjrmb\Sae501ia\tests\Model;

use Mjrmb\Sae501ia\Model\MLP;
use Mjrmb\Sae501ia\Model\Tree;
use PHPUnit\Framework\TestCase;
use Rubix\ML\Classifiers\MultilayerPerceptron;

class MLPTest extends TestCase{
    public function testCreateModelMLP(): void
    {
        $model = (new MLP())->createModelMLP();
        $this->assertInstanceOf(MultilayerPerceptron::class,$model);
    }
}