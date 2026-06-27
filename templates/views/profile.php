<?php
/**
 * Profile (Mijn profiel) view — display + self-edit.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_user = wp_get_current_user();
$edo_name = $edo_user->display_name ? $edo_user->display_name : $edo_user->user_login;

// Raw stored values (empty when not yet set).
$edo_role_raw  = (string) get_user_meta( $edo_user->ID, 'edo_function', true );
$edo_bio_raw   = (string) get_user_meta( $edo_user->ID, 'edo_bio', true );
$edo_avail_raw = (string) get_user_meta( $edo_user->ID, 'edo_availability', true );
$edo_pref_raw  = (string) get_user_meta( $edo_user->ID, 'edo_contact_pref', true );
$edo_exp_raw   = (string) get_user_meta( $edo_user->ID, 'edo_expertise', true );

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only UI flags.
$edo_edit  = isset( $_GET['edit'] );
$edo_saved = isset( $_GET['saved'] );
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$edo_profile_url = edo_view_url( 'profile' );
?>

<?php if ( $edo_edit ) : ?>

	<div class="edo-pagehead">
		<h1><?php esc_html_e( 'Profiel bewerken', 'setg' ); ?></h1>
		<p><?php esc_html_e( 'Werk je gegevens bij. Alleen jij kunt je eigen profiel aanpassen.', 'setg' ); ?></p>
	</div>

	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="edo-card edo-pcard edo-profile-form">
		<input type="hidden" name="action" value="edo_save_profile" />
		<?php wp_nonce_field( EDO_Profile::NONCE ); ?>

		<div>
			<label for="edo-pf-name"><?php esc_html_e( 'Naam', 'setg' ); ?></label>
			<input type="text" id="edo-pf-name" name="edo_name" class="edo-input" value="<?php echo esc_attr( $edo_name ); ?>" required />
		</div>
		<div>
			<label for="edo-pf-func"><?php esc_html_e( 'Functie / rol', 'setg' ); ?></label>
			<input type="text" id="edo-pf-func" name="edo_function" class="edo-input" value="<?php echo esc_attr( $edo_role_raw ); ?>" placeholder="<?php esc_attr_e( 'bijv. Ervaringsdeskundige', 'setg' ); ?>" />
		</div>
		<div>
			<label for="edo-pf-bio"><?php esc_html_e( 'Over mij', 'setg' ); ?></label>
			<textarea id="edo-pf-bio" name="edo_bio" class="edo-textarea" rows="4"><?php echo esc_textarea( $edo_bio_raw ); ?></textarea>
		</div>
		<div>
			<label for="edo-pf-exp"><?php esc_html_e( 'Expertise (komma-gescheiden)', 'setg' ); ?></label>
			<input type="text" id="edo-pf-exp" name="edo_expertise" class="edo-input" value="<?php echo esc_attr( $edo_exp_raw ); ?>" placeholder="<?php esc_attr_e( 'bijv. Online gokken, Storytelling', 'setg' ); ?>" />
		</div>
		<div>
			<label for="edo-pf-avail"><?php esc_html_e( 'Beschikbaarheid', 'setg' ); ?></label>
			<input type="text" id="edo-pf-avail" name="edo_availability" class="edo-input" value="<?php echo esc_attr( $edo_avail_raw ); ?>" placeholder="<?php esc_attr_e( 'bijv. Ma–Wo, overdag', 'setg' ); ?>" />
		</div>
		<div>
			<label for="edo-pf-pref"><?php esc_html_e( 'Contactvoorkeur', 'setg' ); ?></label>
			<input type="text" id="edo-pf-pref" name="edo_contact_pref" class="edo-input" value="<?php echo esc_attr( $edo_pref_raw ); ?>" placeholder="<?php esc_attr_e( 'bijv. Het liefst via e-mail', 'setg' ); ?>" />
		</div>

		<div class="edo-form-actions">
			<a class="edo-btn-ghost" href="<?php echo esc_url( $edo_profile_url ); ?>"><?php esc_html_e( 'Annuleren', 'setg' ); ?></a>
			<button type="submit" class="edo-blockbtn edo-blockbtn--auto"><?php esc_html_e( 'Opslaan', 'setg' ); ?></button>
		</div>
	</form>

<?php else : ?>

	<?php
	$edo_role      = $edo_role_raw ? $edo_role_raw : __( 'Ervaringsdeskundige', 'setg' );
	$edo_bio       = $edo_bio_raw ? $edo_bio_raw : __( 'Nog niet ingevuld.', 'setg' );
	$edo_avail     = $edo_avail_raw ? $edo_avail_raw : '—';
	$edo_pref      = $edo_pref_raw ? $edo_pref_raw : '—';
	$edo_expertise = $edo_exp_raw ? array_filter( array_map( 'trim', explode( ',', $edo_exp_raw ) ) ) : array();
	?>

	<?php if ( $edo_saved ) : ?>
		<div class="edo-savednote"><?php esc_html_e( 'Je profiel is bijgewerkt.', 'setg' ); ?></div>
	<?php endif; ?>

	<div class="edo-prof-grid">

		<div class="edo-card edo-prof-card">
			<span class="edo-avatar edo-avatar--xl"><?php echo esc_html( edo_initials( $edo_name ) ); ?></span>
			<h2><?php echo esc_html( $edo_name ); ?></h2>
			<span class="edo-prof__role"><?php echo esc_html( $edo_role ); ?></span>
			<div class="edo-prof__status"><span></span><?php esc_html_e( 'Beschikbaar voor opdrachten', 'setg' ); ?></div>
			<a class="edo-blockbtn" href="<?php echo esc_url( add_query_arg( 'edit', '1', $edo_profile_url ) ); ?>"><?php esc_html_e( 'Profiel bewerken', 'setg' ); ?></a>
		</div>

		<div class="edo-prof__col">

			<div class="edo-card edo-pcard">
				<h3><?php esc_html_e( 'Over mij', 'setg' ); ?></h3>
				<p><?php echo esc_html( $edo_bio ); ?></p>
			</div>

			<div class="edo-card edo-pcard">
				<h3><?php esc_html_e( 'Expertise', 'setg' ); ?></h3>
				<?php if ( ! empty( $edo_expertise ) ) : ?>
					<div class="edo-xtags">
						<?php foreach ( $edo_expertise as $skill ) : ?>
							<span class="edo-xtag"><?php echo esc_html( $skill ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p><?php esc_html_e( 'Nog geen expertise toegevoegd.', 'setg' ); ?></p>
				<?php endif; ?>
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

<?php endif; ?>
