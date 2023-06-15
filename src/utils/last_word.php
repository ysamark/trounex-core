<?php

function last_word ($str = null) {
  if (!!(is_string ($str) && !empty ($str))) {
    $strWords = preg_split ('/\s+/', $str);

    return $strWords [-1 + count ($strWords)];
  }
}
