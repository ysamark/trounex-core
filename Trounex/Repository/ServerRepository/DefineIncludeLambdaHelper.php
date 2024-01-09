<?php

namespace Trounex\Repository\ServerRepository;

use Trounex\View\ViewGlobalContext;

trait DefineIncludeLambdaHelper {
  protected static function defineIncludeLambda () {
    self::$include = function ($__props) {
      $global = new ViewGlobalContext ($this);

      $viewEngine = 'Default';
      $viewEngineResolveAlternates = [
        // conf()
        'viewEngine',
        'viewEngine.resolve'
      ];

      foreach ($viewEngineResolveAlternates as $viewEngineResolveAlternate) {
        $viewEngineResolveAlternate = conf ($viewEngineResolveAlternate);

        if (is_string ($viewEngineResolveAlternate)) {
          $viewEngine = ucfirst($viewEngineResolveAlternate);
          break;
        }
      }

      $viewEngineAdapterClassName = "Trounex\\View\\ViewEngine\\{$viewEngine }ViewEngine";

      if (class_exists ($viewEngineAdapterClassName)) {
        $viewEngineAdapterProps = [
          'context' => $global,
          'viewFilePath' => $__props ['viewPath'],
          'layoutFilePath' => $__props ['layoutPath']
        ];

        $viewEngineAdapterHandlerArguments = [$global];

        $viewEngineAdapter = new $viewEngineAdapterClassName ($viewEngineAdapterProps);

        return call_user_func_array ([$viewEngineAdapter, 'render'], $viewEngineAdapterHandlerArguments);
      }
    };
  }
}
