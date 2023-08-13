<?php

namespace Trounex\Repository;

use App\Router\Param;

trait RouterRepository {
  /**
   * @method GetRoutesPath ($viewsPath)
   */
  public static function GetRoutesPath ($viewsPath) {
    $routesPath = [];

    if (!(is_string ($viewsPath) && is_dir ($viewsPath))) {
      return $routesPath;
    }

    $directoryFileList = self::getDirectoryFileList ($viewsPath);

    return $directoryFileList;
  }

  /**
   * @method EvaluateRouteParams
   */
  public static function EvaluateRouteParams ($routeParamKeys, $routeParamValues) {
    $routeParams = [];

    foreach ($routeParamKeys as $routeParamKeyIndex => $routeParamKey) {
      $routeParamKeyValue = !isset ($routeParamValues [$routeParamKeyIndex]) ? null : (
        $routeParamValues [$routeParamKeyIndex]
      );

      $routeParams [$routeParamKey] = $routeParamKeyValue;
    }

    Param::MapList ($routeParams);

    return $routeParams;
  }

  /**
   * Get the list of whole the php files in a directory
   */
  protected static function getDirectoryFileList ($directoryPath) {

    $directoryPathRe = (string)($directoryPath);

    $fileList = [];
    $directoryFileList = self::readDir ($directoryPathRe);
    $routeParamRe = '/\\[([^\\]]+)\\]/';

    foreach ($directoryFileList as $directoryFile) {
      if (is_dir ($directoryFile)) {
        $fileList = array_merge ($fileList, self::getDirectoryFileList ($directoryFile));
      } elseif (in_array (pathinfo ($directoryFile, PATHINFO_EXTENSION), ['php']) &&
        preg_match_all ($routeParamRe, $directoryFile, $match)) {

        $directoryFile = realpath ($directoryFile);

        $routeData = [
          'originalFilePath' => $directoryFile,
          'routeRe' => self::routePathRe ($directoryFile),
          'match' => $match
        ];

        // echo "<pre>";
        // print_r($routeData);
        // echo "</pre>";
        array_push ($fileList, $routeData);
      }
    }

    return $fileList;
  }

  protected static function readDir ($dir) {
    $files = [];

    if (is_dir ($dir)) {
      if ($dh = opendir ($dir)) {
        while (($file = readdir ($dh)) !== false) {
          if (!in_array ($file, ['.', '..'])) {
            array_push ($files, realpath ($dir . '/' . $file));
          }
        }

        closedir($dh);
      }
    }

    return $files;
  }

  protected static function routePathRe ($directoryFile) {
    $routeParamRe = '/\\[([^\\]]+)\\]/';

    $re = preg_replace ($routeParamRe, '([^\/\\\\\\]+)', self::sanitizePathStr ($directoryFile));

    return "/^($re)$/i";
  }

  /**
   * Sanitize a given path string
   */
  protected static function sanitizePathStr ($pathStr) {
    return preg_replace_callback ('/[\/\\\\.]/', function ($match) {
      return '\\' . $match [0];
    }, $pathStr);
  }
}
