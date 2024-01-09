<?php

namespace Trounex\Repository\ServerRepository;

trait ViewLoader {
  public static function LoadView (string $viewFilePath, array $viewProps = []) {
    $includeLambda = self::lambda (self::$include);
    $includeLambdaProps = array_merge ($viewProps, [
      'layoutPath' => $viewFilePath,
      'viewPath' => self::GetViewPath ()
    ]);

    return call_user_func_array ($includeLambda, [$includeLambdaProps]);
  }
}
