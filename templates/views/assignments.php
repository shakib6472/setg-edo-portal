<?php
/**
 * Assignments (Opdrachten) view.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_items = EDO_Data::assignments();

/**
 * Map a sample tag type to its CSS modifier.
 *
 * @param string $type Tag type.
 * @return string
 */
$edo_tag_class = static function ( $type ) {
	if ( 'urgent' === $type ) {
		return 'edo-tag edo-tag--urgent';
	}
	if ( 'new' === $type ) {
		return 'edo-tag edo-tag--new';
	}
	return 'edo-tag';
};
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Opdrachten', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Kansen om je ervaring in te zetten. Interesse? Eén tik en wij nemen contact op.', 'setg' ); ?></p>
</div>

<div class="edo-grid2">
	<?php foreach ( $edo_items as $a ) : ?>
		<article class="edo-card edo-acard">

			<div class="edo-acard__head">
				<h3><?php echo esc_html( $a['title'] ); ?></h3>
				<?php if ( ! empty( $a['tag'] ) ) : ?>
					<span class="<?php echo esc_attr( $edo_tag_class( $a['tag_type'] ) ); ?>"><?php echo esc_html( $a['tag'] ); ?></span>
				<?php endif; ?>
			</div>

			<div class="edo-acard__rows">
				<?php if ( ! empty( $a['client'] ) ) : ?>
					<div class="edo-irow"><span class="edo-irow__ic"><?php echo edo_icon( 'building', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span><?php echo esc_html( $a['client'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $a['date'] ) ) : ?>
					<div class="edo-irow"><span class="edo-irow__ic"><?php echo edo_icon( 'calendar', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span><?php echo esc_html( $a['date'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $a['location'] ) ) : ?>
					<div class="edo-irow"><span class="edo-irow__ic"><?php echo edo_icon( 'location', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span><?php echo esc_html( $a['location'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $a['time'] ) ) : ?>
					<div class="edo-irow"><span class="edo-irow__ic"><?php echo edo_icon( 'clock', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span><?php echo esc_html( $a['time'] ); ?></div>
				<?php endif; ?>
			</div>

			<div class="edo-acard__foot">
				<div class="edo-comp">
					<span class="edo-comp__label"><?php esc_html_e( 'Vergoeding', 'setg' ); ?></span>
					<strong><?php echo esc_html( $a['comp'] ); ?></strong>
				</div>
				<button type="button" class="edo-pillbtn"><?php esc_html_e( 'Ik ben geïnteresseerd', 'setg' ); ?></button>
			</div>

		</article>
	<?php endforeach; ?>
</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
