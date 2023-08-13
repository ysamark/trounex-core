<?php

use Trounex\Helper;
use Rakit\Validation\Validator;

function form_validator (array $formData = null, array $formDataRules = null) {
  $validator = new Validator;

  $camel2snakeCase = function ($input) {
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

  $ruleClassFileListArr = [
    glob (Helper::GetModuleRootDir () . '/App/Utils/Validator/Rules/*.php'),
    glob (conf ('rootDir') . '/App/Utils/Validator/Rules/*.php')
  ];

  $ruleClassFileListArr = array_filter ($ruleClassFileListArr, function ($arr) {
    return is_array ($arr);
  });

  $ruleClassFileList = call_user_func_array ('array_merge', $ruleClassFileListArr);

  foreach ($ruleClassFileList as $ruleClassFile) {
    $ruleClassFileName = pathinfo ($ruleClassFile, PATHINFO_FILENAME);
    $ruleName = $camel2snakeCase ($ruleClassFileName);
    $ruleClassName = "App\Utils\Validator\Rules\\{$ruleClassFileName}";

    if (class_exists ($ruleClassName)) {
      $validator->addValidator ($ruleName, new $ruleClassName);
    }
  }

  if ($formData && $formDataRules) {
    $validation = $validator->validate ($formData, $formDataRules);

    $validation->validate ();

    return $validation;
  }

  return $validator;
}
