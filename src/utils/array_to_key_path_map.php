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
if (!function_exists ('array_to_key_path_map')) {
  /**
   * @method array
   * 
   * rewrites a given array nested keys in the path by a given pattern
   * 
   * @param array $array
   * 
   * The array to rewrite
   * 
   * @param string $keyChildPattern
   * 
   * the pattern to rewrite the given array key children list
   * 
   */
  function array_to_key_path_map (array $array, string $keyChildPattern = '.$0', string $keyPrefix = '') {
    $newArray = [];

    foreach ($array as $key => $value) {

      if (is_int ($key)) {
        if (!empty ($keyPrefix)) {
          # make sure the $keyPrefix references an existing 
          # array in the the $newArray array
          # so, assign it otherwise
          if (!(isset ($newArray [$keyPrefix]) 
            && is_array ($newArray [$keyPrefix]))) {
            $newArray [$keyPrefix] = [];
          }

          array_push ($newArray [$keyPrefix], $value);

          continue;
        }

        array_push ($newArray, $value);

        continue;
      }

      $currentKey = empty ($keyPrefix)
        ? $key
        : join ('', [
          $keyPrefix, 
          preg_replace ('/\\$([0-9])/', $key, $keyChildPattern)
        ]);

      if (!is_array ($value)) {
        $newArray [$currentKey] = $value;

        continue;
      }

      $currentArray = array_to_key_path_map ($value, $keyChildPattern, $currentKey);

      $newArray = array_merge ($newArray, $currentArray);
    }

    return $newArray;
  }
}
