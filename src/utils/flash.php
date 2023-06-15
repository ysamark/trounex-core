<?php

function flash ($message = null, string $flashType = 'error') {
  if (!isset ($_SESSION)) {
    return [];
  }

  $defaultFlash = [];
  
  if (is_string ($message) && !empty ($message)) {
    $flashData = [$message, is_string ($flashType) && !empty ($flashType) ? $flashType : 'error'];
    $_SESSION ['flash'] = isset ($_SESSION ['flash']) && is_array ($_SESSION ['flash']) ? array_merge ($_SESSION ['flash'], [$flashData]) : [$flashData];
  }

  if (!(isset ($_SESSION) 
    && is_array ($_SESSION)
    && isset ($_SESSION ['flash'])
    && is_array ($_SESSION ['flash']))) {
    return $defaultFlash;
  }
  
  return $_SESSION ['flash'];
}
