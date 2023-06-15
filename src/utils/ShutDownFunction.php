<?php

namespace App\Utils;

if (!session_id ()) {
  session_start ();
}

function ShutDownFunction () {
  $_SESSION ['flash'] = [];
  $_SESSION ['_post'] = null;
}
