<?php

namespace Mjrmb\Sae501ia\ML\Models;

use Rubix\ML\Datasets\Labeled;
use Rubix\ML\Persistable;

interface ModelInterface
{
  public function train(Labeled $dataset): void;
  public function predict(Labeled $dataset): array;
  public function getModel(): Persistable;
  public function setModel(Persistable $model): void;
}
