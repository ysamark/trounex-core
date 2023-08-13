<?php

namespace Trounex\Repository\Database;

use Closure;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;

trait MigrationRepository {
  use CapsuleSetupRepository;

  public function createTable (string $tableName, Closure $tableCreationContext) {
    $schema = Manager::schema ();

    return call_user_func_array ([$schema, 'create'], [$tableName, function (Blueprint $table) use ($tableCreationContext) {
      $table->increments ('id');
      call_user_func_array ($tableCreationContext, func_get_args ());
      $table->timestamp ('created_at')
        ->useCurrent ();
      $table->timestamp ('updated_at')
        ->useCurrent ()
        ->useCurrentOnUpdate ();
    }]);
  }

  public function useTable () {
    $schema = Manager::schema ();

    return call_user_func_array ([$schema, 'table'], func_get_args ());
  }
}
