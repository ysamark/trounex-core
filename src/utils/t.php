<?php

use Stichoza\GoogleTranslate\GoogleTranslate;
use GuzzleHttp\Exception\ConnectException;

function t ($text) {
  $tr = new GoogleTranslate ('pt-br');

  try {
    $text = $tr->translate ($text);
  } catch (ConnectException $e) {
    # error ...
  }

  return $text;
}
