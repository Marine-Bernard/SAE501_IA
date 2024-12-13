<?php

namespace Tests\Spark\Service;

use PHPUnit\Framework\TestCase;
use Spark\DatasetLoader;
use Spark\Model\MLP;
use Spark\ModelTrainer;
use Spark\Service\vectorizedService;

class vectorizedServicetest extends TestCase
{
    public function testVectorized(): void
    {
        $loader = new DatasetLoader();
        $testingDataSet = $loader->loadDataset(__DIR__ . '/../../image/training');
        $vectorizer = new vectorizedService();
        $vectorizer->vectorizedImage($testingDataSet);
    }
}