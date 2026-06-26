<?php
/*
 * Plugin Name:       SetG
 * Plugin URI:        https://github.com/shakib6472/
 * Description:       Private members-only community portal for the SETG EDO-team: assignments, trainings, documents, announcements, member profiles and contact.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Shakib Shown
 * Author URI:        https://github.com/shakib6472/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       setg
 * Domain Path:       /languages
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'EDO_PORTAL_VERSION', '1.0.0' );
define( 'EDO_PORTAL_FILE', __FILE__ );
define( 'EDO_PORTAL_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDO_PORTAL_URL', plugin_dir_url( __FILE__ ) );

require_once EDO_PORTAL_DIR . 'includes/class-edo-portal.php';

/**
 * Boot the plugin.
 *
 * @return EDO_Portal
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
