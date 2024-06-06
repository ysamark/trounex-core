<?php

namespace Trounex\View\ViewEngine;

use Trounex\Helper;
use Trounex\View\ViewGlobalContext;

abstract class ViewEngine {
  /**
   * @var array
   *
   * view engine adapter props
   */
  private $props;

  public function __construct (array $props = []) {
    $this->props = $props;

    if (!($this->context instanceof ViewGlobalContext)) {
      throw new \Exception("No context defined");
    }
  }

  public function __get (string $property) {
    if (isset ($this->$property)) {
      return $this->props [$property];
    }
  }

  public function __isset (string $property) {
    return ($this->props && isset ($this->props [$property]));
  }

  public function getProp (string $propertyPath) {
    return Helper::getArrayProp ($this->props, $propertyPath);
  }

  public function updateLayoutFilePath ($layoutFilePath) {
    if (is_file ($layoutFilePath)) {
      $this->props ['layoutFilePath'] = $layoutFilePath;
    }
  }

  public function getAllProps () {
    if (!!$this->props) {
      return $this->props;
    }
  }
}
