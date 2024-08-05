<?php

namespace Trounex\Component\Http;

trait RequestInput {
  /**
   * @method array getRequestInput
   *
   * Get the server request input based on the sent
   * content type from the request.
   * @return array
   */
  protected function getRequestInput () {
    $headers = self::getHeaders ();

    $contentTypesRe = '/^((text|application)\/(.+))/i';
    $contentType = 'text/json';

    if ( isset ($headers ['Content-Type']) ) {
      $contentType = $headers['Content-Type'];
    }

    if ( preg_match ( $contentTypesRe, $contentType, $match) ) {
      $phpRawInput =  static::getPhpRawInput ();

      $contentTextType = ucfirst (strtolower ($match[ 3 ]));
      $contentTextTypeParserName = 'parse' . $contentTextType;

      if (method_exists ($this, $contentTextTypeParserName)) {
        $requestInput = call_user_func_array ([$this, $contentTextTypeParserName], [ $phpRawInput ]);
        return array_full_merge ($_POST, $requestInput);
      }
    }

    return $_POST;
  }

  protected function parseJson ( $phpRawInput ) {
    $jsonData = self::jsonObject2Array (
      json_decode ( $phpRawInput )
    );

    return !is_array ($jsonData) ? [] : (
      $jsonData
    );
  }

  protected function getPhpRawInput () {
    return !function_exists('file_get_contents') ? null : (
      call_user_func_array('file_get_contents', array (
        'php://input'
      ))
    );
  }

  private static function jsonObject2Array ($object) {
    if (!(is_object ($object) || is_array ($object))) {
      return $object;
    }

    $arrayFromObject = ((array)($object));
    $newArray = [];

    foreach ($arrayFromObject as $key => $value) {
      $newArray [$key] = self::jsonObject2Array ($value);
    }

    return is_array ($newArray) ? $newArray : (
      ((array)($newArray))
    );
  }

  protected static function RequestInput () {
    return call_user_func_array (
      [new static, 'getRequestInput'],
      func_get_args ()
    );
  }

  protected static function getHeaders () {
    if (function_exists ('getallheaders')) {
      return getallheaders ();
    }

    return [];
  }
}
