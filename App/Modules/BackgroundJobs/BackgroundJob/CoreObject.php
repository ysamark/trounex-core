<?php

namespace App\Modules\BackgroundJobs\BackgroundJob;

trait CoreObject {
  /**
   * @var array
   */
  private $props = [
    'name' => null
  ];

  /**
   * @method constructor
   */
  public function __construct (array $props) {
    /**
     * map each property inside the props array
     * an set it as a property for the current 
     * class object
     */
    foreach ($props as $prop => $value) {
      $this->props[$prop] = $value;
    }
  }

  /**
   * @method getter
   */
  public function __get (string $prop) {
    if (isset ($this->props[$prop])) {
      return $this->props[$prop];
    }
  }
}
