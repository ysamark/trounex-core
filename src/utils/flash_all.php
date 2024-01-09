<?php

use Rakit\Validation\ErrorBag;

function flash_all ($errors) {
  if ($errors instanceof ErrorBag) {
    $errors = $errors->all();
  }

  if (is_array ($errors)) {
    foreach ($errors as $error) {
      $type = 'error';
      $message = $error;

      if (is_array ($error)) {
        $message = isset ($error[0]) && is_string ($error[0]) ? $error[0] : (
          isset ($error['message']) && is_string ($error['message']) ? $error['message'] : $message
        );
        $type = isset ($error[1]) && is_string ($error[1]) ? $error[1] : (
          isset ($error['type']) && is_string ($error['type']) ? $error['type'] : $type
        );
      }

      flash($message, $type);
    }
  }
}
