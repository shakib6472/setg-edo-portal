<?php
/**
 * EDO member fields on the WordPress user edit screen.
 *
 * These feed the portal's Profiel and Leden views. The approval flag is shown
 * only to users who can manage other users.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders and saves the EDO profile fields for a user.
 */
class EDO_User_Fields {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'show_user_profile', array( __CLASS__, 'render' ) );
		add_action( 'edit_user_profile', array( __CLASS__, 'render' ) );
		add_action( 'personal_options_update', array( __CLASS__, 'save' ) );
		add_action( 'edit_user_profile_update', array( __CLASS__, 'save' ) );
	}

	/**
	 * The editable text fields. Key = user meta key.
	 *
	 * @return array<string,array{label:string,type:string}>
	 */
	private static function fields() {
		return array(
			'edo_function'     => array(
				'label' => __( 'Functie / rol', 'setg' ),
				'type'  => 'text',
			),
			'edo_expertise'    => array(
				'label' => __( 'Expertise (komma-gescheiden)', 'setg' ),
				'type'  => 'text',
			),
			'edo_availability' => array(
				'label' => __( 'Beschikbaarheid', 'setg' ),
				'type'  => 'text',
			),
			'edo_contact_pref' => array(
				'label' => __( 'Contactvoorkeur', 'setg' ),
				'type'  => 'text',
			),
			'edo_bio'          => array(
				'label' => __( 'Over mij', 'setg' ),
				'type'  => 'textarea',
			),
		);
	}

	/**
	 * Render the fields.
	 *
	 * @param WP_User $user User being edited.
	 */
	public static function render( $user ) {
		wp_nonce_field( 'edo_user_fields', 'edo_user_nonce' );
		?>
		<h2><?php esc_html_e( 'EDO-portaal', 'setg' ); ?></h2>
		<table class="form-table" role="presentation">
			<tbody>
			<?php foreach ( self::fields() as $key => $field ) : ?>
				<?php $value = get_user_meta( $user->ID, $key, true ); ?>
				<tr>
					<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
					<td>
						<?php if ( 'textarea' === $field['type'] ) : ?>
							<textarea id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" rows="4" class="regular-text"><?php echo esc_textarea( $value ); ?></textarea>
						<?php else : ?>
							<input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>

			<?php if ( current_user_can( 'edit_users' ) ) : ?>
				<tr>
					<th><label for="edo_approved"><?php esc_html_e( 'Portaaltoegang', 'setg' ); ?></label></th>
					<td>
						<label>
							<input type="checkbox" id="edo_approved" name="edo_approved" value="1" <?php checked( get_user_meta( $user->ID, 'edo_approved', true ), 1 ); ?> />
							<?php esc_html_e( 'Goedgekeurd — dit lid mag het portaal gebruiken', 'setg' ); ?>
						</label>
					</td>
				</tr>
			<?php endif; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Save the fields.
	 *
	 * @param int $user_id User ID.
	 */
	public static function save( $user_id ) {
		$nonce = isset( $_POST['edo_user_nonce'] ) ? sanitize_key( wp_unslash( $_POST['edo_user_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'edo_user_fields' ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		foreach ( self::fields() as $key => $field ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$raw = ( 'textarea' === $field['type'] )
				? sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) )
				: sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
			update_user_meta( $user_id, $key, $raw );
		}

		// Approval flag is manageable only by user-managers.
		if ( current_user_can( 'edit_users' ) ) {
			update_user_meta( $user_id, 'edo_approved', isset( $_POST['edo_approved'] ) ? 1 : 0 );
		}
	}
}
