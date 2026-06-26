<?php
/**
 * Dashboard view.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_user    = wp_get_current_user();
$edo_first   = $edo_user->first_name ? $edo_user->first_name : strtok( $edo_user->display_name ? $edo_user->display_name : $edo_user->user_login, ' ' );
$edo_counts  = EDO_Data::counts();
$edo_assign  = EDO_Data::assignments( 3 );
$edo_news    = EDO_Data::announcements( 3 );
$edo_sample  = ! empty( $edo_assign ) && ! empty( $edo_assign[0]['is_sample'] );
?>

<div class="edo-pagehead">
	<h1><?php printf( /* translators: %s: member first name. */ esc_html__( 'Welkom terug, %s 👋', 'setg' ), esc_html( $edo_first ) ); ?></h1>
	<p><?php esc_html_e( 'Je opdrachten, trainingen en mededelingen — in één rustig overzicht.', 'setg' ); ?></p>
</div>

<div class="edo-stats">
	<div class="edo-card edo-stat">
		<div class="edo-stat__icon"><?php echo edo_icon( 'stat-doc', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></div>
		<div class="edo-stat__num"><?php echo esc_html( $edo_counts['assignments'] ); ?></div>
		<div class="edo-stat__label"><?php esc_html_e( 'Open opdrachten', 'setg' ); ?></div>
		<div class="edo-stat__sub"><?php esc_html_e( '2 sluiten deze week', 'setg' ); ?></div>
	</div>
	<div class="edo-card edo-stat">
		<div class="edo-stat__icon"><?php echo edo_icon( 'training', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></div>
		<div class="edo-stat__num"><?php echo esc_html( $edo_counts['trainings'] ); ?></div>
		<div class="edo-stat__label"><?php esc_html_e( 'Aankomende trainingen', 'setg' ); ?></div>
		<div class="edo-stat__sub"><?php esc_html_e( 'Eerstvolgende: 25 juni', 'setg' ); ?></div>
	</div>
	<div class="edo-card edo-stat">
		<div class="edo-stat__icon"><?php echo edo_icon( 'documents', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></div>
		<div class="edo-stat__num"><?php echo esc_html( $edo_counts['documents'] ); ?></div>
		<div class="edo-stat__label"><?php esc_html_e( 'Nieuwe documenten', 'setg' ); ?></div>
		<div class="edo-stat__sub"><?php esc_html_e( 'Sinds je vorige bezoek', 'setg' ); ?></div>
	</div>
</div>

<div class="edo-dashgrid">

	<div>
		<div class="edo-sec-head">
			<h2><?php esc_html_e( 'Aankomende opdrachten', 'setg' ); ?></h2>
			<a class="edo-link" href="<?php echo esc_url( edo_view_url( 'assignments' ) ); ?>"><?php esc_html_e( 'Bekijk alle →', 'setg' ); ?></a>
		</div>
		<div class="edo-stack">
			<?php foreach ( $edo_assign as $a ) : ?>
				<a class="edo-card edo-arow" href="<?php echo esc_url( edo_view_url( 'assignments' ) ); ?>">
					<span class="edo-datechip">
						<span class="edo-datechip__day"><?php echo esc_html( $a['day'] ); ?></span>
						<span class="edo-datechip__mon"><?php echo esc_html( $a['mon'] ); ?></span>
					</span>
					<span class="edo-arow__body">
						<h3><?php echo esc_html( $a['title'] ); ?></h3>
						<p><?php echo esc_html( trim( implode( ' · ', array_filter( array( $a['client'], $a['location'], $a['comp'] ) ) ) ) ); ?></p>
					</span>
					<span class="edo-arow__chev"><?php echo edo_icon( 'chevron', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>

	<div>
		<div class="edo-sec-head">
			<h2><?php esc_html_e( 'Mededelingen', 'setg' ); ?></h2>
			<a class="edo-link" href="<?php echo esc_url( edo_view_url( 'announcements' ) ); ?>"><?php esc_html_e( 'Bekijk alle →', 'setg' ); ?></a>
		</div>
		<div class="edo-stack edo-stack--tight">
			<?php foreach ( $edo_news as $n ) : ?>
				<article class="edo-card edo-ann">
					<div class="edo-ann__meta">
						<?php if ( ! empty( $n['important'] ) ) : ?>
							<span class="edo-ann__dot"></span>
						<?php endif; ?>
						<span class="edo-ann__date"><?php echo esc_html( $n['date'] ); ?></span>
					</div>
					<h3><?php echo esc_html( $n['title'] ); ?></h3>
				</article>
			<?php endforeach; ?>
		</div>
	</div>

</div>

<?php if ( $edo_sample && current_user_can( 'edit_posts' ) ) : ?>
	<div class="edo-samplenote">
		<?php echo edo_icon( 'lock', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
		<span><?php esc_html_e( 'Dit zijn voorbeeldgegevens. Zodra je opdrachten, trainingen en mededelingen toevoegt, verschijnen die hier automatisch.', 'setg' ); ?></span>
	</div>
<?php endif; ?>
