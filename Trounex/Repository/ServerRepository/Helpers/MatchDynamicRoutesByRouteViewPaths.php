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

          if (((int)($index / 2) === $index) && !self::validateSameFileRef ($route ['originalFilePath'], $routeViewPath)) {
            continue;
          }

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

          return $dynamicRoutes;
        }
      }
    }

    return [];
  }

  protected static function validateSameFileRef (string $filePath) {
    $fileName = pathinfo ($filePath, PATHINFO_FILENAME);

    $args = array_slice (func_get_args (), 1, func_num_args ());

    for ($i = 0; $i < count ($args); $i++) {
      if (!(is_string ($args [$i]) && pathinfo ($args [$i], PATHINFO_FILENAME) === $fileName)) {
        return false;
      }
    }

    return true;
  }
}
