<?php

/**
 * Get an input field default value and asign it
 *
 * @param string $inputRef
 */
function inputDefaultValue (string $inputRef = null) {
  $paramValue = post_param ($inputRef);

  if (!is_null ($paramValue)) {
    return ' value="' . $paramValue . '" ';
  }
}
