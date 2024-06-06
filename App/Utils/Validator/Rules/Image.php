<?php

namespace App\Utils\Validator\Rules;

use Rakit\Validation\Rule;

class Image extends Rule {
  /**
   * @var string
   */
  protected $message = ':attribute musts to be a valid and supported image file';

  /**
   * @var array
   */
  protected $fillableParams = ['imageExtensionsList'];

  /**
   * @method boolean
   */
  public function check ($value): bool {

    $imageAbsolutePath = join (DIRECTORY_SEPARATOR, [
      conf('paths.tmpUploadsPath'), $value
    ]);

    $imageType = exif_imagetype ($imageAbsolutePath);

    if (!(is_file ($imageAbsolutePath) && is_numeric ($imageType))) {
      return false;
    }

    $imageExtensionsList = $this->parameter ('imageExtensionsList');

    if (!(is_string ($imageExtensionsList) && !empty ($imageExtensionsList))) {
      return true;
    }

    $acceptedImageExtensionsList = preg_split ('/\s*;+\s*/', strtoupper (trim ($imageExtensionsList)));

    $imageExtensionsMap = [
      // 2 => 'jpeg'
      IMAGETYPE_GIF => 'GIF',
      IMAGETYPE_JPEG => 'JPG',
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

    $imageTypeName = isset ($imageExtensionsMap [$imageType]) ? $imageExtensionsMap [$imageType] : null;

    return in_array ($imageTypeName, $acceptedImageExtensionsList);
  }
}
