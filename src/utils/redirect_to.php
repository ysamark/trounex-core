<?php

function redirect_to () {
  header ('location: ' . call_user_func_array ('path', func_get_args ()));
  exit (0);
}
