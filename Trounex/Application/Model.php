<?php

namespace Trounex\Application;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class Model extends EloquentModel {
  /**
   * call fallback
   */
  public function __call ($methodName, $arguments) {
    $re = '/^(all|(fir|la)st)/i';
    $methodRefName = lcfirst(preg_replace ($re, '', $methodName));

    if (preg_match ('/^all([A-Z_].*)/i', $methodName)
        && method_exists ($this, $methodRefName)) {

      $relationData = call_user_func ([$this, $methodRefName]);

      if ($relationData instanceof HasMany) {
        return $relationData->getResults();
      }
    }

    if (preg_match ('/^(first|last)([A-Z_].*)/i', $methodName, $match)
        && method_exists ($this, $methodRefName)) {

      $relationData = call_user_func ([$this, $methodRefName]);

      if ($relationData instanceof HasMany
        || $relationData instanceof BelongsTo) {
        return call_user_func ([$relationData, strtolower ($match [1])]);
      }
    }

    return parent::__call($methodName, $arguments);
  }
}
