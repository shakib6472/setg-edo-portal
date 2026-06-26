<?php
/**
 * Template helpers shared across the portal.
 *
 * @package SETG_EDO_Portal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The list of portal views and their slugs. The first entry is the default.
 *
 * @return string[]
 */
function edo_views() {
	return array( 'dashboard', 'assignments', 'training', 'documents', 'announcements', 'members', 'profile', 'contact' );
}

/**
 * Build a URL to a portal view. Works with both pretty and plain permalinks.
 *
 * @param string $view View slug (default 'dashboard').
 * @return string
 */
function edo_view_url( $view = 'dashboard' ) {
	if ( get_option( 'permalink_structure' ) ) {
		$path = ( 'dashboard' === $view ) ? '/portal/' : '/portal/' . $view . '/';
		return home_url( $path );
	}

	$args = array( 'edo_portal' => 1 );
	if ( 'dashboard' !== $view ) {
		$args['edo_view'] = $view;
	}
	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * The currently requested portal view, validated against the known list.
 *
 * @return string
 */
function edo_current_view() {
	$view = get_query_var( 'edo_view' );
	if ( ! $view || ! in_array( $view, edo_views(), true ) ) {
		return 'dashboard';
	}
	return $view;
}

/**
 * Navigation items for the sidebar / mobile tabs.
 *
 * Labels are Dutch (the portal's UI language) but kept translatable.
 *
 * @return array<int,array{key:string,label:string,icon:string}>
 */
function edo_nav_items() {
	return array(
		array(
			'key'   => 'dashboard',
			'label' => __( 'Dashboard', 'setg' ),
			'icon'  => 'dashboard',
		),
		array(
			'key'   => 'assignments',
			'label' => __( 'Opdrachten', 'setg' ),
			'icon'  => 'assignments',
		),
		array(
			'key'   => 'training',
			'label' => __( 'Trainingen', 'setg' ),
			'icon'  => 'training',
		),
		array(
			'key'   => 'documents',
			'label' => __( 'Documenten', 'setg' ),
			'icon'  => 'documents',
		),
		array(
			'key'   => 'announcements',
			'label' => __( 'Mededelingen', 'setg' ),
			'icon'  => 'announcements',
		),
		array(
			'key'   => 'members',
			'label' => __( 'Leden', 'setg' ),
			'icon'  => 'members',
		),
		array(
			'key'   => 'contact',
			'label' => __( 'Contact', 'setg' ),
			'icon'  => 'contact',
		),
	);
}

/**
 * The human title for a view, used in the mobile top bar.
 *
 * @param string $view View slug.
 * @return string
 */
function edo_view_title( $view ) {
	$titles = array(
		'dashboard'     => __( 'Dashboard', 'setg' ),
		'assignments'   => __( 'Opdrachten', 'setg' ),
		'training'      => __( 'Trainingen', 'setg' ),
		'documents'     => __( 'Documenten', 'setg' ),
		'announcements' => __( 'Mededelingen', 'setg' ),
		'members'       => __( 'Leden', 'setg' ),
		'profile'       => __( 'Mijn profiel', 'setg' ),
		'contact'       => __( 'Contact', 'setg' ),
	);
	return isset( $titles[ $view ] ) ? $titles[ $view ] : '';
}

/**
 * Return initials (max two letters) for an avatar fallback.
 *
 * @param string $name Full name.
 * @return string
 */
function edo_initials( $name ) {
	$parts = preg_split( '/\s+/', trim( wp_strip_all_tags( $name ) ) );
	if ( empty( $parts[0] ) ) {
		return '?';
	}
	$first = mb_substr( $parts[0], 0, 1 );
	$last  = ( count( $parts ) > 1 ) ? mb_substr( end( $parts ), 0, 1 ) : '';
	return mb_strtoupper( $first . $last );
}

/**
 * Locate and render a portal template, exposing $args as local variables.
 *
 * @param string $relative Template path relative to /templates (no .php).
 * @param array  $args     Variables to extract into the template scope.
 */
function edo_get_template( $relative, $args = array() ) {
	$file = EDO_PORTAL_DIR . 'templates/' . ltrim( $relative, '/' ) . '.php';
	if ( ! file_exists( $file ) ) {
		return;
	}
	if ( ! empty( $args ) ) {
		extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract -- controlled template args.
	}
	include $file;
}

/**
 * Output an inline SVG icon from the portal icon set.
 *
 * Icons are 24x24 stroke icons matching the approved design.
 *
 * @param string $name Icon key.
 * @param int    $size Pixel size (width = height).
 * @return string SVG markup.
 */
function edo_icon( $name, $size = 19 ) {
	$paths = array(
		'dashboard'     => '<rect x="3" y="3" width="7.5" height="7.5" rx="1.6"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.6"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.6"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.6"/>',
		'assignments'   => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 10h6M9 14h6M9 18h4"/>',
		'training'      => '<path d="M3 9.5 12 5l9 4.5-9 4.5-9-4.5Z"/><path d="M7 11.5V16c0 1.1 2.2 2.5 5 2.5s5-1.4 5-2.5v-4.5M21 9.5V15"/>',
		'documents'     => '<path d="M4 7a2 2 0 0 1 2-2h3l2 2h7a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/>',
		'announcements' => '<path d="M4 10v4a1 1 0 0 0 1 1h2l6 4V5L7 9H5a1 1 0 0 0-1 1Z"/><path d="M17 9a4 4 0 0 1 0 6"/>',
		'members'       => '<circle cx="9" cy="8" r="3"/><path d="M3.5 19a5.5 5.5 0 0 1 11 0"/><path d="M16 6a3 3 0 0 1 0 6M17 14.5a5.5 5.5 0 0 1 3.5 4.5"/>',
		'contact'       => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
		'logout'        => '<path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3M10 17l-5-5 5-5M5 12h11"/>',
		'bell'          => '<path d="M18 9a6 6 0 0 0-12 0c0 6-2 7-2 7h16s-2-1-2-7"/><path d="M10.5 20a2 2 0 0 0 3 0"/>',
		'search'        => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.2-3.2"/>',
		'chevron'       => '<path d="m9 6 6 6-6 6"/>',
		'calendar'      => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/>',
		'location'      => '<path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2.4"/>',
		'clock'         => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'building'      => '<rect x="4" y="3" width="11" height="18" rx="1.5"/><path d="M15 8h4a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1h-4M8 7h3M8 11h3M8 15h3"/>',
		'download'      => '<path d="M12 4v11M7 11l5 5 5-5M5 20h14"/>',
		'profile'       => '<circle cx="12" cy="8" r="3.4"/><path d="M5 20a7 7 0 0 1 14 0"/>',
		'lock'          => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>',
		'mail'          => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
		'phone'         => '<path d="M5 4h3l2 5-2.5 1.5a11 11 0 0 0 5 5L19 18l-1 3a14 14 0 0 1-13-13Z"/>',
		'stat-doc'      => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 9h6M9 13h6M9 17h4"/>',
	);

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">%2$s</svg>',
		(int) $size,
		$paths[ $name ]
	);
}
