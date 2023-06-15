<?php

function valid_app_token ($token) {
  return $token === get_app_token ();
}
