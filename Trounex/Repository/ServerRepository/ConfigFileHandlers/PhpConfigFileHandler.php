<?php

namespace Trounex\Repository\ServerRepository\ConfigFileHandlers;

trait PhpConfigFileHandler {
  /**
   * @method mixed
   */
  protected static function handlePHPConfigFile (string $configFile) {
    return self::handleConfigFile ($configFile);
  }
}
