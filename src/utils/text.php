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
use Trounex\Helper;

if (!function_exists ('text')) {
  function text (string $key) {
    $args = array_slice (func_get_args (), 1, func_num_args ());
    $systemLanguageData = application_language_data ();
    $snakeCaseKey = camel_to_snake_case ($key);

    $keyPathAlternates = [
      $key,
      "text.$key",
      $snakeCaseKey,
      "text.$snakeCaseKey",
    ];

    foreach ($keyPathAlternates as $keyPath) {
      $text = isset ($systemLanguageData [$keyPath])
        ? $systemLanguageData [$keyPath]
        : Helper::getArrayProp ($systemLanguageData, $keyPath);

      $textParameterRe = '/\$([0-9]+)(\(([^\)]+)\))?/';

      if (is_string ($text)) {
        $textReplacer = (function ($match) use ($args) {
          $dataParameterIndex = (int)($match [1]);

          $dataParameterValue = isset ($args [$dataParameterIndex])
            ? $args [$dataParameterIndex]
            : null;

          if (count ($match) < 3) {
            return $dataParameterValue;
          }

          $linkLabel = isset ($match [3]) ? $match [3] : $dataParameterValue;

          if (is_array ($dataParameterValue)) {
            $dataParameterValue = join ('/', $dataParameterValue);
          }

          $linkUrl = path ($dataParameterValue);

          return "<a href=\"$linkUrl\">$linkLabel</a>";
        });

        return preg_replace_callback ($textParameterRe, $textReplacer, $text);
      }
    }
  }
}
