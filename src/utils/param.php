<?php

use App\Router\Param;

/**
 * Get a given parameter key value
 * 
 * @method param
 * 
 * @param string $paramKey
 */
function param ($paramKey = null) {
  if (is_string ($paramKey)) {
    $param = new Param ();

    return $param->$paramKey;
  }
}
