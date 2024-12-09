<?php

namespace Mjrmb\Sae501ia\ML;

use RuntimeException;
use DirectoryIterator;

class ImageProcessor
{
    private const TARGET_SIZE = 64;
    private const VALID_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function loadImages(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new RuntimeException("Le répertoire $directory n'existe pas");
        }

        $samples = [];
        $labels = [];
        $errors = [];

        // Compte d'abord le nombre total d'images
        $totalImages = 0;
        foreach (new DirectoryIterator($directory) as $categoryInfo) {
            if ($categoryInfo->isDot() || !$categoryInfo->isDir()) continue;
            foreach (new DirectoryIterator($categoryInfo->getPathname()) as $imageInfo) {
                if ($imageInfo->isDot() || !$imageInfo->isFile()) continue;
                if (in_array(strtolower($imageInfo->getExtension()), self::VALID_EXTENSIONS)) {
                    $totalImages++;
                }
            }
        }

        // Initialisation des variables de progression
        $processedImages = 0;
        $startTime = microtime(true);
        $lastUpdateTime = microtime(true);
        
        echo "Chargement des images : [" . str_repeat("░", 50) . "] 0% - Temps restant estimé : Calcul en cours...\r";

        foreach (new DirectoryIterator($directory) as $categoryInfo) {
            if ($categoryInfo->isDot() || !$categoryInfo->isDir()) {
                continue;
            }

            $category = $categoryInfo->getFilename();
            $categoryPath = $categoryInfo->getPathname();

            foreach (new DirectoryIterator($categoryPath) as $imageInfo) {
                if ($imageInfo->isDot() || !$imageInfo->isFile()) {
                    continue;
                }

                if (in_array(strtolower($imageInfo->getExtension()), self::VALID_EXTENSIONS)) {
                    try {
                        $imageData = $this->processImage($imageInfo->getPathname());
                        if ($imageData !== null) {
                            $samples[] = $imageData;
                            $labels[] = $category;
                        }
                        
                        // Mise à jour de la progression
                        $processedImages++;
                        $currentTime = microtime(true);
                        
                        // Mise à jour toutes les 0.5 secondes
                        if (($currentTime - $lastUpdateTime) >= 0.5) {
                            $progress = round(($processedImages / $totalImages) * 100);
                            
                            // Calcul du temps écoulé et estimation du temps restant
                            $elapsedTime = $currentTime - $startTime;
                            $imagesPerSecond = $processedImages / $elapsedTime;
                            $remainingImages = $totalImages - $processedImages;
                            $estimatedRemainingSeconds = $remainingImages / $imagesPerSecond;
                            
                            // Formatage des temps
                            $remainingTime = $this->formatTime($estimatedRemainingSeconds);
                            $elapsedTimeFormatted = $this->formatTime($elapsedTime);
                            
                            // Création de la barre de progression
                            $bar = str_repeat("█", $progress / 2) . str_repeat("░", 50 - ($progress / 2));
                            
                            echo sprintf(
                                "Chargement des images : [%s] %d%% (%d/%d) - %.2f img/s - Écoulé: %s - Restant: %s\r",
                                $bar,
                                $progress,
                                $processedImages,
                                $totalImages,
                                $imagesPerSecond,
                                $elapsedTimeFormatted,
                                $remainingTime
                            );
                            
                            $lastUpdateTime = $currentTime;
                        }
                        
                    } catch (\Exception $e) {
                        $errors[] = "Erreur avec {$category}/{$imageInfo->getFilename()}: {$e->getMessage()}";
                    }
                }
            }
        }
        echo "\n"; // Nouvelle ligne après la barre de progression

        if (empty($samples)) {
            throw new RuntimeException("Aucune image n'a pu être chargée" .
                (!empty($errors) ? "\nErreurs:\n" . implode("\n", $errors) : ""));
        }

        return [$samples, $labels];
    }

    public function loadSingleImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new RuntimeException("L'image $imagePath n'existe pas");
        }

        $imageData = $this->processImage($imagePath);
        if ($imageData === null) {
            throw new RuntimeException("Impossible de traiter l'image");
        }

        return [$imageData];
    }

    private function processImage(string $path): ?array
    {
        $image = imagecreatefromstring(file_get_contents($path));
        if (!$image) {
            return null;
        }

        // Normalisation de la taille
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

        // Extraction des caractéristiques avec normalisation
        $features = [];
        $totalR = $totalG = $totalB = 0;
        $pixelCount = self::TARGET_SIZE * self::TARGET_SIZE;

        // Première passe : calcul des moyennes
        for ($y = 0; $y < self::TARGET_SIZE; $y++) {
            for ($x = 0; $x < self::TARGET_SIZE; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $totalR += ($rgb >> 16) & 0xFF;
                $totalG += ($rgb >> 8) & 0xFF;
                $totalB += $rgb & 0xFF;
            }
        }

        $meanR = $totalR / $pixelCount;
        $meanG = $totalG / $pixelCount;
        $meanB = $totalB / $pixelCount;

        // Deuxième passe : normalisation centrée-réduite
        for ($y = 0; $y < self::TARGET_SIZE; $y++) {
            for ($x = 0; $x < self::TARGET_SIZE; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $features[] = ((($rgb >> 16) & 0xFF) - $meanR) / 255.0;
                $features[] = ((($rgb >> 8) & 0xFF) - $meanG) / 255.0;
                $features[] = (($rgb & 0xFF) - $meanB) / 255.0;
            }
        }

        imagedestroy($image);
        imagedestroy($resized);

        return $features;
    }

    /**
     * Formate un temps en secondes en une chaîne lisible
     */
    private function formatTime(float $seconds): string
    {
        if ($seconds < 60) {
            return round($seconds) . "s";
        }
        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $seconds = round($seconds % 60);
            return "{$minutes}m {$seconds}s";
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = round($seconds % 60);
        return "{$hours}h {$minutes}m {$seconds}s";
    }
}