<?php

namespace App\Utils\Validator\Rules;

use Rakit\Validation\Rule;

class Image extends Rule {
  /**
   * @var string
   */
  protected $message = ':attribute musts to be a valid image file';

  /**
   * @var array
   */
  protected $fillableParams = ['imageExtensionsList'];

  /**
   * @method boolean
   */
  public function check ($value): bool {

    // echo '<pre>'; print_r ([func_get_args(), $this->getParameters ()]);

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

    $acceptedImageExtensionsList = preg_split ('/\s*;+\s*/', strtolower ($imageExtensionsList));

    $imageExtensionsMap = [
      2 => 'jpeg'
    ];

    $imageTypeName = isset ($imageExtensionsMap [$imageType]) ? $imageExtensionsMap [$imageType] : null;

    return in_array ($imageTypeName, $acceptedImageExtensionsList);
  }
}
