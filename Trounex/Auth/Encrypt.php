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

use App\Server;
use App\Utils\Env;
use Trounex\Helper;
// use Trounex\Application\Model;

trait Encrypt {
  /**
   * @var array
   */
  private static $charList = [
    'a' => 'd',
    'b' => 'j',
    'c' => 'k',
    'd' => 'l',
    'e' => 'm',
    'f' => 'p',
    'g' => 's',
    'h' => 't',
    'i' => 'v',
    'j' => 'w',
    'k' => 'z',
    'l' => 'y',
    'm' => 'q',
    'n' => 'i',
    'o' => 'a',
    'p' => 'u',
    'q' => 'r',
    'r' => 'n',
    's' => 'e',
    't' => 'o',
    'u' => 'x',
    'v' => 'b',
    'w' => 'f',
    'x' => 'c',
    'y' => 'h',
    'z' => 'g',
  ];

  private static function unOrderStr (string $string) {
    $newString = '';

    for ($i = 0; $i < strlen ($string); $i++) {
      $currentStrChar = $string[$i];

      $newString .= isset (self::$charList[$currentStrChar])
        ? self::$charList[$currentStrChar]
        : $currentStrChar;
    }

    return $newString;
  }

  private static function reOrderStr (string $string) {
    $newString = '';
    $charList = [];

    foreach (self::$charList as $char => $replacer) {
      $charList [$replacer] = $char;
    }

    for ($i = 0; $i < strlen ($string); $i++) {
      $currentStrChar = $string[$i];

      $newString .= isset ($charList[$currentStrChar])
        ? $charList[$currentStrChar]
        : $currentStrChar;
    }

    return $newString;
  }

  private static function hashFromStr (string $string) {
    $salt = 2;

    for ($i = 0; $i < $salt; $i++) {
      $string = strrev(base64_encode (strrev ($string)));
    }

    return "$string:$salt";
  }

  private static function strFromHash (string $string) {
    $saltRe = '/:([0-9]+)$/';

    if (preg_match ($saltRe, $string, $saltMatch)) {
      $salt = ((int)($saltMatch [1]));

      $newString = preg_replace($saltRe, '', $string);

      for ($i = 0; $i < $salt; $i++) {
        $newString = strrev(base64_decode (strrev ($newString)));
      }

      return $newString;
    }
  }

  /**
   * @method string
   *
   * encrypt a given token data
   *
   */
  public static function generateToken (array $tokenData, string $key = null) {
    $key = !empty ($key) ? $key : Env::Get('TROUNEX_APP_SECRET_KEY');

    /**
     * $key can not be null
     */
    if (empty ($key)) {
      return;
    }

    $tokenData = self::hashFromStr (json_encode ($tokenData));

    $key = self::hashFromStr($key);

    $tokenData = self::unOrderStr($tokenData);

    $key = self::unOrderStr($key);

    $keyPos = 0;

    $keyLen = strlen ($key);

    return "$key$tokenData\$$keyLen\$0";
  }

  /**
   * @method string
   *
   * encrypt a given token data
   *
   */
  public static function decryptToken (string $token, string $key = null) {
    $key = !empty ($key) ? $key : Env::Get('TROUNEX_APP_SECRET_KEY');
    $re2 = '/(\$([0-9]+))+$/';

    /**
     * $key can not be null
     */
    if (empty ($key)) {
      return;
    }

    preg_match_all ($re2, $token, $endMatches);

    list($endMatch) = $endMatches;

    if (!((is_array ($endMatch) && count ($endMatch) >= 1))) {
      return null;
    }

    $endMatch = preg_split ('/\$/', preg_replace ('/^\$/', '', $endMatch [0]));

    $token = preg_replace ($re2, '', $token);

    if (count ($endMatch) >= 2) {
      $l = (int)($endMatch [0]);

      $k = substr ($token, 0, $l);
      $token = substr_replace ($token, '', 0, $l);
    }

    $token = self::reOrderStr($token);
    $k = self::reOrderStr($k);

    if (self::strFromHash($k) == $key) {
      return Helper::ObjectsToArray(json_decode (self::strFromHash ($token)));
    }
  }
}
