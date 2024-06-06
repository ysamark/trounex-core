<?php

namespace App\Views\Partials\Guards;

use App\Controllers\BaseController;

abstract class Guard extends BaseController {
  /**
   * @method boolean
   */
  abstract function handler (array $args): bool;
}
