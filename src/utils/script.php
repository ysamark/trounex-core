<?php

function script ($javascriptPath = '') {

  $noJS = isset ($_GET ['_NO-JS']) ? trim (strtolower ($_GET ['_NO-JS'])) === 'true' : false;

  if ($noJS) {
    return;
  }

  return call_user_func_array ('asset', array_merge (
    ['javascript'], func_get_args ()
  ));
}
