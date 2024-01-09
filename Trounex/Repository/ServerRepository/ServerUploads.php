<?php

namespace Trounex\Repository\ServerRepository;

use Trounex\Helper;
use Trounex\Helpers\FileUploadHelper;

trait ServerUploads {
  /**
   * @method boolean
   *
   * verify if the request has provided a multi file upload
   *
   */
  protected static function isMultiUpload (array $fileData) {
    return (boolean)(is_array ($fileData ['name']));
  }

  protected static function getKeyValueFromFileData (array $fileData) {
    $areInt = (function () {
      $args = func_get_args ();

      for ($i = 0; $i < count ($args); $i++) {
        if (!is_int ($args [$i])) {
          return false;
        }
      }

      return func_num_args () >= 1 ? true : false;
    });

    $fileDataPath = [];
    $keys = array_keys ($fileData);

    while (!call_user_func_array ($areInt, $keys)) {
      $key = $keys [0];
      $keyData = $fileData [$key];

      array_push ($fileDataPath, $key);

      if (!is_array ($keyData)) {
        return [
          'key' => join ('.', $fileDataPath),
          'value' => $keyData
        ];
      }

      $keys = array_keys ($keyData);
      $fileData = $keyData;
    }

    return [
      'key' => join ('.', $fileDataPath),
      'value' => $fileData
    ];
  }

  /**
   * @method array
   *
   * split a given file data
   * this should return a list of arrays containing single file data
   *
   */
  protected static function splitFileData (array $fileData) {
    $fileDataList = [];

    $fileDataKeys = array_keys ($fileData);
    $fileDataCount = true;

    $key = null;
    $multiple = null;

    foreach ($fileData as $property => $propertyValue) {
      $propertyKeyValueData = self::getKeyValueFromFileData ($propertyValue);

      $key = $propertyKeyValueData ['key'];
      $value = $propertyKeyValueData ['value'];

      if (is_null ($multiple)) {
        $multiple = is_array ($value);
      }

      $value = $multiple ? $value : [$value];

      $valueLen = count ($value);

      for ($i = 0; $i < $valueLen; $i++) {
        if (!isset ($fileDataList [$i])) {
          $fileDataList [$i] = [$property => $value [$i]];
          continue;
        }

        $fileDataList [$i][$property] = $value[$i];
      }
    }

    return [
      'fileDataList' => $fileDataList,
      'multiple' => $multiple,
      'key' => $key,
    ];
  }

  /**
   * @method void
   */
  protected static function processFileField ($fileFieldProperty, $fileData, $fieldSourceKey) {
    $_FILES [$fileFieldProperty] = [null];

    if (!self::isMultiUpload ($fileData)) {
      $file = FileUploadHelper::UploadFile ([
        'data' => $fileData
      ]);

      if (!$file->error) {
        Helper::putPostData ($fieldSourceKey, $file->name);
      }

      return;
    }

    $fieldSourceValue = [];
    $fileDataSplitResult = self::splitFileData ($fileData);
    $fileDataList = $fileDataSplitResult ['fileDataList'];
    $multipleFileUpload = $fileDataSplitResult ['multiple'];
    $fileDataListKey = $fileDataSplitResult ['key'];

    $fieldSourceKeyPathFilter = (function ($path) {
      return !empty ($path);
    });

    foreach ($fileDataList as $fileData) {
      $file = FileUploadHelper::UploadFile ([
        'data' => $fileData
      ]);

      if (!$file->error) {
        array_push ($fieldSourceValue, $file->name);
      }
    }

    if (!$multipleFileUpload && is_array ($fieldSourceValue) && $fieldSourceValue) {
      $fieldSourceValue = $fieldSourceValue [0];
    }

    Helper::putPostData (join ('.', array_filter ([$fieldSourceKey, $fileDataListKey], $fieldSourceKeyPathFilter)), $fieldSourceValue);
  }
}
