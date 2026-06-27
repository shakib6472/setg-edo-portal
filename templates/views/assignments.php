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
				<?php if ( is_numeric( $a['id'] ) ) : ?>
					<?php $edo_on = ! empty( $a['interested'] ); ?>
					<button
						type="button"
						class="edo-pillbtn edo-interest<?php echo $edo_on ? ' is-on' : ''; ?>"
						data-edo-interest="<?php echo (int) $a['id']; ?>"
						aria-pressed="<?php echo $edo_on ? 'true' : 'false'; ?>"
					>
						<span class="edo-interest__off"><?php esc_html_e( 'Ik ben geïnteresseerd', 'setg' ); ?></span>
						<span class="edo-interest__on"><?php esc_html_e( 'Aangemeld ✓', 'setg' ); ?></span>
					</button>
				<?php else : ?>
					<button type="button" class="edo-pillbtn"><?php esc_html_e( 'Ik ben geïnteresseerd', 'setg' ); ?></button>
				<?php endif; ?>
			</div>

			<?php if ( is_numeric( $a['id'] ) ) : ?>
				<div class="edo-acard__meta">
					<button type="button" class="edo-cmtbtn" data-edo-cmt-open="<?php echo (int) $a['id']; ?>" data-title="<?php echo esc_attr( $a['title'] ); ?>">
						<?php echo edo_icon( 'comment', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
						<?php esc_html_e( 'Vragen / reacties', 'setg' ); ?>
						<span class="edo-cmtbtn__count">(<?php echo (int) $a['comments']; ?>)</span>
					</button>
					<span class="edo-likecount" title="<?php esc_attr_e( 'Geïnteresseerde leden', 'setg' ); ?>">
						<?php echo edo_icon( 'heart', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
						<span class="edo-likecount__n"><?php echo (int) $a['interest_count']; ?></span>
					</span>
				</div>
			<?php endif; ?>

		</article>
	<?php endforeach; ?>
</div>

<!-- Comments / questions modal -->
<div class="edo-modal edo-modal--text" id="edo-cmt-modal" hidden>
	<div class="edo-modal__overlay" data-edo-close></div>
	<div class="edo-modal__dialog edo-modal__dialog--text" role="dialog" aria-modal="true" aria-labelledby="edo-cmt-title">
		<div class="edo-modal__head">
			<h3 class="edo-modal__title" id="edo-cmt-title"></h3>
			<button type="button" class="edo-modal__close" data-edo-close aria-label="<?php esc_attr_e( 'Sluiten', 'setg' ); ?>">
				&times;
			</button>
		</div>
		<div class="edo-cmt-list" data-edo-cmt-list></div>
		<form class="edo-cmt-form" data-edo-cmt-form>
			<div class="edo-cmt-replyto" data-edo-cmt-replyto hidden>
				<span><?php esc_html_e( 'Antwoord aan', 'setg' ); ?> <strong data-edo-cmt-replyname></strong></span>
				<button type="button" class="edo-cmt-replycancel" data-edo-cmt-replycancel aria-label="<?php esc_attr_e( 'Annuleren', 'setg' ); ?>">&times;</button>
			</div>
			<textarea class="edo-textarea" data-edo-cmt-input rows="3" placeholder="<?php esc_attr_e( 'Stel een vraag of plaats een reactie…', 'setg' ); ?>" required></textarea>
			<button type="submit" class="edo-blockbtn"><?php esc_html_e( 'Plaats reactie', 'setg' ); ?></button>
		</form>
	</div>
</div>

<?php edo_get_template( 'partials/sample-note', array( 'items' => $edo_items ) ); ?>
