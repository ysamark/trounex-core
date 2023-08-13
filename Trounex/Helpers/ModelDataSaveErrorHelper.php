<?php

namespace Trounex\Helpers;

class ModelDataSaveErrorHelper {
  /**
   * @var array
   *
   * a list of error create objects
   */
  private static $errorProps = [];


  /**
   * @var array
   *
   * error object props
   */
  private $props = [];

  /**
   * @method void
   */
  public function __construct (array $props = null) {
    if (is_array ($props)) {
      array_push (self::$errorProps, $props);
    }

    $currentErrorPropsIndex = -1 + count (self::$errorProps);

    if ($currentErrorPropsIndex >= 0) {
      $errorProps = self::$errorProps [$currentErrorPropsIndex];

      array_slice (self::$errorProps, $currentErrorPropsIndex, 1);

      $this->props = array_merge ($this->props, $errorProps);
    }
  }

  /**
   * @method mixed
   *
   * error object props getter
   */
  public function __get (string $prop) {
    return isset ($this->props [$prop]) ? $this->props [$prop] : null;
  }

  /**
   * @method boolean
   *
   * error object property verify
   */
  public function __isset (string $prop) {
    return isset ($this->props [$prop]);
  }

  /**
   * @method string
   *
   * error object to string
   */
  public function __toString () {
    $bodyProps = [
      'message',
      'body',
      'content'
    ];

    foreach ($bodyProps as $bodyProp) {
      if (isset ($this->$bodyProp) && is_string ($this->$bodyProp)) {
        return $this->$bodyProp;
      }
    }
  }
}
