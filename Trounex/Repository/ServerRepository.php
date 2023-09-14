<?php

namespace Trounex\Repository;

use Closure;
use Exception;
use App\Router;
use Trounex\Helper;
use App\Utils\Http\Request;
use App\Utils\Http\Response;
use App\Utils\PageExceptions\Error;
use App\Controllers\BaseController;
use Trounex\View\ViewGlobalContext;
use Trounex\Exceptions\NoConfigPropertyException;

trait ServerRepository {
  use ServerRepository\ServerGetters;
  use ServerRepository\ServerConfigs;
  use ServerRepository\ServerUploads;
  use ServerRepository\ServerMiddlewares;

  /**
   * @var string
   */
  private static $viewPath = null;

  /**
   * @var array
   */
  private static $pathPrefix = [
    'pattern' => '/^(\/+)/',
    'text' => '/'
  ];

  /**
   * @var string
   */
  private static $viewLayout;

  /**
   * @var array
   */
  private static $defaultHandlerArguments;

  /**
   * @var BaseController
   */
  private static $viewGlobalContext;

  /**
   * @var array
   *
   * server global configs
   */
  private static $config = [];

  /**
   * @var Closure
   */
  private static $include;

  /**
   * run the application server to start serving the pages
   */
  public static function Run () {
    $requestUrl = $_SERVER ['REQUEST_URI'];
    $requestMethod = strtolower($_SERVER ['REQUEST_METHOD']);

    $requestUrlSlices = preg_split ('/\?+/', $requestUrl);

    $viewsPath = self::GetViewsPath ();
    $routePath = trim (preg_replace ('/^(\/|\\\)+/', '', $requestUrlSlices [0]));
    $routePath = trim (preg_replace ('/(\/|\\\)+$/', '', $routePath));

    $routeVerbSuffixRe = '/(\.(get|post|put|patch|delete|options|head)\.php)$/i';

    $routePath = preg_replace (self::$pathPrefix ['pattern'], '', $routePath);

    $routePath = '/' . preg_replace ('/^(\\/)+/', '', $routePath);
    $rootDir = realpath (self::$config ['rootDir']);

    $trounexConfigFilePath = join (DIRECTORY_SEPARATOR, [$rootDir, 'trounex.json']);

    if (is_file ($trounexConfigFilePath)) {
      $trounexConfigFileData = (array)json_decode (file_get_contents ($trounexConfigFilePath));

      if (isset ($trounexConfigFileData ['rewrites'])) {
        $trounexRewrites = (array)($trounexConfigFileData ['rewrites']);

        if (isset ($trounexRewrites [$routePath]) && is_string ($trounexRewrites [$routePath])) {
          $routePath = $trounexRewrites [$routePath];
        }
      }
    }

    $viewsExtensions = self::GetViewsFileExtensions ();
    $viewsRootDir = self::GetViewsRootDir ();

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

    $routeViewPathAlternates = array_filter ($routeViewPathAlternates, $routeViewPathAlternatesFilter);

    self::$include = function ($__props) {
      $global = new ViewGlobalContext ($this);

      $viewEngine = 'Default';
      $viewEngineResolveAlternates = [
        // conf()
        'viewEngine',
        'viewEngine.resolve'
      ];

      foreach ($viewEngineResolveAlternates as $viewEngineResolveAlternate) {
        $viewEngineResolveAlternate = conf ($viewEngineResolveAlternate);

        if (is_string ($viewEngineResolveAlternate)) {
          $viewEngine = ucfirst($viewEngineResolveAlternate);
          break;
        }
      }

      $viewEngineAdapterClassName = "Trounex\\View\\ViewEngine\\{$viewEngine }ViewEngine";

      if (class_exists ($viewEngineAdapterClassName)) {
        $viewEngineAdapterProps = [
          'context' => $global,
          'viewFilePath' => $__props ['viewPath'],
          'layoutFilePath' => $__props ['layoutPath']
        ];

        $viewEngineAdapterHandlerArguments = [$global];

        $viewEngineAdapter = new $viewEngineAdapterClassName ($viewEngineAdapterProps);

        return call_user_func_array ([$viewEngineAdapter, 'render'], $viewEngineAdapterHandlerArguments);
      }
    };

    $actionMethod = 'handler';

    if ($apiSourcePath = self::getApiRouteSourceFile ($routePath)) {
      $apiSourceClassPath = join ('\\', [
        preg_replace ('/\/+/', '\\', $routePath)
      ]);

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

    if ($publicFilePath = self::publicFileExists ($routePath)) {
      return self::serveStaticFile ($publicFilePath);
    }

    if (preg_match ('/^\/api\/?/i', $routePath)) {
      $dynamicRoutesPaths = Router::GetRoutesPath ("$viewsPath/api");
    } else {
      $dynamicRoutesPaths = Router::GetRoutesPath ($viewsPath);
    }

    $routeViewPathRoot = preg_replace ('/[\/\\\]/', DIRECTORY_SEPARATOR, "$viewsRootDir{$routePath}");
    $routeViewPathBase = preg_replace ('/[\/\\\]/', DIRECTORY_SEPARATOR, "$viewsPath{$routePath}");

    $routeViewPaths = [];

    foreach ($viewsExtensions as $viewsExtension) {
      $routeViewPathExtensionMatch = [
        $routeViewPathRoot . DIRECTORY_SEPARATOR . 'index.' . $requestMethod . ".$viewsExtension",
        $routeViewPathRoot . '.' . $requestMethod . ".$viewsExtension",
        $routeViewPathRoot . DIRECTORY_SEPARATOR . "index.$viewsExtension",
        $routeViewPathRoot . ".$viewsExtension"
      ];

      if (preg_match ('/^\/api\/?/i', $routePath)) {
        $routeViewPathExtensionMatch = [
          $routeViewPathBase . DIRECTORY_SEPARATOR . 'index.' . $requestMethod . ".$viewsExtension",
          $routeViewPathBase . '.' . $requestMethod . ".$viewsExtension",
          $routeViewPathBase . DIRECTORY_SEPARATOR . "index.$viewsExtension",
          $routeViewPathBase . ".$viewsExtension"
        ];
      }

      $routeViewPaths = array_merge ($routeViewPaths, $routeViewPathExtensionMatch);
    }

    $dynamicRoutes = [];

    foreach ($dynamicRoutesPaths as $route) {
      $routeRe = $route ['routeRe'];

      foreach ($routeViewPaths as $index => $routeViewPath) {
        if (@preg_match ($routeRe, $routeViewPath, $match)) {
          array_push($dynamicRoutes, $route);

          if (preg_match ($routeVerbSuffixRe, $route ['originalFilePath'], $routeVerbSuffixMatch)) {
            $routeVerb = strtolower ($routeVerbSuffixMatch[2]);

            $dynamicRoutes [$routeVerb] = [
              $index,
              $route,
              $routeViewPath,
              $routePath,
              $match,
              self::$include
            ];
          }

          break;
        }
      }
    }

    if (isset ($dynamicRoutes [$requestMethod])) {
      return forward_static_call_array ([self::class, 'handleRoute'], $dynamicRoutes [$requestMethod]);
    }

    foreach ($dynamicRoutes as $route) {
      $routeRe = $route ['routeRe'];

      foreach ($routeViewPaths as $index => $routeViewPath) {
        if (@preg_match ($routeRe, $routeViewPath, $match)) {

          $match [-1 + count($match)] = self::stripRouteVerb($match [-1 + count($match)]);

          self::handleRoute ($index, $route, $routeViewPath, $routePath, $match, self::$include);
        }
      }
    }

    Error::Throw404 ();
  }

  public static function LoadView (string $viewFilePath, array $viewProps = []) {
    $includeLambda = self::lambda (self::$include);
    $includeLambdaProps = array_merge ($viewProps, [
      'layoutPath' => $viewFilePath,
      'viewPath' => self::GetViewPath ()
    ]);

    return call_user_func_array ($includeLambda, [$includeLambdaProps]);
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

  protected static function isAPIRoutePath ($routePath) {
    return preg_match ('/\/?api\/?/', $routePath);
  }

  /**
   * get the main layout path
   */
  protected static function mainLayoutView () {
    if (is_string (self::$viewLayout)
      && is_file (self::$viewLayout)) {
      return self::$viewLayout;
    }

    $viewsExtensions = conf ('viewEngine.options.extensions');
    $viewsRootDir = conf ('viewEngine.options.rootDir');

    $layoutsDirPath = self::GetLayoutsPath ();
    $viewsDirPathRe = self::path2regex ($viewsRootDir);

    $viewPath = self::GetViewPath ();

    if (!is_array ($viewsExtensions)) {
      $viewsExtensions = [];
    }

    $viewLayoutRelativePath = preg_replace("/^($viewsDirPathRe)(\\/|\\\\)*/i", '', $viewPath);

    $viewLayoutRelativePathSlices = preg_split('/(\/|\\\\)+/', $viewLayoutRelativePath);

    foreach ($viewsExtensions as $viewsExtension) {
      $viewLayoutRelativePathSlicesLen = count ($viewLayoutRelativePathSlices);

      for ($i = 0; $i < $viewLayoutRelativePathSlicesLen; $i++) {
        $viewLayoutRelativePath = dirname ($viewLayoutRelativePath);

        $viewLayoutAbsolutePath = join (DIRECTORY_SEPARATOR, [
          $layoutsDirPath, join ('.', [$viewLayoutRelativePath, $viewsExtension])
        ]);

        if (is_file ($viewLayoutAbsolutePath)) {
          return $viewLayoutAbsolutePath;
        }
      }

      $viewLayoutAbsolutePath = join (DIRECTORY_SEPARATOR, [
        $layoutsDirPath, "app.$viewsExtension"
      ]);

      if (is_file ($viewLayoutAbsolutePath)) {
        return $viewLayoutAbsolutePath;
      }
    }

    exit ("Could not load main layout");
  }

  /**
   * set the view path
   */
  protected static function setViewPath ($viewPath) {
    self::$viewPath = $viewPath;
  }

  /**
   * verify if a given file path exists in the public directory
   *
   * @param string $publicFilePath
   */
  protected static function publicFileExists ($publicFilePath) {
    $publicPath = self::GetPublicPath ();

    $publicFilePath = join (DIRECTORY_SEPARATOR, [
      $publicPath, $publicFilePath
    ]);

    if (is_file ($publicFilePath)) {
      return realpath ($publicFilePath);
    }

    return false;
  }

  /**
   * Serve a static file given its absolute path
   *
   * @param string $publicFilePath
   */
  protected static function serveStaticFile ($publicFilePath) {
    $fileExtension = pathinfo (strtolower ($publicFilePath), PATHINFO_EXTENSION);

    $mimetype = 'application/octet-stream';
    $mimetypeMap = mimetypemap ();

    if (isset ($mimetypeMap [$fileExtension])) {
      $mimetype = $mimetypeMap [$fileExtension];
    }

    @header ('X-Powered-By: Samils SY');
    @header ("Content-Type: {$mimetype}");

    exit (file_get_contents ($publicFilePath));
  }

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
        new Request,
        new Response
      ];
    }

