<?php

namespace App\Utils\Validator\Rules;

use PDOException;
use Rakit\Validation\Rule;

/**
 * unique
 *
 * Use:
 *
 * 'field' => 'unique:user,username'
 */
class Unique extends Rule {
  /**
   * @var string
   */
  protected $message = ':attribute is already being used';

  /**
   * @var array
   */
  protected $fillableParams = [
    'model',
    'column'
  ];

  /**
   * @var string
   *
   * model property ref regular expression
   *
   */
  private $modelPropRe = '/\(([a-zA-Z_\.\s]+)\)$/';

  /**
   * @var string
   *
   * model property data context regular expression
   *
   */
  private $modelPropDataContextRe = '/^(post|param|get|session|cookie)\./i';

  /**
   * @method boolean
   */
  public function check ($value): bool {
    $modelName = ucfirst(trim((string)$this->parameter('model')));
    $columnName = trim((string)$this->parameter('column'));

    $this->message = ':attribute is already being used by another ' . (
      preg_replace ($this->modelPropRe, '', lcfirst ($modelName))
    );

    if (!(empty ($modelName) || empty ($columnName))) {
      $modelPropValue = null;
      $modelPropPath = null;

      if (preg_match ($this->modelPropRe, $modelName, $match)) {
        $modelPropDataContext = 'param';
        $modelPropPath = trim ($match [1]);

        if (preg_match ($this->modelPropDataContextRe, $modelPropPath, $modelPropDataContextMatch)) {
          $modelPropDataContext = $modelPropDataContextMatch [1];
          $modelPropPath = preg_replace ($this->modelPropDataContextRe, '', $modelPropPath);
        }

        $modelPropValue = $this->getModelPropByContext ($modelPropPath, $modelPropDataContext);

        $modelName = preg_replace ($this->modelPropRe, '', $modelName);
      }

      $modelClassName = "\App\Models\\$modelName";

      if (!class_exists($modelClassName)) {
        return false;
      }

      try {
        $lines = $modelClassName::where ([
          $columnName => $value
        ]);

        if (!is_null ($modelPropPath)) {
          $lines->where ($modelPropPath, '!=', $modelPropValue);
        }

        return (boolean)($lines->count () < 1);
      } catch (PDOException $e) {
      }
    }

    return false;
  }

  /**
   * @method mixed
   *
   * get model prop data from a given context
   *
   */
  private function getModelPropByContext (string $prop, string $context = 'param') {
    switch (strtolower ($context)) {
      case 'post':
        return isset ($_POST [$prop]) ? $_POST [$prop] : null;
        break;

      case 'param':
        return param ($prop);
        break;

      case 'get':
        return isset ($_GET [$prop]) ? $_GET [$prop] : null;
        break;

      case 'session':
        return isset ($_SESSION) && isset ($_SESSION [$prop]) ? $_SESSION [$prop] : null;
        break;

      case 'cookie':
        return isset ($_COOKIE [$prop]) ? $_COOKIE [$prop] : null;
        break;

      default:
        return param ($prop);
        break;
    }
  }
}
