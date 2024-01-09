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
use Trounex\Helper;
use App\Utils\Auth;

use function conf;
use function array_full_merge;

trait Config {
  /**
   * @var boolean
   *
   * indicator for configurations setup
   * its true if done and false otherwise
   *
   */
  private static $configurationsSetupDone = false;

  /**
   * @method void
   *
   * setup authentication configurations by updating default configs with
   * the application is
   *
   */
  protected static function setConfigurationsUp () {
    $authenticationConfigurations = conf('authentication');

    if (is_array ($authenticationConfigurations) && $authenticationConfigurations) {
      self::$configurations = array_full_merge (self::$configurations, $authenticationConfigurations);
    }

    self::$configurationsSetupDone = true;
  }


  /**
   * @method mixed
   *
   * get authentication configuration data
   *
   */
  private static function getConf (string $confPath, $defaultValue = null) {
    if (!self::$configurationsSetupDone) {
      self::setConfigurationsUp ();
    }

    return Helper::getArrayProp(self::$configurations, $confPath, $defaultValue);
  }

  /**
   * @method string
   *
   * get trounex app cookie name
   *
   */
  private static function getAuthCookieName () {
    $appName = conf('appName');

    if (!(is_string ($appName) && !empty ($appName))) {
      $appName = Env::Get ('TROUNEX_APP_NAME', 'Trounex');
    }

    $tokenCookieName =self::TOKEN_COOKIE_NAME;

    if (!Server::isHttps ()) {
      $tokenCookieName = preg_replace ('/^(__HOST)/i', '__SITE', $tokenCookieName);
    }

    $twoOrMoreDashesRe = '/(\-){2,}/';
    $startsAndEndDashesRe = '/(^\-+|\-+$)/';
    $charsBeyondAlphaNumAndDashRe = '/([^a-zA-Z0-9\\-]+)/';

    $appName = preg_replace ($startsAndEndDashesRe, '', preg_replace ($twoOrMoreDashesRe, '-', preg_replace ($charsBeyondAlphaNumAndDashRe, '-', camel_to_spinal_case ($appName))));

    return str_replace ('$0', trim ($appName), $tokenCookieName);
  }
}
