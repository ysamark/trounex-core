<?php

namespace Trounex\Application\Http;

abstract class Controller {
  /**
   * controller property list
   * @var array
   */
  private static $props = [];

  /**
   * contructor
   */
  public function __construct () {
    $this->fillPropsWithObjectVars ();
  }

  /**
   * setter
   */
  function __set ($propertyName, $propertyValue) {
    self::$props [ strtolower ($propertyName) ] = [
      'name' => $propertyName,
      'value' => $propertyValue
    ];
  }

  /**
   * getter
   */
  function __get ($propertyName) {
    $propertyName = strtolower ($propertyName);

    $this->fillPropsWithObjectVars ();

    if (isset (self::$props [$propertyName])
      && is_array ($prop = self::$props [$propertyName])
      && isset ($prop ['value'])) {
      return $prop ['value'];
    }
  }

  /**
   * isset
   */
  function __isset ($propertyName) {
    $this->fillPropsWithObjectVars ();

    return (boolean)(
      isset (self::$props [$propertyName])
      && is_array ($prop = self::$props [$propertyName])
      && isset ($prop ['value'])
    );
  }

  function setProp () {
    return call_user_func_array ([$this, '__set'], func_get_args ());
  }

  function getProp () {
    return call_user_func_array ([$this, '__get'], func_get_args ());
  }

  function getProps () {
    $this->fillPropsWithObjectVars ();
    return self::$props;
  }

  /**
   * Fill class properties with the current
   * object vars/properties
   */
  protected function fillPropsWithObjectVars () {
    $objectVars = get_object_vars ($this);

    if (!$objectVars) {
      return;
    }

    foreach ($objectVars as $varName => $varValue) {
      $this->setProp ($varName, $varValue);
    }
  }

  /**
   *
   */
  public static function isControllerInstance ($object) {
    return is_object ($object) && (in_array (self::class, class_parents (get_class ($object))) || get_class ($object) === self::class);
  }
}
