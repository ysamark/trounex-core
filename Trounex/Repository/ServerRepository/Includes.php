<?php

namespace Trounex\Repository\ServerRepository;

trait Includes {
  use ViewLoader;
  use ServerUtils;
  use RouteHandler;
  use ServerGetters;
  use ServerConfigs;
  use ServerUploads;
  use ApiRouteHandler;
  use StaticFileServer;
  use ServerMiddlewares;
  use AppMainLayoutHelper;
  use Helpers\GetRouteData;
  use Helpers\GetRouteViewPaths;
  use Helpers\PathPrefixHelpers;
  use DefineIncludeLambdaHelper;
  use ViewsExtensionsListOrderer;
  use Helpers\GetViewControllerPath;
  use Helpers\IncludeViewFileIfExists;
  use Helpers\HandleDynamicRouteIfExists;
  use Helpers\GetRouteViewPathAlternates;
  use Helpers\MatchDynamicRoutesByRouteViewPaths;
}
