<?php

use Symfony\Component\HttpClient\HttpClient;

function fetch ($url, $method = 'GET') {
  $client = HttpClient::create ();

  $headers = func_get_arg (-1 + func_num_args ());

  if (!is_string ($method)) {
    $method = 'GET';
  }

  if (!is_array ($headers)) {
    $headers = [];
  }

  return $client->request ($method, $url, [
    'headers' => array_merge ([
      'Content-Type' => 'application/json',
      'X-Powered-By' => 'Trounex@1.0.0'
    ], $headers)
  ]);
}
