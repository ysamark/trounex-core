<?php

namespace Trounex\Repository\ServerRepository;

trait ServerConfigs {
  use ConfigFileHandlers;

  /**
   * @method void
   */
  public static function SetupConfigs (array $config = []) {
    $config ['rootDir'] = self::getApplicationRootDir ();

    $configDirPath = join (DIRECTORY_SEPARATOR, [
      $config ['rootDir'], 'config'
    ]);

    $mainConfigFilePath = join (DIRECTORY_SEPARATOR, [
      $configDirPath, 'index.php'
    ]);

    $mainConfigFileData = self::handleConfigFile ($mainConfigFilePath);

    if (is_array ($mainConfigFileData)) {
      $config = array_merge ($config, $mainConfigFileData);
    }

    foreach (self::GetConfigFileTypes () as $configFileType) {
      $configFilesRe = join (DIRECTORY_SEPARATOR, [
        $configDirPath, '*.config.' . $configFileType
      ]);

      $configFilePaths = glob ($configFilesRe);

      foreach ($configFilePaths as $configFilePath) {
        $configFileHandler = join ('', [
          'handle', strtoupper ($configFileType), 'ConfigFile'
        ]);

        $configFileData = null; # self::handleConfigFile ($configFilePath);

        if (method_exists (self::class, $configFileHandler)) {
          $configFileData = forward_static_call_array ([self::class, $configFileHandler], [realpath ($configFilePath)]);
        }

        $configFileName = pathinfo ($configFilePath, PATHINFO_FILENAME);

        $configFileName = preg_replace ('/\.config$/i', '', $configFileName);

        if (isset ($config [$configFileName]) && is_array ($config [$configFileName]) && is_array ($configFileData)) {
          $configFileData = array_full_merge ($config [$configFileName], $configFileData);
        }

        $config [$configFileName] = $configFileData;
      }
    }

    /**
     * Set whole the config data to the app global config
     */
    foreach ($config as $prop => $value) {
      $configPropSetterName = "Set$prop";

      if (method_exists (self::class, $configPropSetterName)) {
        forward_static_call_array ([self::class, $configPropSetterName], [$value]);
      } else {
        self::$config [$prop] = $value;
      }
    }
  }

  /**
   * @method array
   */
  public static function updateServerConfigProperty (sting $propertyKey, $propertyValue = null) {
    $configArrayPropMap = [];

    $propertyKeySlices = preg_split ('/\.+/', $propertyKey);

    $lastIndex = -1 + count ($propertyKeySlices);

    for ($i = $lastIndex; $i >= 0; $i--) {
      $propertyKeySlice = $propertyKeySlices [$i];
      # verify if this is the last array item
      if ($i >= $lastIndex) {
        $configArrayPropMap = [
          $propertyKeySlice => $propertyValue
        ];
      } else {
        $configArrayPropMap = [
          $propertyKeySlice => $configArrayPropMap
        ];
      }
    }

    self::$config = array_full_merge (self::$config, $configArrayPropMap);

    // echo '<pre>';
    // print_r ($configArrayPropMap);
    // echo '</pre>';

    return $configArrayPropMap;
  }
}
