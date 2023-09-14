<?php

namespace Trounex\Repository\ServerRepository;

trait ServerGetters {
  /**
   * @method mixed
   */
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

    return realpath ($rootDir . '/views');
  }

  /**
   * get views root directory
   */
  public static function GetViewsRootDir () {
    $configViewsRootDir = conf ('viewEngine.options.rootDir');

    if (is_string ($configViewsRootDir) && is_dir ($configViewsRootDir)) {
      return realpath ($configViewsRootDir);
    }

    return self::GetViewsPath ();
  }

  /**
   * get views valid file extensions
   */
  public static function GetViewsFileExtensions () {
    $defaultViewsFileExtensions = [
      'php'
    ];
    $viewsFileExtensions = conf ('viewEngine.options.extensions');

    if (is_array ($viewsFileExtensions)) {
      return array_merge ($viewsFileExtensions, $defaultViewsFileExtensions);
    }

    return $defaultViewsFileExtensions;
  }

  /**
   * get the view layouts path
   */
  public static function GetLayoutsPath () {
    $layoutsDirPath = join (DIRECTORY_SEPARATOR, [
      conf ('rootDir'), 'layouts'
    ]);

    try {
      $layoutsDirPath = conf ('viewEngine.options.layoutsDir');
    }
    catch (Exception $e) {}
    catch (NoConfigPropertyException $e) {}

    return realpath ($layoutsDirPath);
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
