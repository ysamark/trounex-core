<?php

namespace App\Repositories;

use Trounex\Helper;

trait HasSettings {
  /**
   * @method void
   *
   * set a setting for the model object
   *
   */
  public function setSetting (string $property, $value, string $type = 'string') {
    $type = strtolower ($type);

    if (preg_match ('/^(array|object)$/i', $type)) {
      $value = json_encode ($value);
    }

    $validTypes = [
      'string',
      'number',
      'boolean',
      'array',
      'object',
    ];

    if (!in_array ($type, $validTypes)) {
      $type = 'string';
    }

    $settings = $this->settings ();

    $settingFetch = $settings->where ([
      'property' => $property
    ]);

    if ($settingFetch->count () >= 1) {
      $setting = $settingFetch->first ();

      $setting->update ([
        'value' => $value,
        'type' => $type
      ]);

      return;
    }

    $settings->create ([
      'property' => $property,
      'value' => $value,
      'type' => $type
    ]);
  }

  /**
   * @method void
   *
   * get a setting for the model object
   *
   */
  public function getSetting (string $property) {
    $setting = $this->settings ()
      ->where (['property' => strtolower ($property)])
      ->first ();

    if ($setting) {
      switch ($setting->type) {
        case 'string':
          return $setting->value;

        case 'number':
          return is_numeric ($setting->value) ? (float)($setting->value) : 0;

        case 'boolean':
          return (boolean)($setting->value);

        case 'array':
          return Helper::ObjectsToArray (@json_decode ($setting->value));

        case 'object':
          return (object)(@json_decode ($setting->value));
      }
    }
  }

  /**
   * @method void
   *
   * set a setting for the model object
   *
   */
  public function defineSetting () {
    return call_user_func_array ([$this, 'setSetting'], func_get_args ());
  }

  /**
   * @method void
   *
   * update a setting for the model object
   *
   */
  public function updateSetting () {
    return call_user_func_array ([$this, 'setSetting'], func_get_args ());
  }

  /**
   * @method void
   *
   * unset a setting from the model object
   *
   */
  public function unsetSetting (string $property) {
    $type = strtolower ($type);

    $settings = $this->settings ();

    $settingFetch = $settings->where ([
      'property' => $property
    ]);

    if ($settingFetch->count () >= 1) {
      $setting = $settingFetch->first ();

      return $setting->delete ();
    }

    return false;
  }

  /**
   * @method boolean
   *
   * remove a setting from the model object
   *
   */
  public function removeSettings () {
    return call_user_func ([$this, 'unsetSetting']);
  }

  /**
   * @method App\Models\Setting[]
   *
   * get whole the settings associated to whole the model object
   *
   */
  public function getSettings () {
    $settings = $this->settings ();

    return $settings->get ();
  }

  /**
   * @method App\Models\Setting[]
   *
   * get whole the settings associated to whole the model object
   *
   */
  public function definedSettings () {
    return call_user_func ([$this, 'getSettings']);
  }

  /**
   * @method Illuminate\Database\Eloquent\Relations\MorphMany
   *
   * set the settings relation to the applied model
   *
   */
  public function settings () {
    return $this->morphMany ('App\Models\Setting', 'context');
  }
}
