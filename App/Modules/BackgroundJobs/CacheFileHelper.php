<?php

namespace App\Modules\BackgroundJobs;

trait CacheFileHelper {
  /**
   * @method void SaveDataInCacheFile
   */
  public static function SaveDataInCacheFile ($queueCacheFilePath, $QueueCacheData) {
    $queueFile = @fopen ($queueCacheFilePath, 'w');

    if (!($queueFile && flock ($queueFile, LOCK_EX))) {
      if (is_resource ($queueFile)) {
        fclose ($queueFile);
      }

      usleep(rand(100, 400));
      
      return forward_static_call_array ([self::class, 'SaveDataInCacheFile'], func_get_args ());
    }
    
    fwrite ($queueFile, json_encode ($QueueCacheData, JSON_PRETTY_PRINT));
    
    flock ($queueFile, LOCK_UN);
    fclose ($queueFile);
  }

  /**
   * @method void createFile
   */
  protected static function createFile (string $filePath) {
    if (!is_dir (dirname ($filePath))) {
      self::createDirIfNotExists(dirname ($filePath));
    }

    $fileHandler = @fopen($filePath, 'w');

    if ($fileHandler) {
      @fclose($fileHandler);
    }    
  }

  /**
   * @method void createFile
   * 
   * Create a new directory from a given
   * absolute path.
   * 
   */
  protected static function createDirIfNotExists (string $dirPath) {
    $paths = preg_split ('/(\/|\\\)+/', $dirPath);
    $pathsCount = count ($paths);

    for ($i = 0; $i < $pathsCount; $i++) {
      $currentPath = join (
        DIRECTORY_SEPARATOR,
        array_slice ($paths, 0, $i + 1)
      );

      if (!file_exists ($currentPath) && $currentPath) {
        mkdir ($currentPath);
      }
    }

    return $dirPath;
  }
}
