<?php
/**
 * Members (Leden) view.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_items = EDO_Data::members();
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Leden', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Het EDO-team — de mensen achter onze missie.', 'setg' ); ?></p>
</div>

<div class="edo-mem-grid">
	<?php foreach ( $edo_items as $m ) : ?>
		<article class="edo-card edo-mem">
			<span class="edo-avatar edo-avatar--lg"><?php echo esc_html( edo_initials( $m['name'] ) ); ?></span>
			<h3><?php echo esc_html( $m['name'] ); ?></h3>
			<span class="edo-mem__role"><?php echo esc_html( $m['role'] ); ?></span>

			<?php if ( ! empty( $m['tags'] ) ) : ?>
				<div class="edo-tags">
					<?php foreach ( $m['tags'] as $tag ) : ?>
						<span class="edo-mtag"><?php echo esc_html( $tag ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $m['avail'] ) ) : ?>
				<div class="edo-mem__avail">
					<?php echo edo_icon( 'clock', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
					<?php
					/* translators: %s: availability, e.g. "Ma–Vr". */
					printf( esc_html__( 'Beschikbaar: %s', 'setg' ), esc_html( $m['avail'] ) );
					?>
				</div>
			<?php endif; ?>
		</article>
	<?php endforeach; ?>
</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
