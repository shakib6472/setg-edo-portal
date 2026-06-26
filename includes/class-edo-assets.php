<?php
/**
 * Front-end asset loading for the portal.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues fonts and the portal stylesheet, only on portal requests.
 */
class EDO_Assets {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Register and enqueue assets when viewing the portal.
	 */
	public static function enqueue() {
		if ( ! EDO_Router::is_portal_request() ) {
			return;
		}

		wp_enqueue_style(
			'edo-google-fonts',
			'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
			array(),
			null
		);

		$css_path = EDO_PORTAL_DIR . 'assets/css/portal.css';
		$version  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : EDO_PORTAL_VERSION;

		wp_enqueue_style(
			'edo-portal',
			EDO_PORTAL_URL . 'assets/css/portal.css',
			array( 'edo-google-fonts' ),
			$version
		);

		$js_path    = EDO_PORTAL_DIR . 'assets/js/portal.js';
		$js_version = file_exists( $js_path ) ? (string) filemtime( $js_path ) : EDO_PORTAL_VERSION;

		wp_enqueue_script(
			'edo-portal',
			EDO_PORTAL_URL . 'assets/js/portal.js',
			array(),
			$js_version,
			true
		);
	}
}
