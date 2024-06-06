<?php

namespace Trounex;

use App\Server;

class Helper {
  /**
   * @method array
   */
  public static function ObjectsToArray ($data) {
    if (!is_array ($data)) {
      $data = is_object ($data) ? (array)($data) : $data;
    }

    if (is_array ($data)) {
      foreach ($data as $key => $value) {
        $data [ $key ] = self::ObjectsToArray ($value);
      }
    }

    return $data;
  }

  /**
   * @method array
   */
  public static function ArrayFullMerge ($array1) {
    $finalArray = $array1;

    $arrayList = array_slice (
      func_get_args (), 1,
      func_num_args ()
    );

    $arrayListCount = count ($arrayList);

    for ($i = 0; $i < $arrayListCount; $i++) {
      $array2 = $arrayList [ $i ];

      foreach ($array2 as $key => $val) {
        $currentKeyAsArrayInFinalArray = ( boolean )(
          is_array ($val) &&
          isset ($finalArray [ $key ]) &&
          is_array ($finalArray [ $key ])
        );

        if ( $currentKeyAsArrayInFinalArray ) {
          $finalArray [ $key ] = ( array ) (
            self::ArrayFullMerge ($finalArray [ $key ], $val)
          );
        } elseif (is_int ($key)) {
          $finalArray [] = $val;
        } else {
          $finalArray [ $key ] = $val;
        }
      }
    }

    return $finalArray;
  }

  /**
   * @method mixed
   */
  public static function putPostData (string $propertyNamePath, $value = null) {
    $data = self::compileConfigs ([
      $propertyNamePath => $value
    ]);

    $_POST = self::ArrayFullMerge ($_POST, $data);
  }

  public static function compileConfigs ($configList = []) {
    if (!(is_array ($configList) && $configList)) {
      return [];
    }

    $finalConfigurationsList = array ();

    $keyRe = '/^([^\.]+)\.?/';

    foreach ($configList as $key => $value) {

      $key = trim ($key);

      if (is_numeric($key) || is_int($key)) {
        $finalConfigurationsList [ $key ] = (
          $value
        );
      } elseif (preg_match ( $keyRe, $key, $match)) {
        $keyMatch = preg_replace (
          '/\.$/', '', $match [ 0 ]
        );

        if (empty ($keyName = trim(preg_replace ($keyRe, '', $key)))) {
          $finalConfigurationsList[ $key ] = !is_array($value) ? $value : self::compileConfigs ($value);
        } else {
          if (!(isset($finalConfigurationsList[ $keyMatch ]) && is_array($finalConfigurationsList[ $keyMatch ]))) {
            $finalConfigurationsList[ $keyMatch ] = self::compileConfigs (
                array ( $keyName => $value )
            );
          } else {
              $finalConfigurationsList[ $keyMatch ] = array_merge (
                  $finalConfigurationsList[ $keyMatch ],
                  self::compileConfigs (array (
                      $keyName => $value
                  ))
              );
          }
        }
      }
    }

    return $finalConfigurationsList;
  }

  public static function getModuleRootDir () {
    return dirname (__DIR__);
  }

  public static function isFileLocatedIn (string $dir, string $filePath) {
    $slashRe = '/(\\\|\/)+/';

    if (!(is_dir ($dir) && is_file ($filePath))) {
      return false;
    }

    $dirSlices = preg_split ($slashRe, $dir);
    $filePathSlices = preg_split ($slashRe, $filePath);


    return (realpath (join (DIRECTORY_SEPARATOR, array_slice ($filePathSlices, 0, count ($dirSlices)))) === realpath ($dir));
  }

  public static function isFileLocatedInLayoutsDir (string $filePath) {
    return self::isFileLocatedIn (Server::GetLayoutsPath (), $filePath);
  }

  public static function getArrayProp (array $array, string $propRef, $defaultValue = null) {
    $propRefSlices = preg_split ('/\.+/', $propRef);
    $propRefSlicesLen = count ($propRefSlices);

    for ($i = 0; $i < (-1 + $propRefSlicesLen); $i++) {
      $propRefSlice = $propRefSlices [$i];

      if (!(isset ($array [$propRefSlice]) && is_array ($array [$propRefSlice]))) {
        return $defaultValue;
      }

      $array = $array [$propRefSlice];
    }

    $lastPropRefSlice = $propRefSlices [-1 + $propRefSlicesLen];

    if (isset ($array [$lastPropRefSlice])) {
      return $array [$lastPropRefSlice];
    }

    return $defaultValue;
  }

  public static function readJsonFile (string $configFile) {
    if (is_file ($configFile)) {
      $configFileContent = file_get_contents ($configFile);

      $configFileData = @json_decode (trim ($configFileContent));

      return Helper::ObjectsToArray ($configFileData);
    }
  }

  public static function stringify ($data) {
    switch (gettype ($data)) {
      case 'array':
        return json_encode ($data);

      case 'object':
        if (method_exists ($data, '__toString')) {
          return call_user_func ([$data, '__toString']);
        }

        return json_encode ($data);

      case 'boolean':
        return $data ? 'true' : 'false';

      default:
        return (string)($data);
    }
  }
}
