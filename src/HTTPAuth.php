<?php

namespace Wpify\Snippets;

class HTTPAuth {
	public function __construct(
		private string $username = '',
		private string $password = ''
	) {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'http_basic_auth' ) );
	}

	public function admin_menu() {
		add_options_page( 'HTTP Basic Auth Settings', 'HTTP Basic Auth', 'manage_options', 'httpauthsettings', array(
			$this,
			'settings_page'
		) );
	}

	public function admin_init() {
		register_setting( 'http-basic-auth-settings', 'http_auth_enable' );
		register_setting( 'http-basic-auth-settings', 'http_auth_username' );
		register_setting( 'http-basic-auth-settings', 'http_auth_password' );
	}

	public function settings_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'HTTP Auth Settings', 'wpify-http-auth' ); ?></h2>
			<p><?php _e( 'Enable HTTP Basic Auth in case when you don\'t want the website to be exposed to the public, e.g. for staging sites.' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'http-basic-auth-settings' ); ?>
				<?php do_settings_sections( 'http-basic-auth-settings' ); ?>
				<table class="form-table">
					<tr>
						<th scope="row"><?php _e( 'Enable Auth', 'wpify-http-auth' ); ?></th>
						<td><input type="checkbox" name="http_auth_enable"
								   value="1" <?php checked( 1, get_option( 'http_auth_enable' ), true ); ?> /></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Username', 'wpify-http-auth' ); ?></th>
						<td><input type="text" name="http_auth_username"
								   value="<?php echo esc_attr( get_option( 'http_auth_username' ) ); ?>"/></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Password', 'wpify-http-auth' ); ?></th>
						<td><input type="text" name="http_auth_password"
								   value="<?php echo esc_attr( get_option( 'http_auth_password' ) ); ?>"/></td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Changes', 'wpify-http-auth' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function http_basic_auth() {
		$enabled = $this->username && $this->password;
		if ( ! $enabled ) {
			$enabled = ! is_admin() && get_option( 'http_auth_enable' ) == 1;
		}

		if ( $enabled ) { // Check if auth is enabled.
			$username = $this->username ?: get_option( 'http_auth_username' );
			$password = $this->password ?: get_option( 'http_auth_password' );

			// Ensure username and password are not empty.
			if ( empty( $username ) || empty( $password ) ) {
				return; // Exit if username or password is empty
			}

			if ( ! isset( $_SERVER['PHP_AUTH_USER'] ) ||
				 ! isset( $_SERVER['PHP_AUTH_PW'] ) ||
				 $_SERVER['PHP_AUTH_USER'] != $username ||
				 $_SERVER['PHP_AUTH_PW'] != $password ) {
				header( 'WWW-Authenticate: Basic realm="Staging Site"' );
				header( 'HTTP/1.0 401 Unauthorized' );
				exit( 'Access denied' );
			}
		}
	}
}
