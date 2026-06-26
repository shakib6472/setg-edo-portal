<?php
/**
 * Admin meta boxes for the portal's content types.
 *
 * One schema drives both rendering and saving so the fields stay in sync with
 * the meta keys registered in EDO_Post_Types.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders and saves the detail fields for each CPT.
 */
class EDO_Meta_Boxes {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add' ) );
		add_action( 'save_post', array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	/**
	 * Field schema per post type. Field key = meta key.
	 *
	 * @return array
	 */
	private static function schema() {
		return array(
			'edo_assignment' => array(
				'title'  => __( 'Opdracht-details', 'setg' ),
				'fields' => array(
					'client'          => array(
						'label'       => __( 'Opdrachtgever', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. ZonMw', 'setg' ),
					),
					'event_date'      => array(
						'label'       => __( 'Datum', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. 12 juni 2026', 'setg' ),
					),
					'location'        => array(
						'label' => __( 'Locatie', 'setg' ),
						'type'  => 'text',
					),
					'time_investment' => array(
						'label'       => __( 'Tijdsinvestering', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. ± 4 uur', 'setg' ),
					),
					'compensation'    => array(
						'label'       => __( 'Vergoeding', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. €75 vergoeding', 'setg' ),
					),
					'target_group'    => array(
						'label' => __( 'Doelgroep', 'setg' ),
						'type'  => 'text',
					),
					'status_tag'      => array(
						'label'       => __( 'Label (badge)', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. Nieuw, Bijna vol', 'setg' ),
					),
					'status_type'     => array(
						'label'   => __( 'Label-kleur', 'setg' ),
						'type'    => 'select',
						'options' => array(
							''       => __( 'Standaard (grijs)', 'setg' ),
							'new'    => __( 'Groen', 'setg' ),
							'urgent' => __( 'Rood', 'setg' ),
						),
					),
				),
			),
			'edo_training'   => array(
				'title'  => __( 'Training-details', 'setg' ),
				'fields' => array(
					'subject'     => array(
						'label' => __( 'Onderwerp', 'setg' ),
						'type'  => 'text',
					),
					'event_date'  => array(
						'label'       => __( 'Datum', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. 25 juni 2026', 'setg' ),
					),
					'event_time'  => array(
						'label' => __( 'Tijd', 'setg' ),
						'type'  => 'text',
					),
					'place'       => array(
						'label'       => __( 'Locatie / platform', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. Amsterdam of Online (Zoom)', 'setg' ),
					),
					'is_online'   => array(
						'label' => __( 'Online training', 'setg' ),
						'type'  => 'checkbox',
					),
					'online_link' => array(
						'label' => __( 'Online link (Zoom/Teams)', 'setg' ),
						'type'  => 'url',
					),
					'spots'       => array(
						'label'       => __( 'Plekken', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. Nog 4 plekken', 'setg' ),
					),
					'preparation' => array(
						'label' => __( 'Voorbereiding', 'setg' ),
						'type'  => 'textarea',
					),
				),
			),
			'edo_document'   => array(
				'title'  => __( 'Document-details', 'setg' ),
				'fields' => array(
					'doc_category'  => array(
						'label'   => __( 'Categorie', 'setg' ),
						'type'    => 'select',
						'options' => array(
							'document' => __( 'Document', 'setg' ),
							'artikel'  => __( 'Artikel', 'setg' ),
							'video'    => __( 'Video', 'setg' ),
							'beleid'   => __( 'Beleid', 'setg' ),
						),
					),
					'attachment_id' => array(
						'label' => __( 'Bestand (upload)', 'setg' ),
						'type'  => 'media',
					),
					'external_url'  => array(
						'label'       => __( 'Externe link (optioneel)', 'setg' ),
						'type'        => 'url',
						'placeholder' => 'https://',
					),
					'meta_line'     => array(
						'label'       => __( 'Metaregel', 'setg' ),
						'type'        => 'text',
						'placeholder' => __( 'bijv. Document · 2,4 MB · PDF', 'setg' ),
					),
				),
			),
			'edo_announcement' => array(
				'title'  => __( 'Mededeling-opties', 'setg' ),
				'fields' => array(
					'important' => array(
						'label' => __( 'Belangrijk markeren', 'setg' ),
						'type'  => 'checkbox',
					),
				),
			),
		);
	}

	/**
	 * Register the meta box on each CPT screen.
	 */
	public static function add() {
		foreach ( self::schema() as $post_type => $box ) {
			add_meta_box(
				'edo_details',
				$box['title'],
				array( __CLASS__, 'render' ),
				$post_type,
				'normal',
				'high'
			);
		}
	}

	/**
	 * Render the meta box.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function render( $post ) {
		$schema = self::schema();
		if ( ! isset( $schema[ $post->post_type ] ) ) {
			return;
		}

		wp_nonce_field( 'edo_meta_' . $post->post_type, 'edo_meta_nonce' );

		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( $schema[ $post->post_type ]['fields'] as $key => $field ) {
			$value = get_post_meta( $post->ID, $key, true );
			echo '<tr><th scope="row"><label for="edo_' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
			self::render_field( $key, $field, $value );
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Render a single field by type.
	 *
	 * @param string $key   Field/meta key.
	 * @param array  $field Field definition.
	 * @param mixed  $value Stored value.
	 */
	private static function render_field( $key, $field, $value ) {
		$name        = 'edo_' . $key;
		$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%1$s" rows="4" class="large-text">%2$s</textarea>',
					esc_attr( $name ),
					esc_textarea( $value )
				);
				break;

			case 'checkbox':
				printf(
					'<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> %3$s</label>',
					esc_attr( $name ),
					checked( $value, 1, false ),
					esc_html__( 'Ja', 'setg' )
				);
				break;

			case 'select':
				echo '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $field['options'] as $opt_val => $opt_label ) {
					printf(
						'<option value="%1$s" %2$s>%3$s</option>',
						esc_attr( $opt_val ),
						selected( (string) $value, (string) $opt_val, false ),
						esc_html( $opt_label )
					);
				}
				echo '</select>';
				break;

			case 'media':
				$att_id   = absint( $value );
				$filename = $att_id ? wp_basename( (string) get_attached_file( $att_id ) ) : '';
				echo '<div class="edo-media-field">';
				printf( '<input type="hidden" class="edo-media-id" name="%1$s" value="%2$s" />', esc_attr( $name ), esc_attr( (string) $att_id ) );
				printf(
					'<span class="edo-media-preview" style="margin-right:10px;">%s</span>',
					$filename ? esc_html( $filename ) : esc_html__( 'Geen bestand gekozen', 'setg' )
				);
				printf( '<button type="button" class="button edo-media-pick">%s</button> ', esc_html__( 'Kies bestand', 'setg' ) );
				printf( '<button type="button" class="button-link edo-media-remove" style="color:#b32d2e;">%s</button>', esc_html__( 'Verwijderen', 'setg' ) );
				echo '</div>';
				break;

			case 'url':
				printf(
					'<input type="url" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s" />',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $placeholder )
				);
				break;

			default: // text.
				printf(
					'<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text" placeholder="%3$s" />',
					esc_attr( $name ),
					esc_attr( $value ),
					esc_attr( $placeholder )
				);
				break;
		}
	}

	/**
	 * Save the meta box fields.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function save( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		$schema    = self::schema();
		if ( ! isset( $schema[ $post_type ] ) ) {
			return;
		}

		$nonce = isset( $_POST['edo_meta_nonce'] ) ? sanitize_key( wp_unslash( $_POST['edo_meta_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'edo_meta_' . $post_type ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		foreach ( $schema[ $post_type ]['fields'] as $key => $field ) {
			$name = 'edo_' . $key;

			switch ( $field['type'] ) {
				case 'checkbox':
					update_post_meta( $post_id, $key, isset( $_POST[ $name ] ) ? 1 : 0 );
					break;

				case 'media':
					$raw = isset( $_POST[ $name ] ) ? absint( wp_unslash( $_POST[ $name ] ) ) : 0;
					update_post_meta( $post_id, $key, $raw );
					break;

				case 'textarea':
					$raw = isset( $_POST[ $name ] ) ? sanitize_textarea_field( wp_unslash( $_POST[ $name ] ) ) : '';
					update_post_meta( $post_id, $key, $raw );
					break;

				case 'url':
					$raw = isset( $_POST[ $name ] ) ? esc_url_raw( wp_unslash( $_POST[ $name ] ) ) : '';
					update_post_meta( $post_id, $key, $raw );
					break;

				case 'select':
					$raw   = isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : '';
					$valid = array_keys( $field['options'] );
					update_post_meta( $post_id, $key, in_array( $raw, $valid, true ) ? $raw : '' );
					break;

				default:
					$raw = isset( $_POST[ $name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name ] ) ) : '';
					update_post_meta( $post_id, $key, $raw );
					break;
			}
		}
	}

	/**
	 * Load the media uploader on the document edit screen.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function enqueue( $hook ) {
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'edo_document' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script(
			'edo-admin',
			EDO_PORTAL_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			file_exists( EDO_PORTAL_DIR . 'assets/js/admin.js' ) ? (string) filemtime( EDO_PORTAL_DIR . 'assets/js/admin.js' ) : EDO_PORTAL_VERSION,
			true
		);
	}
}
