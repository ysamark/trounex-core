<?php

namespace Trounex\Repository\ServerRepository;

use Trounex\RouteData;
use App\Utils\PageExceptions\Error;

trait ApiRouteHandler {
  protected static function handleApiRoute ($routePath, $apiSourcePath) {
    $apiSourceClassPath = join ('\\', [
      preg_replace ('/\/+/', '\\', $routePath)
    ]);

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
