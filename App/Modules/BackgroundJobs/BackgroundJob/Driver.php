<?php

namespace App\Modules\BackgroundJobs\BackgroundJob;

use App\Modules\BackgroundJobs\BackgroundJob;

abstract class Driver {
  /**
   * @method void
   */
  public abstract function set (BackgroundJob $job, array $jobData);

  /**
   * @method BackgroundJob
   */
  public abstract function get ();
}
