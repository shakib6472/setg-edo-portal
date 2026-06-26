<?php
/**
 * Small "this is sample content" note, shown to managers when a view is still
 * rendering the design's sample data (i.e. no real content has been added yet).
 *
 * @package SETG_EDO_Portal
 * @var array $items Rows passed from the view; checked for the is_sample flag.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $items ) || empty( $items[0]['is_sample'] ) || ! current_user_can( 'edit_posts' ) ) {
	return;
}
?>
<div class="edo-samplenote">
	<?php echo edo_icon( 'lock', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
	<span><?php esc_html_e( 'Dit zijn voorbeeldgegevens. Zodra je echte content toevoegt in het beheer, verschijnt die hier automatisch.', 'setg' ); ?></span>
</div>
