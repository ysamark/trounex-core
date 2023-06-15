<?php

use Rakit\Validation\Validator;

function form_validator () {
  $validator = new Validator;

  $camel2snakecase = function ($input) {
    $pattern = '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!';
    preg_match_all ($pattern, $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
      $match = $match == strtoupper ($match) ?
          strtolower ($match) :
        lcfirst ($match);
    }
    return implode('_', $ret);
  };

  $ruleClassFileList = glob (dirname (__DIR__) . '/App/Utils/Validator/Rules/*.php');

  foreach ($ruleClassFileList as $ruleClassFile) {
    $ruleClassFileName = pathinfo ($ruleClassFile, PATHINFO_FILENAME);
    $ruleName = $camel2snakecase ($ruleClassFileName);
    $ruleClassName = "App\Utils\Validator\Rules\\{$ruleClassFileName}";

    $validator->addValidator ($ruleName, new $ruleClassName);
  }

  return $validator;
}
