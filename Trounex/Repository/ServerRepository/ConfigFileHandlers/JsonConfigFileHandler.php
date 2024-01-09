<?php

namespace Trounex\Repository\ServerRepository\ConfigFileHandlers;

use Trounex\Helper;

trait JsonConfigFileHandler {
  /**
   * @method mixed
   */
  protected static function handleJSONConfigFile (string $configFile) {
    $configFileContent = file_get_contents ($configFile);

    $configFileData = json_decode (trim ($configFileContent));

    return Helper::ObjectsToArray ($configFileData);
  }
}
