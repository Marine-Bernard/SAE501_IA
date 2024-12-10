<?php

namespace Mjrmb\Sae501ia\ML;

use DirectoryIterator;
use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Datasets\Labeled;
use RuntimeException;

class DigitClassifier
{
    private $classifier;
    private $type;

    public function __construct(string $type = 'tree')
    {
        $this->type = $type;

        if ($type === 'tree') {
            $this->classifier = new ClassificationTree(15, 5, 0.001, null, null);
        } else {
            $this->classifier = new MultilayerPerceptron([
                new Dense(3),
                new Activation(new ReLU()),
                new Dense(3),
                new Activation(new ReLU()),
            ], 128, new Adam(0.001), 0, 1);
        }
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
            $this->classifier->train($dataset);
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
        $prediction = $this->classifier->predict(new Labeled([$pixels], ["temp"]))[0];
        $probabilities = [];

        if (method_exists($this->classifier, 'proba')) {
            try {
                $probabilities = $this->classifier->proba(new Labeled([$pixels], ["temp"]))[0];
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

    public function getClassifier()
    {
        return $this->classifier;
    }

    public function setModel($model): void
    {
        if (
            !($model instanceof ClassificationTree) &&
            !($model instanceof MultilayerPerceptron)
        ) {
            throw new \RuntimeException("Type de modèle invalide");
        }
        $this->classifier = $model;
    }
}
