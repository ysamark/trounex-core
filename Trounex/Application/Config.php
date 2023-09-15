<?php

namespace Trounex\Application;

use App\Server;

class Config {
  /**
   * @var array
   */
  private $props = [];

  /**
   * @method void
   *
   * constructor
   */
  public function __construct ($props = []) {
    if (!(is_object ($props) || is_array ($props))) {
      $props = Server::GetConfigs ();
    }

    $this->props = $props;
  }

  public function __get (string $prop) {
    if (is_object ($this->props) && isset ($this->props->$prop)) {
      return ($this->props->$prop);
    }

    return isset ($this->props [$prop]) ? ($this->props [$prop]) : null;
  }

  public function __set (string $prop, $value = null) {
    if (is_object ($this->props)) {
      $this->props->$prop = $value;

      return $this->props->$prop;
    }

    $this->props [$prop] = $value;

    return $this->props [$prop];
  }

  public function __isset (string $prop) {
    if (is_object ($this->props)) {
      return isset ($this->props->$prop);
    }

    return isset ($this->props [$prop]);
  }

  public static function ReadConfigValue ($configValue) {
    if (is_string ($configValue)) {
      $configVariableInterpolationRe = '/\\${([a-zA-Z0-9_\.\s\t\n]+)}/';

      $newConfigValue = preg_replace_callback ($configVariableInterpolationRe, function ($match) {
        $configVariableReference = trim ($match [1]);

        $configVariableValue = conf ($configVariableReference);

        if (is_string ($configVariableValue)) {
          return $configVariableValue;
        }

        if (is_array ($configVariableValue)) {
          return '[array Array]';
        }

        if (is_object ($configVariableValue)) {
          $objectClassName = get_class ($configVariableValue);
          return '[object '.$objectClassName.']';
        }
      }, $configValue);

      return $newConfigValue;
    }

    if (is_array ($configValue)) {
      /**
       * Map each property to read it as a single reference
       */
      foreach ($configValue as $property => $value) {
        $configValue [$property] = self::ReadConfigValue ($value);
      }
    }

    return $configValue;
  }
}
