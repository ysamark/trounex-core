<?php

namespace Trounex\Repository\Router;

trait ParamRepository {
  /**
   * A list of the router sent parameters
   * @var array
   */
  private static $paramMapList = [];

  /**
   * @method void __construct
   */
  public function __construct ($paramMapList = []) {
    self::MapList ($paramMapList);
  }

  /**
   * @method mixed __get
   */
  public function __get ($routeParamKey = null) {
    if (!(is_string ($routeParamKey)
      && !empty ($routeParamKey)
      && isset (self::$paramMapList [$routeParamKey]))) {
      return null;
    }

    return self::$paramMapList [$routeParamKey];
  }

  public static function MapList ($paramMapList) {
    if (is_array ($paramMapList) && $paramMapList) {
      self::$paramMapList = array_merge (self::$paramMapList, $paramMapList);
    }
  }

  /**
   * @method array
   *
   * return all the request parameters
   *
   */
  public static function all () {
    return self::$paramMapList;
  }
}
