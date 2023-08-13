<?php

use Trounex\Helpers\FileUploadHelper;

function save_uploaded_file ($fileReference) {
  if (is_string ($fileReference)) {
    return FileUploadHelper::SaveUploadedFile ($fileReference);
  }

  if (!is_array ($fileReference)) {
    throw new Exception ('SaveUploadedFileError: first argument should be a string or an array');
  }

  foreach ($fileReference as $property => $value) {
    $valueType = gettype ($value);

    switch ($valueType) {
      case 'string':
        FileUploadHelper::SaveUploadedFile ($value);
        break;
      case 'array':
        save_uploaded_file ($value);
        break;
    }
  }
}
