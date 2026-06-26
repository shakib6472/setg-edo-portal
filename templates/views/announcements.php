<?php
/**
 * Announcements (Mededelingen) view.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_items = EDO_Data::announcements();
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Mededelingen', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Nieuws en updates van het EDO-team.', 'setg' ); ?></p>
</div>

<div class="edo-anclist">
	<?php foreach ( $edo_items as $n ) : ?>
		<article class="edo-card edo-anc">
			<div class="edo-anc__icon<?php echo ! empty( $n['important'] ) ? ' edo-anc__icon--important' : ''; ?>">
				<?php echo edo_icon( 'announcements', 19 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
			</div>
			<div class="edo-anc__body">
				<div class="edo-anc__head">
					<?php if ( ! empty( $n['important'] ) ) : ?>
						<span class="edo-badge-important"><?php esc_html_e( 'Belangrijk', 'setg' ); ?></span>
					<?php endif; ?>
					<span class="edo-anc__date"><?php echo esc_html( $n['date'] ); ?></span>
				</div>
				<h3><?php echo esc_html( $n['title'] ); ?></h3>
				<?php if ( ! empty( $n['body'] ) ) : ?>
					<p><?php echo esc_html( $n['body'] ); ?></p>
				<?php endif; ?>
			</div>
		</article>
	<?php endforeach; ?>
</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
