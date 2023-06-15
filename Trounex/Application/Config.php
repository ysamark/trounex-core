<?php

namespace Trounex\Application;

use App\Server;

class Config {
  /**
   * @var array
   */
  private $props = [];

  /**
   * @method void
   *
   * constructor
   */
  public function __construct ($props = []) {
    if (!(is_object ($props) || is_array ($props))) {
      $props = Server::GetConfigs ();
    }

    $this->props = $props;
  }

  public function __get (string $prop) {
    if (is_object ($this->props)) {
      return isset ($this->props->$prop) ? $this->props->$prop : null;
    }

    return isset ($this->props [$prop]) ? $this->props [$prop] : null;
  }
}
