<?php
/**
 * Portal login screen (standalone document). Authenticates via core wp-login.php.
 *
 * @package SETG_EDO_Portal
 * @var bool $pending True when a logged-in user lacks portal access (awaiting approval).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pending      = ! empty( $pending );
$edo_redirect = edo_view_url( 'dashboard' );
$edo_error    = isset( $_GET['login'] ) && 'failed' === $_GET['login']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only UI flag.
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

			<h1 class="edo-login__title"><?php esc_html_e( 'EDO Community Portal', 'setg-edo-portal' ); ?></h1>
			<p class="edo-login__subtitle"><?php esc_html_e( 'Besloten omgeving voor het EDO-team van SETG', 'setg-edo-portal' ); ?></p>

			<?php if ( $pending ) : ?>

				<div class="edo-login__notice">
					<?php esc_html_e( 'Je account wacht nog op goedkeuring door SETG. Je ontvangt bericht zodra je toegang hebt.', 'setg-edo-portal' ); ?>
				</div>
				<a class="edo-btn edo-btn--block" href="<?php echo esc_url( wp_logout_url( home_url( '/portal/' ) ) ); ?>">
					<?php esc_html_e( 'Uitloggen', 'setg-edo-portal' ); ?>
				</a>

			<?php else : ?>

				<?php if ( $edo_error ) : ?>
					<div class="edo-login__error"><?php esc_html_e( 'Inloggen mislukt. Controleer je e-mailadres en wachtwoord.', 'setg-edo-portal' ); ?></div>
				<?php endif; ?>

				<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" method="post" class="edo-login__form">

					<label for="edo-user"><?php esc_html_e( 'E-mailadres', 'setg-edo-portal' ); ?></label>
					<input type="text" name="log" id="edo-user" autocomplete="username" placeholder="jij@voorbeeld.nl" required />

					<label for="edo-pass"><?php esc_html_e( 'Wachtwoord', 'setg-edo-portal' ); ?></label>
					<input type="password" name="pwd" id="edo-pass" autocomplete="current-password" placeholder="••••••••" required />

					<div class="edo-login__forgot">
						<a href="<?php echo esc_url( wp_lostpassword_url( $edo_redirect ) ); ?>"><?php esc_html_e( 'Wachtwoord vergeten?', 'setg-edo-portal' ); ?></a>
					</div>

					<input type="hidden" name="redirect_to" value="<?php echo esc_url( $edo_redirect ); ?>" />
					<input type="hidden" name="testcookie" value="1" />

					<button type="submit" class="edo-btn edo-btn--block"><?php esc_html_e( 'Inloggen', 'setg-edo-portal' ); ?></button>
				</form>

			<?php endif; ?>

			<div class="edo-login__secure">
				<?php echo edo_icon( 'lock', 13 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
				<span><?php esc_html_e( 'Beveiligde omgeving · alleen voor leden', 'setg-edo-portal' ); ?></span>
			</div>

		</div>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
<?php
