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

use App\Server;
use Trounex\Helper;
use App\Models\Language;

if (!function_exists ('application_language_data')) {
  /**
   * Get application language data
   * this should the configurations for the selected language
   *
   * @method array
   *
   */
  function application_language_data () {
    $clientLanguageFilePath = Server::getClientLanguageFilePath ();
    $clientLanguageDataObject = Server::getClientLanguageDataObject ();

    if (!empty ($clientLanguageFilePath) && is_file ($clientLanguageFilePath)) {
      return Helper::ObjectsToArray (json_decode (file_get_contents ($clientLanguageFilePath)));
    }

    if ($clientLanguageDataObject instanceof Language) {
      return language_data ($clientLanguageDataObject);
    }

    return [];
  }
}
