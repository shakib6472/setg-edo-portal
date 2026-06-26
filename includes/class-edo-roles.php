<?php
/**
 * Member role and capabilities.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the "EDO member" role and the capability that gates portal access.
 */
class EDO_Roles {

	const ROLE       = 'edo_member';
	const CAP_ACCESS = 'access_edo_portal';

	/**
	 * Create the member role and grant the access cap to admins.
	 * Called on activation.
	 */
	public static function add_role() {
		add_role(
			self::ROLE,
			__( 'EDO-lid', 'setg-edo-portal' ),
			array(
				'read'             => true,
				self::CAP_ACCESS   => true,
			)
		);

		// Administrators and editors manage the portal and can always view it.
		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				$role->add_cap( self::CAP_ACCESS );
			}
		}
	}

	/**
	 * Remove the role and caps. Called on uninstall (not deactivation).
	 */
	public static function remove_role() {
		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( $role ) {
				$role->remove_cap( self::CAP_ACCESS );
			}
		}
		remove_role( self::ROLE );
	}

	/**
	 * Whether the current (or given) user may enter the portal.
	 *
	 * A user needs the access capability and, for non-admins, an approved flag.
	 * Admins/editors bypass the approval requirement.
	 *
	 * @param int|null $user_id Optional user ID; defaults to current user.
	 * @return bool
	 */
	public static function user_can_access( $user_id = null ) {
		$user = $user_id ? get_userdata( $user_id ) : wp_get_current_user();
		if ( ! $user || ! $user->exists() ) {
			return false;
		}

		if ( ! user_can( $user, self::CAP_ACCESS ) ) {
			return false;
		}

		// Managers always pass.
		if ( user_can( $user, 'edit_pages' ) ) {
			return true;
		}

		// Regular members must be approved.
		return self::is_approved( $user->ID );
	}

	/**
	 * Whether a member has been approved by an admin.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function is_approved( $user_id ) {
		return (bool) get_user_meta( $user_id, 'edo_approved', true );
	}
}
