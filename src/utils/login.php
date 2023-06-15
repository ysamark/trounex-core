<?php

use App\Models\User;

function logged_in_user () {
  if (!(isset ($_SESSION)
    && is_array ($_SESSION)
    && isset ($_SESSION ['user'])
    && is_array ($_SESSION ['user'])
    && isset ($_SESSION ['user']['id']))) {
    return false;
  }

  $userId = (int)($_SESSION ['user']['id']);

  $userFetch = User::where ([ 'id' => $userId ]);

  if ($userFetch->count () >= 1) {
    return $userFetch->first ();
  }

  return false;
}
