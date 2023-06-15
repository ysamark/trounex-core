<?php

function post_param ($inputRef = null) {
  if (!(is_string ($inputRef) 
    && !empty ($inputRef))) {
    return null;
  }

  $inputRefSlices = array_merge (['_post'], preg_split ('/\.+/', $inputRef));

  $source = $_SESSION;

  for ($i = 0; $i < count ($inputRefSlices); $i++) {
    $inputRefSlicesItem = $inputRefSlices [$i];

    # Verify if it's at the end of the array
    if ($i + 1 >= count ($inputRefSlices)) {
      # it is the end
      if (isset ($source [$inputRefSlicesItem]) 
        && is_scalar ($source [$inputRefSlicesItem]) 
        && !empty ($source [$inputRefSlicesItem])
        && !!$source [$inputRefSlicesItem]) {
        return $source [$inputRefSlicesItem];
      }
    }

    if (!(isset ($source [$inputRefSlicesItem])
      && is_array ($source [$inputRefSlicesItem]))) {
      return null;
    }

    $source = $source [$inputRefSlicesItem];
  }
}
