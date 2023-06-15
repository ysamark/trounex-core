<?php

function image () {
  $imagePath = join (DIRECTORY_SEPARATOR, [
    App\Server::GetRootPath (),
    'assets',
    'images',
    join (DIRECTORY_SEPARATOR, func_get_args ())
  ]);

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
