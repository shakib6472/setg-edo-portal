<?php
/**
 * Top bar. Contains both the desktop and mobile variants; CSS shows the right
 * one per breakpoint.
 *
 * @package SETG_EDO_Portal
 * @var string $view  Current view slug.
 * @var string $title Current view title.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_user = wp_get_current_user();
$edo_name = $edo_user->display_name ? $edo_user->display_name : $edo_user->user_login;
?>

<!-- Desktop top bar -->
<header class="edo-topbar edo-topbar--desktop">
	<div class="edo-search" role="search">
		<?php echo edo_icon( 'search', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
		<span><?php esc_html_e( 'Zoek opdrachten, trainingen…', 'setg' ); ?></span>
	</div>
	<div class="edo-topbar__actions">
		<button type="button" class="edo-iconbtn" aria-label="<?php esc_attr_e( 'Meldingen', 'setg' ); ?>">
			<?php echo edo_icon( 'bell', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
			<span class="edo-dot"></span>
		</button>
		<a href="<?php echo esc_url( edo_view_url( 'profile' ) ); ?>" class="edo-avatar edo-avatar--sm" aria-label="<?php esc_attr_e( 'Mijn profiel', 'setg' ); ?>">
			<?php echo esc_html( edo_initials( $edo_name ) ); ?>
		</a>
	</div>
</header>

<!-- Mobile top bar -->
<header class="edo-topbar edo-topbar--mobile">
	<span class="edo-topbar__logo"><img src="<?php echo esc_url( EDO_PORTAL_URL . 'assets/img/setg-logo.png' ); ?>" alt="SETG" /></span>
	<strong class="edo-topbar__title"><?php echo esc_html( $title ); ?></strong>
	<button type="button" class="edo-iconbtn edo-iconbtn--plain" aria-label="<?php esc_attr_e( 'Meldingen', 'setg' ); ?>">
		<?php echo edo_icon( 'bell', 19 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
		<span class="edo-dot edo-dot--onbar"></span>
	</button>
	<a href="<?php echo esc_url( wp_logout_url( home_url( '/portal/' ) ) ); ?>" class="edo-iconbtn edo-iconbtn--plain" aria-label="<?php esc_attr_e( 'Uitloggen', 'setg' ); ?>">
		<?php echo edo_icon( 'logout', 19 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
	</a>
</header>
