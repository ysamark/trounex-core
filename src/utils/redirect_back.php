<?php

function redirect_back () {
  $path = path ('/');
  
  if (isset ($_SERVER ['HTTP_REFERER'])
    && is_string ($_SERVER ['HTTP_REFERER'])
    && !empty ($_SERVER ['HTTP_REFERER'])) {
    $path = trim ($_SERVER ['HTTP_REFERER']);
  }

  @header ('location: ' . $path);

  exit (0);
}
