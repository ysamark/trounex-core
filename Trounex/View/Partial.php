<?php

namespace Trounex\View;

use App\Server;
use Trounex\Auth;
use Trounex\Helper;

class Partial {
  /**
   * @method boolean
   *
   * verify if a partial exists by a given reference
   *
   * @param string $partialReference
   *
   */
  public static function exists (string $partialReference) {
    $partialFilePath = self::path ($partialReference);

    return (boolean)(!empty ($partialFilePath) && is_file ($partialFilePath));
  }

  /**
   * @method string|null
   *
   * get a partial file path by a given reference
   *
   */
  public static function path (string $partialReference) {
    $partialDir = join (DIRECTORY_SEPARATOR, [
      Server::GetRootPath (),
      'views',
      '_partials',
      dirname ($partialReference)
    ]);

    $partialFileExtension = pathinfo ($partialReference, PATHINFO_EXTENSION);
    $partialFileName = pathinfo ($partialReference, PATHINFO_FILENAME);

    $partialAbsolutePathAlternates = array_merge (glob ("$partialDir/$partialFileName.*.php"), ["$partialDir/$partialFileName.php"]);

    $partialFileMatchRules = PartialMatchingRule::getRules ();

    $partialAbsolutePathAlternatesCount = count ($partialAbsolutePathAlternates);

    foreach ($partialFileMatchRules as $rule => $applier) {
      $i = 0;

      for ( ; $i < $partialAbsolutePathAlternatesCount; $i++) {

        if (!is_file ($partialAbsolutePathAlternates [$i])) {
          continue;
        }

        $partialAbsolutePathAlternate = preg_replace ('/\.php$/i', '', $partialAbsolutePathAlternates [$i]);
        $partialAbsolutePathAlternateFileName = pathinfo ($partialAbsolutePathAlternate, PATHINFO_EXTENSION);

        if (preg_match ($rule, $partialAbsolutePathAlternateFileName, $match)) {
          $data = call_user_func_array ($applier, [$partialAbsolutePathAlternateFileName, $match]);

          if ($data) {
            return realpath ($partialAbsolutePathAlternates [$i]);
          }
        }
      }
    }
  }
}
