<?php

use Trounex\Application\Model;

/**
 * setup model observers
 */
$observerFiles = call_user_func_array ('array_merge', [
  glob (conf ('rootDir') . '/App/Models/*Observer.php'),
  glob (conf ('rootDir') . '/App/Models/Observers/*.php')
]);

foreach ($observerFiles as $fileAbsolutePath) {
  $observerFileName = pathinfo ($fileAbsolutePath, PATHINFO_FILENAME);
  $observerDirName = pathinfo (dirname ($fileAbsolutePath), PATHINFO_FILENAME);
  $observerModelFileName = preg_replace ('/Observer$/', '', $observerFileName);

  $observerModelRef = join ('\\', ['App', 'Models', $observerModelFileName]);
  $observerClassRef = join ('\\', [
    'App',
    preg_match ('/Observer/i', $observerDirName) ? 'Models\\Observer' : 'Models',
    $observerFileName
  ]);

  if (class_exists ($observerModelRef) && in_array (Model::class, class_parents ($observerModelRef)) && class_exists ($observerClassRef)) {
    $observerModelRef::observe (new $observerClassRef);
  }
}
