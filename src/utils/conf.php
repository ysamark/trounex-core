<?php

use Trounex\Application\Config;

function conf (array $props = null) {
  return new Config ($props);
}
