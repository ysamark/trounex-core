<?php

namespace Trounex\View\ViewEngine;

class DefaultViewEngine extends ViewEngine {
  /**
   * @method void
   */
  public function render () {
    $renderScopeFunc = function (string $__viewFileName) {
      $vars = $this->getAllProps ();

      foreach ($vars as $key => $var) {
        $varName = is_array ($var) ? $var ['name'] : $key;
        $value = is_array ($var) ? $var ['value'] : $vars [$key];

        if (preg_match ('/^([a-zA-Z0-9_]+)$/', $varName)) {
          $$varName = $value;
        }
      }

      include ($__viewFileName);
    };

    $renderScope = Closure::bind ($renderScopeFunc, $this->context, get_class ($this->context));

    call_user_func_array ($renderScope, [$this->viewFileName]);
  }
}
