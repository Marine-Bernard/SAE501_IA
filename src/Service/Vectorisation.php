<?php 

namespace Mjrmb\Sae501ia\Service;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Transformers\ImageVectorizer;

class Vectorisation{
    public function vectoriser(Labeled $trainingDataset){
        $vectorizer = new ImageVectorizer();
        $trainingDataset->apply($vectorizer);
    }
    
}