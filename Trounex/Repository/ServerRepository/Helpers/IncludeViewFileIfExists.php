<?php

namespace Trounex\Repository\ServerRepository\Helpers;

trait IncludeViewFileIfExists {
  /**
   * include view file if it exists
   * in route view path alternates list
   *
   * @param array $routeViewPathAlternates
   *
   * @return void
   */
  protected static function includeViewFileIfExists () {
    $routeViewPathAlternates = self::getRouteViewPathAlternates();

    if (!(is_array ($routeViewPathAlternates) && count ($routeViewPathAlternates) >= 1)) {
      return;
    }

    foreach ($routeViewPathAlternates as $routeViewPath) {
      if ($routeViewPath && is_file ($routeViewPath)) {
        self::setViewPath (realpath ($routeViewPath));

        self::beforeRender ();

        call_user_func_array (self::lambda (self::$include), [
          [
            'layoutPath' => self::mainLayoutView (),
            'viewPath' => self::GetViewPath ()
          ]
        ]);

        exit (0);
      }
    }
  }
}
