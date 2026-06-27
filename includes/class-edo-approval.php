<?php
/**
 * Admin approval flow for EDO members.
 *
 * Adds an "EDO-toegang" column and quick Goedkeuren / Intrekken row actions to
 * the Users list, plus a pending-count notice.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Users-list integration for approving members.
 */
class EDO_Approval {

	/**
	 * Hook in (admin only).
	 */
	public static function init() {
		add_filter( 'manage_users_columns', array( __CLASS__, 'column' ) );
		add_filter( 'manage_users_custom_column', array( __CLASS__, 'column_content' ), 10, 3 );
		add_filter( 'user_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_post_edo_approve', array( __CLASS__, 'do_approve' ) );
		add_action( 'admin_post_edo_revoke', array( __CLASS__, 'do_revoke' ) );
		add_action( 'admin_notices', array( __CLASS__, 'pending_notice' ) );
	}

	/**
	 * Is the given user an EDO member?
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	private static function is_member( $user_id ) {
		$user = get_userdata( $user_id );
		return $user && in_array( EDO_Roles::ROLE, (array) $user->roles, true );
	}

	/**
	 * Add the access column.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public static function column( $columns ) {
		$columns['edo_access'] = __( 'EDO-toegang', 'setg' );
		return $columns;
	}

	/**
	 * Render the access column.
	 *
	 * @param string $output  Current output.
	 * @param string $column  Column key.
	 * @param int    $user_id User ID.
	 * @return string
	 */
	public static function column_content( $output, $column, $user_id ) {
		if ( 'edo_access' !== $column ) {
			return $output;
		}
		if ( ! self::is_member( $user_id ) ) {
			return '—';
		}
		return EDO_Roles::is_approved( $user_id )
			? '<span style="color:#1b5e3b;font-weight:600;">' . esc_html__( 'Goedgekeurd', 'setg' ) . '</span>'
			: '<span style="color:#c81e2c;font-weight:600;">' . esc_html__( 'In afwachting', 'setg' ) . '</span>';
	}

	/**
	 * Add Goedkeuren / Intrekken row actions for member rows.
	 *
	 * @param array   $actions Row actions.
	 * @param WP_User $user    User.
	 * @return array
	 */
	public static function row_actions( $actions, $user ) {
		if ( ! current_user_can( 'edit_users' ) || ! self::is_member( $user->ID ) ) {
			return $actions;
		}

		if ( EDO_Roles::is_approved( $user->ID ) ) {
			$url                  = wp_nonce_url( admin_url( 'admin-post.php?action=edo_revoke&user=' . $user->ID ), 'edo_revoke_' . $user->ID );
			$actions['edo_revoke'] = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Toegang intrekken', 'setg' ) . '</a>';
		} else {
			$url                   = wp_nonce_url( admin_url( 'admin-post.php?action=edo_approve&user=' . $user->ID ), 'edo_approve_' . $user->ID );
			$actions['edo_approve'] = '<a href="' . esc_url( $url ) . '" style="color:#1b5e3b;font-weight:600;">' . esc_html__( 'Goedkeuren', 'setg' ) . '</a>';
		}
		return $actions;
	}

	/**
	 * Approve a member.
	 */
	public static function do_approve() {
		self::set_approval( 1 );
	}

	/**
	 * Revoke a member's access.
	 */
	public static function do_revoke() {
		self::set_approval( 0 );
	}

	/**
	 * Shared approve/revoke handler.
	 *
	 * @param int $value 1 = approve, 0 = revoke.
	 */
	private static function set_approval( $value ) {
		$user_id = isset( $_GET['user'] ) ? absint( wp_unslash( $_GET['user'] ) ) : 0;
		$action  = $value ? 'edo_approve_' : 'edo_revoke_';
		check_admin_referer( $action . $user_id );

		if ( ! current_user_can( 'edit_users' ) || ! $user_id ) {
			wp_die( esc_html__( 'Niet toegestaan.', 'setg' ) );
		}

		update_user_meta( $user_id, 'edo_approved', (int) $value );

		/**
		 * Fires when a member is approved or revoked (member email = Phase 4).
		 *
		 * @param int $user_id User ID.
		 * @param int $value   1 approved, 0 revoked.
		 */
		do_action( 'edo_member_approval_changed', $user_id, (int) $value );

		$back = wp_get_referer();
		wp_safe_redirect( $back ? $back : admin_url( 'users.php' ) );
		exit;
	}

	/**
	 * Show a notice on the Users screen when members await approval.
	 */
	public static function pending_notice() {
		$screen = get_current_screen();
		if ( ! $screen || 'users' !== $screen->id || ! current_user_can( 'edit_users' ) ) {
			return;
		}

		$members = get_users(
			array(
				'role'   => EDO_Roles::ROLE,
				'fields' => 'ID',
			)
		);
		$pending = 0;
		foreach ( $members as $id ) {
			if ( ! EDO_Roles::is_approved( $id ) ) {
				$pending++;
			}
		}

		if ( $pending < 1 ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %d: number of pending members. */
					_n( '%d EDO-lid wacht op goedkeuring.', '%d EDO-leden wachten op goedkeuring.', $pending, 'setg' ),
					$pending
				)
			)
		);
	}
}
