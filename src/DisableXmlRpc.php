<?php

namespace Wpify\Snippets;

class DisableXmlRpc {
  public function __construct() {
    add_filter( 'xmlrpc_enabled', '__return_false' );
  }
}
