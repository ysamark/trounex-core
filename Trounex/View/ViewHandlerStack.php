<?php

namespace Trounex\View;

abstract class ViewHandlerStack {
  /**
   * @var array<ViewHandler>
   */
  private static $stack = [];

  /**
   * @method void
   *
   * add a new item at the end of the stack
   *
   */
  public static function push (ViewHandler $viewHandler) {
    array_push (self::$stack, $viewHandler);
  }

  /**
   * @method ViewHandler
   *
   * remove the last item of the stack and return it
   *
   */
  public static function pop () {
    if (count (self::$stack) < 1) {
      return;
    }

    $lastItemIndex = -1 + count (self::$stack);
    $lastItem = self::$stack [$lastItemIndex];

    array_splice (self::$stack, $lastItemIndex, 1);

    return $lastItem;
  }
}
