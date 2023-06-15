<?php

function partial ($partialRelativePath) {
  $partialAbsolutePath = join (DIRECTORY_SEPARATOR, [
    App\Server::GetRootPath (),
    'views',
    '_partials',
    $partialRelativePath
  ]);

  $args = array_slice (func_get_args (), 1, func_num_args ());

  if (!preg_match ('/(\.php)$/i', $partialAbsolutePath)) {
    $partialAbsolutePath .= '.php';
  }

  if (is_file ($partialAbsolutePath)) {
    forward_static_call_array ([App\View::class, 'Render'],
      array_merge ([$partialAbsolutePath], $args)
    );
  }
}
