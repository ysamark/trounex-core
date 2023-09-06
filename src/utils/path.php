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

use App\Server;

if (!function_exists ('path')) {
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

    $serverRequestUri = isset ($_SERVER ['REQUEST_URI']) ? $_SERVER ['REQUEST_URI'] : '/';
    $path = empty ($path) ? '/' : $path;

    if (!preg_match ('/^(\/)/', $path)) {
      $initialPath = join ('/', [
        '',
        $stripInitialAndFinalBar (
          preg_replace ('/\?(.*)$/', '', $serverRequestUri)
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
}
