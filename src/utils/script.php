<?php

function script ($javascriptPath = '') {

  $noJS = isset ($_GET ['_NO-JS']) ? trim (strtolower ($_GET ['_NO-JS'])) === 'true' : false;

  if ($noJS) {
    return;
  }

  $defaultProps = [];

  $arguments = func_get_args ();

  list ($firstArgument) = $arguments;

  if (!preg_match ('/\.+\//', $firstArgument)) {
    array_push ($defaultProps, 'javascript');
  }

  $props = array_merge ($defaultProps, $arguments);

  return call_user_func_array ('asset', $props);
}
