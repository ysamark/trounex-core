<?php

use App\Server;

function path () {
  $serverPathPrefix = Server::PathPrefix ();

  $pathList = [];

  $argsList = func_get_args ();
  $argsListCount = count ($argsList);

  $stripInitialAndFinalBar = function ($path) {
    return preg_replace ('/^\/+/', '', preg_replace ('/\/+$/', '', $path));
  };

  for ($i = 0; $i < $argsListCount; $i++) {
    if (is_scalar ($argsList [$i]) 
      && !empty ($argsList [$i])) {
      array_push ($pathList, preg_replace ('/\/+$/', '', 
        preg_replace ('/\/{2,}/', '/', (string)$argsList [$i])
      ));
    }
  }

  $path = join ('/', $pathList);

  if (preg_match ($re = '/^\.(\/)[^\.]/', $path)) {
    $path = preg_replace ($re, '', $path);
  }

  /*
  if (preg_match ($re = '/^(\.{2}(\/))+/', $path, $match)) {
    $initialPath = $stripInitialAndFinalBar (
      preg_replace ('/\?(.*)$/', '', $_SERVER ['REQUEST_URI'])
    );

    echo '<pre>';
    print_r ($match);
    
    exit (0);
  }
  */

  $path = empty ($path) ? '/' : $path;

  if (!preg_match ('/^(\/)/', $path)) {
    $initialPath = join ('/', [
      '',
      $stripInitialAndFinalBar (
        preg_replace ('/\?(.*)$/', '', $_SERVER ['REQUEST_URI'])
      )
    ]);

    $pathSlices = preg_split ('/\/+/', $path);

    foreach ($pathSlices as $index => $pathSlice) {
      if ($pathSlice === '.') continue;

      if ($pathSlice === '..') {
        $initialPath = dirname ($initialPath);
      } else {
        $initialPath = join ('/', [$initialPath, $pathSlice]);
      }
    }

    return $initialPath;
  }

  return join ('', [
    '/', 
    $stripInitialAndFinalBar ($serverPathPrefix),
    '/',
    $stripInitialAndFinalBar (preg_replace ('/^\/+/', '/', $path))
  ]);
}

