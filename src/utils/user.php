<?php

use App\Models\User;

function user ($userId = null) {
  if (is_null ($userId)
    && isset ($_SESSION)
    && is_array ($_SESSION)
    && isset ($_SESSION ['user'])
    && is_array ($_SESSION ['user'])
    && isset ($_SESSION ['user']['id'])) {
    $userId = $_SESSION ['user']['id'];
  }

  $userFetch = User::where ([ 'id' => $userId ]);

  if ($userFetch->count () >= 1) {
    return $userFetch->first ();
  }

  return new User;
}
