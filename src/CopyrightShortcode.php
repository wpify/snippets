<?php

namespace Wpify\Tools;

use Wpify\Core_4_0\Abstracts\AbstractComponent;

class CopyrightShortcode extends AbstractComponent
{
  public function setup()
  {
    add_shortcode('copyright', [$this, 'render']);
  }

  public function render($atts, $content = '')
  {
    return '&copy;&nbsp;' . date('Y') . '&nbsp;' . $content;
  }
}
