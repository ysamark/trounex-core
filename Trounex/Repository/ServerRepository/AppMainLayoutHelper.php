<?php

namespace Trounex\Repository\ServerRepository;

trait AppMainLayoutHelper {
  /**
   * get the main layout path
   */
  protected static function mainLayoutView () {
    if (is_string (self::$viewLayout)
      && is_file (self::$viewLayout)) {
      return self::$viewLayout;
    }

    $viewsExtensions = conf ('viewEngine.options.extensions');
    $viewsRootDir = conf ('viewEngine.options.rootDir');

    $layoutsDirPath = self::GetLayoutsPath ();
    $viewsDirPathRe = self::path2regex ($viewsRootDir);

    $viewPath = self::GetViewPath ();

    if (!is_array ($viewsExtensions)) {
      $viewsExtensions = [];
    }

    $viewLayoutRelativePath = preg_replace("/^($viewsDirPathRe)(\\/|\\\\)*/i", '', $viewPath);

    $viewLayoutRelativePathSlices = preg_split('/(\/|\\\\)+/', $viewLayoutRelativePath);

    foreach ($viewsExtensions as $viewsExtension) {
      $viewLayoutRelativePathSlicesLen = count ($viewLayoutRelativePathSlices);

      for ($i = 0; $i < $viewLayoutRelativePathSlicesLen; $i++) {
        $viewLayoutRelativePath = dirname ($viewLayoutRelativePath);

        $viewLayoutAbsolutePath = join (DIRECTORY_SEPARATOR, [
          $layoutsDirPath, join ('.', [$viewLayoutRelativePath, $viewsExtension])
        ]);

        if (is_file ($viewLayoutAbsolutePath)) {
          return $viewLayoutAbsolutePath;
        }
      }

      $viewLayoutAbsolutePath = join (DIRECTORY_SEPARATOR, [
        $layoutsDirPath, "app.$viewsExtension"
      ]);

      if (is_file ($viewLayoutAbsolutePath)) {
        return $viewLayoutAbsolutePath;
      }
    }

    exit ("Could not load main layout");
  }
}
