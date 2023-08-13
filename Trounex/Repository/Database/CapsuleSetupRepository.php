<?php

namespace Trounex\Repository\Database;

use App\Database\Database;
use Illuminate\Database\Capsule\Manager;
// use Doctrine\DBAL\Types\{StringType, Type};

trait CapsuleSetupRepository {
  /**
   * @var \Illuminate\Database\Capsule\Manager
   */
  public $capsule;

  /**
   * @var \Illuminate\Database\Schema\Builder
   */
  public $schema;

  /**
   * @var \Illuminate\Database\Capsule\Manager
   */
  public static $CAPSULE;

  public function init () {

    if (self::$CAPSULE) {
      $this->capsule = self::$CAPSULE;
      $this->schema = $this->capsule->schema ();
    } else {
      $this->capsule = new Manager;
      $this->capsule->addConnection (Database::GetConfig ());

      $platform = $this->capsule
        ->getConnection ()
        ->getDoctrineSchemaManager ()
        ->getDatabasePlatform ();

      $platform->registerDoctrineTypeMapping ('enum', 'string');

      $this->capsule->bootEloquent ();
      $this->capsule->setAsGlobal ();
      $this->schema = $this->capsule->schema ();

      self::$CAPSULE = $this->capsule;
    }
  }
}
