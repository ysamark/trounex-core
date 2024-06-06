<?php

namespace Trounex\Repository;

use PDOException;
use Trounex\RouteData;
use App\Models\Language;
use App\Utils\PageExceptions\Error;
use Illuminate\Database\Eloquent\Model;

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
    self::getRouteData ();
    self::defineIncludeLambda ();

    if (!self::isForbiddenPage ()) {
      self::handleApiRouteIfExists ();
      self::includeViewFileIfExists ();
      self::serveStaticFileIfExists ();
      self::handleDynamicRouteIfExists ();
    }

    /**
     * it should already be flushed down if some handler took care of the request
     * on condition that nothing was done across the current request, it must return
     * a 404 error, assuming that the request page does not exists
     */
    Error::Throw404 ();
  }

  /**
   * @method boolean
   *
   * verify is the request page forbidden by trounex router policies
   *
   */
  protected static final function isForbiddenPage () {
    $routeData = new RouteData;

    $forbiddenPagesReList = [
      /**
       * user could not access a partial or layout as if it was a page
       */
      '/^(\/?(_partials|layouts)(\/|\s*$))/i',

      /**
       * user could not access a dynamic route without passing the required parameters
       */
      '/(\[|\])/',

      /**
       * user could not access a route page context directly
       */
      '/(\(|\))/',

      /**
       * user could not access a request method based route page directly
       */
      '/(\.(get|post|patch|head|delete|options?)(\.php)?)$/i'
    ];

    foreach ($forbiddenPagesReList as $forbiddenPagesRe) {
      if (preg_match ($forbiddenPagesRe, $routeData->path)) {
        return true;
      }
    }

    return false;
  }

  /**
   * set the view path
   */
  protected static function setViewPath ($viewPath) {
    self::$viewPath = $viewPath;
  }

  /**
   * @method array
   *
   * get client language key alternates
   *
   * should get it from a cookie, database or from the client system/browser data;
   * lastly, it should use the default language as the client's
   *
   */
  public static function getClientLanguageAlternates () {
    $alternates = [];

    $user = user ();
    $clientDefinedLanguageRe = '/^([a-zA-Z-]+)$/';

    if (is_object ($user) && class_exists (Language::class) && in_array (Model::class, class_parents (Language::class))) {
      try {
        $userLanguage = $user->getSetting ('language');

        if (!empty ($userLanguage) && preg_match ($clientDefinedLanguageRe, $userLanguage)) {
          array_push ($alternates, $userLanguage);
        }
      } catch (PDOException $e) {
        unset ($user);
      }
    }


    $clientDefinedLanguage = (string)(self::Get ('cookie:__SITE-data-lang'));

    if (preg_match ($clientDefinedLanguageRe, $clientDefinedLanguage)) {
      array_push ($alternates, $clientDefinedLanguage);
    }

    $clientLanguageDataListMap = (function ($clientLanguageDataListItem) {
      return preg_replace ('/^(\s*q\s*=\s*([0-9\.]+),?)/i', '', $clientLanguageDataListItem);
    });

    $clientLanguageData = self::Get ('HTTP_ACCEPT_LANGUAGE');
    $clientLanguageDataList = preg_split ('/\s*;\s*/', $clientLanguageData);

    $clientLanguageDataList = array_map ($clientLanguageDataListMap, $clientLanguageDataList);

    foreach ($clientLanguageDataList as $clientLanguage) {
      $clientLanguageVariants = preg_split ('/\s*,\s*/', $clientLanguage);

      if (empty ($clientLanguage)) {
        continue;
      }

      foreach ($clientLanguageVariants as $clientLanguageVariant) {
        array_push ($alternates, $clientLanguageVariant);
      }
    }

    return array_merge ($alternates, [conf ('languages.defaultLang')]);
  }

  /**
   * @method string
   */
  public static function getClientLanguageFilePath () {
    $clientLanguageAlternates = self::getClientLanguageAlternates ();

    foreach ($clientLanguageAlternates as $clientLanguageAlternate) {
      $clientLanguageVariantFilePath = self::resolveLanguageFilePath ($clientLanguageAlternate);

      if (is_file ($clientLanguageVariantFilePath)) {
        return realpath ($clientLanguageVariantFilePath);
      }
    }
  }

  /**
   * @method string
   */
  public static function getClientLanguageKey () {
    $clientLanguageAlternates = self::getClientLanguageAlternates ();

    foreach ($clientLanguageAlternates as $clientLanguageAlternate) {
      $clientLanguageVariantFilePath = self::resolveLanguageFilePath ($clientLanguageAlternate);

      if (is_file ($clientLanguageVariantFilePath)) {
        return $clientLanguageAlternate;
      }
    }
  }

  /**
   * @method string
   */
  public static function getDefaultLanguageFilePath () {
    $clientLanguageVariantFilePath = self::resolveLanguageFilePath ((string)(conf ('languages.defaultLang')));

    if (is_file ($clientLanguageVariantFilePath)) {
      return realpath ($clientLanguageVariantFilePath);
    }
  }

  /**
   * @method App\Models\Language|null
   *
   * get the client language object from the database by using the Language model
   *
   */
  public static function getClientLanguageDataObject () {

    if (!(class_exists (Language::class)
      && in_array (Model::class, class_parents (Language::class)))) {
      return;
    }

    $clientLanguageAlternates = self::getClientLanguageAlternates ();

    foreach ($clientLanguageAlternates as $clientLanguageAlternate) {
      try {
        $clientLanguageDataObjectFetch = Language::where ([
          'key' => filter_var ($clientLanguageAlternate, FILTER_SANITIZE_STRING)
        ]);

        if ($clientLanguageDataObjectFetch->count () >= 1) {
          return $clientLanguageDataObjectFetch->first ();
        }
      } catch (PDOException $e) {
        continue;
      }
    }
  }
}
