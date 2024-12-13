<?php

namespace Tests\Spark;

use PHPUnit\Framework\TestCase;
use Spark\ModelTrainer;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Datasets\Generators\Circle;

class ModelTrainerTest extends TestCase
{
    private string $tempModelFile;

    protected function setUp(): void
    {
        // Crée un fichier temporaire pour tester la sauvegarde du modèle
        $this->tempModelFile = sys_get_temp_dir() . '/test_model_trainer.model';
    }

    protected function tearDown(): void
    {
        // Supprime le fichier temporaire après les tests
        if (file_exists($this->tempModelFile)) {
            unlink($this->tempModelFile);
        }
    }

    public function testTrainAndSaveModel(): void
    {
        // Prépare un jeu de données factice
        $generator = new Circle();
        $dataset = $generator->generate(100);
        $labels = array_map(fn() => 'label', range(1, 100)); // Labels factices
        $labeledDataset = Labeled::quick($dataset->samples(), $labels);

        // Instancie le ModelTrainer
        $trainer = new ModelTrainer();

        // Entraîne le modèle
        $trainer->train($labeledDataset);

        // Vérifie que le modèle est bien initialisé
        $this->assertNotNull($trainer->getEstimator());

        // Sauvegarde le modèle dans un fichier
        $trainer->saveModel($this->tempModelFile);

        // Vérifie que le fichier a été créé
        $this->assertFileExists($this->tempModelFile);

        // Charge le modèle sauvegardé pour vérifier qu'il est récupérable
        $loadedModel = unserialize(file_get_contents($this->tempModelFile));
        $this->assertInstanceOf(get_class($trainer->getEstimator()), $loadedModel);
    }
}