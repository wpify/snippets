<?php

namespace Wpify\Snippets;

/**
 * AdminNotices Class
 *
 * This class is responsible for managing the admin notices.
 * It provides methods for creating and dismissing admin notices.
 */
class AdminNotices {
	/**
	 * The meta key for storing the dismissed admin notices for a user.
	 */
	const DISMISSED_MESSAGES_META_KEY = 'wpify_dismissed_notices';

	/**
	 * The AJAX action for dismissing an admin notice.
	 */
	const DISMISS_AJAX_ACTION = 'wpify_notice_dismiss';

	/**
	 * The CSS class for an admin notice.
	 */
	const ADMIN_NOTICE_CLASS = 'wpify-notice';

	/**
	 * @var array The list of admin notices.
	 */
	private array $notices = array();

	private bool $script_added = false;

	/**
	 * The constructor method.
	 *
	 * It sets up the hooks for displaying and dismissing the admin notices.
	 */
	public function __construct() {
		add_action( 'wp_ajax_' . self::DISMISS_AJAX_ACTION, array( $this, 'dismiss_notice' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'network_admin_notices', array( $this, 'network_admin_notices' ) );
	}

	/**
	 * The method for adding an admin notice.
	 *
	 * It adds the admin notice to the list of admin notices.
	 */
	public function add_notice( string $id, callable $callback, array $args = array() ): void {
		$args = wp_parse_args(
			$args,
			array(
				'type'               => 'info',
				'dismissible'        => true,
				'additional_classes' => array(),
				'attributes'         => array(),
				'global'             => false,
				'network'            => false,
				'capability'         => 'read',
			),
		);

		$this->notices[ $id ] = array(
			'callback'           => $callback,
			'type'               => $args['type'],
			'dismissible'        => $args['dismissible'],
			'additional_classes' => $args['additional_classes'],
			'attributes'         => $args['attributes'],
			'global'             => $args['global'],
			'network'            => $args['network'],
			'capability'         => $args['capability'],
		);
	}

	/**
	 * The method for displaying the admin notices.
	 *
	 * It displays the admin notices that have not been dismissed by the current user.
	 *
	 * @internal
	 */
	public function admin_notices( bool $network = false ): void {
		foreach ( $this->get_notices( $network ) as $id => $notice ) {
			$notice['id'] = $id;

			$this->render_notice( $notice );
		}
	}

	/**
	 * The method for displaying the network admin notices.
	 *
	 * It displays the network admin notices that have not been dismissed by the current user.
	 *
	 * @internal
	 */
	public function network_admin_notices(): void {
		$this->admin_notices( true );
	}

	/**
	 * The method for dismissing an admin notice.
	 *
	 * @internal
	 */
	public function dismiss_notice(): void {
		if ( ! wp_verify_nonce( filter_input( INPUT_GET, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS ), self::DISMISS_AJAX_ACTION ) ) {
			wp_send_json_error( array( 'message' => 'Invalid nonce' ) );

			return;
		}

		$id                   = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS );
		$dismissed_messages   = $this->get_dismissed_messages_by_notice_id( $id );
		$dismissed_messages[] = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_SPECIAL_CHARS );

