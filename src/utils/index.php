<?php

namespace App\Utils;

$utilsFileList = glob (__DIR__ . '/*.php');

foreach ($utilsFileList as $utilFile) {
  $utilFilePath = realpath ($utilFile);

  if (__FILE__ !== $utilFilePath) {
    @include_once ($utilFilePath);
  }
}
