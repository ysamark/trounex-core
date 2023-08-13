<?php

namespace App\Utils;

use Exception;

class ErrorHandler {
  /**
   * handler
   */
  public static function handle (string $message) {
    $originalBackTrace = debug_backtrace();

    $backTrace = array_slice($originalBackTrace, 1, count ($originalBackTrace));

    $trace = $backTrace[0];

    $completeMessage = join ('', [
      $message, 
      ' at ', 
      (isset($trace['file']) ? $trace['file'] : ''), 
      ':', 
      (isset($trace['line']) ? $trace['line'] : '')
    ]);

    throw new Exception($completeMessage);
  }
}
