<?php

namespace Trounex;

use App\Server;

const AN_HOUR = 3600;
const A_DAY = AN_HOUR * 24;

class Cookie {
  /**
   * @var string
   */
  private const COOKIE_NAME_PATTERN = '__Host-data-$0-$1';

  /**
   * @var string
   *
   * 5 Days
   *
   */
  private const DEFAULT_COOKIE_EXPIRE_TIME = A_DAY * 5;

  /**
   * @var string
   *
   * Default cookie path
   *
   */
  private const DEFAULT_COOKIE_PATH = '/';

  /**
   * @method void
   *
   * Define a new cookie
   *
   */
  public static function set (string $cookieName, $cookieValue = null) {
    $cookiePath = self::DEFAULT_COOKIE_PATH;
    $cookieName = self::rewriteCookieName ($cookieName);
    $cookieExpireTime = time() + self::DEFAULT_COOKIE_EXPIRE_TIME;

    $cookieValue = Helper::stringify ($cookieValue);

    setcookie (
      $cookieName,
      $cookieValue,
      $cookieExpireTime,
      $cookiePath,
      (Server::isHttps ()
        ? null
        : Server::Get ('name')),
      true,
      true
    );
  }

  /**
   * @method mixed
   *
   * get a cookie
   *
   */
  public static function get (string $cookieName) {
    return Server::GetCookie ($cookieName);
  }

  /**
   * @method string
   *
   * get trounex app cookie name
   *
   */
  private static function rewriteCookieName (string $cookieName) {
    $appName = conf ('appName');

    if (!(is_string ($appName) && !empty ($appName))) {
      $appName = Env::Get ('TROUNEX_APP_NAME', 'Trounex');
    }

    $cookieNamePattern = self::COOKIE_NAME_PATTERN;

    if (!Server::isHttps ()) {
      $cookieNamePattern = preg_replace ('/^(__HOST)/i', '__SITE', $cookieNamePattern);
    }

    $cookieNameVariableBinds = [
      $appName,
      $cookieName
    ];

    foreach ($cookieNameVariableBinds as $variableIndex => $variableValue) {
      $variableValue = self::rewriteCookieVariableName ($variableValue);
      $cookieNamePattern = str_replace ("\$$variableIndex", $variableValue, $cookieNamePattern);
    }

    return $cookieNamePattern;
  }

  /**
   * @method string
   *
   * rewrite cookie name
   *
   */
  private static function rewriteCookieVariableName (string $cookieVariableName) {
    $twoOrMoreDashesRe = '/(\-){2,}/';
    $startsAndEndDashesRe = '/(^\-+|\-+$)/';
    $charsBeyondAlphaNumAndDashRe = '/([^a-zA-Z0-9\\-]+)/';

    return preg_replace ($startsAndEndDashesRe, '', preg_replace ($twoOrMoreDashesRe, '-', preg_replace ($charsBeyondAlphaNumAndDashRe, '-', camel_to_spinal_case ($cookieVariableName))));
  }
}
