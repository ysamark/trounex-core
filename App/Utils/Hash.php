<?php

namespace App\Utils;

class Hash {
  /**
   * create a hash from string
   */
  public static function Make ($password = null) {
    if (!(is_string ($password) && $password)) {
      return false;
    }

    return password_hash ($password, PASSWORD_DEFAULT);
  }
}
