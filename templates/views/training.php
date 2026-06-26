<?php
/**
 * Trainings (Trainingen) view.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_items = EDO_Data::trainings();
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Trainingen', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Ontwikkel je als ervaringsdeskundige — van vroegsignalering tot storytelling.', 'setg' ); ?></p>
</div>

<div class="edo-grid2">
	<?php foreach ( $edo_items as $tr ) : ?>
		<article class="edo-card edo-tcard">

			<div class="edo-tcard__top">
				<?php if ( ! empty( $tr['online'] ) ) : ?>
					<span class="edo-mode edo-mode--online"><?php esc_html_e( 'Online', 'setg' ); ?></span>
				<?php else : ?>
					<span class="edo-mode"><?php esc_html_e( 'Op locatie', 'setg' ); ?></span>
				<?php endif; ?>
				<?php if ( ! empty( $tr['spots'] ) ) : ?>
					<span class="edo-spots"><?php echo esc_html( $tr['spots'] ); ?></span>
				<?php endif; ?>
			</div>

			<div>
				<h3 class="edo-tcard__title"><?php echo esc_html( $tr['title'] ); ?></h3>
				<?php if ( ! empty( $tr['subject'] ) ) : ?>
					<p class="edo-tcard__subject"><?php echo esc_html( $tr['subject'] ); ?></p>
				<?php endif; ?>
			</div>

			<div class="edo-tcard__rows">
				<?php if ( ! empty( $tr['date'] ) ) : ?>
					<div class="edo-irow"><span class="edo-irow__ic"><?php echo edo_icon( 'calendar', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span><?php echo esc_html( $tr['date'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $tr['place'] ) ) : ?>
					<div class="edo-irow"><span class="edo-irow__ic"><?php echo edo_icon( 'location', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span><?php echo esc_html( $tr['place'] ); ?></div>
				<?php endif; ?>
			</div>

			<button type="button" class="edo-blockbtn"><?php esc_html_e( 'Inschrijven', 'setg' ); ?></button>

		</article>
	<?php endforeach; ?>
</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
