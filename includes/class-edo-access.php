<?php
/**
 * Access behaviour around login: where members land, and admin-bar hiding.
 *
 * The hard gate (showing the login screen vs the app) lives in EDO_Router;
 * this class just shapes the surrounding WordPress login experience.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Login redirects and chrome for portal members.
 */
class EDO_Access {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 20, 3 );
		add_action( 'after_setup_theme', array( __CLASS__, 'maybe_hide_admin_bar' ) );
		add_action( 'wp_login_failed', array( __CLASS__, 'login_failed' ) );
	}

	/**
	 * Keep failed logins that came from the portal on the portal's own login
	 * screen (with an error), instead of the default wp-login.php page.
	 *
	 * @param string $username Attempted username (unused).
	 */
	public static function login_failed( $username ) {
		// Only intercept submissions from our portal login form.
		if ( empty( $_POST['edo_login'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- core handles auth; this only routes the error page.
			return;
		}
		wp_safe_redirect( add_query_arg( 'login', 'failed', edo_view_url( 'dashboard' ) ) );
		exit;
	}

	/**
	 * Send plain members to the portal after logging in, instead of wp-admin.
	 *
	 * @param string           $redirect_to Default redirect.
	 * @param string           $requested   Requested redirect.
	 * @param WP_User|WP_Error $user        Authenticated user or error.
	 * @return string
	 */
	public static function login_redirect( $redirect_to, $requested, $user ) {
		if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
			return $redirect_to;
		}

		// Managers keep their normal destination.
		if ( user_can( $user, 'edit_pages' ) ) {
			return $redirect_to;
		}

		if ( user_can( $user, EDO_Roles::CAP_ACCESS ) ) {
			return edo_view_url( 'dashboard' );
		}

		return $redirect_to;
	}

	/**
	 * Hide the WordPress admin bar for members who cannot edit content.
	 */
	public static function maybe_hide_admin_bar() {
		if ( is_user_logged_in() && ! current_user_can( 'edit_posts' ) ) {
			show_admin_bar( false );
		}
	}
}
