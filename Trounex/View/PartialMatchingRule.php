<?php

namespace Trounex\View;

use Trounex\Auth;
use Trounex\Helper;

class PartialMatchingRule {
  /**
   * @method array
   */
  public static function getRules () {
    return [
      /**
       * role oriented partial (ROP)
       */
      '/(^(\$)?\((.+)\)(\$)?$)/' => function (string $partialFileName, $match) {
        $orientationKey = 'role';
        $orientationKeyRe = '/^(role|permission|if)#/i';
        $endRe = empty ($match [4]) ? '' : '$';
        $startRe = empty ($match [2]) ? '' : '^';
        $orientationData = trim ($match [3]);

        if (preg_match ($orientationKeyRe, $orientationData, $orientationKeyMatch)) {
          $orientationData = preg_replace ($orientationKeyRe, '', $orientationData);
          $orientationKey = trim ($orientationKeyMatch [1]);
        }

        switch (strtolower ($orientationKey)) {
          case 'role':
            $orientationData = preg_replace ('/\.+/', ':', camel_to_any_case ($orientationData, '.'));
            $authenticatedUserRole = role ();

            if (!$authenticatedUserRole) {
              return false;
            }

            $re = join ('', ['/' . $startRe . '(', $orientationData, ')' . $endRe . '/i']);

            return (boolean)($authenticatedUserRole && preg_match ($re, $authenticatedUserRole->key));

          case 'permission':
            $orientationData = preg_replace ('/\.+/', ':', camel_to_any_case ($orientationData, '.'));
            $authenticatedUserRole = role ();

            if (!$authenticatedUserRole) {
              return false;
            }

            $userPermissions = $authenticatedUserRole->permissions ()
              ->where (['key' => $orientationData]);

            return $userPermissions->count () >= 1;

          case 'if':
            $trueConditionsCount = 0;
            $partialFileGuardList = preg_split ('/\\#+/', $orientationData);
            $totalConditionsCount = count ($partialFileGuardList);

            foreach ($partialFileGuardList as $partialFileGuard) {
              $guardName = $partialFileGuard;

              if (self::matchGuard ($guardName)) {
                $trueConditionsCount++;
              }
            }

            return ($trueConditionsCount === $totalConditionsCount);

          default:
            return false;
        }
      },

      /**
       * for authenticated users
       */
      '/^(auth(|enticated))$/i' => function () {
        return Auth::authenticated ();
      },

      /**
       * for no authenticated users
       */
      '/^((not?|un)auth(|enticated))$/i' => function () {
        return !Auth::authenticated ();
      },

      /**
       * Default
       */
      '/^(default|\s*)$/i' => function (string $partialFileName, $match) {
        return true;
      },
    ];
  }

  /**
   * @method string
   *
   * get a guard class reference by a given guard name
   *
   */
  protected static function getGuardClassRef (string $guardName, string $suffix = '') {
    return join ('\\', [
      'App',
      'Views',
      'Partials',
      'Guards',
      join ('', [ucfirst ($guardName), $suffix])
    ]);
  }

  /**
   * @method bool
   *
   * verify if a given guard matches the requested partial file
   *
   */
  protected static function matchGuard (string $guardName) {
    $abstractGuardClassRef = self::getGuardClassRef('Guard');

    $guardClassRefSuffixAlternates = [
      '',
      'Guard'
    ];

    foreach ($guardClassRefSuffixAlternates as $suffix) {
      $guardClassRef = self::getGuardClassRef ($guardName, $suffix);

      if (class_exists ($guardClassRef)
        && in_array ($abstractGuardClassRef, class_parents ($guardClassRef))) {
        $guardObject = new $guardClassRef;

        $guardPasses = call_user_func_array ([$guardObject, 'handler'], [
          []
        ]);

        if ($guardPasses) {
          return true;
        }
      }
    }

    return false;
  }
}
