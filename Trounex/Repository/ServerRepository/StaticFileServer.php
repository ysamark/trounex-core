<?php

namespace Trounex\Repository\ServerRepository;

use Trounex\RouteData;

trait StaticFileServer {
  /**
   * Serve a static file given its absolute path
   *
   * @param string $staticFilePath
   */
  protected static function serveStaticFile ($staticFilePath) {
    $fileExtension = pathinfo (strtolower ($staticFilePath), PATHINFO_EXTENSION);

    $mimetype = 'application/octet-stream';
    $mimetypeMap = mimetype_map ();

    if (isset ($mimetypeMap [$fileExtension])) {
      $mimetype = $mimetypeMap [$fileExtension];
    }

    @header ('X-Powered-By: Samils SY');
    @header ("Content-Type: {$mimetype}");

    exit (file_get_contents ($staticFilePath));
  }

  /**
   * verify if a given file path exists in the public directory
   *
   * @param string $staticFilePath
   */
  /**
   * verify if a given file path exists in the public directory
   *
   * @param string $staticFilePath
   */
  protected static function staticFileExists (string $staticFilePath) {
    $publicPath = self::GetPublicPath ();
    $imageUploadsPath = conf ('paths.imageUploadsPath');
    $uploadFileRe = '/^((\/)?static\/uploads\/)/';

    $staticFileAbsolutePath = join (DIRECTORY_SEPARATOR, [
      $publicPath, $staticFilePath
    ]);

    if (is_file ($staticFileAbsolutePath)) {
      return realpath ($staticFileAbsolutePath);
    }

    if (preg_match ($uploadFileRe, $staticFilePath)) {
      $uploadFileAbsolutePath = join (DIRECTORY_SEPARATOR, [
        $imageUploadsPath,
        preg_replace ($uploadFileRe, '', $staticFilePath)
      ]);

      if (is_file ($uploadFileAbsolutePath)) {
        return realpath ($uploadFileAbsolutePath);
      }
    }

    return false;
  }

  /**
   * Serve a static file given its absolute path
   *
   * @param string $staticFilePath
   */
  protected static function serveStaticFileIfExists () {
    $routeData = new RouteData;

    $staticFilePath = self::staticFileExists ($routeData->path);

    if ($staticFilePath) {
      return self::serveStaticFile ($staticFilePath);
    }
  }
}
