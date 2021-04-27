<?php

function makeSuccessMessage($message) {
  return '<div class="alert alert-success alert-dismissible fade show" role="alert">'
      . $message
      . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>';
}

function makeErrorMessage($message) {
  return '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
      . $message
      . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>';
}

function getSpeechFiles($dir)
{
  $files = scandir($dir, SCANDIR_SORT_DESCENDING);
  $result = [];
  foreach ($files as $file) {
    if(strpos($file, '.ogg') !== false)
    {
      $result[] = $file;
    }
  }
  return $result;
}

?>