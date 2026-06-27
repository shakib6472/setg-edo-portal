<?php
/**
 * Portal login / registration screen (standalone document).
 *
 * Login authenticates via core wp-login.php; registration posts to
 * admin-post.php (EDO_Registration). New members are pending until approved.
 *
 * @package SETG_EDO_Portal
 * @var bool $pending True when a logged-in user lacks portal access.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pending      = ! empty( $pending );
$edo_redirect = edo_view_url( 'dashboard' );
$reg_enabled  = EDO_Registration::is_enabled();

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only UI flags.
$is_register   = ! $pending && $reg_enabled && isset( $_GET['register'] );
$login_failed  = isset( $_GET['login'] ) && 'failed' === $_GET['login'];
$registered_ok = isset( $_GET['registered'] );
$reg_error     = isset( $_GET['reg_error'] ) ? sanitize_key( wp_unslash( $_GET['reg_error'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

$reg_errors = array(
	'invalid'  => __( 'Controleer je gegevens: geldig e-mailadres en een wachtwoord van minimaal 8 tekens.', 'setg' ),
	'exists'   => __( 'Er bestaat al een account met dit e-mailadres.', 'setg' ),
	'failed'   => __( 'Aanmaken mislukt. Probeer het later opnieuw.', 'setg' ),
	'disabled' => __( 'Registratie is momenteel uitgeschakeld.', 'setg' ),
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body class="edo-body edo-body--login">

	<div class="edo-login">
		<div class="edo-login__card">

			<div class="edo-login__logo">
				<img src="<?php echo esc_url( EDO_PORTAL_URL . 'assets/img/setg-logo.png' ); ?>" alt="SETG" />
			</div>

			<h1 class="edo-login__title"><?php esc_html_e( 'EDO Community Portal', 'setg' ); ?></h1>
			<p class="edo-login__subtitle"><?php esc_html_e( 'Besloten omgeving voor het EDO-team van SETG', 'setg' ); ?></p>

			<?php if ( $pending ) : ?>

				<div class="edo-login__notice edo-login__notice--warn">
					<?php esc_html_e( 'Je account wacht nog op goedkeuring door SETG. Je ontvangt bericht zodra je toegang hebt.', 'setg' ); ?>
				</div>
				<a class="edo-btn edo-btn--block" href="<?php echo esc_url( wp_logout_url( home_url( '/portal/' ) ) ); ?>">
					<?php esc_html_e( 'Uitloggen', 'setg' ); ?>
				</a>

			<?php else : ?>

				<?php if ( $registered_ok ) : ?>
					<div class="edo-login__notice"><?php esc_html_e( 'Je aanmelding is ontvangen. Je krijgt bericht zodra SETG je account goedkeurt.', 'setg' ); ?></div>
				<?php endif; ?>
				<?php if ( $login_failed ) : ?>
					<div class="edo-login__error"><?php esc_html_e( 'Inloggen mislukt. Controleer je e-mailadres en wachtwoord.', 'setg' ); ?></div>
				<?php endif; ?>
				<?php if ( $reg_error && isset( $reg_errors[ $reg_error ] ) ) : ?>
					<div class="edo-login__error"><?php echo esc_html( $reg_errors[ $reg_error ] ); ?></div>
				<?php endif; ?>

				<?php if ( $is_register ) : ?>

					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="edo-login__form">
						<input type="hidden" name="action" value="edo_register" />
						<?php wp_nonce_field( EDO_Registration::NONCE ); ?>

						<label for="edo-name"><?php esc_html_e( 'Naam', 'setg' ); ?></label>
						<input type="text" name="edo_name" id="edo-name" autocomplete="name" required />

						<label for="edo-email"><?php esc_html_e( 'E-mailadres', 'setg' ); ?></label>
						<input type="email" name="edo_email" id="edo-email" autocomplete="email" placeholder="jij@voorbeeld.nl" required />

						<label for="edo-newpass"><?php esc_html_e( 'Wachtwoord (min. 8 tekens)', 'setg' ); ?></label>
						<input type="password" name="edo_pass" id="edo-newpass" autocomplete="new-password" minlength="8" placeholder="••••••••" required />

						<button type="submit" class="edo-btn edo-btn--block"><?php esc_html_e( 'Account aanmaken', 'setg' ); ?></button>
					</form>

					<p class="edo-login__switch">
						<?php esc_html_e( 'Al een account?', 'setg' ); ?>
						<a href="<?php echo esc_url( $edo_redirect ); ?>"><?php esc_html_e( 'Inloggen', 'setg' ); ?></a>
					</p>

				<?php else : ?>

					<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post" class="edo-login__form">
						<label for="edo-user"><?php esc_html_e( 'E-mailadres', 'setg' ); ?></label>
						<input type="text" name="log" id="edo-user" autocomplete="username" placeholder="jij@voorbeeld.nl" required />

						<label for="edo-pass"><?php esc_html_e( 'Wachtwoord', 'setg' ); ?></label>
						<input type="password" name="pwd" id="edo-pass" autocomplete="current-password" placeholder="••••••••" required />

						<div class="edo-login__forgot">
							<a href="<?php echo esc_url( wp_lostpassword_url( $edo_redirect ) ); ?>"><?php esc_html_e( 'Wachtwoord vergeten?', 'setg' ); ?></a>
						</div>

						<input type="hidden" name="redirect_to" value="<?php echo esc_url( $edo_redirect ); ?>" />
						<input type="hidden" name="testcookie" value="1" />
						<input type="hidden" name="edo_login" value="1" />

						<button type="submit" class="edo-btn edo-btn--block"><?php esc_html_e( 'Inloggen', 'setg' ); ?></button>
					</form>

					<?php if ( $reg_enabled ) : ?>
						<p class="edo-login__switch">
							<?php esc_html_e( 'Nog geen account?', 'setg' ); ?>
							<a href="<?php echo esc_url( add_query_arg( 'register', '1', $edo_redirect ) ); ?>"><?php esc_html_e( 'Aanmelden', 'setg' ); ?></a>
						</p>
					<?php endif; ?>

				<?php endif; ?>

			<?php endif; ?>

			<div class="edo-login__secure">
				<?php echo edo_icon( 'lock', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
				<span><?php esc_html_e( 'Beveiligde omgeving · alleen voor leden', 'setg' ); ?></span>
			</div>

		</div>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
<?php
