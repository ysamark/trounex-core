<?php

namespace App\Utils;

use App\Server;
use Symfony\Component\Dotenv\Dotenv;

final class Env {
  /**
   * @var boolean
   */
  private static $dotEnvLoaded = false;

  /**
   * @method array
   */
  public static function Get ($envVariableName, $envVariableDefaultValue = null) {
    if (!self::dotEnvLoaded ()) {

      $rootDir = Server::getApplicationRootDir ();
      $dotEnvFilePath = $rootDir . '/.env';
      $dotEnvLocalFilePath = $dotEnvFilePath . '.local';

      $dotEnvFilePathPaths = array_filter ([$dotEnvFilePath, $dotEnvLocalFilePath], function ($dotEnvFilePath) {
        return is_file ($dotEnvFilePath);
      });

      self::loadEnvBasedConfigFile ($dotEnvFilePathPaths);

      $envBasedConfigFile = self::envBasedConfigFileExists ();

      if ($envBasedConfigFile) {
        self::loadEnvBasedConfigFile ($envBasedConfigFile);
      }
    }

    if (is_string ($envVariableName) && $envVariableName) {
      if (isset ($_ENV [$envVariableName])) {
        return $_ENV [$envVariableName];
      }

      return $envVariableDefaultValue;
    }
  }

  /**
   * @method boolean|string
   */
  protected static function envBasedConfigFileExists () {
    $env = isset ($_ENV ['PHP_ENV']) ? $_ENV ['PHP_ENV'] : 'production';

    $rootDir = Server::getApplicationRootDir ();
    $dotEnvFilePath = $rootDir . '/.env.' . $env;
    $dotEnvLocalFilePath = $rootDir . '/.env.' . $env . '.local';

    $dotEnvFilePaths = array_filter ([$dotEnvFilePath, $dotEnvLocalFilePath], function ($dotEnvFilePath) {
      return is_file ($dotEnvFilePath);
    });

    return is_file ($dotEnvFilePath) ? $dotEnvFilePaths : false;
  }

  /**
   * @method void
   */
  protected static function loadEnvBasedConfigFile (array $envBasedConfigFile) {
    call_user_func_array ([new Dotenv, 'overload'], $envBasedConfigFile);

    if (!self::$dotEnvLoaded) {
      self::$dotEnvLoaded = true;
    }
  }

  /**
   * @method boolean
   */
  protected static function dotEnvLoaded () {
    return self::$dotEnvLoaded;
  }
}
