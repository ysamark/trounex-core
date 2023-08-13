<?php

use Trounex\Application\Config;

function conf ($props = null) {
  $config = new Config ($props);

  if (is_array ($props) || is_null ($props)) {
    return $config;
  }

  if (!is_string ($props)) {
    throw new Exception ('TypeError: first argument for the conf helper should be a string or an array, leave it null to use the default configs');
  }

  $propKeyMap = preg_split ('/\.+/', preg_replace ('/\s+/', '', $props));
  $propKeyMapLastIndex = -1 + count ($propKeyMap);

  foreach ($propKeyMap as $index => $propKey) {
    if (is_object ($config) && isset ($config->$propKey)) {
      if ($index >= $propKeyMapLastIndex) {
        return $config->$propKey;
      }

      $config = $config->$propKey;
    } elseif (is_array ($config) && isset ($config [$propKey])) {
      if ($index >= $propKeyMapLastIndex) {
        return $config [$propKey];
      }

      $config = $config [$propKey];
    } else {
      throw new Exception ('TypeError: type of config('.gettype($config).') property does not support nested keys');
    }
  }
}
