<?php

namespace Trounex\Repository\ServerRepository\Helpers;

use Trounex\RouteData;

trait GetRouteViewPathAlternates {
  /**
   * @method array
   */
  protected static function getRouteViewPathAlternates () {
    $routeData = new RouteData;
    $routePath = $routeData->path;
    $viewsRootDir = self::GetViewsRootDir ();
    $viewsExtensions = self::GetViewsFileExtensions ();
    $routeViewPathAlternates = [];

    $routeViewPathAlternatesFilter = function ($routeViewPathAlternate) {
      return (boolean)($routeViewPathAlternate);
    };

    if (!is_string ($viewsRootDir)) {
      $viewsRootDir = '';
    }

    foreach ($viewsExtensions as $viewsExtension) {
      $routeViewPathAlternates = array_merge (
        $routeViewPathAlternates,
        [
          realpath ("$viewsRootDir/{$routePath}.$viewsExtension"),
          realpath ("$viewsRootDir/$routePath/index.$viewsExtension")
        ]
      );
    }

    return array_filter ($routeViewPathAlternates, $routeViewPathAlternatesFilter);
  }
}
