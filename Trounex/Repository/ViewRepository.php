<?php

namespace Trounex\Repository;

use Closure;
use App\Server;
use App\Controllers\BaseController;

trait ViewRepository {
  /**
   * Render the requested view according to the page route
   *
   * @return void
   */
  public static function Render () {
    $backTrace = debug_backtrace ();

    // echo '<ul style="background-color: green; color: white; margin: 30px 0px;"><pre>';
    // print_r(array_slice($backTrace, 0));
    // echo '</pre></ul>';

    // echo '<ul style="background-color: teal; color: white; margin: 30px 0px;"><pre>';
    // print_r($backTrace[3]);
    // echo '</pre></ul>';

    $viewHandlerId = self::getViewHandlerIdByTrace ($backTrace);

    if (is_numeric ($viewHandlerId)) {
      $viewHandler = self::getViewHandlerByTrace ($backTrace, $viewHandlerId);

      // $viewHandler = !!$viewHandler ? $viewHandler : self::getViewHandlerByTrace ($backTrace, 2);

      $validTraceFileReference = (boolean)(
        isset ($backTrace [1]) &&
        is_array ($backTrace [1]) &&
        isset ($backTrace [1]['file'])
      );

      if ($viewHandler) {
        $viewHandlerProps = [
          'viewHandler' => $viewHandler,
          'layoutPath' => $validTraceFileReference ? $backTrace[1]['file'] : null,
          'viewHandlerBodyElement' => 'Get Me'
        ];

        return call_user_func_array ($viewHandler, [$viewHandlerProps]);
      }
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

  /**
   * Yield
   */
  public static function Yields () {
    $backTrace = debug_backtrace ();

    foreach ($backTrace as $i => $trace) {
      if (is_array ($trace)
        && isset ($trace ['function'])
        && !($trace ['function'] != 'App\{closure}')
        && isset ($trace ['class'])
        && !($trace ['class'] != BaseController::class)
        && isset ($trace ['args'])
        && is_array ($args = $trace ['args'])
        && $args [-1 + count ($args)] instanceof Closure) {
        $handler = Server::lambda ($args [-1 + count ($args)]);

        return call_user_func_array ($handler, []);
      }
    }
  }

  /**
   * @method Closure|boolean
   *
   * Verify if ...
   */
  private static function getViewHandlerByTrace (array $trace, int $traceId = 3) {
    if (isset ($trace[$traceId])
      && is_array ($trace[$traceId])
      && isset ($trace[$traceId]['args'])
      && is_array ($trace[$traceId]['args'])
      && count ($trace[$traceId]['args'])) {
      $lastIndex = -1 + count ($trace[$traceId]['args']);

      if (isset ($trace[$traceId]['args'][$lastIndex])
        && is_array ($trace[$traceId]['args'][$lastIndex])
        && isset ($trace[$traceId]['args'][$lastIndex]['viewHandler'])
        && $trace[$traceId]['args'][$lastIndex]['viewHandler'] instanceof Closure) {
        return $trace[$traceId]['args'][$lastIndex]['viewHandler'];
      }
    }

    return false;
  }

  /**
   * @method int|boolean
   */
  private static function getViewHandlerIdByTrace (array $trace) {
    $i = -1 + count ($trace);

    for ( ; $i >= 0; $i--) {
      $traceHasViewHandler = (boolean)(self::getViewHandlerByTrace($trace, $i));

      if ($traceHasViewHandler) {
        $args = $trace[$i]['args'];

        $lastArgIndex = -1 + count ($args);

        $lastArg = $args [$lastArgIndex];

        $layoutPath = $lastArg ['layoutPath'];

        if ($layoutPath === $trace[1]['file']) {
          return $i;
        }
      }
    }
  }
}
