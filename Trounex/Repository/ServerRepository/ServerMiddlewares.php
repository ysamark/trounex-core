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
}
