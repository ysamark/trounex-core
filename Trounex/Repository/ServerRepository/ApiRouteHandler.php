<?php

namespace Trounex\Repository\ServerRepository;

use Trounex\RouteData;
use App\Utils\PageExceptions\Error;

trait ApiRouteHandler {
  /**
   * @method string
   *
   * rewrite whole the route paths to valid class reference~
   *
   */
  protected static function rewriteRoutePathToClassRef (string $routePath) {
    $routePathSlices = preg_split ('/(\/|\\\)+/', $routePath);

    $routePathSlicesMapper = (function ($routePathSlice) {
      $routerContextRe = '/^(\((.+)\))$/';

      $anyToCamelCase = (function ($str) {
        $replacer = (function ($match) {
          return strtoupper ($match [2]);
        });

        return preg_replace_callback ('/(\-|\.|@|\$)+(.)/', $replacer, ucfirst ($str));
      });

      if (preg_match ($routerContextRe, $routePathSlice, $match)) {
        return call_user_func ($anyToCamelCase, trim ($match [2]));
      }

      return call_user_func ($anyToCamelCase, $routePathSlice);
    });

    return join ('\\', array_map ($routePathSlicesMapper, $routePathSlices));
  }

  protected static function handleApiRoute ($routePath, $apiSourcePath) {
    $apiSourceClassPath = self::rewriteRoutePathToClassRef ($routePath);

    $actionMethod = 'handler';

    self::setViewPath (realpath ($apiSourcePath));

    $apiSource = include_once $apiSourcePath;

    if (class_exists ($apiSourceClassPath) || is_object ($apiSource)) {
      $api = !is_object ($apiSource) ? new $apiSourceClassPath () : $apiSource;

      if (method_exists ($api, $actionMethod)) {
        self::beforeAPIHandler ();

        call_user_func_array ([$api, $actionMethod], self::defaultHandlerArguments ());

        exit (0);
      }
    }

    Error::Throw404 ();
  }

  protected static function handleApiRouteIfExists () {
    $routeData = new RouteData;
    $apiSourcePath = self::getApiRouteSourceFile ($routeData->path);

    if ($apiSourcePath) {
      return self::handleApiRoute($routeData->path, $apiSourcePath);
    }
  }
}
