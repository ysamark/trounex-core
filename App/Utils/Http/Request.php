<?php

namespace App\Utils\Http;

use Symfony\Component\HttpFoundation\Request as RequestBase;

class Request {
  /**
   * @var Symfony\Component\HttpFoundation\Request
   */
  private $request;

  /**
   * constructor
   */
  public function __construct () {
    $this->request = RequestBase::createFromGlobals ();
  }

  /**
   * @method mixed
   */
  public function __call ($methodName, array $arguments = []) {
    if (method_exists ($this->request, $methodName)) {
      return call_user_func_array ([$this->request, $methodName], $arguments);
    }
  }
}