		$this->set_dismissed_message( $id, $dismissed_messages );
	}

	/**
	 * The method for rendering an admin notice.
	 *
	 * It renders the admin notice and enqueues the script for dismissing the admin notice.
	 */
	private function render_notice( array $notice ): void {
		$paragraph_wrap       = true;
		$id                   = $notice['id'];
		$type                 = $notice['type'];
		$dismissible          = $notice['dismissible'];
		$additional_classes   = $notice['additional_classes'];
		$additional_classes[] = self::ADMIN_NOTICE_CLASS;
		$attributes           = $notice['attributes'] ?? array();

		ob_start();
		$result  = call_user_func( $notice['callback'] );
		$message = ob_get_clean();

		if ( ! empty( $result ) ) {
			$notice['title']   = $result['title'] ?? '';
			$notice['message'] = $result['message'] ?? $message;
		} elseif ( ! empty( $message ) ) {
			$notice['title']   = '';
			$notice['message'] = $message;
		}

		if ( empty( $notice['title'] ) && empty( $notice['message'] ) ) {
			return;
		}

		if ( ! empty( $notice['title'] ) ) {
			$message        .= '<h3>' . esc_html( $notice['title'] ) . '</h3><p>' . $notice['message'] . '</p>';
			$paragraph_wrap = false;
		} else {
			$message = $notice['message'];
		}

		$args = array(
			'type'               => $type,
			'dismissible'        => $dismissible,
			'id'                 => $id,
			'additional_classes' => $additional_classes,
			'paragraph_wrap'     => $paragraph_wrap,
			'attributes'         => $attributes,
		);

		if ( function_exists( 'wp_admin_notice' ) ) {
			wp_admin_notice( $message, $args );
		} else {
			$this->render_notice_legacy( $message, $args );
		}

		if ( $dismissible && ! $this->script_added ) {
			$this->script_added = true;

			$this->render_dismiss_script();
		}
	}

	/**
	 * The method for rendering the script for dismissing the admin notice.
	 *
	 * It renders the script for dismissing the admin notice.
	 */
	private function render_dismiss_script(): void {
		$ajax_url    = esc_js( admin_url( 'admin-ajax.php' ) );
		$ajax_action = esc_js( self::DISMISS_AJAX_ACTION );
		$nonce       = esc_js( wp_create_nonce( self::DISMISS_AJAX_ACTION ) );
		$classname   = esc_js( self::ADMIN_NOTICE_CLASS );

		$script = <<<JS
		jQuery(document).ready(function ($) {
			$(document).on('click', '.$classname .notice-dismiss', function () {
				$.ajax({
					url: '$ajax_url',
					data: {
						action: '$ajax_action',
						nonce: '$nonce',
						id: $(this).closest('.$classname').attr('id'),
					},
				});
			});
		});
		JS;

		if ( wp_script_is( 'jquery', 'done' ) ) {
			echo '<script type="text/javascript">' . $script . '</script>';
		} else {
			wp_add_inline_script( 'jquery', $script );
		}
	}

	/**
	 * The method for getting the dismissed admin notices by the notice ID.
	 *
	 * It gets the dismissed admin notices by the notice ID.
	 */
	private function get_dismissed_messages_by_notice_id( string $notice_id ) {
		$dismissed_messages = array();

		if ( $this->is_network_notice( $notice_id ) ) {
			$dismissed_messages = get_site_option( self::DISMISSED_MESSAGES_META_KEY, array() );
		} elseif ( $this->is_site_notice( $notice_id ) ) {
			$dismissed_messages = get_option( self::DISMISSED_MESSAGES_META_KEY, array() );
		} elseif ( $this->is_user_notice( $notice_id ) ) {
			$user               = wp_get_current_user();
			$dismissed_messages = get_user_meta( $user->ID, self::DISMISSED_MESSAGES_META_KEY, true );
			$dismissed_messages = is_array( $dismissed_messages ) ? $dismissed_messages : array();
		}

		return $dismissed_messages;
	}

	/**
	 * The method for setting the dismissed admin notice.
	 *
	 * It sets the dismissed admin notice for the current user.
	 */
	private function set_dismissed_message( string $id, array $dismissed_messages ): void {
		if ( $this->is_network_notice( $id ) ) {
			update_site_option( self::DISMISSED_MESSAGES_META_KEY, $dismissed_messages );
		} elseif ( $this->is_site_notice( $id ) ) {
			update_option( self::DISMISSED_MESSAGES_META_KEY, $dismissed_messages );
		} elseif ( $this->is_user_notice( $id ) ) {
			$user = wp_get_current_user();
			update_user_meta( $user->ID, self::DISMISSED_MESSAGES_META_KEY, $dismissed_messages );
		}
	}

	/**
	 * The method for checking if an admin notice has been dismissed by the current user.
	 *
	 * It checks if the admin notice has been dismissed by the current user.
	 */
	private function is_dismissed( string $id ): bool {
		$dismissed_messages = $this->get_dismissed_messages_by_notice_id( $id );

		return is_array( $dismissed_messages ) && in_array( $id, $dismissed_messages, true );
	}

	/**
	 * The method for getting the admin notices.
	 *
	 * It gets the admin notices that have not been dismissed by the current user.
	 */
	private function get_notices( bool $network = false ): array {
		$notices = array();

		foreach ( $this->notices as $id => $notice ) {
			if ( defined( 'DISABLE_NAG_NOTICES' ) && DISABLE_NAG_NOTICES ) {
				continue;
			}

			if ( $network && ! $notice['network'] ) {
				continue;
			}

			if ( ! $network && $notice['network'] ) {
				continue;
			}

			if ( ! current_user_can( $notice['capability'] ) ) {
				continue;
			}

			if ( $this->is_dismissed( $id ) ) {
				continue;
			}

			$notices[ $id ] = $notice;
		}

		return $notices;
	}

	/**
	 * The method for checking if an admin notice is a site notice.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	private function is_site_notice( string $id ): bool {
		$is_global  = $this->notices[ $id ]['global'] ?? false;
		$is_network = $this->notices[ $id ]['network'] ?? false;

		return $is_global && ! $is_network;
	}

	/**
	 * The method for checking if an admin notice is a network notice.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	private function is_network_notice( string $id ): bool {
		$is_global  = $this->notices[ $id ]['global'] ?? false;
		$is_network = $this->notices[ $id ]['network'] ?? false;

		return $is_global && $is_network;
	}

	/**
	 * The method for checking if an admin notice is a user notice.
	 *
	 * @param string $id
	 *
	 * @return bool
	 */
	private function is_user_notice( string $id ): bool {
		$is_global = $this->notices[ $id ]['global'] ?? false;

		return ! $is_global;
	}

	/**
	 * The method for rendering an admin notice in a legacy way.
	 *
	 * It renders the admin notice in a legacy way.
	 */
	private function render_notice_legacy( mixed $message, array $args ): void {
		$classes        = $args['additional_classes'] ?? array();
		$classes[]      = 'notice';
		$id             = $args['id'] ?? '';
		$paragraph_wrap = $args['paragraph_wrap'] ?? true;

		if ( ! empty( $args['dismissible'] ) ) {
			$classes[] = 'is-dismissible';
		}

		if ( ! empty( $args['type'] ) ) {
			$classes[] = 'notice-' . $args['type'];
		}

		if ( ! empty( $args['additional_classes'] ) && is_array( $args['additional_classes'] ) ) {
			$classes = array_merge( $classes, $args['additional_classes'] );
		}

		$message    = $paragraph_wrap ? '<p>' . $message . '</p>' : $message;
		$attributes = array( 'class' => implode( ' ', $classes ) );

		if ( ! empty( $id ) ) {
			$attributes['id'] = $id;
		}

		$attributes = join(
			' ',
			array_map(
				fn( $value, $key ) => $key . '="' . esc_attr( $value ) . '"',
				$attributes,
				array_keys( $attributes ),
			),
		);

		echo wp_kses_post( sprintf( '<div %s>%s</div>', $attributes, $message ) );
	}
}
