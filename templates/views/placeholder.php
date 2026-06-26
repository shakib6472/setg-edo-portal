<?php
/**
 * Placeholder shown for views that are not built yet (later phases).
 *
 * @package SETG_EDO_Portal
 * @var string $title View title.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="edo-placeholder">
	<div class="edo-placeholder__icon"><?php echo edo_icon( 'dashboard', 28 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></div>
	<h1><?php echo esc_html( isset( $title ) ? $title : __( 'Binnenkort', 'setg' ) ); ?></h1>
	<p><?php esc_html_e( 'Dit onderdeel wordt in een volgende stap toegevoegd.', 'setg' ); ?></p>
	<span class="edo-badge-soon"><?php esc_html_e( 'In aanbouw', 'setg' ); ?></span>
</div>
