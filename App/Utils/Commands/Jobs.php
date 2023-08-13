<?php

namespace App\Utils\Commands;

use App\Models\User;
use App\Modules\BackgroundJobs\Watcher;

class Jobs {
  /**
   * Run jobs
   */
  public static function Handle () {
    print ("\nMail Queue Started...!!\n");

    Watcher::Start ();

    print ("\nMail Queue Ended...!!\n\tBye...! :)");

    exit (0);
  }
}
