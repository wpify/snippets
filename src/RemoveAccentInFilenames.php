<?php

namespace Wpify\Snippets;

class RemoveAccentInFilenames {
  public function __construct() {
    add_filter( 'sanitize_file_name', array( $this, 'sanitize_file_name' ) );
  }

  public function sanitize_file_name( $filename ) {
    $dotp = strrpos( $filename, '.' );

    return sanitize_title( substr( $filename, 0, $dotp ) ) . '.' . sanitize_title( substr( $filename, $dotp + 1 ) );
  }
}
