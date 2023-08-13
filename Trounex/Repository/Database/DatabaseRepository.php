<?php

namespace Trounex\Repository\Database;

use App\Utils\Env;
use Illuminate\Database\Capsule\Manager;

trait DatabaseRepository {
  /**
   * bootstrap the database config
   */
  public static function Boot () {
    $database = self::getEnvironmentDatabaseConfig ();

    $capsuleManager = new Manager;

    $capsuleManager->addConnection ($database);

    if (isset ($database ['database'])
      && is_string ($database ['database'])) {
      $capsuleManager->addConnection ($database, $database ['database']);
    }

    $capsuleManager->setAsGlobal ();

    $capsuleManager->bootEloquent ();
  }

  public static function GetConfig () {
    return self::getEnvironmentDatabaseConfig ();
  }

  protected static function getEnvironmentDatabaseConfig () {
    $environment = Env::Get ('PHP_ENV', 'development');

    $databaseConfig = conf()->database;

    $defaultDatabaseConfig = [];

    if (!is_array ($databaseConfig)) {
      return [];
    }

    if (isset ($databaseConfig ['default'])) {
      $defaultDatabaseConfig = $databaseConfig ['default'];
    }

    if (isset ($databaseConfig [$environment])
      && is_array ($databaseConfig [$environment])) {
      return array_merge ($defaultDatabaseConfig, $databaseConfig [$environment]);
    }

    return $defaultDatabaseConfig;
  }
}
