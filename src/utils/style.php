<?php

function style ($stylesheetPath = '') {
  return call_user_func_array ('asset', array_merge (
    ['stylesheet'], func_get_args ()
  ));
}
