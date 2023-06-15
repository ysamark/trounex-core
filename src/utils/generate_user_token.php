<?php

function generate_user_token () {
  $randomString = rand (0, 999999999) . 'hey';

  return md5 ($randomString);
}
