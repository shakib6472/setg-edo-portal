<?php
/**
 * Custom admin list columns for the portal content types.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds at-a-glance columns to each CPT list table.
 */
class EDO_Admin_Columns {

	/**
	 * Column config per post type: column key => label/source.
	 *
	 * @return array
	 */
	private static function config() {
		return array(
			'edo_assignment'   => array(
				'edo_client' => array(
					'label' => __( 'Opdrachtgever', 'setg' ),
					'meta'  => 'client',
				),
				'edo_date'   => array(
					'label' => __( 'Datum', 'setg' ),
					'meta'  => 'event_date',
				),
				'edo_tag'    => array(
					'label' => __( 'Label', 'setg' ),
					'meta'  => 'status_tag',
				),
			),
			'edo_training'     => array(
				'edo_date'  => array(
					'label' => __( 'Datum', 'setg' ),
					'meta'  => 'event_date',
				),
				'edo_mode'  => array(
					'label'    => __( 'Vorm', 'setg' ),
					'callback' => 'training_mode',
				),
				'edo_spots' => array(
					'label' => __( 'Plekken', 'setg' ),
					'meta'  => 'spots',
				),
			),
			'edo_document'     => array(
				'edo_cat'  => array(
					'label' => __( 'Categorie', 'setg' ),
					'meta'  => 'doc_category',
				),
				'edo_file' => array(
					'label'    => __( 'Bestand', 'setg' ),
					'callback' => 'doc_file',
				),
			),
			'edo_announcement' => array(
				'edo_important' => array(
					'label'    => __( 'Belangrijk', 'setg' ),
					'callback' => 'announcement_important',
				),
			),
		);
	}

	/**
	 * Hook in.
	 */
	public static function init() {
		foreach ( array_keys( self::config() ) as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( __CLASS__, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( __CLASS__, 'render' ), 10, 2 );
		}
	}

	/**
	 * Inject the custom columns after the title, keeping the date column last.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public static function columns( $columns ) {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return $columns;
		}
		$config = self::config();
		if ( ! isset( $config[ $screen->post_type ] ) ) {
			return $columns;
		}

		$date = isset( $columns['date'] ) ? $columns['date'] : null;
		unset( $columns['date'] );

		foreach ( $config[ $screen->post_type ] as $key => $def ) {
			$columns[ $key ] = $def['label'];
		}

		if ( null !== $date ) {
			$columns['date'] = $date;
		}
		return $columns;
	}

	/**
	 * Render a custom column cell.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function render( $column, $post_id ) {
		$config    = self::config();
		$post_type = get_post_type( $post_id );
		if ( ! isset( $config[ $post_type ][ $column ] ) ) {
			return;
		}

		$def = $config[ $post_type ][ $column ];

		if ( isset( $def['callback'] ) ) {
			echo wp_kses_post( call_user_func( array( __CLASS__, $def['callback'] ), $post_id ) );
			return;
		}

		$value = get_post_meta( $post_id, $def['meta'], true );
		echo esc_html( $value ? $value : '—' );
	}

	/**
	 * Training mode cell.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private static function training_mode( $post_id ) {
		return get_post_meta( $post_id, 'is_online', true )
			? esc_html__( 'Online', 'setg' )
			: esc_html__( 'Op locatie', 'setg' );
	}

	/**
	 * Document file cell — link to the attachment or external URL.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private static function doc_file( $post_id ) {
		$att_id = absint( get_post_meta( $post_id, 'attachment_id', true ) );
		if ( $att_id ) {
			$url = wp_get_attachment_url( $att_id );
			if ( $url ) {
				return '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Bestand', 'setg' ) . '</a>';
			}
		}

		$ext = get_post_meta( $post_id, 'external_url', true );
		if ( $ext ) {
			return '<a href="' . esc_url( $ext ) . '" target="_blank" rel="noopener">' . esc_html__( 'Link', 'setg' ) . '</a>';
		}

		return '—';
	}

	/**
	 * Announcement importance cell.
	 *
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private static function announcement_important( $post_id ) {
		return get_post_meta( $post_id, 'important', true )
			? '<span style="color:#c81e2c;font-weight:600;">' . esc_html__( 'Belangrijk', 'setg' ) . '</span>'
			: '—';
	}
}
