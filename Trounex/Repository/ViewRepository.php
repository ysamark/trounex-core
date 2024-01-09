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
   */
  public static function Render () {
    $viewHandler = ViewHandlerStack::pop ();

    if ($viewHandler instanceof ViewHandler) {
      $viewHandlerProps = [
      ];

      return call_user_func_array ($viewHandler, [$viewHandlerProps]);
    }

    call_user_func_array (Server::lambda (function ($viewPath = null) {
      $vars = get_object_vars ($this);

      if (BaseController::isControllerInstance ($this)) {
        $vars = $this->getProps ();
      }

      foreach ($vars as $key => $var) {
        $varName = is_array ($var) ? $var ['name'] : $key;
        $value = is_array ($var) ? $var ['value'] : $vars [$key];

        if (preg_match ('/^([a-zA-Z0-9_]+)$/', $varName)) {
          $$varName = $value;
        }
      }

      if (!(is_string ($viewPath) && is_file ($viewPath))) {
        $viewPath = Server::GetViewPath ();
      }

      $args = func_get_args ();

      include ($viewPath);
    }), func_get_args ());
  }
}
