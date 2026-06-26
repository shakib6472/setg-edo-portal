<?php
/**
 * Plugin Name:       SETG EDO Community Portal
 * Plugin URI:        https://slicks.info/
 * Description:        Private members-only community portal for the SETG EDO-team: assignments, trainings, documents, announcements, member profiles and contact.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            SETG
 * Text Domain:       setg-edo-portal
 * Domain Path:       /languages
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'EDO_PORTAL_VERSION', '0.1.0' );
define( 'EDO_PORTAL_FILE', __FILE__ );
define( 'EDO_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDO_PORTAL_URL', plugin_dir_url( __FILE__ ) );

require_once EDO_PORTAL_DIR . 'includes/class-edo-portal.php';

/**
 * Boot the plugin once all plugins are loaded.
 */
function edo_portal() {
	return EDO_Portal::instance();
}
edo_portal();

/*
 * Activation / deactivation.
 *
 * On activation we register the member role and post types, then flush rewrite
 * rules so the /portal/ endpoint resolves. Deactivation flushes them back out.
 */
register_activation_hook( __FILE__, array( 'EDO_Portal', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'EDO_Portal', 'deactivate' ) );
