<?php

namespace Trounex\View\ViewEngine;

use Sammy\Packs\Capsule;
// use Sammy\Packs\ViewEngine as ViewEngineManager;
use Sammy\Packs\Samils\Capsule\CapsuleGlobalContext;

class CapsuleViewEngine extends ViewEngine {
  /**
   * @method void
   */
  public function render () {
    // $viewEngineManager = new ViewEngineManager ();

    $defaultViewEngineOptions = [
      'rootDir' => null,
      'extensions' => [
        'cache.php', 'php'
      ]
    ];

    $viewEngineOptions = conf ('viewEngine.options');

    if (!is_array ($viewEngineOptions)) {
      $viewEngineOptions = $defaultViewEngineOptions;
    } else {
      $viewEngineOptions = array_merge ($defaultViewEngineOptions, $viewEngineOptions);
    }

    // $viewEngineManagerOptions = array_merge ($viewEngineOptions, [
    //   'view-engine' => 'capsule/capsule',
    //   'viewsDir' => ,
    //   'file_extensions' => $viewEngineOptions ['extensions']
    // ]);

    // $viewEngineManager->start ($viewEngineManagerOptions);

    CapsuleGlobalContext::addProvider ($this->context, get_class ($this->context));

    // [
    //   ${ view_path },
    //   [
    //     'viewsDir' => ${ viewsDir },
    //     'template' => '${ viewPath }',
    //     'layout' => { default_layout },
    //     'action' => ${ action },
    //     'responseData' => ${ responseDataObject }
    //   ]
    // ]

    Capsule::RenderDOM (
      $this->viewFilePath,
      [
        'viewsDir' => $viewEngineOptions ['rootDir'],
        'layoutDir' => $this->layoutFilePath,
        'template' => $this->viewFilePath,
        'layout' => 'app',
        'action' => '${ action }',
        'responseData' => [
          'data' => 'Hello, Sam'
        ]
      ]
    );

    exit ('render a capsule component!!');
  }
}
