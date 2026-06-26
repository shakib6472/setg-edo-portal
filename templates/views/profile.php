<?php
/**
 * Profile (Mijn profiel) view.
 *
 * Shows the logged-in member. Fields not yet captured (bio, expertise, etc.)
 * fall back to placeholder copy until profile editing lands in a later phase.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_user = wp_get_current_user();
$edo_name = $edo_user->display_name ? $edo_user->display_name : $edo_user->user_login;

$edo_role  = get_user_meta( $edo_user->ID, 'edo_function', true );
$edo_role  = $edo_role ? $edo_role : __( 'Ervaringsdeskundige', 'setg' );
$edo_bio   = get_user_meta( $edo_user->ID, 'edo_bio', true );
$edo_bio   = $edo_bio ? $edo_bio : __( 'Ik zet mijn ervaring met online gokken in om anderen te versterken. Ik werk aan communicatie, vroegsignalering en het toegankelijk maken van hulp — zonder oordeel.', 'setg' );
$edo_avail = get_user_meta( $edo_user->ID, 'edo_availability', true );
$edo_avail = $edo_avail ? $edo_avail : __( 'Maandag t/m woensdag, overdag', 'setg' );
$edo_pref  = get_user_meta( $edo_user->ID, 'edo_contact_pref', true );
$edo_pref  = $edo_pref ? $edo_pref : __( 'Het liefst via e-mail', 'setg' );

$edo_expertise = get_user_meta( $edo_user->ID, 'edo_expertise', true );
if ( is_string( $edo_expertise ) && '' !== $edo_expertise ) {
	$edo_expertise = array_filter( array_map( 'trim', explode( ',', $edo_expertise ) ) );
}
if ( empty( $edo_expertise ) || ! is_array( $edo_expertise ) ) {
	$edo_expertise = array(
		__( 'Online gokken', 'setg' ),
		__( 'Communicatie', 'setg' ),
		__( 'Vroegsignalering', 'setg' ),
		__( 'Storytelling', 'setg' ),
	);
}
?>

<div class="edo-prof-grid">

	<div class="edo-card edo-prof-card">
		<span class="edo-avatar edo-avatar--xl"><?php echo esc_html( edo_initials( $edo_name ) ); ?></span>
		<h2><?php echo esc_html( $edo_name ); ?></h2>
		<span class="edo-prof__role"><?php echo esc_html( $edo_role ); ?></span>
		<div class="edo-prof__status"><span></span><?php esc_html_e( 'Beschikbaar voor opdrachten', 'setg' ); ?></div>
		<button type="button" class="edo-blockbtn"><?php esc_html_e( 'Profiel bewerken', 'setg' ); ?></button>
	</div>

	<div class="edo-prof__col">

		<div class="edo-card edo-pcard">
			<h3><?php esc_html_e( 'Over mij', 'setg' ); ?></h3>
			<p><?php echo esc_html( $edo_bio ); ?></p>
		</div>

		<div class="edo-card edo-pcard">
			<h3><?php esc_html_e( 'Expertise', 'setg' ); ?></h3>
			<div class="edo-xtags">
				<?php foreach ( $edo_expertise as $skill ) : ?>
					<span class="edo-xtag"><?php echo esc_html( $skill ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="edo-grid2">
			<div class="edo-card edo-pcard">
				<h3><?php esc_html_e( 'Beschikbaarheid', 'setg' ); ?></h3>
				<p><?php echo esc_html( $edo_avail ); ?></p>
			</div>
			<div class="edo-card edo-pcard">
				<h3><?php esc_html_e( 'Contactvoorkeur', 'setg' ); ?></h3>
				<p><?php echo esc_html( $edo_pref ); ?></p>
			</div>
		</div>

	</div>

</div>
