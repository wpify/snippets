<?php

namespace Wpify\Snippets;

class DeferScripts {
  public function __construct() {
    add_action( 'script_loader_tag', [ $this, 'defer_scripts' ], 10, 2 );
  }

  public function defer_scripts( $tag, $handle ) {
    $no_defer = apply_filters( 'wpify_snippets_no_defer_scripts', [ 'jquery-core' ] );

    if ( ! is_admin() && preg_match( '/\sdefer(=["\']defer["\'])?\s/', $tag ) !== 1 && ! in_array( $handle, $no_defer ) ) {
      return str_replace( ' src', ' defer src', $tag );
    }

    return $tag;
  }
}
