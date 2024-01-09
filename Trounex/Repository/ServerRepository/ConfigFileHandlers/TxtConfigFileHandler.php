<?php

namespace Trounex\Repository\ServerRepository\ConfigFileHandlers;

trait TxtConfigFileHandler {
  /**
   * @method mixed
   */
  protected static function handleTXTConfigFile (string $configFile) {
    $configFileContent = trim (file_get_contents ($configFile));

    return preg_replace ('/(\\\)$/', '', preg_replace ('/^(\\\)/', '', $configFileContent));
  }
}
