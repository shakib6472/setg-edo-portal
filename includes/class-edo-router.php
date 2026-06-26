<?php
/**
 * Front-end routing for the portal.
 *
 * The portal renders as a standalone full-page app at /portal/{view}, bypassing
 * the active theme so the approved design stays pixel-accurate.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Maps requests to portal views and renders them.
 */
class EDO_Router {

	/**
	 * Hook in (rewrite rules are added on the 'init' action by the bootstrap).
	 */
	public static function init() {
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_render' ) );
	}

	/**
	 * Pretty URLs: /portal/ and /portal/{view}/.
	 */
	public static function add_rewrite_rules() {
		add_rewrite_rule( '^portal/?$', 'index.php?edo_portal=1', 'top' );
		add_rewrite_rule( '^portal/([^/]+)/?$', 'index.php?edo_portal=1&edo_view=$matches[1]', 'top' );
	}

	/**
	 * Register our public query vars.
	 *
	 * @param string[] $vars Existing vars.
	 * @return string[]
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'edo_portal';
		$vars[] = 'edo_view';
		return $vars;
	}

	/**
	 * Is the current request a portal request?
	 *
	 * @return bool
	 */
	public static function is_portal_request() {
		return (bool) get_query_var( 'edo_portal' );
	}

	/**
	 * Render the portal (or its login screen) and stop WordPress' normal output.
	 */
	public static function maybe_render() {
		if ( ! self::is_portal_request() ) {
			return;
		}

		// Not logged in, or not allowed in → show the login screen.
		if ( ! is_user_logged_in() || ! EDO_Roles::user_can_access() ) {
			status_header( is_user_logged_in() ? 403 : 200 );
			$pending = is_user_logged_in() && ! EDO_Roles::user_can_access();
			edo_get_template( 'login', array( 'pending' => $pending ) );
			exit;
		}

		$view = edo_current_view();
		status_header( 200 );
		nocache_headers();
		edo_get_template(
			'app-shell',
			array(
				'view'  => $view,
				'title' => edo_view_title( $view ),
			)
		);
		exit;
	}
}
