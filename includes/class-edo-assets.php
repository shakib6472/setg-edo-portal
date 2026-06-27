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
		// Run late so it catches everything the theme/other plugins enqueued.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'isolate' ), 9999 );
	}

	/**
	 * Keep the portal visually isolated: on portal requests, drop every front-end
	 * stylesheet except the portal's own, so the active theme and page-builder CSS
	 * can't leak in (e.g. styling our buttons).
	 */
	public static function isolate() {
		if ( ! EDO_Router::is_portal_request() ) {
			return;
		}

		$keep = array( 'edo-portal', 'edo-google-fonts' );

		global $wp_styles;
		if ( $wp_styles instanceof WP_Styles ) {
			foreach ( (array) $wp_styles->queue as $handle ) {
				if ( ! in_array( $handle, $keep, true ) ) {
					wp_dequeue_style( $handle );
				}
			}
		}
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

		wp_localize_script(
			'edo-portal',
			'edoPortal',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( EDO_Interest::NONCE ),
			)
		);
	}
}
