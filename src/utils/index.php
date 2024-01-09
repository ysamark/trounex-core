<?php

namespace App\Utils;

$utilsFileList = glob (__DIR__ . '/*.php');

foreach ($utilsFileList as $utilFile) {
  $utilFilePath = realpath ($utilFile);
  $includedFiles = get_included_files ();

  if (!in_array ($utilFilePath, $includedFiles)) {
    @include_once ($utilFilePath);
  }
}
