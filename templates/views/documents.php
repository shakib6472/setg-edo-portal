<?php
/**
 * Documents & resources (Documenten) view.
 *
 * Filtering is progressive-enhancement: chips + rows carry data attributes and
 * portal.js toggles visibility. Without JS, all documents simply show.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_items = EDO_Data::documents();

$edo_cats = array(
	'all'      => __( 'Alle', 'setg' ),
	'artikel'  => __( 'Artikelen', 'setg' ),
	'video'    => __( 'Video’s', 'setg' ),
	'document' => __( 'Documenten', 'setg' ),
	'beleid'   => __( 'Beleid', 'setg' ),
);

/**
 * Tile colour modifier for a document category.
 *
 * @param string $cat Category key.
 * @return string
 */
$edo_tile_class = static function ( $cat ) {
	$map = array(
		'artikel' => ' edo-doc__tile--artikel',
		'video'   => ' edo-doc__tile--video',
		'beleid'  => ' edo-doc__tile--beleid',
	);
	return 'edo-doc__tile' . ( isset( $map[ $cat ] ) ? $map[ $cat ] : '' );
};
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Documenten &amp; bronnen', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Artikelen, video’s en documenten — gefilterd op categorie.', 'setg' ); ?></p>
</div>

<div data-edo-docs>

	<div class="edo-filters">
		<?php $edo_first = true; ?>
		<?php foreach ( $edo_cats as $key => $label ) : ?>
			<button type="button" class="edo-chip<?php echo $edo_first ? ' is-active' : ''; ?>" data-filter="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
			<?php $edo_first = false; ?>
		<?php endforeach; ?>
	</div>

	<div class="edo-doclist">
		<?php foreach ( $edo_items as $d ) : ?>
			<article class="edo-card edo-doc" data-cat="<?php echo esc_attr( $d['cat'] ); ?>">
				<div class="<?php echo esc_attr( $edo_tile_class( $d['cat'] ) ); ?>"><?php echo esc_html( $d['short'] ); ?></div>
				<div class="edo-doc__body">
					<h3><?php echo esc_html( $d['title'] ); ?></h3>
					<?php if ( ! empty( $d['meta'] ) ) : ?>
						<p><?php echo esc_html( $d['meta'] ); ?></p>
					<?php endif; ?>
				</div>
				<button type="button" class="edo-doc__dl" aria-label="<?php esc_attr_e( 'Downloaden', 'setg' ); ?>">
					<?php echo edo_icon( 'download', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
				</button>
			</article>
		<?php endforeach; ?>
	</div>

</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
