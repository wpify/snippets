<?php

namespace Wpify\Tools;

use Wpify\Core\AbstractComponent;

class RemoveScriptVersionFunctionality extends AbstractComponent
{
  public function setup()
  {
    add_filter('style_loader_src', [$this, 'remove_script_version'], 9999, 2);
    add_filter('script_loader_src', [$this, 'remove_script_version'], 9999, 2);
    remove_action('wp_head', 'wp_generator');
    add_filter('the_generator', '__return_empty_string');
  }

  public function remove_script_version($src, $handle)
  {
    if (strpos($src, 'ver=')) {
      $src = remove_query_arg('ver', $src);
    }

    return $src;
  }
}