    return self::$defaultHandlerArguments;
  }

  /**
   * @method void
   *
   * Handle a route
   */
  protected static function handleRoute ($index, $route, $routeViewPath, $routePath, $match) {
    self::setViewPath (realpath ($route ['originalFilePath']));

    $callback = func_get_arg (-1 + func_num_args ());

    if ($index === 0 && !is_file ($routeViewPath)) {
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
        return call_user_func_array ([$api, $actionMethod], self::defaultHandlerArguments ());
      }

      Error::Throw404 ();
    } else {
      self::beforeRender ();

      // return call_user_func_array (self::lambda ($callback), [['path' => self::mainLayoutView ()]]);
      return self::LoadView (self::mainLayoutView ());
    }

    exit (0);
  }

  /**
   * @method string
   */
  protected static function stripRouteVerb ($string) {
    return preg_replace ('/\.(get|post|put|patch|delete|options|head)$/i', '', $string);
  }

  /**
   * @method mixed
   */
  protected static function handleTXTConfigFile (string $configFile) {
    $configFileContent = trim (file_get_contents ($configFile));

    return preg_replace ('/(\\\)$/', '', preg_replace ('/^(\\\)/', '', $configFileContent));
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

  /**
   * get views extensions list ordered from longer
   * to shorter by dots division
   */
  protected static function orderViewsExtensionsList ($viewsExtensions = null) {
    if (is_null ($viewsExtensions)) {
      $viewsExtensions = self::GetViewsFileExtensions ();
    }

    $longerExtension = '';
    $longerExtensionIndex = -1;

    $getStrDotsLen = function ($string) {
      if (is_string ($string)) {
        $strSplit = preg_split ('/\\./', $string);

        return -1 + count ($strSplit);
      }

      return -1;
    };

    foreach ($viewsExtensions as $viewsExtensionIndex => $viewsExtension) {
      $viewsExtensionDotsLen = $getStrDotsLen ($viewsExtension);
      $longerExtensionDotsLen = $getStrDotsLen ($longerExtension);

      if ($longerExtensionIndex < 0 || $viewsExtensionDotsLen > $longerExtensionDotsLen) {
        $longerExtension = $viewsExtension;
        $longerExtensionIndex = $viewsExtensionIndex;
      }
    }

    if ($longerExtensionIndex >= 0) {
      array_splice ($viewsExtensions, $longerExtensionIndex, 1);

      $orderedList = array_merge ([$longerExtension], self::orderViewsExtensionsList ($viewsExtensions));

      return $orderedList;
    }

    return $viewsExtensions;
  }

  /**
   * get view controller file path
   */
  public static function GetViewControllerPath (string $viewPath) {
    /**
     * @var array
     *
     * order the views file extensions from
     * longer to shorter according to dots
     * division
     */
    $orderedViewsExtensions = self::orderViewsExtensionsList ();

    $viewsDir = self::GetViewsPath ();
    $viewsRootDirRe = self::path2regex (self::GetViewsRootDir ());
    $viewsRootDirRe = "/^($viewsRootDirRe)/";

    if (preg_match ($viewsRootDirRe, $viewPath)) {
      $viewAbsolutePath = preg_replace ($viewsRootDirRe, $viewsDir, $viewPath);

      foreach ($orderedViewsExtensions as $viewsExtension) {
        $viewsExtensionRe = join ('', ['/(', self::path2regex ($viewsExtension), ')$/']);

        if (preg_match ($viewsExtensionRe, $viewAbsolutePath)) {
          $viewControllerFileName = preg_replace ($viewsExtensionRe, 'controller.php', $viewAbsolutePath);

          return $viewControllerFileName;
        }
      }
    }
  }

  /**
   * @method void
   *
   * set server router path prefix
   */
  public static function SetPathPrefix ($pathPrefix = null) {
    self::PathPrefix ($pathPrefix);
  }

  /**
   * set the router path  prefix
   */
  public static function PathPrefix ($pathPrefix = null) {
    if (is_string ($pathPrefix) && !empty ($pathPrefix)) {
      $pathPrefix = preg_replace ('/^\/+/', '',
        preg_replace ('/\/+$/', '', preg_replace ('/\/{2,}/', '/', $pathPrefix))
      );

      self::$pathPrefix ['pattern'] = '/^('.self::path2regex ($pathPrefix).')/i';
      self::$pathPrefix ['text'] = trim ($pathPrefix);
    }

    if (isset (self::$pathPrefix ['text'])) {
      return trim (self::$pathPrefix ['text']);
    }
  }
}
