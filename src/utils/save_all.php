<?php

use PDOException;
use App\Models\AppModel;
use Trounex\Helpers\ModelDataSaveErrorHelper;

function save_all () {
  $modelObjects = array_filter (func_get_args (), function ($modelObject) {
    $modelObjectClassName = get_class ($modelObject);
    $modelObjectClassParents = class_parents ($modelObjectClassName);

    return in_array (AppModel::class, $modelObjectClassParents);
  });

  foreach ($modelObjects as $index => $modelObject) {
    $modelObjectClassNamePaths = preg_split ('/\\\+/', get_class ($modelObject));
    $modelName = join ('', array_slice ($modelObjectClassNamePaths, -1 + count ($modelObjectClassNamePaths), 1));

    try {
      if (!$modelObject->save ()) {
        new ModelDataSaveErrorHelper ([
          'index' => $index,
          'message' => join (' ', [
            $modelName,
            'could not be saved'
          ])
        ]);

        return false;
      }
    } catch (PDOException $error) {
      new ModelDataSaveErrorHelper ([
        'index' => $index,
        'message' => join (' ', [
          $modelName,
          'could not be saved'
        ]),
        'error' => $error
      ]);

      return false;
    }

  }

  return true;
}
