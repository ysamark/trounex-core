<?php

namespace Trounex\View\ViewEngine;

use Closure;
use App\View;
use App\Server;
use Trounex\Helper;
use Trounex\View\ViewHandler;
use Trounex\View\ViewHandlerStack;

class DefaultViewEngine extends ViewEngine {
  private function registerViewHandlers ($layoutFilePath) {
    $n = count (preg_split ('/(\\\|\/)+/', $layoutFilePath));

    for ($i = 0; $i < $n; $i++) {

      $viewHandler = $this->viewHandlerFactory ($layoutFilePath);

      ViewHandlerStack::push ($viewHandler);

      if (Server::isRootLayout ($layoutFilePath)) {
        break;
      }

      $layoutFilePath = Server::getLayoutParent ($layoutFilePath);
    }
  }

  private function viewHandlerFactory (string $layoutFilePath) {
    $vars = [];

    return new ViewHandler (function () use ($layoutFilePath, $vars) {
      include ($layoutFilePath);
    });
  }

  /**
   * @method void
   */
  public function render () {
    $viewHandlers = $this->registerViewHandlers ($this->layoutFilePath);

    View::Render ();
  }
}
