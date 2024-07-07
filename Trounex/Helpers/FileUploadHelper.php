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
    $clientDeviceId = get_client_device_id ();
    $fileName = self::generateFileNameIfNull ($fileData);

    $fileAbsolutePath = join (DIRECTORY_SEPARATOR, [
      conf('paths.tmpUploadsPath'), "$fileName.$clientDeviceId"
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

      $uploadedFileDestinationDirectory = self::resolveUploadedFileDestinationDirectory ($uploadedFileName);

      $uploadedFileNewAbsolutePath = join (DIRECTORY_SEPARATOR, [
        $uploadedFileDestinationDirectory, $uploadedFileName
      ]);

      if (@rename ($uploadedFileAbsolutePath, $uploadedFileNewAbsolutePath)) {
        return true;
      }
    }

    return false;
  }

  /**
   * @method void
   */
  public static function DeleteUploadedFile (string $uploadedFileName) {
    $uploadedFileDestinationDirectory = self::resolveUploadedFileDestinationDirectory ($uploadedFileName);

    $uploadedFileAbsolutePath = join (DIRECTORY_SEPARATOR, [
      $uploadedFileDestinationDirectory, $uploadedFileName
    ]);

    if (is_file ($uploadedFileAbsolutePath) && @unlink (realpath ($uploadedFileAbsolutePath))) {
      return true;
    }

    return false;
  }

  /**
   * @method string
   */
  public static function generateFileNameIfNull ($fileData) {
    if (isset ($fileData ['fileName']) && is_string ($fileData ['fileName']) && !empty ($fileData ['fileName'])) {
      return $fileData ['fileName'];
    }

    $uploadedFileData = isset ($fileData ['data']) ? $fileData ['data'] : [];

    $uploadedFileExtension = (isset ($uploadedFileData ['tmp_name']) && is_file ($uploadedFileData ['tmp_name']))
      ? resolve_image_file_extension ($uploadedFileData ['tmp_name'])
      : null;

    if (empty ($uploadedFileExtension)) {
      $uploadedFileExtension = pathinfo ($uploadedFileData ['name'], PATHINFO_EXTENSION);
    }

    $namePrefix = join ('', ['1000', rand (111111, 99999999)]);

    $name = join (!empty ($uploadedFileExtension) ? '.' : '', [
      join ('-', [$namePrefix, uuid ()]), $uploadedFileExtension
    ]);

    return strtolower ($name);
  }

  /**
   * @method string
   *
   * Resolve an uploaded file destination directory based on the given file name
   * It should resolve by mapping a list of file extensions and assume the file
   *  type the file extension, then get the directory path according to this data.
   *
   */
  public static function resolveUploadedFileDestinationDirectory (string $uploadedFileName) {
    $uploadedFileExtension = strtolower (pathinfo ($uploadedFileName, PATHINFO_EXTENSION));

    $mimeTypeMap = mimetype_map ();

    $uploadedFileMimeType = isset ($mimeTypeMap [$uploadedFileExtension])
      ? strtolower ($mimeTypeMap [$uploadedFileExtension])
      : 'application/octet-stream';

    $uploadedFileType = join ('', array_slice (preg_split ('/(\/)/', $uploadedFileMimeType) , 0, 1));

    $uploadedFileDestinationDirectoryMap = [
      'image' => conf ('paths.imageUploadsPath'),
      'video' => conf ('paths.videoUploadsPath'),
      'audio' => conf ('paths.audioUploadsPath'),
      'document' => conf ('paths.documentUploadsPath'),
      'application' => conf ('paths.applicationUploadsPath'),
      'text' => call_user_func (function () {
        $textUploadsPath = conf ('paths.textUploadsPath');

        if (is_dir ($textUploadsPath)) {
          return $textUploadsPath;
        }

        return conf ('paths.documentUploadsPath');
      }),
    ];

    $isExistingFileUploadDestinationPath = (boolean)(
      isset ($uploadedFileDestinationDirectoryMap [$uploadedFileType]) &&
      is_dir ($uploadedFileDestinationDirectoryMap [$uploadedFileType])
    );

    return ($isExistingFileUploadDestinationPath)
      ? $uploadedFileDestinationDirectoryMap [$uploadedFileType]
      : conf ('paths.uploadsPath');
  }
}
