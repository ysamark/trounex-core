<?php
/**
 * @version 2.0
 * @author Ysamark
 *
 * @keywords Trouter, Trounex, php framework
 * -----------------
 * @package Trounex
 *
 * MIT License
 *
 * Copyright (c) 2020 Ysare
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
namespace Trounex\Auth;

trait TokenSourceReader {
  /**
   * @method number
   *
   * get user id from stored authentication data
   */
  protected static function getUserIdFromAuthData () {
    // if (isset ($_SESSION)
    //   && is_array ($_SESSION)
    //   && isset ($_SESSION ['user'])
    //   && is_array ($_SESSION ['user'])
    //   && isset ($_SESSION ['user']['userId'])) {
    //   $userId = $_SESSION ['user']['userId'];
    // }

    // if (is_null ($userId)
    //   && isset ($_COOKIE)
    //   && is_array ($_COOKIE)
    //   && isset ($_COOKIE [self::getAuthCookieName ()])
    //   && !empty ($_COOKIE [self::getAuthCookieName ()])) {
    //   $decryptedToken = self::decryptToken ($_COOKIE [self::getAuthCookieName ()]);

    //   if ($decryptedToken && self::validTokenData ($decryptedToken)) {
    //     $userId = $decryptedToken ['userId'];
    //   }
    // }
    $authData = self::getAuthData ();

    if (is_array ($authData) && isset ($authData ['user'])) {
      return $authData ['user']['id'];
    }
  }

  /**
   * @method number
   *
   * get data from stored authentication data
   */
  protected static function getAuthData () {
    $authenticationTokenSources = conf ('authentication.token.sources');

    if (!is_array ($authenticationTokenSources)) {
      return null;
    }

    foreach ($authenticationTokenSources as $i => $source) {
      if (!is_string ($source)) {
        continue;
      }

      $sourceHandlerName = join ('', [
        'get', ucfirst ($source), 'AuthData'
      ]);

      if (method_exists (self::class, $sourceHandlerName)) {
        $authData = call_user_func ([self::class, $sourceHandlerName]);

        if (is_array ($authData)) {
          return $authData;
        }
      }
    }
  }

  /**
   * @method number
   *
   * get data from session stored authentication data
   */
  protected static function getSessionAuthData () {
    if (isset ($_SESSION)
      && is_array ($_SESSION)
      && isset ($_SESSION ['user'])
      && is_array ($_SESSION ['user'])
      && isset ($_SESSION ['user']['userId'])) {
      return [
        'user' => [
          'id' => $_SESSION ['user']['userId']
        ]
      ];
    }
  }

  /**
   * @method number
   *
   * get data from cookie stored authentication data
   */
  protected static function getCookieAuthData () {
    $authCookieName = self::getAuthCookieName ();

    if (isset ($_COOKIE)
      && is_array ($_COOKIE)
      && isset ($_COOKIE [$authCookieName])
      && !empty ($_COOKIE [$authCookieName])) {
      $decryptedToken = self::decryptToken ($_COOKIE [$authCookieName]);

      if ($decryptedToken && self::validTokenData ($decryptedToken)) {
        $userId = $decryptedToken ['userId'];

        return [
          'user' => [
            'id' => $userId
          ]
        ];
      }
    }
  }

  /**
   * @method number
   *
   * get data from bearer token authentication data
   */
  protected static function getBearerAuthData () {
    $requestHeaders = getallheaders ();
    $bearerAuthenticationTokenRe = '/^Bearer\s+(.+)$/';

    if (!(is_array ($requestHeaders)
      && isset ($requestHeaders ['Authorization'])
      && is_string ($requestHeaders ['Authorization'])
      && !empty ($requestHeaders ['Authorization'])
      && preg_match ($bearerAuthenticationTokenRe, $requestHeaders ['Authorization']))) {
      return null;
    }

    $bearerAuthenticationToken = preg_replace ('/^Bearer\s+/', '', trim ($requestHeaders ['Authorization']));

    $decryptedToken = self::decryptToken ($bearerAuthenticationToken);

    if ($decryptedToken && self::validTokenData ($decryptedToken)) {
      $userId = $decryptedToken ['userId'];

      return [
        'user' => [
          'id' => $userId
        ]
      ];
    }
  }
}
