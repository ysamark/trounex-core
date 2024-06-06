<?php

namespace App\Server\Http;

use Trounex\Helper;
use Trounex\Component\Http\RequestInput;
use Symfony\Component\HttpFoundation\Request as RequestBase;

class Request extends RequestBase {
  /**
   * trounex request input component
   */
  use RequestInput;

  /**
   * constructor
   */
  public function __construct () {
    call_user_func_array ([parent::class, '__construct'], func_get_args ());
  }

  /**
   * @method array
   *
   * get data from request
   *
   */
  public function get (string $key, $defaultValue = null) {
    $requestInput = $this->getRequestInput ();

    $keySlices = preg_split ('/\\.+/', $key);
    $firstKeySlice = trim ($keySlices [0]);

    $requestData = null;

    $requestData = (isset ($requestInput [$firstKeySlice]))
      ? $requestInput [$firstKeySlice]
      : parent::get ($firstKeySlice, $defaultValue);

    if (count ($keySlices) < 2) {
      return $requestData;
    }

    $keySlicesTail = array_slice ($keySlices, 1, count ($keySlices));

    return Helper::getArrayProp ($requestData, join ('.', $keySlicesTail), $defaultValue);
  }

  /**
   * @method array
   *
   * get from request only specified
   *
   */
  public function only (array $keys) {
    $data = [];

    foreach ($keys as $key) {
      $data [$key] = $this->get ($key);
    }

    return Helper::compileConfigs ($data);
  }

  /**
   * @method array
   *
   * get a data from request
   *
   */
  public function all () {
    $requestInput = $this->getRequestInput ();

    return array_full_merge ($this->request->all (), $requestInput);
  }

  /**
   * @method mixed
   * 
   * get data from query request params
   * 
   */
  public function query ($propRef) {
    if (is_string ($propRef)) {
      return $this->query->get ($propRef);
    }

    if (is_array ($propRef)) {
      $propRef = array_filter ($propRef, function ($prop) {
        return is_string ($prop);
      });

      $queryProps = [];

      foreach ($propRef as $prop) {
        $queryProps [$prop] = $this->query->get ($prop);
      }

      return $queryProps;
    }
  }

  /**
   * @method mixed
   */
  // public function __call ($methodName, array $arguments = []) {
  //   if (method_exists ($this->request, $methodName)) {
  //     return call_user_func_array ([$this->request, $methodName], $arguments);
  //   }
  // }
}
