<?php

namespace App\Server\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseBase;

class Response extends ResponseBase {
  /**
   * @var int
   *
   * http response status
   *
   */
  private $status = ResponseBase::HTTP_OK;

  /**
   * @method void
   *
   * end a response
   *
   */
  public function end () {
  }

  /**
   * @method App\Server\Http\Response
   *
   * set the response status
   *
   */
  public function status (int $status = ResponseBase::HTTP_OK) {
    $this->status = $status;

    http_response_code($status);

    return $this;
  }

  /**
   * @method App\Server\Http\Response
   *
   * sent a json response
   *
   */
  public function json ($data = null) {
    $json = new JsonResponse ($data, $this->status);

    $json->send();

    return $this;
  }

  /**
   * @method void
   *
   * redirect the request
   *
   */
  public function redirect () {
    return call_user_func_array ('redirect_to', func_get_args ());
  }

  /**
   * @method void
   *
   * redirect the request
   *
   */
  public function redirect_to () {
    return call_user_func_array ('redirect_to', func_get_args ());
  }

  /**
   * @method void
   *
   * redirect the request back to the referer
   *
   */
  public function redirect_back () {
    return call_user_func_array ('redirect_back', func_get_args ());
  }

}
