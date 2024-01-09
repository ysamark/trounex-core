<?php

namespace Trounex\Repository\ServerRepository\Helpers;

use App\Router;
use Trounex\RouteData;

trait HandleDynamicRouteIfExists {
  /**
   * @method void
   *
   * handle dynamic route if it matches an existing view file exists
   */
  protected static function handleDynamicRouteIfExists () {
    $routeData = new RouteData;
    $dynamicRoutesRootDir = self::GetViewsRootDir ();

    if (self::isAPIRoutePath ($routeData->path)) {
      $dynamicRoutesRootDir = "$dynamicRoutesRootDir/api";
    }

    $dynamicRoutesPaths = Router::GetRoutesPath ($dynamicRoutesRootDir);
    $routeViewPaths = self::getRouteViewPaths();
    $dynamicRoutes = self::matchDynamicRoutesByRouteViewPaths($dynamicRoutesPaths, $routeViewPaths);

    if (isset ($dynamicRoutes [$routeData->requestMethod])) {
      return forward_static_call_array ([self::class, 'handleRoute'], $dynamicRoutes [$routeData->requestMethod]);
    }

    foreach ($dynamicRoutes as $route) {
      $routeRe = $route ['routeRe'];

      foreach ($routeViewPaths as $index => $routeViewPath) {
        if (@preg_match ($routeRe, $routeViewPath, $match)) {

          $match [-1 + count($match)] = self::stripRouteVerb($match [-1 + count($match)]);

          self::handleRoute ($index, $route, $route ['originalFilePath'], $routeData->path, $match, self::$include);

          exit (0);
        }
      }
    }
  }
}
