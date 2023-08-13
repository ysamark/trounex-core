<?php

function style ($stylesheetPath = '') {
  $defaultProps = [];

  $arguments = func_get_args ();

  list ($firstArgument) = $arguments;

  if (!preg_match ('/\.+\//', $firstArgument)) {
    array_push ($defaultProps, 'stylesheet');
  }

  $props = array_merge ($defaultProps, $arguments);

  return call_user_func_array ('asset', $props);
}
