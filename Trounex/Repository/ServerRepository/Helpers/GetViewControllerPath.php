<?php

namespace Trounex\Repository\ServerRepository\Helpers;


trait GetViewControllerPath {
  /**
   * get view controller file path
   */
  public static function GetViewControllerPath (string $viewPath) {
    /**
     * @var array
     *
     * order the views file extensions from
     * longer to shorter according to dots
     * division
     */
    $orderedViewsExtensions = self::orderViewsExtensionsList ();

    $viewsDir = self::GetViewsPath ();
    $viewsRootDirRe = self::path2regex (self::GetViewsRootDir ());
    $viewsRootDirRe = "/^($viewsRootDirRe)/";

    if (preg_match ($viewsRootDirRe, $viewPath)) {
      $viewAbsolutePath = preg_replace ($viewsRootDirRe, $viewsDir, $viewPath);

      foreach ($orderedViewsExtensions as $viewsExtension) {
        $viewsExtensionRe = join ('', ['/(', self::path2regex ($viewsExtension), ')$/']);

        if (preg_match ($viewsExtensionRe, $viewAbsolutePath)) {
          $viewControllerFileName = preg_replace ($viewsExtensionRe, 'controller.php', $viewAbsolutePath);

          return $viewControllerFileName;
        }
      }
    }
  }
}
