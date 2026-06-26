<?php
/**
 * Trainings (Trainingen) view — powered by TutorLMS courses.
 *
 * Each card links to the course's own single page (styled in the final pass).
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_courses = EDO_Data::courses();
$edo_can_edit = current_user_can( 'edit_posts' );
?>

<div class="edo-pagehead">
	<h1><?php esc_html_e( 'Trainingen', 'setg' ); ?></h1>
	<p><?php esc_html_e( 'Ontwikkel je als ervaringsdeskundige — volg cursussen op je eigen tempo.', 'setg' ); ?></p>
</div>

<?php if ( ! edo_tutor_active() ) : ?>

	<div class="edo-card edo-emptybox">
		<?php echo edo_icon( 'training', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
		<p>
			<?php
			echo $edo_can_edit
				? esc_html__( 'TutorLMS is niet actief. Activeer TutorLMS om cursussen te tonen.', 'setg' )
				: esc_html__( 'Er zijn op dit moment geen cursussen beschikbaar.', 'setg' );
			?>
		</p>
	</div>

<?php elseif ( empty( $edo_courses ) ) : ?>

	<div class="edo-card edo-emptybox">
		<?php echo edo_icon( 'training', 26 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
		<p>
			<?php esc_html_e( 'Er zijn nog geen cursussen.', 'setg' ); ?>
			<?php if ( $edo_can_edit ) : ?>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . tutor()->course_post_type ) ); ?>"><?php esc_html_e( 'Voeg een cursus toe in TutorLMS.', 'setg' ); ?></a>
			<?php endif; ?>
		</p>
	</div>

<?php else : ?>

	<div class="edo-toolbar">
		<a class="edo-linkbtn" href="<?php echo esc_url( edo_courses_archive_url() ); ?>">
			<?php echo edo_icon( 'training', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
			<?php esc_html_e( 'Alle cursussen', 'setg' ); ?>
		</a>
	</div>

	<div class="edo-course-grid">
		<?php foreach ( $edo_courses as $c ) : ?>
			<a class="edo-card edo-course" href="<?php echo esc_url( $c['url'] ); ?>">
				<div class="edo-course__thumb<?php echo empty( $c['thumb'] ) ? ' edo-course__thumb--empty' : ''; ?>"
					<?php if ( ! empty( $c['thumb'] ) ) : ?>style="background-image:url('<?php echo esc_url( $c['thumb'] ); ?>');"<?php endif; ?>>
					<?php if ( empty( $c['thumb'] ) ) : ?>
						<?php echo edo_icon( 'training', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
					<?php endif; ?>
					<?php if ( ! empty( $c['enrolled'] ) ) : ?>
						<span class="edo-course__badge"><?php esc_html_e( 'Ingeschreven', 'setg' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="edo-course__body">
					<h3><?php echo esc_html( $c['title'] ); ?></h3>
					<?php if ( ! empty( $c['excerpt'] ) ) : ?>
						<p><?php echo esc_html( $c['excerpt'] ); ?></p>
					<?php endif; ?>
					<div class="edo-course__foot">
						<span class="edo-course__meta">
							<?php
							if ( $c['lessons'] > 0 ) {
								/* translators: %d: number of lessons. */
								printf( esc_html( _n( '%d les', '%d lessen', $c['lessons'], 'setg' ) ), (int) $c['lessons'] );
							}
							?>
						</span>
						<span class="edo-course__cta">
							<?php echo ! empty( $c['enrolled'] ) ? esc_html__( 'Doorgaan →', 'setg' ) : esc_html__( 'Bekijk cursus →', 'setg' ); ?>
						</span>
					</div>
				</div>
			</a>
		<?php endforeach; ?>
	</div>

<?php endif; ?>
