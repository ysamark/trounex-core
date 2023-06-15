<?php

use Ramsey\Uuid\Uuid;

function uuid () {
  return call_user_func ([Uuid::uuid4 (), 'toString']);
}
