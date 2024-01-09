<?php

namespace Trounex\Repository\ServerRepository\Helpers;

use Trounex\RouteData;

trait GetRouteData {
  /**
   * Get request route data
   *
   * it should return a RouteData object
   *
   * @return Trounex\RouteData
   */
  protected static function getRouteData () {
    $requestUrl = self::Get('uri'); # $_SERVER ['REQUEST_URI'];
    $requestMethod = strtolower(self::Get('method') /* $_SERVER ['REQUEST_METHOD'] */);

    $requestUrlSlices = preg_split ('/\?+/', $requestUrl);

    $viewsPath = self::GetViewsPath ();
    $routePath = trim (preg_replace ('/^(\/|\\\)+/', '', $requestUrlSlices [0]));
    $routePath = trim (preg_replace ('/(\/|\\\)+$/', '', $routePath));

    $routePath = preg_replace (self::$pathPrefix ['pattern'], '', $routePath);

    $routePath = '/' . preg_replace ('/^(\\/)+/', '', $routePath);
    $rootDir = realpath (self::$config ['rootDir']);

    $trounexConfigFilePath = join (DIRECTORY_SEPARATOR, [$rootDir, 'trounex.json']);

    if (is_file ($trounexConfigFilePath)) {
      $trounexConfigFileData = self::handleJSONConfigFile ($trounexConfigFilePath);

      if (isset ($trounexConfigFileData ['rewrites']) && is_array ($trounexConfigFileData ['rewrites'])) {
        $trounexRewrites = $trounexConfigFileData ['rewrites'];

        if (isset ($trounexRewrites [$routePath]) && is_string ($trounexRewrites [$routePath])) {
          $routePath = $trounexRewrites [$routePath];
        }
      }
    }

    $routeData = new RouteData($routePath);

    $routeData->setGlobalProps([
      'requestUrl' => $requestUrl,
      'requestMethod' => $requestMethod,
      'viewsPath' => $viewsPath,
      'routePath' => $routePath,
      'rootDir' => $rootDir,
    ]);

    return $routeData;
  }
}
