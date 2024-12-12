<?php 

namespace Spark\tests\Fabric;

use PHPUnit\Framework\TestCase;
use Spark\Exception\NoModelException;
use Spark\Fabric\ModelFabric;
use Spark\Model\MLP;
use Spark\Model\Tree;

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