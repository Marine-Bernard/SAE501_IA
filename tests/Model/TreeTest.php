<?php 

namespace Mjrmb\Sae501ia\tests\Model;

use Mjrmb\Sae501ia\Model\Tree;
use PHPUnit\Framework\TestCase;
use Rubix\ML\Classifiers\ClassificationTree;

class treeTest extends TestCase{
    public function testCreateModelTree(): void
    {
        $model = (new Tree())->createModelTree();
        $this->assertInstanceOf(ClassificationTree::class,$model);
    }
}