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

use Trounex\Application\Config;

if (!function_exists ('conf')) {
  function conf ($props = null) {
    $config = new Config ($props);

    if (is_array ($props) || is_null ($props)) {
      return $config;
    }

    if (!is_string ($props)) {
      throw new Exception ('TypeError: first argument for the conf helper should be a string or an array, leave it null to use the default configs');
    }

    $propKeyMap = preg_split ('/\.+/', preg_replace ('/\s+/', '', $props));
    $propKeyMapLastIndex = -1 + count ($propKeyMap);

    foreach ($propKeyMap as $index => $propKey) {
      if (is_object ($config) && isset ($config->$propKey)) {
        if ($index >= $propKeyMapLastIndex) {
          return Config::ReadConfigValue ($config->$propKey);
        }

        $config = $config->$propKey;
      } elseif (is_array ($config) && isset ($config [$propKey])) {
        if ($index >= $propKeyMapLastIndex) {
          return Config::ReadConfigValue ($config [$propKey]);
        }

        $config = $config [$propKey];
      } else {
        throw new Exception ('TypeError: type of config('.gettype($config).') property does not support nested keys');
      }
    }
  }
}
