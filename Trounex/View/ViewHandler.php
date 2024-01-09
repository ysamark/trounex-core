<?php

namespace Trounex\View;

use Closure;
use App\Server;
// use App\Controllers\BaseController;

class ViewHandler {
  /**
   * @var Closure
   */
  private $body;

  /**
   * @var string
   *
   * view handler id
   */
  private $id;

  public function __construct (Closure $body) {
    $this->body = $body;
    $this->id = uuid ();
  }

  public function __invoke () {
    return call_user_func_array ($this->getBody (), func_get_args ());
  }

  public function getBody () {
    return Server::lambda ($this->body);
  }

  public function getId () {
    return $this->id;
  }
}
