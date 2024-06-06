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
if (!function_exists ('generate_unique_slags')) {
  /**
   * generate a unique slag by a list of registered slag and a given title/name (data)
   */
  function generate_unique_slags (string $data, array $registeredSlags, int $quantity = 1) {
    $slags = [];

    $alternatesMapper = (function ($data) {
      return $data . str_repeat ((string)(rand(0, 999 * 999)), rand(1, 3));
    });

    $registeredSlagsMapper = (function ($registeredSlag) {
      return (is_string ($registeredSlag) ? strtolower ($registeredSlag) : null);
    });

    $registeredSlags = array_map ($registeredSlagsMapper, $registeredSlags);

    for ($i = 1; $i <= $quantity; $i++) {

      $data = preg_replace ('/(^\_+|\_+$)/', '', preg_replace ('/[^a-zA-Z0-9_]+/', '_', strtolower (strip_accents ($data))));

      $alternates = [
        $data,
        snake_to_camel_case ($data),
        preg_replace ('/_+/', '-', $data),
        preg_replace ('/_+/', '.', $data),
        preg_replace ('/_+/', '', $data),
        snake_to_camel_case (join ('_', array_reverse (preg_split ('/_+/', $data)))),
        join ('-', array_reverse (preg_split ('/_+/', $data))),
        join ('.', array_reverse (preg_split ('/_+/', $data))),
        join ('', array_reverse (preg_split ('/_+/', $data))),
      ];

      $alternates = array_merge ($alternates, array_map ($alternatesMapper, $alternates));

      foreach ($alternates as $slag) {
        $slag = strtolower ($slag);

        if (!(in_array ($slag, $slags)
          || in_array ($slag, $registeredSlags)
          || count ($slags) >= $quantity)) {
          array_push ($slags, $slag);
        }
      }
    }

    return $slags;
  }
}
