<?php

use App\Server;

function extend_layout (string $viewLayoutRelativePath, Closure $viewHandler) {
  $viewLayoutRelativePath = preg_replace('/(\.php)$/i', '', $viewLayoutRelativePath);
  $relativePathRe = '/^((\.)+\/)/';

  $viewLayoutBasePath = Server::GetLayoutsPath ();

  if (preg_match ($relativePathRe, $viewLayoutRelativePath)) {
    $backTrace = debug_backtrace ();

    if (isset ($backTrace[0]) && is_array ($backTrace[0]) && isset ($backTrace[0]['file'])) {
      $viewLayoutBasePath = dirname ($backTrace[0]['file']);
    }
  }
  
  $viewLayoutAbsolutePath = join (DIRECTORY_SEPARATOR, [
    $viewLayoutBasePath, join('.', [
      $viewLayoutRelativePath, 'php'
    ])
  ]);

  if (!is_file ($viewLayoutAbsolutePath)) {
    exit ('Trounex Error :: Could not extend layout ' . $viewLayoutRelativePath);
  }

  Server::LoadView ($viewLayoutAbsolutePath, [
    'viewHandler' => Server::lambda ($viewHandler),
    'layout' => $viewLayoutRelativePath,
    'layoutPath' => realpath($viewLayoutAbsolutePath)
  ]);
}
