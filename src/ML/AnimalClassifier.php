<?php

namespace Mjrmb\Sae501ia\ML;

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\CrossValidation\Metrics\Accuracy;
use Rubix\ML\Datasets\Dataset;

class AnimalClassifier
{
    private KNearestNeighbors $classifier;
    private ImageProcessor $imageProcessor;

    public function __construct()
    {
        $this->classifier = new KNearestNeighbors(3);  // k=3 voisins
        $this->imageProcessor = new ImageProcessor();
    }

    public function train(string $datasetPath): array
    {
        ini_set('memory_limit', '10G');
        $startTime = microtime(true);
    
        echo "Étape 1/3 : Chargement des images...\n";
        [$samples, $labels] = $this->imageProcessor->loadImages($datasetPath);
    
        // Afficher la distribution des classes
        $classDistribution = array_count_values($labels);
        echo "\nDistribution des classes :\n";
        foreach ($classDistribution as $class => $count) {
            echo "- $class : $count images\n";
        }
    
        $dataset = new Labeled($samples, $labels);
        $totalImages = count($samples);
    
        echo "\nÉtape 2/3 : Stockage du modèle...\n";
        $this->classifier->train($dataset);
        echo "Stockage terminé !\n";
    
        echo "\nÉtape 3/3 : Évaluation du modèle...\n";
        echo "Total images à évaluer : $totalImages\n\n";
    
        // Initialisation des variables de progression
        $processedImages = 0;
        $startEvalTime = microtime(true);
        $lastUpdateTime = microtime(true);
        
        // Prédiction par lots pour montrer la progression
        $batchSize = 10; // Nombre d'images à traiter par lot
        $predictions = [];
        
        for ($i = 0; $i < $totalImages; $i += $batchSize) {
            // Prépare le lot d'images
            $batchEnd = min($i + $batchSize, $totalImages);
            $batchSamples = array_slice($samples, $i, $batchSize);
            $batchLabels = array_fill(0, count($batchSamples), 'temp');
            $batchDataset = new Labeled($batchSamples, $batchLabels);
            
            // Fait les prédictions pour ce lot
            $batchPredictions = $this->classifier->predict($batchDataset);
            $predictions = array_merge($predictions, $batchPredictions);
            
            // Met à jour la progression
            $processedImages = $batchEnd;
            $currentTime = microtime(true);
            
            // Mise à jour toutes les 0.5 secondes
            if (($currentTime - $lastUpdateTime) >= 0.5) {
                $progress = round(($processedImages / $totalImages) * 100);
                $elapsedTime = $currentTime - $startEvalTime;
                $imagesPerSecond = $processedImages / $elapsedTime;
                $remainingImages = $totalImages - $processedImages;
                $estimatedRemainingSeconds = $remainingImages / $imagesPerSecond;
                
                // Création de la barre de progression
                $bar = str_repeat("█", $progress / 2) . str_repeat("░", 50 - ($progress / 2));
                
                echo sprintf(
                    "Évaluation : [%s] %d%% (%d/%d images) - %.2f img/s - Écoulé: %s - Restant: %s\r",
                    $bar,
                    $progress,
                    $processedImages,
                    $totalImages,
                    $imagesPerSecond,
                    $this->formatTime($elapsedTime),
                    $this->formatTime($estimatedRemainingSeconds)
                );
                
                $lastUpdateTime = $currentTime;
            }
        }
        echo "\n\n"; // Nouvelles lignes après la barre de progression
    
        // Calcul de la précision
        $accuracy = (new Accuracy())->score($predictions, $dataset->labels());
    
        echo "\nRésultats de l'évaluation :\n";
        echo "Précision globale : " . round($accuracy * 100, 2) . "%\n";
    
        // Analyse détaillée par classe
        $confusionMatrix = [];
        foreach ($classDistribution as $class => $count) {
            $confusionMatrix[$class] = ['correct' => 0, 'total' => 0];
        }
    
        foreach ($predictions as $i => $prediction) {
            $actual = $dataset->labels()[$i];
            $confusionMatrix[$actual]['total']++;
            if ($prediction === $actual) {
                $confusionMatrix[$actual]['correct']++;
            }
        }
    
        echo "\nPrécision par classe :\n";
        foreach ($confusionMatrix as $class => $stats) {
            $classAccuracy = ($stats['correct'] / $stats['total']) * 100;
            echo "- $class : " . round($classAccuracy, 2) . "% (" . 
                 $stats['correct'] . "/" . $stats['total'] . ")\n";
        }
    
        return [
            'training_time' => round(microtime(true) - $startTime, 2),
            'accuracy' => round($accuracy * 100, 2),
            'samples_count' => count($samples),
            'classes' => array_unique($labels),
            'class_distribution' => $classDistribution,
            'confusion_matrix' => $confusionMatrix
        ];
    }

    public function predict(string $imagePath): array
    {
        $imageData = $this->imageProcessor->loadSingleImage($imagePath)[0] ?? null;

        if ($imageData === null) {
            throw new \RuntimeException("Impossible de traiter l'image");
        }

        $dataset = new Labeled([$imageData], ['temp']);
        $prediction = $this->classifier->predict($dataset)[0];
        
        // KNN peut fournir des probabilités
        $probabilities = [];
        try {
            $probabilities = $this->classifier->proba($dataset);
            $probabilities = $probabilities[0];
        } catch (\Exception $e) {
            // Si les probabilités ne sont pas disponibles, on continue sans elles
        }

        return [
            'prediction' => $prediction,
            'probabilities' => $probabilities
        ];
    }

    public function getModel(): KNearestNeighbors
    {
        return $this->classifier;
    }

    public function setModel(KNearestNeighbors $model): void
    {
        $this->classifier = $model;
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