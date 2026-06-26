<?php
/**
 * Contact view. The message form is display-only in this phase; sending is
 * wired up in a later phase.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Contact', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Vragen of hulp nodig? Het team van SETG staat voor je klaar.', 'setg' ); ?></p>
</div>

<div class="edo-grid2">

	<div class="edo-card edo-cinfo">
		<h3><?php esc_html_e( 'SETG · SLICKS Ervaringslab', 'setg' ); ?></h3>

		<div class="edo-cinfo__row">
			<span class="edo-cinfo__icon"><?php echo edo_icon( 'location', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
			<?php esc_html_e( 'Marco Polostraat 291B · 1056 DN Amsterdam', 'setg' ); ?>
		</div>
		<div class="edo-cinfo__row">
			<span class="edo-cinfo__icon"><?php echo edo_icon( 'mail', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
			<a href="mailto:info@slicks.info">info@slicks.info</a>
		</div>
		<div class="edo-cinfo__row">
			<span class="edo-cinfo__icon"><?php echo edo_icon( 'phone', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
			<a href="tel:+31207008389">020 700 83 89</a>
		</div>
		<div class="edo-cinfo__row">
			<span class="edo-cinfo__icon"><?php echo edo_icon( 'clock', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
			<?php esc_html_e( 'Ma t/m vr · 11.00–16.00 uur', 'setg' ); ?>
		</div>
	</div>

	<div class="edo-card edo-form">
		<h3><?php esc_html_e( 'Stuur een bericht', 'setg' ); ?></h3>
		<input type="text" class="edo-input" placeholder="<?php esc_attr_e( 'Onderwerp', 'setg' ); ?>" />
		<textarea class="edo-textarea" rows="4" placeholder="<?php esc_attr_e( 'Je bericht…', 'setg' ); ?>"></textarea>
		<button type="button" class="edo-blockbtn"><?php esc_html_e( 'Versturen', 'setg' ); ?></button>
	</div>

</div>
