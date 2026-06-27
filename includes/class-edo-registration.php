<?php
/**
 * Member self-registration.
 *
 * A logged-out visitor can request an account from the portal login screen.
 * The account is created as an unapproved EDO member; an admin approves it
 * before the member can enter the portal.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the registration form submission.
 */
class EDO_Registration {

	const NONCE = 'edo_register';

	/**
	 * Hook the form handler (admin-post.php, logged-out and logged-in).
	 */
	public static function init() {
		add_action( 'admin_post_nopriv_edo_register', array( __CLASS__, 'handle' ) );
		add_action( 'admin_post_edo_register', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Whether self-registration is allowed. Filterable so SETG can switch to
	 * invite-only (admins create members) by returning false.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return (bool) apply_filters( 'edo_registration_enabled', true );
	}

	/**
	 * Process the registration form.
	 */
	public static function handle() {
		if ( ! self::is_enabled() ) {
			self::redirect( array( 'reg_error' => 'disabled' ) );
		}

		check_admin_referer( self::NONCE );

		$name  = isset( $_POST['edo_name'] ) ? sanitize_text_field( wp_unslash( $_POST['edo_name'] ) ) : '';
		$email = isset( $_POST['edo_email'] ) ? sanitize_email( wp_unslash( $_POST['edo_email'] ) ) : '';
		$pass  = isset( $_POST['edo_pass'] ) ? (string) wp_unslash( $_POST['edo_pass'] ) : '';

		if ( '' === $name || ! is_email( $email ) || strlen( $pass ) < 8 ) {
			self::redirect(
				array(
					'register'  => 1,
					'reg_error' => 'invalid',
				)
			);
		}

		if ( email_exists( $email ) || username_exists( $email ) ) {
			self::redirect(
				array(
					'register'  => 1,
					'reg_error' => 'exists',
				)
			);
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => $pass,
				'display_name' => $name,
				'first_name'   => $name,
				'role'         => EDO_Roles::ROLE,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			self::redirect(
				array(
					'register'  => 1,
					'reg_error' => 'failed',
				)
			);
		}

		update_user_meta( $user_id, 'edo_approved', 0 );
		self::notify_admin( $name, $email, $user_id );

		/**
		 * Fires after a member registers (still pending approval).
		 *
		 * @param int $user_id New user ID.
		 */
		do_action( 'edo_member_registered', $user_id );

		self::redirect( array( 'registered' => 1 ) );
	}

	/**
	 * Email the site admin about a pending registration.
	 *
	 * @param string $name    Member name.
	 * @param string $email   Member email.
	 * @param int    $user_id New user ID.
	 */
	private static function notify_admin( $name, $email, $user_id ) {
		$to      = get_option( 'admin_email' );
		$subject = __( 'Nieuwe EDO-aanmelding', 'setg' );
		$approve = admin_url( 'users.php' );
		$body    = sprintf(
			/* translators: 1: name, 2: email, 3: link. */
			__( "Er is een nieuwe aanmelding voor het EDO-portaal:\n\nNaam: %1\$s\nE-mail: %2\$s\n\nKeur het lid goed via: %3\$s", 'setg' ),
			$name,
			$email,
			$approve
		);
		wp_mail( $to, $subject, $body );
	}

	/**
	 * Redirect back to the portal login screen with status args.
	 *
	 * @param array $args Query args.
	 */
	private static function redirect( $args ) {
		wp_safe_redirect( add_query_arg( $args, edo_view_url( 'dashboard' ) ) );
		exit;
	}
}
