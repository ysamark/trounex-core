<?php

function url ($path = null) {
  $path = path ($path);
  $url = "http://";

  if (isset ($_SERVER ['HTTPS']) && $_SERVER ['HTTPS'] === 'on') {
    $url = "https://";   
  }

  // Append the host(domain name, ip) to the URL.   
  $url .= $_SERVER ['HTTP_HOST'];
  
  if (!in_array ((float)$_SERVER ['SERVER_PORT'], [80])
    && !preg_match ('/:([0-9]+)$/', $url)) {
    $url .= $_SERVER ['SERVER_PORT'];
  }

  return $url . $path;
}
