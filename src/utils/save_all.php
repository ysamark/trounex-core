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

use PDOException;
use App\Models\AppModel;
use Trounex\Helpers\ModelDataSaveErrorHelper;

if (!function_exists ('save_all')) {
  function save_all () {
    $modelObjects = array_filter (func_get_args (), function ($modelObject) {
      $modelObjectClassName = get_class ($modelObject);
      $modelObjectClassParents = class_parents ($modelObjectClassName);

      return in_array (AppModel::class, $modelObjectClassParents);
    });

    foreach ($modelObjects as $index => $modelObject) {
      $modelObjectClassNamePaths = preg_split ('/\\\+/', get_class ($modelObject));
      $modelName = join ('', array_slice ($modelObjectClassNamePaths, -1 + count ($modelObjectClassNamePaths), 1));

      try {
        if (!$modelObject->save ()) {
          new ModelDataSaveErrorHelper ([
            'index' => $index,
            'message' => join (' ', [
              $modelName,
              'could not be saved'
            ])
          ]);

          return false;
        }
      } catch (PDOException $error) {
        new ModelDataSaveErrorHelper ([
          'index' => $index,
          'message' => join (' ', [
            $modelName,
            'could not be saved'
          ]),
          'error' => $error
        ]);

        return false;
      }

    }

    return true;
  }
}
