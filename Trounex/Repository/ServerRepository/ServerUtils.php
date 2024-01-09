<?php

namespace Trounex\Repository\ServerRepository;

// use App\Utils\PageExceptions\Error;
use Closure;
use App\Server;
use Trounex\Helper;
use App\Server\Http\Request;
use App\Server\Http\Response;
use App\Controllers\BaseController;

trait ServerUtils {
  /**
   * Rewrite a route path to a regular expression
   */
  protected static function path2regex ($path) {
    $specialCharsList = '/[\/\^\$\[\]\{\}\(\)\\\\.]/';

    return preg_replace_callback (
      $specialCharsList, function ($match) {
        return '\\' . $match[0];
    }, (string)$path);
  }

  /**
   *
   */
  protected static function defaultHandlerArguments () {
    if (!is_array (self::$defaultHandlerArguments)) {
      self::$defaultHandlerArguments = [
        new Request ($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER),
        new Response
      ];
    }

    return self::$defaultHandlerArguments;
  }

  protected static function isAPIRoutePath ($routePath) {
    return preg_match ('/\/?api\/?/', $routePath);
  }

  protected static function buildRouteViewPathExtensionMap ($routeViewPathBase, $viewsExtension) {
    $requestMethod = strtolower($_SERVER ['REQUEST_METHOD']);

    return [
      $routeViewPathBase . DIRECTORY_SEPARATOR . 'index.' . $requestMethod . ".$viewsExtension",
      $routeViewPathBase . '.' . $requestMethod . ".$viewsExtension",
      $routeViewPathBase . DIRECTORY_SEPARATOR . "index.$viewsExtension",
      $routeViewPathBase . ".$viewsExtension"
    ];
  }

  protected static function getApiRouteSourceFile ($routePath) {
    $viewsPath = self::GetViewsPath ();
    $requestMethod = strtolower($_SERVER ['REQUEST_METHOD']);

    if (self::isAPIRoutePath ($routePath)) {
      $routeApiPathAlternates = [
        "$viewsPath/{$routePath}.$requestMethod.php",
        "$viewsPath/$routePath/index.$requestMethod.php",
        "$viewsPath/{$routePath}.php",
        "$viewsPath/$routePath/index.php",
      ];

      foreach ($routeApiPathAlternates as $routeApiPath) {
        if (is_file ($routeApiPath)) {
          return $routeApiPath;
        }
      }
    }
  }

  /**
   * @method string
   */
  protected static function stripRouteVerb ($string) {
    return preg_replace ('/\.(get|post|put|patch|delete|options|head)$/i', '', $string);
  }

  public static function lambda ($callback) {
    if (!($callback instanceof Closure)) {
      return;
    }

    if (!(is_object (self::$viewGlobalContext) )) {
      self::$viewGlobalContext = new BaseController;
    }

    return $callback->bindTo (self::$viewGlobalContext, get_class (self::$viewGlobalContext));
  }

  protected static function realRoutePath (string $routePath) {
    return preg_replace (self::$slashRe, DIRECTORY_SEPARATOR, $routePath);
  }

  public static function isHttps () {
    return (self::Get ('HTTPS') === 'on');
  }

  public static function getLayoutParent (string $layoutPath) {
    if (!Helper::isFileLocatedInLayoutsDir ($layoutPath)) {
      return null;
    }

    $layoutPathDir = dirname (realpath ($layoutPath));

    // print(nl2br("\n\n\n$layoutPathDir\n\n\n\n"));

    while ($layoutPathDir !== Server::GetLayoutsPath ()) {

      $layoutFileName = pathinfo ($layoutPath, PATHINFO_FILENAME);

      $layoutFileNameIsIndex = false; # preg_match ('/^(index)$/i', $layoutFileName);

      $layoutParentFileNameAlternates = [
        join (DIRECTORY_SEPARATOR, [$layoutPathDir, !$layoutFileNameIsIndex ? 'index.php' : null]),
        join ('.', [$layoutPathDir, 'php']),
      ];

      foreach ($layoutParentFileNameAlternates as $layoutParentFileName) {
        if (is_file ($layoutParentFileName) && realpath ($layoutParentFileName) !== realpath ($layoutPath)) {
          return ($layoutParentFileName);
        }
      }

      $layoutPathDir = dirname ($layoutPathDir);
    }

    return join (DIRECTORY_SEPARATOR, [Server::GetLayoutsPath (), 'app.php']);
  }

  public static function isRootLayout (string $layoutPath) {
    $rootLayoutPath = realpath (join (DIRECTORY_SEPARATOR, [self::GetLayoutsPath (), 'app.php']));

    return (boolean)(realpath ($layoutPath) === $rootLayoutPath);
  }
}
