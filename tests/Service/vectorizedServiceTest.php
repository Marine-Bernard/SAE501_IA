<?php

namespace Tests\Spark\Service;

use PHPUnit\Framework\TestCase;
use Spark\Service\vectorizedService;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Transformers\ImageVectorizer;

class vectorizedServiceTest extends TestCase
{
    public function testVectorizedImage(): void
    {
        // Crée un jeu de données factice (Labeled) avec des images simples représentées par des pixels
        $samples = [
            [255, 205, 0], // Exemple d'image sous forme de pixels (Rouge)
            [0, 255, 0],   // Vert
            [0, 0, 255]    // Bleu
        ];
        $labels = ['red', 'green', 'blue'];

        // Crée un objet Labeled avec les données d'image factices
        $dataset = Labeled::quick($samples, $labels);

        // Instancie le service vectorized
        $service = new vectorizedService();

        // Applique la transformation d'image
        $service->vectorizedImage($dataset);

        // Récupère les nouvelles données après transformation
        $transformedData = $dataset->samples();

        // Vérifie que les données ont bien été transformées (pas vides)
        $this->assertNotEmpty($transformedData);

        // Vérifie que les données sont sous forme d'un tableau
        $this->assertIsArray($transformedData[0]);

        // Vérifie que chaque vecteur est un tableau avec des dimensions
        $this->assertGreaterThan(0, count($transformedData[0]));

        // Optionnel : Vérifiez que les données ne sont plus les mêmes qu'au départ
        $this->assertEquals($samples, $transformedData);
    }
}
