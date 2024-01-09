<?php

namespace Trounex\Repository\ServerRepository;

trait ViewsExtensionsListOrderer {
  /**
   * get views extensions list ordered from longer
   * to shorter by dots division
   */
  protected static function orderViewsExtensionsList ($viewsExtensions = null) {
    if (is_null ($viewsExtensions)) {
      $viewsExtensions = self::GetViewsFileExtensions ();
    }

    $viewsExtensions = array_filter ($viewsExtensions, function ($extension) {
      static $seen = [];

      if (!in_array ($extension, $seen)) {
        array_push ($seen, $extension);
        return true;
      }

      return false;
    });

    $longerExtension = '';
    $longerExtensionIndex = -1;

    $getStrDotsLen = function ($string) {
      if (is_string ($string)) {
        $strSplit = preg_split ('/\\./', $string);

        return -1 + count ($strSplit);
      }

      return -1;
    };

    foreach ($viewsExtensions as $viewsExtensionIndex => $viewsExtension) {
      $viewsExtensionDotsLen = $getStrDotsLen ($viewsExtension);
      $longerExtensionDotsLen = $getStrDotsLen ($longerExtension);

      if ($longerExtensionIndex < 0 || $viewsExtensionDotsLen > $longerExtensionDotsLen) {
        $longerExtension = $viewsExtension;
        $longerExtensionIndex = $viewsExtensionIndex;
      }
    }

    if ($longerExtensionIndex >= 0) {
      array_splice ($viewsExtensions, $longerExtensionIndex, 1);

      $orderedList = array_merge ([$longerExtension], self::orderViewsExtensionsList ($viewsExtensions));

      return $orderedList;
    }

    return $viewsExtensions;
  }
}
