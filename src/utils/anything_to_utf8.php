<?php

function anything_to_utf8 ($var, $deep=TRUE) {
  if (is_array ($var)){
    foreach ($var as $key => $value) {
      if ($deep) {
        $var [$key] = anything_to_utf8 ($value, $deep);
      } elseif (!is_array ($value) && !is_object ($value) && !mb_detect_encoding ($value, 'utf-8', true)){
        $var[$key] = utf8_encode($var);
      }
    }

    return $var;
  } elseif (is_object ($var)) {
    foreach ($var as $key => $value) {
      if ($deep) {
        $var->$key = anything_to_utf8 ($value,$deep);
      } elseif (!is_array ($value)
        && !is_object ($value)
        && !mb_detect_encoding ($value, 'utf-8', true)) {
        $var->$key = utf8_encode($var);
      }
    }

    return $var;
  } else {
    return (!mb_detect_encoding ($var, 'utf-8', true)) ? utf8_encode ($var) : $var;
  }
}
