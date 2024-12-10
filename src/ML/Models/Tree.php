<?php

namespace Mjrmb\Sae501ia\ML\Models;

use Rubix\ML\Classifiers\ClassificationTree;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Persistable;

class Tree implements ModelInterface
{
  private ClassificationTree $model;

  public function __construct()
  {
    $this->model = new ClassificationTree(15, 5, 0.001, null, null);
  }

  public function train(Labeled $dataset): void
  {
    $this->model->train($dataset);
  }

  public function predict(Labeled $dataset): array
  {
    return $this->model->predict($dataset);
  }

  public function getModel(): Persistable
  {
    return $this->model;
  }

  public function setModel(Persistable $model): void
  {
    if (!($model instanceof ClassificationTree)) {
      throw new \RuntimeException("Le modèle doit être une instance de ClassificationTree");
    }
    $this->model = $model;
  }
}
