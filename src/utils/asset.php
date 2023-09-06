<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Samils\Capsule\MarkDown
 * - Autoload, application dependencies
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!function_exists ('asset')) {
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
}
