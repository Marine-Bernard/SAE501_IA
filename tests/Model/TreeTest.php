<?php 

namespace Tests\Spark\Model;

use Spark\Model\Tree;
use PHPUnit\Framework\TestCase;
use Rubix\ML\Classifiers\RandomForest;

class treeTest extends TestCase{
    public function testCreateModelTree(): void
    {
        $model = (new Tree())->createModelTree();
        $this->assertInstanceOf(RandomForest::class,$model);
    }
}