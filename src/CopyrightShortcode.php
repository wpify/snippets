<?php

namespace Wpify\Snippets;

class CopyrightShortcode {
  public function __construct() {
    add_shortcode( 'copyright', [ $this, 'render' ] );
  }

  public function render( $atts, $content = '' ) {
    return '&copy;&nbsp;' . date( 'Y' ) . '&nbsp;' . $content;
  }
}
