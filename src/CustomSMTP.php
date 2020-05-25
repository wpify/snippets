<?php

namespace Wpify\Tools;

use PHPMailer;
use Wpify\Core\AbstractComponent;

class CustomSMTP extends AbstractComponent
{
  public function setup()
  {
    add_action('phpmailer_init', [$this, 'smtp']);
  }

  public function smtp(PHPMailer $mailer)
  {
    if (defined('SMTP_HOST')) {
      $mailer->Host = SMTP_HOST;
      $mailer->IsSMTP();
    }

    if (defined('SMTP_PORT')) {
      $mailer->Port = SMTP_PORT;
    }

    if (defined('SMTP_USERNAME')) {
      $mailer->Username = SMTP_USERNAME;
    }

    if (defined('SMTP_PASSWORD')) {
      $mailer->Password = SMTP_PASSWORD;
    }
  }
}
