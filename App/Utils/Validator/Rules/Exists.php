<?php

namespace App\Utils\Validator\Rules;

use PDOException;
use Rakit\Validation\Rule;

/**
 * exists
 *
 * Use:
 *
 * 'field' => 'exists:user,username'
 */
class Exists extends Rule {
  /**
   * @var string
   */
  protected $message = 'There is not a $0 with such :attribute';

  /**
   * @var array
   */
  protected $fillableParams = [
    'model',
    'column'
  ];

  /**
   * @method boolean
   */
  public function check ($value): bool {
    $modelName = ucfirst (trim ((string)$this->parameter ('model')));
    $columnName = trim ((string)$this->parameter ('column'));

    $this->message = str_replace ('$0', lcfirst ($modelName), $this->message);

    $columnName = empty ($columnName) ? 'id' : $columnName;

    if (!(empty($modelName))) {
      $modelClassName = "\App\Models\\$modelName";

      if (!class_exists($modelClassName)) {
        return false;
      }

      try {
        $lines = $modelClassName::where([ $columnName => $value ]);
        return (boolean)($lines->count() >= 1);
      } catch (PDOException $e) {
      }
    }

    return false;
  }
}
