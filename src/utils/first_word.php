<?php

function first_word ($str = null) {
  if (!!(is_string ($str) && !empty ($str))) {
    return join (array_slice (preg_split ('/\s+/', $str), 0, 1), '');
  }
}
