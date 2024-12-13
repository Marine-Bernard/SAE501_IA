<?php 

namespace Spark\tests ;

use Exception;
use PHPUnit\Framework\TestCase;
use Spark\DatasetLoader;
use Rubix\ML\Datasets\Labeled;

class DatasetLoaderTest extends TestCase
{
    private $tempDir;

    protected function setUp(): void
    {
        // Crée un répertoire temporaire pour le dataset
        $this->tempDir = sys_get_temp_dir() . '/test_dataset';
        mkdir($this->tempDir, 0777, true);

        // Crée des sous-dossiers et des fichiers PNG factices
        foreach (range(0, 1) as $label) {
            $labelDir = $this->tempDir . '/' . $label;
            mkdir($labelDir, 0777, true);

            for ($i = 0; $i < 2; $i++) {
                $image = imagecreatetruecolor(10, 10); // Crée une image 10x10
                imagefilledrectangle($image, 0, 0, 10, 10, imagecolorallocate($image, $label * 10, $label * 10, $label * 10));
                imagepng($image, $labelDir . "/image_$i.png");
                imagedestroy($image);
            }
        }
    }

    protected function tearDown(): void
    {
        // Supprime le répertoire temporaire
        foreach (glob($this->tempDir . '/*') as $file) {
            if (is_dir($file)) {
                array_map('unlink', glob("$file/*.*"));
                rmdir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($this->tempDir);
    }

    public function testLoadDatasetReturnsLabeledDataset()
    {
        $loader = new DatasetLoader();
        $dataset = $loader->loadDataset($this->tempDir);

        $this->assertInstanceOf(Labeled::class, $dataset);
    }

    public function testLoadDatasetSamplesAndLabels()
    {
        $loader = new DatasetLoader();
        $dataset = $loader->loadDataset($this->tempDir);

        // Vérifie le nombre de samples et de labels
        $this->assertCount(4, $dataset->samples(), 'Le nombre de samples doit être 4');
        $this->assertCount(4, $dataset->labels(), 'Le nombre de labels doit être 4');

        // Vérifie les labels
        $this->assertEquals(['digit_0', 'digit_0', 'digit_1', 'digit_1'], $dataset->labels(), 'Les labels doivent correspondre');
    }
}
