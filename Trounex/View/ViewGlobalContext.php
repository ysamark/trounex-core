<?php

namespace Trounex\View;

use App\Controllers\BaseController;

class ViewGlobalContext {
  /**
   * @var object
   *
   * the context source data object
   */
  private $source;

  public function __construct ($source) {
    if (is_object ($source)) {
      $this->source = $source;
    }
  }

  public function __get (string $property) {
    if (isset ($this->$property)) {
      return $this->source->$property;
    }
  }

  public function __isset (string $property) {
    return ($this->source && isset ($this->source->$property));
  }

  public function getAllProps () {
    if (!$this->source) {
      return [];
    }

    $contextSourceProps = get_object_vars ($this->source);

    if (BaseController::isControllerInstance ($this->source)) {
      $contextSourceProps = $this->source->getProps ();
    }

    return $contextSourceProps;
  }
}
