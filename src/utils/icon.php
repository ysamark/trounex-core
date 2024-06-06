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

if (!function_exists ('icon')) {
  function icon (string $iconName, string $color = 'currentColor') {
    $iconsSetRe = '/^@?(fa|ion)-/';
    $appRootDir = App\Server::GetRootPath ();

    $trounexRootDir = join (DIRECTORY_SEPARATOR, [
      $appRootDir, 'vendor', 'ysamark', 'trounex-core', 'src', 'static'
    ]);

    $rootDirs = [
      $appRootDir,
      $trounexRootDir
    ];

    $args = array_filter (func_get_args (), function ($item) {
      return is_scalar ($item);
    });

    $iconsSetNameResolvers = [
      'fa' => function (string $iconsPath, string $iconName) {
        $iconCategoryDirs = [
          'brand',
          'regular',
          'solid',
        ];

        $iconCategoryRe = '/-(brand|regular|solid)$/';

        if (preg_match ($iconCategoryRe, $iconName, $match)) {
          $iconCategory = trim ($match [1]);

          $iconFileRef = join (DIRECTORY_SEPARATOR, [
            $iconsPath, $iconCategory, preg_replace ($iconCategoryRe, '', $iconName)
          ]);

          $iconFilePath = join ('.', [$iconFileRef, 'svg']);

          return is_file ($iconFilePath) ? realpath ($iconFilePath) : null;
        }

        for ($i = 0; $i < 3; $i++) {
          $iconFileRef = join (DIRECTORY_SEPARATOR, [
            $iconsPath, $iconCategoryDirs [$i], $iconName
          ]);

          $iconFilePath = join ('.', [$iconFileRef, 'svg']);

          if (is_file ($iconFilePath)) {
            return realpath ($iconFilePath);
          }
        }
      },

      'ion' => function (string $iconsPath, string $iconName) {
        $iconFileRef = join (DIRECTORY_SEPARATOR, [
          $iconsPath, 'ionIcons', $iconName
        ]);

        $iconFilePath = join ('.', [$iconFileRef, 'svg']);

        if (is_file ($iconFilePath)) {
          return realpath ($iconFilePath);
        }
      }
    ];

    foreach ($rootDirs as $rootDir) {
      $iconsPath = join (DIRECTORY_SEPARATOR, [
        $rootDir,
        'assets',
        'images',
        '@icons',
      ]);

      $iconFilePath = null;

      if (preg_match ($iconsSetRe, $iconName, $match)) {
        $iconSet = trim ($match [1]);

        if (isset ($iconsSetNameResolvers [$iconSet])
          && is_callable ($iconsSetNameResolvers [$iconSet])) {
            $iconFilePath = call_user_func_array ($iconsSetNameResolvers [$iconSet], [
              $iconsPath, preg_replace ($iconsSetRe, '', $iconName)
            ]);
        }
      }

      /**
       * Make sure iconFilePath is a string and it's a valid
       * reference for an existing file.
       * If it's not, resolve the file path by matching the icon name
       * in the icons directory subdirectories
       */
      if (!(is_string ($iconFilePath) && is_file ($iconFilePath))) {
        $iconsDirAlternates = array_merge ([$iconsPath], glob ($iconsPath . '/*'));

        $iconsDirAlternatesCount = count ($iconsDirAlternates);

        for ($i = 0; $i < $iconsDirAlternatesCount; $i++) {
          $iconFileRef = join (DIRECTORY_SEPARATOR, [
            $iconsDirAlternates [$i], $iconName
          ]);

          $iconFilePath = join ('.', [$iconFileRef, 'svg']);

          if (is_file ($iconFilePath)) {
            break;
          }
        }
      }

      if (is_file ($iconFilePath)) {
        $re = '/^<svg\s+/i';
        $iconFileContent = file_get_contents ($iconFilePath);

        if (!empty ($color) && preg_match ($re, $iconFileContent)) {
          $iconFileContent = preg_replace ($re, "<svg fill=\"$color\" ", $iconFileContent);
        }

        return $iconFileContent;
      }
    }
  }
}
