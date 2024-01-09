<?php

namespace Trounex\Repository\ServerRepository\Helpers;

use Trounex\RouteData;

trait GetRouteViewPaths {
  /**
   * @method array
   */
  protected static function getRouteViewPaths () {
    $routeData = new RouteData;
    $viewsRootDir = self::GetViewsRootDir ();
    $viewsExtensions = self::GetViewsFileExtensions ();
    $routeViewPathRoot = self::realRoutePath ($viewsRootDir . $routeData->path);
    $routeViewPathBase = self::realRoutePath ($routeData->viewsPath . $routeData->path);

    $routeViewPaths = [];

    foreach ($viewsExtensions as $viewsExtension) {
      if (!self::isAPIRoutePath ($routeData->path)) {
        $routeViewPathBase = $routeViewPathRoot;
      }

      $routeViewPathExtensionMap = self::buildRouteViewPathExtensionMap($routeViewPathBase, $viewsExtension);

      $routeViewPaths = array_merge ($routeViewPaths, $routeViewPathExtensionMap);
    }

    return $routeViewPaths;
  }
}
