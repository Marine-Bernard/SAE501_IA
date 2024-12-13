<?php

namespace Spark\Service;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Transformers\ImageVectorizer;

class vectorizedService
{
    public function vectorizedImage(Labeled $trainingDataset): void
    {
        $vector = new ImageVectorizer();
        $trainingDataset->apply($vector);
    }

}