<?php

namespace Trounex\Helpers;

class FileUploadHelper {
  /**
   * @var array
   *
   * uploaded file props
   */
  private $props;

  /**
   * @var array
   *
   * A list of files that was been uploaded
   * mapped by fileName => fileAbsolutePath
   */
  private static $uploadedFilesList = [];

  public function __construct (array $props) {
    $this->props = $props;
  }

  public function __get (string $prop) {
    return isset ($this->props [$prop]) ? $this->props [$prop] : null;
  }

  public function __isset (string $prop) {
    return isset ($this->props [$prop]);
  }

  /**
   * @method mixed
   */
  public static function UploadFile (array $fileData) {
    $fileName = self::generateFileNameIfNull ($fileData);

    $fileAbsolutePath = join (DIRECTORY_SEPARATOR, [
      conf('paths.tmpUploadsPath'), $fileName
    ]);

    $props = [
      'error' => true
    ];

    if (@move_uploaded_file ($fileData ['data']['tmp_name'], $fileAbsolutePath)) {
      self::$uploadedFilesList [$fileName] = $fileAbsolutePath;

      $props = [
        'name' => $fileName,
        'path' => $fileAbsolutePath
      ];
    }

    return new static ($props);
  }

  /**
   * @method array
   */
  public static function GetUploadedFilesList () {
    return self::$uploadedFilesList;
  }

  /**
   * @method void
   */
  public static function SaveUploadedFile (string $uploadedFileName) {
    if (isset (self::$uploadedFilesList [$uploadedFileName])) {
      $uploadedFileAbsolutePath = self::$uploadedFilesList [$uploadedFileName];

      $uploadedFileNewAbsolutePath = join (DIRECTORY_SEPARATOR, [
        conf('paths.imageUploadsPath'), $uploadedFileName
      ]);

      if (@rename ($uploadedFileAbsolutePath, $uploadedFileNewAbsolutePath)) {
        return true;
      }
    }
  }

  /**
   * @method string
   */
  private static function generateFileNameIfNull ($fileData) {
    if (isset ($fileData ['fileName']) && is_string ($fileData ['fileName']) && !empty ($fileData ['fileName'])) {
      return $fileData ['fileName'];
    }

    $uploadedFileData = isset ($fileData ['data']) ? $fileData ['data'] : [];

    $uploadedFileExtension = pathinfo ($uploadedFileData ['name'], PATHINFO_EXTENSION);

    $namePrefix = join ('', ['1000', rand (111111, 99999999)]);

    $name = join ('.', [
      join ('', [$namePrefix, uuid ()]), $uploadedFileExtension
    ]);

    return strtolower ($name);
  }
}
