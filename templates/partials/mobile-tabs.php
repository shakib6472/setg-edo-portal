<?php
/**
 * Mobile bottom tab bar (shown only on small screens via CSS).
 *
 * @package SETG_EDO_Portal
 * @var string $view Current view slug.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_tabs = array(
	array(
		'key'   => 'dashboard',
		'icon'  => 'dashboard',
		'label' => __( 'Start', 'setg' ),
	),
	array(
		'key'   => 'assignments',
		'icon'  => 'assignments',
		'label' => __( 'Opdrachten', 'setg' ),
	),
	array(
		'key'   => 'training',
		'icon'  => 'training',
		'label' => __( 'Training', 'setg' ),
	),
	array(
		'key'   => 'documents',
		'icon'  => 'documents',
		'label' => __( 'Documenten', 'setg' ),
	),
	array(
		'key'   => 'profile',
		'icon'  => 'profile',
		'label' => __( 'Profiel', 'setg' ),
	),
);
?>
<nav class="edo-tabbar" aria-label="<?php esc_attr_e( 'Navigatie', 'setg' ); ?>">
	<?php foreach ( $edo_tabs as $tab ) : ?>
		<a
			href="<?php echo esc_url( edo_view_url( $tab['key'] ) ); ?>"
			class="edo-tabbar__item<?php echo ( $view === $tab['key'] ) ? ' is-active' : ''; ?>"
			<?php echo ( $view === $tab['key'] ) ? 'aria-current="page"' : ''; ?>
		>
			<?php echo edo_icon( $tab['icon'], 21 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG. ?>
			<span><?php echo esc_html( $tab['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
