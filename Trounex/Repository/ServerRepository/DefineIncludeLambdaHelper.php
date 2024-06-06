<?php

namespace Trounex\Repository\ServerRepository;

use App\Server;
use App\Router\Param;
use Trounex\RouteData;
use Trounex\View\ViewGlobalContext;

trait DefineIncludeLambdaHelper {
  protected static function defineIncludeLambda () {
    list ($request, $response) = self::defaultHandlerArguments ();

    self::$include = function ($__props) use ($request, $response) {
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
        $trounexAction = $request->headers->get ('---trounex-action');
        $trounexActionArguments = (string)$request->headers->get ('---trounex-action-arguments');
        $viewEngineAdapterProps = [
          'context' => $global,
          'viewFilePath' => $__props ['viewPath'],
          'layoutFilePath' => $__props ['layoutPath']
        ];

        $readArgumentValue = (function ($value) {
          $trueValuesRe = '/^(true|yes|on)$/i';

          if (preg_match ('/^(true|false|yes|not?|off|on)$/i', $value)) {
            return (boolean)(preg_match ($trueValuesRe, $value));
          }

          if (is_numeric ($value)) {
            $tmpValue = (float)($value);

            if (preg_match ('/^([0-9\.]+)$/', (string)($tmpValue))) {
              return $tmpValue;
            }
          }

          return $value;
        });

        $layoutParentsMapper = (function ($parent) {
          return [
            'id' => get_layout_id ($parent),
            'name' => pathinfo ($parent, PATHINFO_FILENAME)
          ];
        });

        $trounexActionArgumentList = preg_split ('/\s*,\s*/', $trounexActionArguments); // array_map ($trounexActionArgumentsMapper, );
        $trounexActionArguments = [];

        foreach ($trounexActionArgumentList as $trounexActionArgument) {
          $trounexActionArgumentSlices = preg_split ('/\s*(=)\s*/', $trounexActionArgument);
          $trounexActionArgumentName = camel_to_spinal_case (trim ($trounexActionArgumentSlices [0]));
          $trounexActionArgumentValue = isset ($trounexActionArgumentSlices [1])
            ? call_user_func ($readArgumentValue, trim ($trounexActionArgumentSlices [1]))
            : true;

          $trounexActionArguments [$trounexActionArgumentName] = $trounexActionArgumentValue;
        }

        $actionName = camel_to_spinal_case ((string)$trounexAction);

        switch ($actionName) {
          case 'get-route-data':
            $layoutFileId = get_layout_id ($__props ['layoutPath']);
            $routeData = new RouteData;

            $response->end ([
              'route' => $routeData->path,
              'params' => Param::all (),
              'request' => [
                'method' => $routeData->method,
                'body' => $request->all ()
              ],
              'data' => [
                'route' => $routeData->path,
                'layout' => [
                  'id' => $layoutFileId,
                  'name' => pathinfo ($__props ['layoutPath'], PATHINFO_FILENAME),
                  'parents' => array_map ($layoutParentsMapper, Server::getLayoutParents ($__props ['layoutPath']))
                ]
              ]
            ]);
            break;

          case 'render-view':
            $viewEngineAdapterProps ['action'] = [
              'name' => $actionName,
              'arguments' => $trounexActionArguments
            ];
            break;

          default:
            break;
        }

        $viewEngineAdapterHandlerArguments = [$global];

        $viewEngineAdapter = new $viewEngineAdapterClassName ($viewEngineAdapterProps);

        return call_user_func_array ([$viewEngineAdapter, 'render'], $viewEngineAdapterHandlerArguments);
      }
    };
  }
}
