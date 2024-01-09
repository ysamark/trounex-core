<?php

namespace Trounex\Repository\ServerRepository;

trait ConfigFileHandlers {
  use ConfigFileHandlers\PhpConfigFileHandler;
  use ConfigFileHandlers\JsonConfigFileHandler;
  use ConfigFileHandlers\YamlConfigFileHandler;
  use ConfigFileHandlers\XmlConfigFileHandler;
  use ConfigFileHandlers\TxtConfigFileHandler;

  /**
   * @method mixed
   */
  private static function handleConfigFile ($configFile) {
    if (is_file ($configFile)) {
      $configFileData = @require ($configFile);

      return $configFileData;
    }
  }
}
