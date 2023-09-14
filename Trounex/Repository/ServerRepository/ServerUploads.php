<?php

namespace Trounex\Repository\ServerRepository;

use Trounex\Helper;
use Trounex\Helpers\FileUploadHelper;

trait ServerUploads {
  /**
   * @method void
   */
  protected static function processFileField ($fileFieldProperty, $fileData, $fieldSourceKey) {
    $_FILES [$fileFieldProperty] = [null];

    $file = FileUploadHelper::UploadFile ([
      'data' => $fileData
    ]);

    if (!$file->error) {
      Helper::putPostData ($fieldSourceKey, $file->name);
    }
  }
}
