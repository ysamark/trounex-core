<?php

namespace Trounex\Repository\ServerRepository\Helpers;

use Trounex\RouteData;

trait MatchDynamicRoutesByRouteViewPaths {
  /**
   * @method array
   */
  protected static function matchDynamicRoutesByRouteViewPaths (array $dynamicRoutesPaths, array $routeViewPaths) {
    $routeData = new RouteData;

    # GetDynamicRoutesPaths
    $dynamicRoutes = [];

    foreach ($dynamicRoutesPaths as $route) {
      $routeRe = $route ['routeRe'];

      foreach ($routeViewPaths as $index => $routeViewPath) {

        if (@preg_match ($routeRe, $routeViewPath, $match)) {
          array_push($dynamicRoutes, $route);

          if (preg_match (self::$routeVerbSuffixRe, $route ['originalFilePath'], $routeVerbSuffixMatch)) {
            $routeVerb = strtolower ($routeVerbSuffixMatch[2]);

            $dynamicRoutes [$routeVerb] = [
              $index,
              $route,
              $route ['originalFilePath'], #$routeViewPath,
              $routeData->path,
              $match,
              self::$include
            ];
          }

          break;
        }
      }
    }

    return $dynamicRoutes;
  }
}
