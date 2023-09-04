<?php

namespace Trounex\Repository;

use Closure;
use App\Router;
use Trounex\Helper;
use App\Utils\Http\Request;
use App\Utils\Http\Response;
use App\Utils\PageExceptions\Error;
use App\Controllers\BaseController;
use Trounex\View\ViewGlobalContext;
use Trounex\Helpers\FileUploadHelper;

trait ServerRepository {
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

    $viewsExtensions = conf ('viewEngine.options.extensions');
    $viewsRootDir = conf ('viewEngine.options.rootDir');

    $routeViewPathAlternates = [];

    $routeViewPathAlternatesFilter = function ($routeViewPathAlternate) {
      return (boolean)($routeViewPathAlternate);
    };

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

    self::$include = function ($__view) {
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
          'viewFilePath' => $__view ['path']
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

        call_user_func_array (self::lambda (self::$include), [['path' => self::mainLayoutView ()]]);

        exit (0);
      }
    }

    if ($publicFilePath = self::publicFileExists ($routePath)) {
      return self::serveStaticFile ($publicFilePath);
    }

    $dynamicRoutesPaths = Router::GetRoutesPath ($viewsPath);

    $routeViewPathBase = preg_replace ('/[\/\\\]/', DIRECTORY_SEPARATOR, "$viewsPath{$routePath}");

    $routeViewPaths = [
      $routeViewPathBase . DIRECTORY_SEPARATOR . 'index.' . $requestMethod . '.php',
      $routeViewPathBase . '.' . $requestMethod . '.php',
      $routeViewPathBase . DIRECTORY_SEPARATOR . 'index.php',
      $routeViewPathBase . '.php'
    ];

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

          self::handleRoute($index, $route, $routeViewPath, $routePath, $match, self::$include);
        }
      }
    }

    Error::Throw404 ();
  }

  public static function Get (string $property = null) {
    $propertyMap = [
      'port' => 'SERVER_PORT'
    ];

    if (isset ($propertyMap [$property])) {
      $propertyLoader = $propertyMap [$property];

      if (is_string ($propertyLoader)) {
        return isset ($_SERVER [$propertyLoader]) ? $_SERVER [$propertyLoader] : null;
      } elseif ($propertyLoader instanceof \Closure) {
        return call_user_func_array ($propertyLoader, [$property]);
      }
    }
  }

  public static function LoadView (string $viewFilePath, array $viewProps = []) {
    $includeLambda = self::lambda (self::$include);
    $includeLambdaProps = array_merge ($viewProps, ['path' => $viewFilePath]);

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

  protected static function beforeAPIHandler () {
    self::beforeRenderOrAPIHandler ();

    $_SESSION ['_post'] = $_POST;

    $fieldSources = isset ($_POST ['_source']) ? $_POST ['_source'] : [];

    if (isset ($_FILES) && $_FILES) {
      $pairedFileFieldProperties = [];

      // echo '<pre>';
      // print_r (['file' => $_FILES, 'source' => $fieldSources]);

      foreach ($_FILES as $fileFieldProperty => $fileData) {
        // $file = FileUploadHelper::UploadFile ([
        //   'data' => $fileData
        // ]);

        // exit ($file->name);

        foreach ($fieldSources as $fieldSourceKey => $fieldSourceValue) {
          if (strtolower ($fileFieldProperty) === strtolower ($fieldSourceValue)) {
            array_push ($pairedFileFieldProperties, $fileFieldProperty);

            self::processFileField ($fileFieldProperty, $fileData, $fieldSourceKey);
          }
        }

        if (!in_array ($fileFieldProperty, $pairedFileFieldProperties)) {
          $fieldSourceKey = preg_replace ('/\-+/', '.', $fileFieldProperty);

          self::processFileField ($fileFieldProperty, $fileData, $fieldSourceKey);
        }
      }
    }
  }

  protected static function processFileField ($fileFieldProperty, $fileData, $fieldSourceKey) {
    $_FILES [$fileFieldProperty] = [null];

    $file = FileUploadHelper::UploadFile ([
      'data' => $fileData
    ]);

    if (!$file->error) {
      Helper::putPostData ($fieldSourceKey, $file->name);
    }
  }

  protected static function beforeRender () {
    self::beforeRenderOrAPIHandler ();
    register_shutdown_function ('App\Utils\ShutDownFunction');

    $viewPath = self::GetViewPath ();

    $viewControllerPath = join ('', [
      preg_replace ('/(\.php)$/', '.controller.php', $viewPath)
    ]);

    if (is_file ($viewControllerPath)) {
      $viewControllerInstance = require ($viewControllerPath);

      if (is_callable ($viewControllerInstance) && $viewControllerInstance instanceof Closure) {
        #self::$viewGlobalContext = $viewControllerInstance;
        return call_user_func_array (self::lambda ($viewControllerInstance), self::defaultHandlerArguments ());
      } elseif (is_object ($viewControllerInstance) && method_exists ($viewControllerInstance, 'handler')) {
        self::$viewGlobalContext = $viewControllerInstance;
        call_user_func_array ([$viewControllerInstance, 'handler'], self::defaultHandlerArguments ());
      }
    }
  }

  protected static function beforeRenderOrAPIHandler () {
    $viewPath = dirname (self::GetViewPath ());

    # Run middlewares
    # $middlewaresList = [];

    $viewPathSlices = preg_split ('/(\/|\\\)+/', $viewPath);
    $viewPathSlicesCount = count ($viewPathSlices);

    for ($i = 0; $i < $viewPathSlicesCount; $i++) {
      $viewMiddlewarePath = join (DIRECTORY_SEPARATOR, [
        $viewPath,
        pathinfo ($viewPath, PATHINFO_FILENAME) . '.middleware.php'
      ]);

      if (is_null (self::$viewLayout)) {
        $viewLayoutPath = join (DIRECTORY_SEPARATOR, [
          $viewPath,
          pathinfo ($viewPath, PATHINFO_FILENAME) . '.layout.php'
        ]);

        if (is_file ($viewLayoutPath)) {
          self::$viewLayout = $viewLayoutPath;
        }
      }

      if (is_file ($viewMiddlewarePath)) {
        $viewMiddlewareInstance = require ($viewMiddlewarePath);

        if (is_callable ($viewMiddlewareInstance) && $viewMiddlewareInstance instanceof Closure) {
          call_user_func_array (self::lambda ($viewMiddlewareInstance), self::defaultHandlerArguments ());
        } elseif (is_object ($viewMiddlewareInstance) && method_exists ($viewMiddlewareInstance, 'handler')) {
          call_user_func_array ([$viewMiddlewareInstance, 'handler'], self::defaultHandlerArguments ());
        }
      }

      $viewPath = dirname ($viewPath);
    }
    # End
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

    $layoutsDirPath = self::GetLayoutsPath ();
    $viewsDirPathRe = self::path2regex(self::GetViewsPath ());

    $viewPath = self::GetViewPath ();

    $viewLayoutRelativePath = preg_replace("/^($viewsDirPathRe)(\\/|\\\\)*/i", '', $viewPath);

    $viewLayoutRelativePathSlices = preg_split('/(\/|\\\\)+/', $viewLayoutRelativePath);

    for ($i = 0; $i < count($viewLayoutRelativePathSlices); $i++) {
      $viewLayoutRelativePath = dirname($viewLayoutRelativePath);

      $viewLayoutAbsolutePath = join(DIRECTORY_SEPARATOR, [
        $layoutsDirPath, join('.', [$viewLayoutRelativePath, 'php'])
      ]);

      if (is_file($viewLayoutAbsolutePath)) {
        return $viewLayoutAbsolutePath;
      }
    }

    $viewLayoutAbsolutePath = join(DIRECTORY_SEPARATOR, [
      $layoutsDirPath, 'app.php'
    ]);

    if (!is_file($viewLayoutAbsolutePath)) {
      exit ('Could not load main layout');
    }

    return $viewLayoutAbsolutePath;
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
      return self::LoadView(self::mainLayoutView ());
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

  /**
   * @method mixed
   */
  protected static function handlePHPConfigFile (string $configFile) {
    return self::handleConfigFile ($configFile);
  }

  /**
   * @method mixed
   */
  protected static function handleJSONConfigFile (string $configFile) {
    $configFileContent = file_get_contents ($configFile);

    $configFileData = json_decode (trim ($configFileContent));

    return Helper::ObjectsToArray ($configFileData);
  }

  /**
   * @method mixed
   */
  protected static function handleYAMLConfigFile (string $configFile) {}

  /**
   * @method mixed
   */
  protected static function handleXMLConfigFile (string $configFile) {}

  /**
   * @method mixed
   */
  private static function handleConfigFile ($configFile) {
    if (is_file ($configFile)) {
      $configFileData = @require ($configFile);

      return $configFileData;
    }
  }

  /**
   * @method string
   */
  public static function getApplicationRootDir () {
    $isRootDir = function ($dir) {
      return (boolean)(
        is_file ($dir . '/composer.json') &&
        is_dir ($dir . '/vendor/ysamark/trounex-core/Trounex')
      );
    };

    $currentDir = dirname (__DIR__);

    $rootDirFetchIntervalCount = count (preg_split ('/(\/|\\\\)/', $currentDir));

    for ( ; $rootDirFetchIntervalCount >= 0; $rootDirFetchIntervalCount--) {
      if (call_user_func_array ($isRootDir, [$currentDir])) {
        return $currentDir;
      }

      $currentDir = dirname ($currentDir);
    }
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
   * get the view path
   */
  public static function GetViewPath () {
    return self::$viewPath;
  }

  /**
   * get the views path
   */
  public static function GetViewsPath () {
    $rootDir = self::GetRootPath ();

    return realpath($rootDir . '/views');
  }

  /**
   * get the view layouts path
   */
  public static function GetLayoutsPath () {
    $rootDir = self::GetRootPath ();

    return realpath($rootDir . '/layouts');
  }

  /**
   * get the public path
   */
  public static function GetPublicPath () {
    $rootDir = self::GetRootPath ();

    return realpath($rootDir . '/public');
  }

  /**
   * get the root path
   */
  public static function GetRootPath () {
    return isset (self::$config ['rootDir']) ? realpath (self::$config ['rootDir']) : '/';
  }

  /**
   *
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
      // $port = self::Get ('port');

      // if (in_array ((int)($port), [80])) {
      //   return '/as';
      // }

      return trim (self::$pathPrefix ['text']);
    }
  }

  /**
   * @method array
   *
   * Config file types
   *
   * a map of file extensions related to the
   * config file type handler
   */
  public static function GetConfigFileTypes () {
    $re = '/handle(.+)ConfigFile/i';
    $classMethods = get_class_methods (self::class);

    $configFileTypes = [];

    foreach ($classMethods as $classMethod) {
      if (preg_match ($re, $classMethod, $classMethodMatch)) {
        $configFileType = strtolower ($classMethodMatch [1]);

        array_push ($configFileTypes, $configFileType);
      }
    }

    return $configFileTypes;
  }

  /**
   * @method void
   */
  public static function SetupConfigs (array $config = []) {
    $config ['rootDir'] = self::getApplicationRootDir ();

    $configDirPath = join (DIRECTORY_SEPARATOR, [
      $config ['rootDir'], 'config'
    ]);

    $mainConfigFilePath = join (DIRECTORY_SEPARATOR, [
      $configDirPath, 'index.php'
    ]);

    $mainConfigFileData = self::handleConfigFile ($mainConfigFilePath);

    if (is_array ($mainConfigFileData)) {
      $config = array_merge ($config, $mainConfigFileData);
    }

    foreach (self::GetConfigFileTypes () as $configFileType) {
      $configFilesRe = join (DIRECTORY_SEPARATOR, [
        $configDirPath, '*.config.' . $configFileType
      ]);

      $configFilePaths = glob ($configFilesRe);

      foreach ($configFilePaths as $configFilePath) {
        $configFileHandler = join ('', [
          'handle', strtoupper ($configFileType), 'ConfigFile'
        ]);

        $configFileData = null; # self::handleConfigFile ($configFilePath);

        if (method_exists (self::class, $configFileHandler)) {
          $configFileData = forward_static_call_array ([self::class, $configFileHandler], [realpath ($configFilePath)]);
        }

        $configFileName = pathinfo ($configFilePath, PATHINFO_FILENAME);

        $configFileName = preg_replace ('/\.config$/i', '', $configFileName);

        if (isset ($config [$configFileName]) && is_array ($config [$configFileName]) && is_array ($configFileData)) {
          $configFileData = array_full_merge ($config [$configFileName], $configFileData);
        }

        $config [$configFileName] = $configFileData;
      }
    }

    /**
     * Set whole the config data to the app global config
     */
    foreach ($config as $prop => $value) {
      $configPropSetterName = "Set$prop";

      if (method_exists (self::class, $configPropSetterName)) {
        forward_static_call_array ([self::class, $configPropSetterName], [$value]);
      } else {
        self::$config [$prop] = $value;
      }
    }
  }

  /**
   * @method array
   *
   * Get all the server config data
   */
  public function GetConfigs () {
    if (!self::$config) {
      self::SetupConfigs ();
    }

    return self::$config;
  }
}
