<?php

namespace Wpify\Tools;

use Wpify\Core_3_0\Abstracts\AbstractComponent;

class DisableXmlRpc extends AbstractComponent
{
  public function setup()
  {
    add_filter('xmlrpc_enabled', '__return_false');
  }
}
