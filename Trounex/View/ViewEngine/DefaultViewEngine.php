<?php

namespace Trounex\View\ViewEngine;

use App\View;
use App\Server;
use Trounex\View\ViewHandler;
use Trounex\View\ViewHandlerStack;

class DefaultViewEngine extends ViewEngine {
  /**
   * @method void
   *
   * register trounex view handlers
   * this should create a view context stack
   *
   */
  private function registerViewHandlers ($layoutFilePath, $layoutStackLimit = null) {
    $n = count (preg_split ('/(\\\|\/)+/', $layoutFilePath));

    $layoutFilePath = realpath ($layoutFilePath);

    for ($i = 0; $i < $n; $i++) {

      $viewHandler = $this->viewHandlerFactory ($layoutFilePath);

      ViewHandlerStack::push ($viewHandler);

      if (!empty ($layoutStackLimit)) {
        $layoutId = get_layout_id ($layoutFilePath);

        if ((string)$layoutStackLimit == $layoutId) {
          break;
        }
      }

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

  private function shouldRenderLayout ($renderViewLayout, $layoutStackLimit) {
    $n = count (preg_split ('/(\\\|\/)+/', $this->layoutFilePath));

    if (!$renderViewLayout) {
      return false;
    }

    $layoutFilePath = realpath ($this->layoutFilePath);

    for ($i = 0; $i < $n; $i++) {

      $layoutId = get_layout_id ($layoutFilePath);

      if ((string)$layoutStackLimit == (string)$layoutId) {
        return true;
      }

      $layoutFilePath = Server::getLayoutParent ($layoutFilePath);
    }

    return false;
  }

  /**
   * @method void
   */
  public function render () {
    $skipLayoutRender = $this->getProp ('action.arguments.skip-layout-stack-render');
    $viewLayoutId = $this->getProp ('action.arguments.view-layout-id');
    $renderViewLayout = $this->getProp ('action.arguments.render-view-layout');

    if (!$skipLayoutRender || $this->shouldRenderLayout ($renderViewLayout, $viewLayoutId)) {
      $viewHandlers = $this->registerViewHandlers ($this->layoutFilePath, $viewLayoutId);
    }

    View::Render ();
  }
}
