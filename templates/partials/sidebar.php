<?php
/**
 * Desktop sidebar navigation.
 *
 * @package SETG_EDO_Portal
 * @var string $view Current view slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_user     = wp_get_current_user();
$edo_name     = $edo_user->display_name ? $edo_user->display_name : $edo_user->user_login;
$edo_subtitle = get_user_meta( $edo_user->ID, 'edo_function', true );
if ( ! $edo_subtitle ) {
	$edo_subtitle = __( 'EDO-teamlid', 'setg-edo-portal' );
}
?>
<aside class="edo-sidebar">

	<div class="edo-sidebar__brand">
		<img src="<?php echo esc_url( EDO_PORTAL_URL . 'assets/img/setg-logo.png' ); ?>" alt="SETG" />
	</div>

	<span class="edo-sidebar__label"><?php esc_html_e( 'Menu', 'setg-edo-portal' ); ?></span>

	<nav class="edo-nav" aria-label="<?php esc_attr_e( 'Hoofdnavigatie', 'setg-edo-portal' ); ?>">
		<?php foreach ( edo_nav_items() as $item ) : ?>
			<a
				href="<?php echo esc_url( edo_view_url( $item['key'] ) ); ?>"
				class="edo-nav__item<?php echo ( $view === $item['key'] ) ? ' is-active' : ''; ?>"
				<?php echo ( $view === $item['key'] ) ? 'aria-current="page"' : ''; ?>
			>
				<span class="edo-nav__icon"><?php echo edo_icon( $item['icon'], 19 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?></span>
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<div class="edo-sidebar__user">
		<a href="<?php echo esc_url( edo_view_url( 'profile' ) ); ?>" class="edo-userchip">
			<span class="edo-avatar"><?php echo esc_html( edo_initials( $edo_name ) ); ?></span>
			<span class="edo-userchip__text">
				<strong><?php echo esc_html( $edo_name ); ?></strong>
				<span><?php echo esc_html( $edo_subtitle ); ?></span>
			</span>
		</a>
		<a href="<?php echo esc_url( wp_logout_url( home_url( '/portal/' ) ) ); ?>" class="edo-logout">
			<?php echo edo_icon( 'logout', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
			<?php esc_html_e( 'Uitloggen', 'setg-edo-portal' ); ?>
		</a>
	</div>

</aside>
