<?php

namespace Trounex\Repository\ServerRepository;

trait ServerMiddlewares {
  /**
   * @method void
   */
  protected static function beforeAPIHandler () {
    self::beforeRenderOrAPIHandler ();

    $_SESSION ['_post'] = $_POST;

    $fieldSources = isset ($_POST ['_source']) ? $_POST ['_source'] : [];

    if (isset ($_FILES) && $_FILES) {
      $pairedFileFieldProperties = [];

      foreach ($_FILES as $fileFieldProperty => $fileData) {

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

  protected static function beforeRender () {
    self::beforeRenderOrAPIHandler ();
    register_shutdown_function ('App\Utils\ShutDownFunction');

    $viewPath = self::GetViewPath ();

    $viewControllerPath = self::GetViewControllerPath ($viewPath);

    if (is_file ($viewControllerPath)) {
      $viewControllerInstance = require ($viewControllerPath);

      if (is_callable ($viewControllerInstance) && $viewControllerInstance instanceof Closure) {
        #self::$viewGlobalContext = $viewControllerInstance;
        return call_user_func_array (self::lambda ($viewControllerInstance), self::defaultHandlerArguments ());
      } elseif (is_object ($viewControllerInstance) && method_exists ($viewControllerInstance, 'handler')) {
        self::$viewGlobalContext = $viewControllerInstance;
        return call_user_func_array ([$viewControllerInstance, 'handler'], self::defaultHandlerArguments ());
      }

      $viewControllerRelativePath = self::getViewFileRelativePath ($viewControllerPath);
      $viewControllerClassRef = preg_replace ('/((index)?\.controller\.php)$/i', '', $viewControllerRelativePath);
      $viewControllerClassRef = self::rewriteRoutePathToClassRef ($viewControllerClassRef);
      $viewControllerClassAbsoluteRef = join ('\\', [
        'Views', $viewControllerClassRef
      ]);

      $viewControllerClassAbsoluteAlternatesRef = [
        $viewControllerClassAbsoluteRef,
        join ('', [$viewControllerClassAbsoluteRef, 'Controller'])
      ];

      foreach ($viewControllerClassAbsoluteAlternatesRef as $viewControllerClassAbsoluteAlternateRef) {
        if (class_exists ($viewControllerClassAbsoluteAlternateRef)) {
          $viewControllerInstance = new $viewControllerClassAbsoluteAlternateRef;

          if (method_exists ($viewControllerInstance, 'handler')) {
            return call_user_func_array ([$viewControllerInstance, 'handler'], self::defaultHandlerArguments ());
          }
        }
      }
    }
  }

  protected static function beforeRenderOrAPIHandler () {
    $viewPath = dirname (self::GetViewPath ());

    $viewPaths = [
      $viewPath
    ];

    # Run middlewares
    # $middlewaresList = [];

    $viewPathSlices = preg_split ('/(\/|\\\)+/', $viewPath);
    $viewPathSlicesCount = count ($viewPathSlices);

    for ($i = 0; $i < $viewPathSlicesCount; $i++) {
      $viewPath = dirname ($viewPath);
      array_push ($viewPaths, $viewPath);
    }

    $viewPaths = array_reverse ($viewPaths);

    foreach ($viewPaths as $viewPath) {
      $viewMiddlewarePaths = [
        pathinfo ($viewPath, PATHINFO_FILENAME) . '.middleware.php',
        '@middleware.php'
      ];

      if (is_null (self::$viewLayout)) {
        $viewLayoutPath = join (DIRECTORY_SEPARATOR, [
          $viewPath,
          pathinfo ($viewPath, PATHINFO_FILENAME) . '.layout.php'
        ]);

        if (is_file ($viewLayoutPath)) {
          self::$viewLayout = $viewLayoutPath;
        }
      }

      foreach ($viewMiddlewarePaths as $viewMiddlewarePath) {
        $viewMiddlewarePath = join (DIRECTORY_SEPARATOR, [
          $viewPath, $viewMiddlewarePath
        ]);

        if (is_file ($viewMiddlewarePath)) {
          $viewMiddlewareInstance = require ($viewMiddlewarePath);

          if (is_callable ($viewMiddlewareInstance) && $viewMiddlewareInstance instanceof Closure) {
            call_user_func_array (self::lambda ($viewMiddlewareInstance), self::defaultHandlerArguments ());
          } elseif (is_object ($viewMiddlewareInstance) && method_exists ($viewMiddlewareInstance, 'handler')) {
            call_user_func_array ([$viewMiddlewareInstance, 'handler'], self::defaultHandlerArguments ());
          }
        }
      }
    }
    # End
  }
}
