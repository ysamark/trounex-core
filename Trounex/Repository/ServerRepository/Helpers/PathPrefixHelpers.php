<?php

namespace Trounex\Repository\ServerRepository\Helpers;

trait PathPrefixHelpers {
  /**
   * @method void
   *
   * set server router path prefix
   */
  public static function SetPathPrefix (string $pathPrefix = '') {
    self::PathPrefix ($pathPrefix);
  }

  /**
   * set the router path  prefix
   */
  public static function PathPrefix ($pathPrefix = null) {
    if (is_string ($pathPrefix) && !empty ($pathPrefix)) {
      $pathPrefix = preg_replace ('/^\/+/', '',
        preg_replace ('/\/+$/', '', preg_replace ('/\/{2,}/', '/', $pathPrefix))
      );

      self::$pathPrefix ['pattern'] = '/^('.self::path2regex ($pathPrefix).')/i';
      self::$pathPrefix ['text'] = trim ($pathPrefix);
    }

    if (isset (self::$pathPrefix ['text'])) {
      return trim (self::$pathPrefix ['text']);
    }
  }
}
