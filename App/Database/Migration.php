<?php

namespace App\Database;

use Phinx\Migration\AbstractMigration;
use Trounex\Repository\Database\MigrationRepository;

abstract class Migration extends AbstractMigration {
  use MigrationRepository;
}
