<?php

namespace Trounex\Helpers;

class FormDataFileHelper {
  /**
   * @var files
   */
  private $files = [];

  public function __construct (array $files) {
    $this->files = $files;
  }

  public function readFormDataFileInput () {
    $fileFields = [];

    foreach ($this->files as $property => $value) {
      $fileFields[$property] = $this->readFormDataFileField ($value);
    }

    return $fileFields;
  }

  protected function readFormDataFileField (array $inputFieldProps) {
    if (!isset ($inputFieldProps ['tmp_name'])) {
      return null;
    }

    return $this->moveUploadedFile ($inputFieldProps ['tmp_name']);
  }

  protected function moveUploadedFile ($uploadedFileTmpName) {
    if (is_string ($uploadedFileTmpName)) {

      $uploadedFileData = FileUploadHelper::UploadFile ([
        'data' => [
          'tmp_name' => $uploadedFileTmpName
        ]
      ]);

      return $uploadedFileData->name;
    }

    if (is_array ($uploadedFileTmpName)) {
      $uploadedFileMoveResult = [];

      foreach ($uploadedFileTmpName as $key => $value) {
        $uploadedFileMoveResult [$key] = $this->moveUploadedFile ($value);
      }

      return $uploadedFileMoveResult;
    }
  }
}
