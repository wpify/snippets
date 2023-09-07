<?php

namespace Wpify\Snippets;

/**
 * Example config:
 * SMTP::class    => ( new CreateDefinitionHelper() )
 * ->constructor(
 * env( 'SMTP_HOST' ),
 * env( 'SMTP_USERNAME' ),
 * env( 'SMTP_PASSWORD' ),
 * env( 'SMTP_FROM' ),
 * env( 'SMTP_FROM_NAME' ),
 * env( 'SMTP_PORT' ),
 * env( 'SMTP_SECURE' ),
 * env( 'SMTP_AUTH' ),
 * array(
 * 'ssl' => array(
 * 'verify_peer'       => false,
 * 'verify_peer_name'  => false,
 * 'allow_self_signed' => true,
 * ),
 * ),
 * ),
 * Example .env:
 * SMTP_HOST="smtp.com"
 * SMTP_FROM="my@email.com"
 * SMTP_FROM_NAME="Me"
 * SMTP_USERNAME="my@email.com"
 * SMTP_PASSWORD="password"
 * SMTP_PORT="25"
 * SMTP_SECURE=''
 * SMTP_AUTH=true
 */
class SMTP {
	public function __construct(
		private string $host,
		private string $username,
		private string $password,
		private string $from = '',
		private string $from_name = '',
		private int $port = 465,
		private string $secure = 'ssl',
		private bool $auth = true,
		private array $smtp_options = array(),

	) {
		add_action( 'phpmailer_init', [ $this, 'smtp_email' ] );
	}

	/**
	 * Add SMTP Email
	 *
	 * @param $phpmailer
	 *
	 * @return void
	 */
	public function smtp_email( $phpmailer ): void {
		$phpmailer->isSMTP();
		$phpmailer->Host        = $this->host;
		$phpmailer->SMTPAuth    = $this->auth;
		$phpmailer->Port        = $this->port;
		$phpmailer->Username    = $this->username;
		$phpmailer->Password    = $this->password;
		$phpmailer->SMTPOptions = $this->smtp_options;

		if ( $this->secure ) {
			$phpmailer->SMTPSecure = $this->secure;
		}

		if ( $this->from ) {
			$phpmailer->From = $this->from;
		}
		if ( $this->from_name ) {
			$phpmailer->FromName = $this->from_name;
		}
	}
}
