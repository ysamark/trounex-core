<?php

namespace App\Modules\BackgroundJobs\BackgroundJob\Drivers;

use App\Modules\BackgroundJobs\BackgroundJob\Driver;

class FileSystem extends Driver {
  /**
   * @method void
   */
  function set (BackgroundJob $job, array $jobData) {
    $queueCacheFilePath = join (DIRECTORY_SEPARATOR, [
      dirname(dirname (dirname (__DIR__))), 
      'db', 
      'caches', 
      'queue', 
      $job->name,
      '__data__.cache.json'
    ]);

    if (!file_exists ($queueCacheFilePath)) {
      self::createFile ($queueCacheFilePath);
    }

    # $queueCacheFileHandler = fopen ($queueCacheFilePath, 'r');

    if (!!is_file ($queueCacheFilePath)/* $queueCacheFileHandler */) {
      #$queueCacheFileLines = [];
      /**
       * Fetch the file content
       */
      #while (!feof ($queueCacheFileHandler)) {
      #  @array_push ($queueCacheFileLines, fgets ($queueCacheFileHandler));
      #}

      #@fclose ($queueCacheFileHandler);

      $queueCacheFileContent = file_get_contents ($queueCacheFilePath); # join ('', $queueCacheFileLines);

      $queueCache = (array)(json_decode ($queueCacheFileContent));

      array_push ($queueCache, [
        'JobProps' => $jobData,
        'JobHandler' => $job->className,
        "JobId" => uuid ()
      ]);

      return self::SaveDataInCacheFile ($queueCacheFilePath, $queueCache);
    }

    return 0;
  }

  function get () {}
}
