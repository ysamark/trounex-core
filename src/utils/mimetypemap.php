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

if (!function_exists ('mimetype_map')) {
  function mimetype_map () {
    return [
      'css'      => 'text/css',
      'csv'      => 'text/csv',
      'doc'      => 'application/msword',
      'gif'      => 'image/gif',
      'htm'      => 'text/html',
      'html'     => 'text/html',
      'ico'      => 'image/x-icon',
      'jpe'      => 'image/jpeg',
      'jpeg'     => 'image/jpeg',
      'jpg'      => 'image/jpeg',
      'js'       => 'application/x-javascript',
      'json'     => 'application/json',
      'm3u'      => 'audio/x-mpegurl',
      'man'      => 'application/x-troff-man',
      'mathml'   => 'application/mathml+xml',
      'me'       => 'application/x-troff-me',
      'pdf'      => 'application/pdf',
      'png'      => 'image/png',
      'pnm'      => 'image/x-portable-anymap',
      'ppm'      => 'image/x-portable-pixmap',
      'ppt'      => 'application/vnd.ms-powerpoint',
      'ps'       => 'application/postscript',
      'qt'       => 'video/quicktime',
      'ra'       => 'audio/x-pn-realaudio',
      'ram'      => 'audio/x-pn-realaudio',
      'ras'      => 'image/x-cmu-raster',
      'rdf'      => 'application/rdf+xml',
      'rgb'      => 'image/x-rgb',
      'rm'       => 'application/vnd.rn-realmedia',
      'roff'     => 'application/x-troff',
      'rss'      => 'application/rss+xml',
      'rtf'      => 'text/rtf',
      'rtx'      => 'text/richtext',
      'sgm'      => 'text/sgml',
      'sgml'     => 'text/sgml',
      'sh'       => 'application/x-sh',
      'shar'     => 'application/x-shar',
      'silo'     => 'model/mesh',
      'sit'      => 'application/x-stuffit',
      'skd'      => 'application/x-koan',
      'skm'      => 'application/x-koan',
      'skp'      => 'application/x-koan',
      'skt'      => 'application/x-koan',
      'smi'      => 'application/smil',
      'smil'     => 'application/smil',
      'snd'      => 'audio/basic',
      'so'       => 'application/octet-stream',
      'spl'      => 'application/x-futuresplash',
      'src'      => 'application/x-wais-source',
      'sv4cpio'  => 'application/x-sv4cpio',
      'sv4crc'   => 'application/x-sv4crc',
      'svg'      => 'image/svg+xml',
      'svgz'     => 'image/svg+xml',
      'swf'      => 'application/x-shockwave-flash',
      't'        => 'application/x-troff',
      'tar'      => 'application/x-tar',
      'tcl'      => 'application/x-tcl',
      'tex'      => 'application/x-tex',
      'texi'     => 'application/x-texinfo',
      'texinfo'  => 'application/x-texinfo',
      'tif'      => 'image/tiff',
      'tiff'     => 'image/tiff',
      'tr'       => 'application/x-troff',
      'tsv'      => 'text/tab-separated-values',
      'txt'      => 'text/plain',
      'ustar'    => 'application/x-ustar',
      'vcd'      => 'application/x-cdlink',
      'vrml'     => 'model/vrml',
      'vxml'     => 'application/voicexml+xml',
      'wav'      => 'audio/x-wav',
      'wbmp'     => 'image/vnd.wap.wbmp',
      'wbxml'    => 'application/vnd.wap.wbxml',
      'wml'      => 'text/vnd.wap.wml',
      'wmlc'     => 'application/vnd.wap.wmlc',
      'wmls'     => 'text/vnd.wap.wmlscript',
      'wmlsc'    => 'application/vnd.wap.wmlscriptc',
      'wrl'      => 'model/vrml',
      'xbm'      => 'image/x-xbitmap',
      'xht'      => 'application/xhtml+xml',
      'xhtml'    => 'application/xhtml+xml',
      'xls'      => 'application/vnd.ms-excel',
      'xml'      => 'application/xml',
      'xpm'      => 'image/x-xpixmap',
      'xsl'      => 'application/xml',
      'xslt'     => 'application/xslt+xml',
      'xul'      => 'application/vnd.mozilla.xul+xml',
      'xwd'      => 'image/x-xwindowdump',
      'xyz'      => 'chemical/x-xyz',
      'zip'      => 'application/zip'
    ];
  }
}
