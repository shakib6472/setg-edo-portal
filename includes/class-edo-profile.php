<?php
/**
 * Front-end profile editing.
 *
 * A member edits their own profile from the portal. The form posts to
 * admin-post.php; a member can only ever change their own account.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saves the member's own profile fields.
 */
class EDO_Profile {

	const NONCE = 'edo_save_profile';

	/**
	 * Hook the form handler.
	 */
	public static function init() {
		add_action( 'admin_post_edo_save_profile', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Save the current member's profile, then return to the profile screen.
	 */
	public static function handle() {
		check_admin_referer( self::NONCE );

		if ( ! is_user_logged_in() || ! EDO_Roles::user_can_access() ) {
			wp_die( esc_html__( 'Niet toegestaan.', 'setg' ) );
		}

		$user_id = get_current_user_id();

		$name = isset( $_POST['edo_name'] ) ? sanitize_text_field( wp_unslash( $_POST['edo_name'] ) ) : '';
		if ( '' !== $name ) {
			wp_update_user(
				array(
					'ID'           => $user_id,
					'display_name' => $name,
				)
			);
		}

		$text_fields = array( 'edo_function', 'edo_expertise', 'edo_availability', 'edo_contact_pref' );
		foreach ( $text_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				update_user_meta( $user_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
			}
		}

		if ( isset( $_POST['edo_bio'] ) ) {
			update_user_meta( $user_id, 'edo_bio', sanitize_textarea_field( wp_unslash( $_POST['edo_bio'] ) ) );
		}

		wp_safe_redirect( add_query_arg( 'saved', '1', edo_view_url( 'profile' ) ) );
		exit;
	}
}
