<?php

namespace App\Utils;

if (!session_id ()) {
  session_start ();
}

function ShutDownFunction () {
  $_SESSION ['flash'] = [];
  $_SESSION ['_post'] = null;

  $temporaryUploadFilesRe = join (DIRECTORY_SEPARATOR, [
    conf('paths.tmpUploadsPath'), '*'
  ]);

  $temporaryUploadFiles = glob ($temporaryUploadFilesRe);

  if (count ($temporaryUploadFiles) >= 2) {
    foreach ($temporaryUploadFiles as $temporaryUploadFile) {
      @unlink ($temporaryUploadFile);
    }
  }
}
