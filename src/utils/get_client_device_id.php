<?php
/**
 * @version 2.0
 * @author Sammy
 *
 * @keywords Samils, ils, php framework
 * -----------------
 * @package Sammy\Packs\Samils\Capsule\MarkDown
 * - Autoload, application dependencies
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
use Trounex\Cookie;

if (!function_exists ('get_client_device_id')) {
  function get_client_device_id () {
    $headers = getallheaders ();
    $clientDeviceIdCookie = Cookie::get ('client-device-id');

    $clientDeviceIdHeader = isset ($headers ['X-Client-Device-Id'])
      ? $headers ['X-Client-Device-Id']
      : null;

    $clientDeviceId = !empty ($clientDeviceIdCookie)
      ? $clientDeviceIdCookie
      : $clientDeviceIdHeader;

    if (empty ($clientDeviceId)) {
      $clientDeviceId = join ('.', [generate_unique_id (), uuid ()]);

      Cookie::set ('client-device-id', $clientDeviceId);
    }

    @header ("X-Client-Device-Id $clientDeviceId");

    return $clientDeviceId;
  }
}
