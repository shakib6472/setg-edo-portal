<?php
/**
 * Announcements (Mededelingen) view.
 *
 * Each card shows a one-line teaser and opens a modal with the full message.
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
		<article
			class="edo-card edo-anc edo-anc--open"
			role="button"
			tabindex="0"
			data-edo-anc-open
			data-title="<?php echo esc_attr( $n['title'] ); ?>"
			data-date="<?php echo esc_attr( $n['date'] ); ?>"
			data-important="<?php echo ! empty( $n['important'] ) ? '1' : '0'; ?>"
		>
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
					<p class="edo-anc__excerpt"><?php echo esc_html( $n['body'] ); ?></p>
				<?php endif; ?>
			</div>
			<span class="edo-anc__more" aria-hidden="true"><?php echo edo_icon( 'chevron', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>

			<div class="edo-anc__full" hidden><?php echo wp_kses_post( wpautop( $n['full'] ) ); ?></div>
		</article>
	<?php endforeach; ?>
</div>

<!-- Announcement detail modal -->
<div class="edo-modal edo-modal--text" id="edo-anc-modal" hidden>
	<div class="edo-modal__overlay" data-edo-close></div>
	<div class="edo-modal__dialog edo-modal__dialog--text" role="dialog" aria-modal="true" aria-labelledby="edo-anc-title">
		<div class="edo-modal__head">
			<h3 class="edo-modal__title" id="edo-anc-title"></h3>
			<button type="button" class="edo-modal__close" data-edo-close aria-label="<?php esc_attr_e( 'Sluiten', 'setg' ); ?>">
				&times;
			</button>
		</div>
		<div class="edo-ancmodal__meta" data-edo-anc-meta></div>
		<div class="edo-modal__body edo-modal__body--text" data-edo-modal-body></div>
	</div>
</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
