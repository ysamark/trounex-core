<?php

namespace Trounex\Repository\ServerRepository;

trait ViewLoader {
  /**
   * @method mixed
   *
   * Trounex view loader
   * load a view file by its corresponding layout stack
   *
   * @param string $viewFilePath
   * @param array $viewProps
   */
  public static function LoadView (string $viewFilePath, array $viewProps = []) {
    $includeLambda = self::lambda (self::$include);
    $includeLambdaProps = array_merge ($viewProps, [
      'layoutPath' => $viewFilePath,
      'viewPath' => self::GetViewPath ()
    ]);

    return call_user_func_array ($includeLambda, [$includeLambdaProps]);
  }
}
