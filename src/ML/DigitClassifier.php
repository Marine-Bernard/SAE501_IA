<?php

namespace Mjrmb\Sae501ia\ML;

use Mjrmb\Sae501ia\ML\Models\Tree;
use Mjrmb\Sae501ia\ML\Models\MLP;
use Mjrmb\Sae501ia\ML\Models\ModelInterface;
use DirectoryIterator;
use Rubix\ML\Datasets\Labeled;
use RuntimeException;
use Rubix\ML\Classifiers\MultilayerPerceptron;

class DigitClassifier
{
    private ModelInterface $model;
    private string $type;

    public function __construct(string $type = 'tree')
    {
        $this->type = $type;
        $this->model = match ($type) {
            'tree' => new Tree(),
            'mlp' => new MLP(),
            default => throw new RuntimeException("Type de modèle invalide")
        };
    }

    public function train(string $datasetPath): void
    {
        echo "Chargement des images...\n";
        $samples = [];
        $labels = [];
        $count = 0;
        $uniqueLabels = [];

        foreach (new DirectoryIterator($datasetPath) as $categoryInfo) {
            if ($categoryInfo->isDot() || !$categoryInfo->isDir()) continue;

            $category = $categoryInfo->getFilename();
            echo "Traitement du dossier $category...\n";
            $categoryCount = 0;

            foreach (new DirectoryIterator($categoryInfo->getPathname()) as $imageInfo) {
                if ($imageInfo->isDot() || !$imageInfo->isFile()) continue;

                // Chargement de l'image avec GD
                $image = imagecreatefromstring(file_get_contents($imageInfo->getPathname()));
                if ($image === false) {
                    echo "Erreur lors du chargement de l'image : " . $imageInfo->getPathname() . "\n";
                    continue;
                }

                // Redimensionnement à 28x28 pixels (format MNIST)
                $resized = imagecreatetruecolor(28, 28);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, 28, 28, imagesx($image), imagesy($image));

                // Extraction des pixels
                $pixels = [];
                for ($y = 0; $y < 28; $y++) {
                    for ($x = 0; $x < 28; $x++) {
                        $pixels[] = imagecolorat($resized, $x, $y);
                    }
                }

                $label = "digit_" . $category;
                $samples[] = $pixels;
                $labels[] = $label;
                $uniqueLabels[$label] = true;
                $count++;
                $categoryCount++;

                // Libération de la mémoire
                imagedestroy($image);
                imagedestroy($resized);
            }

            echo "- $categoryCount images trouvées pour le chiffre $category\n";
        }

        echo "\nNombre total d'images : $count\n";
        echo "Labels uniques trouvés : " . implode(", ", array_keys($uniqueLabels)) . "\n";

        if (empty($samples)) {
            throw new RuntimeException("Aucune image n'a pu être chargée");
        }

        echo "Début de l'entraînement...\n";
        try {
            $dataset = new Labeled($samples, $labels);
            $this->model->train($dataset);
            echo "Entraînement terminé !\n";
        } catch (\Exception $e) {
            echo "Erreur pendant l'entraînement : " . $e->getMessage() . "\n";
            echo "Trace : " . $e->getTraceAsString() . "\n";
            throw $e;
        }
    }

    public function predict(string $imagePath): array
    {
        // Chargement et traitement de l'image
        $image = imagecreatefromstring(file_get_contents($imagePath));
        if ($image === false) {
            throw new RuntimeException("Impossible de charger l'image");
        }

        $resized = imagecreatetruecolor(28, 28);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, 28, 28, imagesx($image), imagesy($image));

        $pixels = [];
        for ($y = 0; $y < 28; $y++) {
            for ($x = 0; $x < 28; $x++) {
                $pixels[] = imagecolorat($resized, $x, $y);
            }
        }

        imagedestroy($image);
        imagedestroy($resized);

        // Prédiction
        $dataset = new Labeled([$pixels], ["temp"]);
        $prediction = $this->model->predict($dataset)[0];
        $probabilities = [];

        // Récupération des probabilités si disponibles
        $baseModel = $this->model->getModel();
        if ($baseModel instanceof MultilayerPerceptron) {
            try {
                $probabilities = $baseModel->proba($dataset)[0];
            } catch (\Exception $e) {
                // Ignore if probabilities are not available
            }
        }

        $digit = (int)str_replace('digit_', '', $prediction);

        return [
            'prediction' => $digit,
            'probabilities' => $probabilities
        ];
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function setModel($model): void
    {
        if (
            $this->type === 'tree' && !($model instanceof Tree) ||
            $this->type === 'mlp' && !($model instanceof MLP)
        ) {
            throw new RuntimeException("Type de modèle invalide");
        }
        $this->model = $model;
    }
}
