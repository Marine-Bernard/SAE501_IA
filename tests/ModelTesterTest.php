<?php

namespace Tests\Spark;

use PHPUnit\Framework\TestCase;
use Spark\ModelTester;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Generators\Circle;
use Rubix\ML\Report;
use Spark\ModelTrainer;

class ModelTesterTest extends TestCase
{
    private string $tempModelFile;

    protected function setUp(): void
    {
        // Crée un fichier temporaire pour sauvegarder le modèle
        $this->tempModelFile = sys_get_temp_dir() . '/test_model_tester.model';

        // Entraîne et sauvegarde un modèle factice pour le test
        $generator = new Circle();
        $dataset = $generator->generate(100);
        $labels = array_map(fn($i) => $i % 2 === 0 ? 'A' : 'B', range(1, 100)); // Labels factices
        $labeledDataset = Labeled::quick($dataset->samples(), $labels);

        $trainer = new ModelTrainer();
        $trainer->train($labeledDataset);
        $trainer->saveModel($this->tempModelFile);
    }

    protected function tearDown(): void
    {
        // Supprime le fichier temporaire après les tests
        if (file_exists($this->tempModelFile)) {
            unlink($this->tempModelFile);
        }
    }

    public function testModelTester(): void
    {
        // Instancie le ModelTester et charge le modèle sauvegardé
        $tester = new ModelTester();
        $tester->loadModel($this->tempModelFile);

        // Prépare un jeu de données de test factice
        $generator = new Circle();
        $testDataset = $generator->generate(20);
        $testLabels = array_map(fn($i) => $i % 2 === 0 ? 'A' : 'B', range(1, 20));
        $labeledTestDataset = Labeled::quick($testDataset->samples(), $testLabels);

        // Teste le modèle
        $results = $tester->test($labeledTestDataset);

        // Vérifie que les résultats contiennent "accuracy" et "confusion_matrix"
        $this->assertArrayHasKey('accuracy', $results);
        $this->assertArrayHasKey('confusion_matrix', $results);

        // Vérifie que l'accuracy est un float valide
        $this->assertIsFloat($results['accuracy']);
        $this->assertGreaterThanOrEqual(0.0, $results['accuracy']);
        $this->assertLessThanOrEqual(1.0, $results['accuracy']);

        // Vérifie que la confusion_matrix est un tableau
        $this->assertInstanceOf(Report::class,$results['confusion_matrix']);
    }
}