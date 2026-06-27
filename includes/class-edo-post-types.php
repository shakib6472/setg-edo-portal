<?php
/**
 * Custom post types and meta — the portal's data models.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the content types that back the portal: assignments, trainings,
 * documents and announcements. Member profiles live on WP users (see EDO_Roles),
 * not on a post type.
 */
class EDO_Post_Types {

	/**
	 * Register every post type and its meta.
	 */
	public static function register() {
		self::register_assignment();
		self::register_training();
		self::register_document();
		self::register_announcement();
	}

	/**
	 * Shared base arguments. Items are managed in wp-admin but have no public
	 * single pages — the portal front end renders them.
	 *
	 * @param array $labels   Label set.
	 * @param array $supports Editor supports.
	 * @param array $overrides Extra args.
	 * @return array
	 */
	private static function base_args( $labels, $supports, $overrides = array() ) {
		$defaults = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'menu_position'       => 26,
			'supports'            => $supports,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		);
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Helper to register a string/bool meta key visible to REST.
	 *
	 * @param string $post_type Post type.
	 * @param string $key       Meta key.
	 * @param string $type      Data type.
	 */
	private static function meta( $post_type, $key, $type = 'string' ) {
		register_post_meta(
			$post_type,
			$key,
			array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => ( 'boolean' === $type ) ? 'rest_sanitize_boolean' : 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Assignments (Opdrachten).
	 */
	private static function register_assignment() {
		$labels = array(
			'name'          => __( 'Opdrachten', 'setg' ),
			'singular_name' => __( 'Opdracht', 'setg' ),
			'add_new_item'  => __( 'Nieuwe opdracht', 'setg' ),
			'edit_item'     => __( 'Opdracht bewerken', 'setg' ),
			'menu_name'     => __( 'EDO · Opdrachten', 'setg' ),
		);

		register_post_type(
			'edo_assignment',
			self::base_args(
				$labels,
				array( 'title', 'editor', 'thumbnail', 'comments' ),
				array( 'menu_icon' => 'dashicons-clipboard' )
			)
		);

		self::meta( 'edo_assignment', 'client' );
		self::meta( 'edo_assignment', 'event_date' );
		self::meta( 'edo_assignment', 'location' );
		self::meta( 'edo_assignment', 'time_investment' );
		self::meta( 'edo_assignment', 'compensation' );
		self::meta( 'edo_assignment', 'target_group' );
		self::meta( 'edo_assignment', 'status_tag' );
		self::meta( 'edo_assignment', 'status_type' );
	}

	/**
	 * Trainings (Trainingen).
	 */
	private static function register_training() {
		$labels = array(
			'name'          => __( 'Trainingen', 'setg' ),
			'singular_name' => __( 'Training', 'setg' ),
			'add_new_item'  => __( 'Nieuwe training', 'setg' ),
			'edit_item'     => __( 'Training bewerken', 'setg' ),
			'menu_name'     => __( 'EDO · Trainingen', 'setg' ),
		);

		// Retired: trainings are now delivered via TutorLMS courses. The type stays
		// registered (so any existing data survives) but is hidden from the admin
		// so SETG manages courses in one place — TutorLMS.
		register_post_type(
			'edo_training',
			self::base_args(
				$labels,
				array( 'title', 'editor', 'thumbnail' ),
				array(
					'menu_icon'    => 'dashicons-welcome-learn-more',
					'show_ui'      => false,
					'show_in_menu' => false,
					'show_in_rest' => false,
				)
			)
		);

		self::meta( 'edo_training', 'subject' );
		self::meta( 'edo_training', 'event_date' );
		self::meta( 'edo_training', 'event_time' );
		self::meta( 'edo_training', 'place' );
		self::meta( 'edo_training', 'online_link' );
		self::meta( 'edo_training', 'is_online', 'boolean' );
		self::meta( 'edo_training', 'spots' );
		self::meta( 'edo_training', 'preparation' );
	}

	/**
	 * Documents & resources (Documenten).
	 */
	private static function register_document() {
		$labels = array(
			'name'          => __( 'Documenten', 'setg' ),
			'singular_name' => __( 'Document', 'setg' ),
			'add_new_item'  => __( 'Nieuw document', 'setg' ),
			'edit_item'     => __( 'Document bewerken', 'setg' ),
			'menu_name'     => __( 'EDO · Documenten', 'setg' ),
		);

		register_post_type(
			'edo_document',
			self::base_args(
				$labels,
				array( 'title', 'editor' ),
				array( 'menu_icon' => 'dashicons-media-document' )
			)
		);

		// Category: document | artikel | video | beleid.
		self::meta( 'edo_document', 'doc_category' );
		self::meta( 'edo_document', 'attachment_id', 'integer' );
		self::meta( 'edo_document', 'external_url' );
		self::meta( 'edo_document', 'meta_line' );
	}

	/**
	 * Announcements (Mededelingen).
	 */
	private static function register_announcement() {
		$labels = array(
			'name'          => __( 'Mededelingen', 'setg' ),
			'singular_name' => __( 'Mededeling', 'setg' ),
			'add_new_item'  => __( 'Nieuwe mededeling', 'setg' ),
			'edit_item'     => __( 'Mededeling bewerken', 'setg' ),
			'menu_name'     => __( 'EDO · Mededelingen', 'setg' ),
		);

		register_post_type(
			'edo_announcement',
			self::base_args(
				$labels,
				array( 'title', 'editor' ),
				array( 'menu_icon' => 'dashicons-megaphone' )
			)
		);

		self::meta( 'edo_announcement', 'important', 'boolean' );
	}
}
