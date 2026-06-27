<?php
/**
 * Main plugin bootstrap.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Singleton that wires up every subsystem of the portal.
 */
final class EDO_Portal {

	/**
	 * Single instance.
	 *
	 * @var EDO_Portal|null
	 */
	private static $instance = null;

	/**
	 * Get the shared instance.
	 *
	 * @return EDO_Portal
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor: load dependencies and hook everything in.
	 */
	private function __construct() {
		$this->includes();
		$this->init();
	}

	/**
	 * Load all class files and helpers.
	 */
	private function includes() {
		require_once EDO_PORTAL_DIR . 'includes/functions.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-post-types.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-roles.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-access.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-router.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-assets.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-data.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-interest.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-comments.php';

		// Admin-only subsystems.
		if ( is_admin() ) {
			require_once EDO_PORTAL_DIR . 'includes/class-edo-meta-boxes.php';
			require_once EDO_PORTAL_DIR . 'includes/class-edo-admin-columns.php';
			require_once EDO_PORTAL_DIR . 'includes/class-edo-user-fields.php';
		}
	}

	/**
	 * Instantiate the subsystems.
	 */
	private function init() {
		add_action( 'init', array( 'EDO_Post_Types', 'register' ) );
		add_action( 'init', array( 'EDO_Router', 'add_rewrite_rules' ) );

		EDO_Access::init();
		EDO_Router::init();
		EDO_Assets::init();
		EDO_Interest::init();
		EDO_Comments::init();

		if ( is_admin() ) {
			EDO_Meta_Boxes::init();
			EDO_Admin_Columns::init();
			EDO_User_Fields::init();
		}

		add_action( 'init', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load translations (Dutch is the default UI language; strings stay translatable).
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'setg', false, dirname( plugin_basename( EDO_PORTAL_FILE ) ) . '/languages' );
	}

	/**
	 * Activation: ensure post types + role exist, then flush rewrite rules.
	 */
	public static function activate() {
		require_once EDO_PORTAL_DIR . 'includes/functions.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-post-types.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-roles.php';
		require_once EDO_PORTAL_DIR . 'includes/class-edo-router.php';

		EDO_Post_Types::register();
		EDO_Roles::add_role();
		EDO_Router::add_rewrite_rules();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation: flush rewrite rules. The role is intentionally kept so member
	 * assignments survive a temporary deactivation; it is removed on uninstall.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}
