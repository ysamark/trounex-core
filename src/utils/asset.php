<?php

function asset () {
  $arguments = func_get_args ();

  if (count ($arguments) >= 1) {
    list ($firstArgument) = $arguments;

    if (preg_match ('/^\.+\//', $firstArgument)) {
      $backTrace = debug_backtrace ();

      list ($firstTrace, $secondTrace) = $backTrace;

      $traceBaseDir = dirname ($firstTrace ['file']);

      if ($traceBaseDir === __DIR__) {
        $traceBaseDir = dirname ($secondTrace ['file']);
      }

      $assetPath = join (DIRECTORY_SEPARATOR, array_merge (
        [$traceBaseDir], $arguments
      ));
    }
  }

  $assetPath = isset ($assetPath) ? $assetPath : join (DIRECTORY_SEPARATOR, [
    App\Server::GetRootPath (),
    'assets',
    join (DIRECTORY_SEPARATOR, $arguments)
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
