<?php

namespace Wpify\Snippets;

class HTTPAuth {
  public function __construct(
	  private string $username,
	  private string $password
  ) {
	  $this->require_auth();
  }

	public function require_auth() {
		$AUTH_USER = $this->username;
		$AUTH_PASS = $this->password;
		header('Cache-Control: no-cache, must-revalidate, max-age=0');
		$has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
		$is_not_authenticated = (
			!$has_supplied_credentials ||
			$_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
			$_SERVER['PHP_AUTH_PW']   != $AUTH_PASS
		);
		if ($is_not_authenticated) {
			header('HTTP/1.1 401 Authorization Required');
			header('WWW-Authenticate: Basic realm="Access denied"');
			exit;
		}
	}
}
