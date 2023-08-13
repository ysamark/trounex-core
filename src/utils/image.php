<?php

function image () {
  $imagePathPrefixes = [
    '', 'uploads'
  ];

  foreach ($imagePathPrefixes as $imagePathPrefix) {
    $imagePath = join (DIRECTORY_SEPARATOR, [
      App\Server::GetRootPath (),
      'assets',
      'images',
      $imagePathPrefix,
      join (DIRECTORY_SEPARATOR, func_get_args ())
    ]);

    $imagePath = realpath ($imagePath);

    if (!empty ($imagePath) && is_file ($imagePath)) {
      $imagePath = realpath ($imagePath);

      $imageFileContent = file_get_contents ($imagePath);
      $imageFileExtension = pathinfo ($imagePath, PATHINFO_EXTENSION);

      return join (',', [
        'data:image/'.$imageFileExtension.';base64',
        base64_encode ($imageFileContent)
      ]);
    }
  }
}
