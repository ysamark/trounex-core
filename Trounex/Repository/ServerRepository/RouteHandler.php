<?php

namespace Trounex\Repository\ServerRepository;

use App\Router;
use App\Utils\PageExceptions\Error;

trait RouteHandler {
  /**
   * @method void
   *
   * Handle a route
   */
  protected static function handleRoute ($index, $route, $routeViewPath, $routePath, $match) {
    self::setViewPath (realpath ($route ['originalFilePath']));

    $callback = func_get_arg (-1 + func_num_args ());

    if (!is_file ($routeViewPath)) {
      return;
    }

    $routeParamKeys = $route ['match'][1];
    $routeParamValues = array_slice ($match, 2, count ($match));

    Router::EvaluateRouteParams ($routeParamKeys, $routeParamValues);

    if (self::isAPIRoutePath ($routePath)) {
      self::setViewPath (realpath ($route ['originalFilePath']));
      $api = require (realpath ($route ['originalFilePath']));

      self::beforeAPIHandler ();

      $action = param ('_action');

      $action = self::stripRouteVerb($action);

      $actionMethod = is_string ($action) && !empty ($action) ? $action : 'handler';

      if (is_object ($api) && method_exists ($api, $actionMethod)) {
        call_user_func_array ([$api, $actionMethod], self::defaultHandlerArguments ());
        exit(0);
      }

      Error::Throw404 ();
    } else {
      self::beforeRender ();
      self::LoadView (self::mainLayoutView ());
    }

    exit (0);
  }
}
