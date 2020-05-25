<?php

namespace Wpify\Tools;

use Wpify\Core\AbstractComponent;

class RemoveAccentInFilenamesFunctionality extends AbstractComponent
{
  public function setup()
  {
    add_filter('sanitize_file_name', [$this, 'sanitize_file_name']);
  }

  public function sanitize_file_name($filename)
  {
    $dotp = strrpos($filename, '.');
    return sanitize_title(substr($filename, 0, $dotp)) . '.' . sanitize_title(substr($filename, $dotp + 1));
  }
}
