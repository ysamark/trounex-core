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

if (!function_exists ('resolve_image_file_extension')) {
  /**
   * Resolve a referenced image file extension
   */
  function resolve_image_file_extension (string $imageFilePath) {
    $resolveExtensionByPath = (function ($path) {
      return pathinfo ($path, PATHINFO_EXTENSION);
    });

    if (!is_file ($imageFilePath)) {
      return call_user_func ($resolveExtensionByPath, $imageFilePath);
    }

    $imageType = exif_imagetype ($imageFilePath);

    $imageExtensionsMap = [
      // 2 => 'jpeg'
      IMAGETYPE_GIF => 'GIF',
      IMAGETYPE_JPEG => 'JPEG',
      IMAGETYPE_PNG => 'PNG',
      IMAGETYPE_SWF => 'SWF',
      IMAGETYPE_PSD => 'PSD',
      IMAGETYPE_BMP => 'BMP',
      IMAGETYPE_TIFF_II => 'TIFF_II',
      IMAGETYPE_TIFF_MM => 'TIFF_MM',
      IMAGETYPE_JPC => 'JPC',
      IMAGETYPE_JP2 => 'JP2',
      IMAGETYPE_JPX => 'JPX',
      IMAGETYPE_JB2 => 'JB2',
      IMAGETYPE_SWC => 'SWC',
      IMAGETYPE_IFF => 'IFF',
      IMAGETYPE_WBMP => 'WBMP',
      IMAGETYPE_XBM => 'XBM',
      IMAGETYPE_ICO => 'ICO',
      IMAGETYPE_WEBP => 'WEBP',
      // IMAGETYPE_AVIF => 'AVIF',
    ];

    if (!(is_numeric ($imageType)
      && in_array ($imageType, array_keys ($imageExtensionsMap)))) {
      return null;
    }

    return strtolower ($imageExtensionsMap [$imageType]);
  }
}
