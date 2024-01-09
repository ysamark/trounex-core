<?php

namespace Trounex;

class RouteData {
  /**
   * @var array
   *
   * class global props
   */
  private static $props = [];

  /**
   * @var array
   *
   * class context props
   */
  private $contextProps = [];

  /**
   * @var string
   */
  private static $globalPath;

  /**
   * @param string $routePath
   */
  public function __construct (string $globalPath = '') {
    if (!!empty (self::$globalPath)) {
      self::$globalPath = $globalPath;
    }
  }

  /**
   * object property setter
   */
  public function __set (string $property, $value) {
    $this->contextProps [$property] = $value;
  }

  /**
   * @method mixed
   *
   * get property from context scope
   */
  public function getProp (string $property) {
    if (isset ($this->contextProps [$property])) {
      return $this->contextProps [$property];
    }
  }

  /**
   * @method mixed
   *
   * get property from global scope
   */
  public function getGlobalProp (string $property) {
    if (isset (self::$props [$property])) {
      return self::$props [$property];
    }
  }

  /**
   * @method void
   *
   * set property in context scope
   */
  public function setProp (string $property, $value = null) {
    $this->contextProps [$property] = $value;
  }

  /**
   * @method void
   *
   * set property in global scope
   */
  public function setGlobalProp (string $property, $value = null) {
    self::$props [$property] = $value;
  }

  /**
   * @method void
   *
   * set properties in context scope
   */
  public function setProps (array $properties) {
    foreach ($properties as $property => $value) {
      $this->setProp($property, $value);
    }
  }

  /**
   * @method void
   *
   * set properties in global scope
   */
  public function setGlobalProps (array $properties) {
    foreach ($properties as $property => $value) {
      $this->setGlobalProp($property, $value);
    }
  }

  /**
   * object property getter
   */
  public function __get (string $property) {
    if (isset ($this->contextProps [$property])) {
      return $this->contextProps [$property];
    }

    if (isset (self::$props [$property])) {
      return self::$props [$property];
    }

    $alternatePropName = 'global' . ucfirst($property);

    if (isset (self::$$alternatePropName)) {
      return self::$$alternatePropName;
    }
  }

  /**
   * object property getter
   */
  public function __isset (string $property) {
    return (boolean)(
      isset ($this->contextProps [$property])
      || isset (self::$props [$property])
    );
  }
}
