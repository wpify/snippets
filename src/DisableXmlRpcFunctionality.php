<?php

namespace Wpify\Tools;

use Wpify\Core\AbstractComponent;

class DisableXmlRpcFunctionality extends AbstractComponent
{
  public function setup()
  {
    add_filter('xmlrpc_enabled', '__return_false');
  }
}
