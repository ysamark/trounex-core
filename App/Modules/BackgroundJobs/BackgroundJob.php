<?php

namespace App\Modules\BackgroundJobs;

use App\Modules\BackgroundJobs\Jobs\MailJob;
use App\Utils\ErrorHandler;

use App\Modules\BackgroundJobs\BackgroundJob\Drivers\FileSystem;
// use App\Modules\BackgroundJobs\BackgroundJob\Drivers\Database;
// use App\Modules\BackgroundJobs\BackgroundJob\Drivers\S3;

class BackgroundJob {
  use CacheFileHelper;
  use BackgroundJob\CoreObject;
  use BackgroundJob\CoreDriver;
  use BackgroundJob\DefaultDriver;
  
  /**
   * @method void Queue
   */
  public static function Queue (BackgroundJob $job, array $jobData) {
    $fs = new FileSystem ();

    $fs->set($job, $jobData);
  }

  public static function __callStatic ($methodName, array $arguments = []) {
    $queueHelperPrefixRe = '/^(Queue)/';

    if (preg_match ($queueHelperPrefixRe, $methodName)) {
      $jobName = preg_replace ($queueHelperPrefixRe, '', $methodName);
      $jobClassName = join ('', [$jobName, 'Job']);
      $jobClassRef = join ('\\', [
        'App',
        'Jobs',
        $jobClassName
      ]);

      if (!(class_exists ($jobClassRef) && in_array (Jobs\Job::class, class_parents ($jobClassRef)))) {
        return ErrorHandler::handle ('No job called ' . $jobName);
      }

      $jobData = isset ($arguments [0]) ? $arguments [0] : null;

      $job = new BackgroundJob ([
        'name' => $jobName,
        'className' => $jobClassRef,
        'callArgs' => $arguments,
        'data' => $jobData
      ]);

      return self::Queue ($job, $jobData);
    }

    ErrorHandler::handle ("Not defined method: $methodName for BackgroundJob class");
  }
}
