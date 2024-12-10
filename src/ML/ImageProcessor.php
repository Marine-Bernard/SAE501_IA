<?php

namespace Mjrmb\Sae501ia\ML;

use RuntimeException;
use DirectoryIterator;

class ImageProcessor
{
    private const TARGET_SIZE = 28; // Taille standard pour les chiffres MNIST
    private const VALID_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function loadImages(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException("Le répertoire $directory n'existe pas");
        }

        $samples = [];
        $labels = [];
        $totalImages = $this->countImages($directory);
        $processedImages = 0;
        $startTime = microtime(true);

        echo "Nombre total d'images trouvées : $totalImages\n";

        foreach (new DirectoryIterator($directory) as $categoryInfo) {
            if ($categoryInfo->isDot() || !$categoryInfo->isDir()) continue;

            $category = $categoryInfo->getFilename();
            $categoryCount = 0;

            echo "\nChargement du dossier $category...\n";

            foreach (new DirectoryIterator($categoryInfo->getPathname()) as $imageInfo) {
                if ($imageInfo->isDot() || !$imageInfo->isFile()) continue;

                if (in_array(strtolower($imageInfo->getExtension()), self::VALID_EXTENSIONS)) {
                    try {
                        $imageData = $this->processImage($imageInfo->getPathname());
                        if ($imageData !== null) {
                            $samples[] = $imageData;
                            $labels[] = (int)$category;
                            $categoryCount++;
                            $processedImages++;
                            $this->updateProgress($processedImages, $totalImages, $startTime);
                        }
                    } catch (\Exception $e) {
                        error_log("Erreur avec {$category}/{$imageInfo->getFilename()}: {$e->getMessage()}");
                    }
                }
            }

            echo "\nDossier $category : $categoryCount images chargées\n";
        }

        if (empty($samples)) {
            throw new RuntimeException("Aucune image n'a pu être chargée");
        }

        echo "\nTotal des images chargées : " . count($samples) . "\n";

        return [$samples, $labels];
    }

    private function countImages(string $directory): int
    {
        $count = 0;
        foreach (new DirectoryIterator($directory) as $categoryInfo) {
            if ($categoryInfo->isDot() || !$categoryInfo->isDir()) continue;

            $categoryCount = 0;
            foreach (new DirectoryIterator($categoryInfo->getPathname()) as $imageInfo) {
                if ($imageInfo->isDot() || !$imageInfo->isFile()) continue;
                if (in_array(strtolower($imageInfo->getExtension()), self::VALID_EXTENSIONS)) {
                    $categoryCount++;
                    $count++;
                }
            }
            echo "Dossier {$categoryInfo->getFilename()} : $categoryCount images\n";
        }
        return $count;
    }

    private function processImage(string $path): ?array
    {
        $image = imagecreatefromstring(file_get_contents($path));
        if (!$image) return null;

        // Conversion en niveaux de gris et redimensionnement
        $resized = imagecreatetruecolor(self::TARGET_SIZE, self::TARGET_SIZE);
        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            self::TARGET_SIZE,
            self::TARGET_SIZE,
            imagesx($image),
            imagesy($image)
        );

        // Extraction des caractéristiques
        $features = [];
        for ($y = 0; $y < self::TARGET_SIZE; $y++) {
            for ($x = 0; $x < self::TARGET_SIZE; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                // Conversion en niveau de gris et normalisation
                $gray = ($r + $g + $b) / (3 * 255.0);
                $features[] = $gray;
            }
        }

        imagedestroy($image);
        imagedestroy($resized);

        return $features;
    }

    private function updateProgress(int $current, int $total, float $startTime): void
    {
        $progress = ($current / $total) * 100;
        $elapsed = microtime(true) - $startTime;
        $rate = $current / $elapsed;
        $eta = ($total - $current) / $rate;

        printf(
            "\rProgression : [%-50s] %d%% - %d/%d images - %.2f img/s - ETA: %s",
            str_repeat('█', $progress / 2) . str_repeat('░', 50 - ($progress / 2)),
            $progress,
            $current,
            $total,
            $rate,
            $this->formatTime($eta)
        );
    }

    private function formatTime(float $seconds): string
    {
        if ($seconds < 60) return round($seconds) . "s";
        if ($seconds < 3600) return sprintf("%dm %ds", floor($seconds / 60), $seconds % 60);
        return sprintf("%dh %dm %ds", floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);
    }


    public function loadSingleImage(string $path): array
    {
        if (!file_exists($path)) {
            throw new RuntimeException("L'image $path n'existe pas");
        }

        $imageData = $this->processImage($path);
        if ($imageData === null) {
            throw new RuntimeException("Impossible de traiter l'image $path");
        }

        return $imageData;
    }
}
