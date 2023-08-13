<?php

namespace App\Modules\BackgroundJobs;

class Watcher {
  use CacheFileHelper;
  /**
   * @var boolean $running
   */
  private static $running = true;

  private static $executedJobs = [];

  /***
   * @method void Run
   
  public static function Run () {
    $queueCacheDirectoryPath = join (DIRECTORY_SEPARATOR, [
      dirname (dirname (dirname (__DIR__))), 'db', 'caches', 'queue', 'mail', ''
    ]);

    $queueCacheDirectoryFileList = glob ($queueCacheDirectoryPath . '*.json');
    #self::$running = false;

    if (!(is_array ($queueCacheDirectoryFileList)
      && $queueCacheDirectoryFileList)) {
      return;
    }

    # [ 'data' => $mailDatasAsString ]

    foreach ($queueCacheDirectoryFileList as $queueCacheFile) {
      $queueCacheFileContent = file_get_contents ($queueCacheFile);
      $queueCacheFileData = (array)(json_decode ($queueCacheFileContent));

      $mailDatas = (array)(json_decode (base64_decode ($queueCacheFileData ['data'])));

      if (Mail::SendMail ($mailDatas, SMTP::DEBUG_SERVER)) {

        print ("\nEmail Sent!\n");

        print_r ($mailDatas);
        print ("\n\n\n");

        @unlink ($queueCacheFile);
      }
    }
  }
  */

  /***
   * @method void Run
   
  public static function Run () {
    $queueCacheFilePath = join (DIRECTORY_SEPARATOR, [
      dirname (dirname (dirname (__DIR__))), 
      'db', 'caches', 'queue', 'mail', '_mail_queue.cache.json'
    ]);

    if (!(is_file ($queueCacheFilePath))) {
      return;
    }

    $queueCacheFileContent = file_get_contents ($queueCacheFilePath);
    $queueCache = (array)(json_decode ($queueCacheFileContent));

    # [ 'data' => $mailDatasAsString ]

    foreach ($queueCache as $index => $queueMailDatas) {
      $mailDatas = (array)($queueMailDatas);

      # $mailDatas = (array)(json_decode (base64_decode ($queueCacheFileData ['data'])));

      if (Mail::SendMail ($mailDatas, SMTP::DEBUG_SERVER)) {

        print ("\nEmail Sent!\n");

        #print_r ($mailDatas);
        #print ("\n\n\n");

        array_splice ($queueCache, $index, 1);

        $queueCacheFilePathHandler = fopen ($queueCacheFilePath, 'w');

        fwrite ($queueCacheFilePathHandler, json_encode ($queueCache, JSON_PRETTY_PRINT));

        fclose ($queueCacheFilePathHandler);

        #@unlink ($queueCacheFile);
      } else {
        print ("\n\n\n\n\n\n\n\n\n\n\nFAIL\n\n\n\n\n\n\n\n\n\n\n");
      }
    }
  }
  */

  /***
   * @method void Run
   */
  public static function Run () {
    $queueCacheDirPath = join (DIRECTORY_SEPARATOR, [
      dirname (dirname (dirname (__DIR__))), 'db', 'caches', 'queue'
    ]);

    if (!(is_dir ($queueCacheDirPath))) {
      return;
    }
    
    $queueCacheDirFileList = self::readDir ($queueCacheDirPath);

    foreach ($queueCacheDirFileList as $queueCacheFilePath) {
      if (self::validJobCacheFile ($queueCacheFilePath)) {
        $queueCacheFileContent = file_get_contents ($queueCacheFilePath);
        $queueCacheData = json_decode ($queueCacheFileContent);

        if (is_object ($queueCacheData) 
          && isset ($queueCacheData->JobHandler) 
          && is_string ($queueCacheData->JobHandler) 
          && self::callJobHandler ($queueCacheData->JobHandler, (array)($queueCacheData))) {
          @unlink ($queueCacheFilePath);
        } elseif (is_array ($queueCacheData) && $queueCacheData) {
          $queueCacheDataObjects = [];

          ## echo "\ncount () => ", count ($queueCacheData), "\n\n";
          /**
           * 
           */
          foreach ($queueCacheData as $i => $queueCacheDataObject) {
            if (is_object ($queueCacheDataObject) 
              && $queueCacheDataObject->JobHandler
              && !in_array ($queueCacheDataObject->JobId, self::$executedJobs)) {
              if (!self::callJobHandler ($queueCacheDataObject->JobHandler, (array)($queueCacheDataObject))) {
                array_push ($queueCacheDataObjects, $queueCacheDataObject);
              } else {
                array_push (self::$executedJobs, $queueCacheDataObject->JobId);
              }
            }
          }

          # self::SaveDataInCacheFile ($queueCacheFilePath, $queueCacheDataObjects);
        }
      }

    }
  }

  protected static function callJobHandler ($jobHandler, $JobDataObject) {
    $rootDir = join (DIRECTORY_SEPARATOR, [dirname (dirname (dirname (__DIR__))), '']);

    if (is_file ($jobHandlerFilePath = join ('', [$rootDir, $jobHandler, '.php']))) {
      require_once $jobHandlerFilePath;
    }
    
    if (class_exists ($jobHandler)) {
      $jobHandlerObject = new $jobHandler;

      if (!isset ($JobDataObject ['JobProps'])) {
        $JobDataObject ['JobProps'] = [];
      }

      if (method_exists ($jobHandlerObject, 'run')) {
        return call_user_func ([$jobHandlerObject, 'run'], (array)$JobDataObject ['JobProps']);
      }
    }
  }

  protected static function readDir ($dir) {
    $files = [];

    if (is_dir ($dir)) {
      if ($dh = opendir ($dir)) {
        while (($file = readdir ($dh)) !== false) {
          if (!in_array ($file, ['.', '..'])) {
            $contentPath = realpath ($dir . '/' . $file);
            $contentFileExtension = strtolower (pathinfo ($contentPath, PATHINFO_EXTENSION));

            if (is_file ($contentPath) &&
              in_array ($contentFileExtension, ['json'])) {
              array_push ($files, $contentPath);
            } elseif (is_dir ($contentPath)) {
              $files = array_merge ($files, self::readDir ($contentPath));
            }
          }
        }

        closedir($dh);
      }
    }

    return $files;
  }

  protected static function validJobCacheFile ($queueCacheFilePath) {
    $queueCacheFileExtensions = ['json'];

    return (boolean)(
      in_array (pathinfo ($queueCacheFilePath, PATHINFO_EXTENSION), $queueCacheFileExtensions)
    );
  }

  /**
   * @method void Start
   */
  public static function Start () {
    while (self::Running ()) {
      self::Run ();
    }
  }

  /**
   * @method void Stop
   */
  public static function Stop () {
    self::$running = false;
  }

  /**
   * @method void Running
   */
  public static function Running () {
    return self::$running;
  }
}
