<?php

namespace Wpify\Snippets;

/**
 * Fix URLs for Bedrock installs.
 * Original source: https://github.com/roots/multisite-url-fixer
 */
class FixBedrockSiteURL {
	/**
     * Add filters to verify / fix URLs.
     */
	public function __construct() {
		add_filter( 'option_home', array( $this, 'fix_home_url' ) );
		add_filter( 'option_siteurl', array( $this, 'fix_site_url' ) );
		add_filter( 'network_site_url', array( $this, 'fix_network_site_url' ) );
	}

	/**
	 * Ensure that home URL does not contain the /wp subdirectory.
	 *
	 * @param string $value the unchecked home URL
	 * @return string the verified home URL
	 */
	public function fix_home_url( $value ) {
		if ( substr( $value, -3 ) === '/wp' ) {
			$value = substr( $value, 0, -3 );
		}

		return $value;
	}

	/**
	 * Ensure that site URL contains the /wp subdirectory.
	 *
	 * @param string $url the unchecked site URL
	 * @return string the verified site URL
	 */
	public function fix_site_url( $url ) {
		if ( substr( $url, -3 ) !== '/wp' && ( is_main_site() || is_subdomain_install() ) ) {
			$url .= '/wp';
		}

		return $url;
	}

	/**
	 * Ensure that the network site URL contains the /wp subdirectory.
	 *
	 * @param string $url    the unchecked network site URL with path appended
	 * @param string $path   the path for the URL
	 * @param string $scheme the URL scheme
	 * @return string the verified network site URL
	 */
	public function fix_network_site_url( $url, $path, $scheme ) {
		$path = ltrim( $path, '/' );
		$url  = substr( $url, 0, strlen( $url ) - strlen( $path ) );

		if ( substr( $url, -3 ) !== 'wp/' ) {
			$url .= 'wp/';
		}

		return $url . $path;
	}
}
