<?php

use App\Server;

function asset_path () {
  $serverPathPrefix = Server::PathPrefix ();

  $assetPaths = [];

  $argsList = func_get_args ();
  $argsListCount = count ($argsList);

  for ($i = 0; $i < $argsListCount; $i++) {
    if (is_string ($argsList [$i]) 
      && !empty ($argsList [$i])) {
      array_push ($assetPaths, $argsList [$i]);
    }
  }

  $asset = join ('/', $assetPaths);

  return join ('', [
    '/', 
    preg_replace ('/^\/+/', '', $serverPathPrefix),
    '/assets/',
    preg_replace ('/^\/+/', '', $asset)
  ]);
}
