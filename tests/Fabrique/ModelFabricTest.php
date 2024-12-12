<?php 

namespace Mjrmb\Sae501ia\tests\Fabrique;

use Mjrmb\Sae501ia\Exception\NoModelException;
use Mjrmb\Sae501ia\Fabric\ModelFabric;
use Mjrmb\Sae501ia\Model\MLP;
use Mjrmb\Sae501ia\Model\Tree;
use PHPUnit\Framework\TestCase;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\MultilayerPerceptron;

class ModelFabricTest extends TestCase{
    public function testCreateModelMLPWithFabric(): void
    {
        $fabric = new ModelFabric();
        $model = $fabric->createModel('mlp');
        $this->assertInstanceOf(MLP::class,$model);
    }

    public function testCreateModelTreeWithFabric(): void
    {
        $fabric = new ModelFabric();
        $model = $fabric->createModel('tree');
        $this->assertInstanceOf(Tree::class,$model);
    }

    public function testExceptionFabric(): void 
    {
        $this->expectException(NoModelException::class);
        $this->expectExceptionMessage("Bad model name, please enter 'mlp' or 'tree'");
        $fabric = new ModelFabric();
        $model = $fabric->createModel("noModel");

    }
}