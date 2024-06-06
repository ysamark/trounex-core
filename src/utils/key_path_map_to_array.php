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
if (!function_exists ('key_path_map_to_array')) {
  /**
   * @method array
   *
   * rewrites a given array from the path by a given pattern array nested keys array
   *
   * @param array $array
   *
   * The array to rewrite
   *
   * @param string $separator
   *
   * key path separator regular expression
   *
   */
  function key_path_map_to_array (array $array, string $separator = '\\.') {
    $newArray = [];

    foreach ($array as $key => $value) {
      if (!is_string ($key)) {
        $newArray [$key] = $value;
        continue;
      }

      $keySlices = @preg_split ("/($separator)/", $key);

      if (!is_array ($keySlices)) {
        continue;
      }

      $keyArray = $value;

      $i = -1 + count ($keySlices);

      for ( ; $i >= 1; $i--) {
        $keySlice = $keySlices [$i];

        if (!empty ($keySlice)) {
          $keyArray = [$keySlice => $keyArray];
        }
      }

      $keyArray = [$keySlices [0] => $keyArray];

      $newArray = array_full_merge ($newArray, $keyArray);
    }

    return $newArray;
  }
}
