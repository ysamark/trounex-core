<?php

function asset () {
  $assetPath = join (DIRECTORY_SEPARATOR, [
    App\Server::GetRootPath (),
    'assets',
    join (DIRECTORY_SEPARATOR, func_get_args ())
  ]);

  $assetRenderTagListByExtension = [
    'js' => ['<script type="text/javascript">', '</script>'],
    'css' => ['<style type="text/css">', '</style>']
  ];


  if (is_file ($assetPath . '.php')) {
    $assetFileExtension = pathinfo ($assetPath, PATHINFO_EXTENSION);

    if (isset ($assetRenderTagListByExtension [$assetFileExtension])) {
      $renderData = $assetRenderTagListByExtension [$assetFileExtension];

      echo $renderData [0];
      include $assetPath . '.php';
      echo $renderData [1];
    }
  }


  if (!empty ($assetPath) && is_file ($assetPath)) {
    $assetPath = realpath ($assetPath);

    $assetFileContent = file_get_contents ($assetPath);
    $assetFileExtension = pathinfo ($assetPath, PATHINFO_EXTENSION);

    if (isset ($assetRenderTagListByExtension [$assetFileExtension])) {
      $renderData = $assetRenderTagListByExtension [$assetFileExtension];

      return join ('', [
        $renderData [0],
        trim ($assetFileContent),
        $renderData [1]
      ]);
    }
  }
}
