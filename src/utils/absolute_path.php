<?php

function absolute_path () {
  $args = array_filter (func_get_args (), function ($item) {
    return is_string ($item);
  });

  $absolutePath = join (DIRECTORY_SEPARATOR, $args);

  return realpath ($absolutePath);
}
