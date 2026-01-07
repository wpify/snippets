<?php

namespace Wpify\Snippets;

class RemoveAccentInFilenames {
  public function __construct() {
    add_filter( 'sanitize_file_name', array( $this, 'sanitize_file_name' ) );
  }

  public function sanitize_file_name( $filename ) {
	  $parts = explode( '.', $filename );
	  foreach ( $parts as $key => $part ) {
		  $parts[ $key ] = sanitize_title( $part );
	  }

	  return implode( '.', $parts );
  }
}
