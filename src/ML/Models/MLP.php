<?php

namespace Mjrmb\Sae501ia\ML\Models;

use Rubix\ML\Classifiers\MultilayerPerceptron;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\NeuralNet\Layers\Dense;
use Rubix\ML\NeuralNet\Layers\Activation;
use Rubix\ML\NeuralNet\ActivationFunctions\ReLU;
use Rubix\ML\NeuralNet\Optimizers\Adam;
use Rubix\ML\Persistable;

class MLP implements ModelInterface
{
  private MultilayerPerceptron $model;

  public function __construct()
  {
    $this->model = new MultilayerPerceptron([
      new Dense(3),
      new Activation(new ReLU()),
      new Dense(3),
      new Activation(new ReLU()),
    ], 128, new Adam(0.001), 0, 1);
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
    if (!($model instanceof MultilayerPerceptron)) {
      throw new \RuntimeException("Le modèle doit être une instance de MultilayerPerceptron");
    }
    $this->model = $model;
  }
}
