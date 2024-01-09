<?php

namespace Trounex\Application;

use Closure;
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

  public static function ReadConfigValue ($configValue, string $configPathPrefix = '') {
    if (is_string ($configValue)) {
      $configVariableInterpolationRe = '/\\${([a-zA-Z0-9_\.\s\t\n]+)}/';

      $newConfigValueCallbackContext = new static;
      $newConfigValueCallback = Closure::bind (function ($match) {
        $configVariableReference = trim ($match [1]);

        $configVariableReferencePath = self::configPropertyPath ($this->pathPrefix, $configVariableReference);

        $configVariableValue = conf ($configVariableReferencePath);

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
      }, $newConfigValueCallbackContext, static::class);

      $newConfigValueCallbackContext->pathPrefix = $configPathPrefix;

      $newConfigValue = preg_replace_callback ($configVariableInterpolationRe, $newConfigValueCallback, $configValue);

      return $newConfigValue;
    }

    if (is_array ($configValue)) {
      /**
       * Map each property to read it as a single reference
       */
      foreach ($configValue as $property => $value) {
        $configPropertyPathPrefix = join ('', [
          $configPathPrefix,
          empty ($configPathPrefix) ? '' : '.',
          $property
        ]);

        $configValue [$property] = self::ReadConfigValue ($value, $configPropertyPathPrefix);
      }
    }

    return $configValue;
  }

  protected static function configPropertyPath (string $configPropertyPathPrefix, string $configPropertyPath) {
    // $configPropertyPathDots

    if (preg_match ('/^\.+/', $configPropertyPath, $match)) {
      list ($configPropertyPathDots) = $match;

      $splitStringChars = function (string $string) {
        $stringLen = strlen ($string);

        $strArray = [];

        for ($i = 0; $i < $stringLen; $i++) {
          array_push ($strArray, $string [$i]);
        }

        return $strArray;
      };

      $configPropertyPathPrefix = preg_split ('/\.+/', $configPropertyPathPrefix);
        // print_r ($configPropertyPathPrefix);
      $configPropertyPathDotsCount = count (call_user_func ($splitStringChars, $configPropertyPathDots));
      // print ($configPropertyPathDotsCount);
      for ($i = 0; $i < $configPropertyPathDotsCount; $i++) {
        $configPropertyPathPrefix = array_slice ($configPropertyPathPrefix, 0, -1 + count ($configPropertyPathPrefix));
      }

      $configPropertyPath = preg_replace ('/^\.+/', '', $configPropertyPath);

      return join ('.', array_merge ($configPropertyPathPrefix, [ $configPropertyPath ]));
    }

    return $configPropertyPath;
  }
}
