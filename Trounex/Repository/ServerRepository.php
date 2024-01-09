<?php

namespace Trounex\Repository;

use App\Utils\PageExceptions\Error;

trait ServerRepository {
  use ServerRepository\Includes;

  /**
   * @var string
   */
  private static $viewPath = null;

  /**
   * @var array
   */
  private static $pathPrefix = [
    'pattern' => '/^(\/+)/',
    'text' => '/'
  ];

  /**
   * @var string
   */
  private static $viewLayout;

  /**
   * @var array
   */
  private static $defaultHandlerArguments;

  /**
   * @var BaseController
   */
  private static $viewGlobalContext;

  /**
   * @var array
   *
   * server global configs
   */
  private static $config = [];

  /**
   * @var Closure
   */
  private static $include;

  /**
   * @var string
   */
  protected static $slashRe = '/[\/\\\]/';

  /**
   * @var string
   */
  protected static $routeVerbSuffixRe = '/(\.(get|post|put|patch|delete|options|head)\.php)$/i';

  /**
   * run the application server to start serving the pages
   */
  public static function Run () {
    self::getRouteData();
    self::defineIncludeLambda();
    self::handleApiRouteIfExists();
    self::includeViewFileIfExists();
    self::serveStaticFileIfExists();
    self::handleDynamicRouteIfExists();

    /**
     * it should already be flushed down if some handler took care of the request
     * on condition that nothing was done across the current request, it must return
     * a 404 error, assuming that the request page does not exists
     */
    Error::Throw404 ();
  }


  /**
   * set the view path
   */
  protected static function setViewPath ($viewPath) {
    self::$viewPath = $viewPath;
  }
}
