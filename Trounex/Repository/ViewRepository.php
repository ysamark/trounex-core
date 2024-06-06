<?php

namespace Trounex\Repository;

use Closure;
use App\Server;
use Trounex\View\ViewHandler;
use Trounex\View\ViewHandlerStack;
use App\Controllers\BaseController;

trait ViewRepository {
  // /**
  //  * @var integer
  //  */
  // private static $currentViewHandlerId = 0;

  // /**
  //  * @var array
  //  */
  // private static $loadedHandlers = [];

  /**
   * Render the requested view according to the page route
   *
   * @return void
   *
   */
  public static function Render () {
    $viewHandler = ViewHandlerStack::pop ();

    if ($viewHandler instanceof ViewHandler) {
      $viewHandlerProps = [
      ];

      call_user_func_array ($viewHandler, [$viewHandlerProps]);

      return;
    }

    forward_static_call_array ([self::class, 'RenderFile'], func_get_args ());
  }

  /**
   * Render a given view file by path
   * if sent path is null, render the requested path view path
   *
   * @param string $viewPath
   *
   */
  public static function RenderFile ($viewPath = null) {
    $args = func_num_args () >= 2 ? func_get_arg (1) : [];

    if (!is_array ($args)) {
      $args = [];
    }

    $renderScope = Server::lambda (function ($viewPath = null) use ($args) {
      $variableNameRe = '/^([a-zA-Z0-9_]+)$/';
      $vars = get_object_vars ($this);

      if (BaseController::isControllerInstance ($this)) {
        $vars = $this->getProps ();
      }

      foreach ($vars as $key => $var) {
        $varName = is_array ($var) ? $var ['name'] : $key;
        $value = is_array ($var) ? $var ['value'] : $vars [$key];

        if (preg_match ($variableNameRe, $varName)) {
          $$varName = $value;
        }
      }

      foreach ($args as $argumentName => $argumentValue) {
        if (preg_match ($variableNameRe, $argumentName)) {
          $$argumentName = $argumentValue;
        }
      }

      if (!(is_string ($viewPath) && is_file ($viewPath))) {
        $viewPath = Server::GetViewPath ();
      }

      include ($viewPath);
    });

    call_user_func_array ($renderScope, [$viewPath]);
  }
}
