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
use Rakit\Validation\Validator;

if (!function_exists ('form_validator')) {
  function form_validator (array $formData = null, array $formDataRules = null) {
    $validator = new Validator;

    $camel2snakeCase = function ($input) {
      $pattern = '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!';
      preg_match_all ($pattern, $input, $matches);
      $ret = $matches[0];
      foreach ($ret as &$match) {
        $match = $match == strtoupper ($match) ?
            strtolower ($match) :
          lcfirst ($match);
      }
      return implode('_', $ret);
    };

    $ruleClassFileListArr = [
      glob (Helper::GetModuleRootDir () . '/App/Utils/Validator/Rules/*.php'),
      glob (conf ('rootDir') . '/App/Utils/Validator/Rules/*.php')
    ];

    $ruleClassFileListArr = array_filter ($ruleClassFileListArr, function ($arr) {
      return is_array ($arr);
    });

    $ruleClassFileList = call_user_func_array ('array_merge', $ruleClassFileListArr);

    foreach ($ruleClassFileList as $ruleClassFile) {
      $ruleClassFileName = pathinfo ($ruleClassFile, PATHINFO_FILENAME);
      $ruleName = $camel2snakeCase ($ruleClassFileName);
      $ruleClassName = "App\Utils\Validator\Rules\\{$ruleClassFileName}";

      if (class_exists ($ruleClassName)) {
        $validator->addValidator ($ruleName, new $ruleClassName);
      }
    }

    if ($formData && $formDataRules) {
      $validation = $validator->validate ($formData, $formDataRules);

      $validation->validate ();

      return $validation;
    }

    return $validator;
  }
}
