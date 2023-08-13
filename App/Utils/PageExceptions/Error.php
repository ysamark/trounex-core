<?php

namespace App\Utils\PageExceptions;

use Trounex\Helper;

class Error {
  /**
   * Throw any thing
   */
  public static function __callStatic ($pageReference, $arguments) {
    # Throw404
    $pageReferenceRe = '/^throw([0-9]+)$/i';

    if (preg_match ($pageReferenceRe, $pageReference, $pageReferenceMatch)) {
      list ($exceptionStatusCode) = array_slice ($pageReferenceMatch, 1, 1);

      $exceptionStatusCode .= '.php';

      $pageAbsolutePath = join (DIRECTORY_SEPARATOR, [
        Helper::getModuleRootDir (), 'src', 'static', 'pages', $exceptionStatusCode
      ]);

      if (is_file ($pageAbsolutePath)) {
        call_user_func_array (function () {
          include func_get_arg (0);
        }, [$pageAbsolutePath]);

        exit (0);
      }
    }
  }
}
