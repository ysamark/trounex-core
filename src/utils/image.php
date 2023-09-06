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

if (!function_exists ('image')) {
  function image () {
    $imagePathPrefixes = [
      '', 'uploads'
    ];

    foreach ($imagePathPrefixes as $imagePathPrefix) {
      $imagePath = join (DIRECTORY_SEPARATOR, [
        App\Server::GetRootPath (),
        'assets',
        'images',
        $imagePathPrefix,
        join (DIRECTORY_SEPARATOR, func_get_args ())
      ]);

      $imagePath = realpath ($imagePath);

      if (!empty ($imagePath) && is_file ($imagePath)) {
        $imagePath = realpath ($imagePath);

        $imageFileContent = file_get_contents ($imagePath);
        $imageFileExtension = pathinfo ($imagePath, PATHINFO_EXTENSION);

        return join (',', [
          'data:image/'.$imageFileExtension.';base64',
          base64_encode ($imageFileContent)
        ]);
      }
    }
  }
}
