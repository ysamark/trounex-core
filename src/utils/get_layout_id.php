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

use Trounex\Helper;

if (!function_exists ('get_layout_id')) {
  /**
   * @method string
   *
   * get rendering view layout id in the layout store cache
   * it should register the layout in the store if that was not registered yet
   */
  function get_layout_id (string $layoutFilePath = null) {
    $backTrace = debug_backtrace ();

    // $layoutFilePath = !empty ();

    if (!(!empty ($layoutFilePath) && is_file ($layoutFilePath))) {
      $layoutFilePath = Helper::getArrayProp ($backTrace, '1.file');
    }

    $layoutStoreCacheFilePath = join (DIRECTORY_SEPARATOR, [
      conf ('paths.cachesPath'), 'layout-store-cache.json'
    ]);

    if (!is_file ($layoutStoreCacheFilePath)) {
      $fileHandle = fopen ($layoutStoreCacheFilePath, 'w');
      $layoutFileId = generate_unique_id ();

      fwrite ($fileHandle, json_encode ([$layoutFilePath => $layoutFileId]));

      fclose ($fileHandle);

      return $layoutFileId;
    }

    $layoutStoreCache = Helper::readJsonFile ($layoutStoreCacheFilePath);

    if (is_array ($layoutStoreCache)
      && isset ($layoutStoreCache [$layoutFilePath])) {
      return $layoutStoreCache [$layoutFilePath];
    }

    $layoutStoreCache [$layoutFilePath] = generate_unique_id ();

    file_put_contents ($layoutStoreCacheFilePath, json_encode ($layoutStoreCache));

    return $layoutStoreCache [$layoutFilePath];
  }
}
