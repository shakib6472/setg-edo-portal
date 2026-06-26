<?php
/**
 * Portal app shell: full HTML document, sidebar, top bar and the active view.
 *
 * @package SETG_EDO_Portal
 * @var string $view  Current view slug.
 * @var string $title Current view title.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edo_view_file = EDO_PORTAL_DIR . 'templates/views/' . $view . '.php';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="robots" content="noindex, nofollow">
	<?php wp_head(); ?>
</head>
<body class="edo-body edo-view-<?php echo esc_attr( $view ); ?>">

	<div class="edo-portal">

		<?php edo_get_template( 'partials/sidebar', array( 'view' => $view ) ); ?>

		<div class="edo-main">

			<?php
			edo_get_template(
				'partials/topbar',
				array(
					'view'  => $view,
					'title' => $title,
				)
			);
			?>

			<div class="edo-scroll scrollarea">
				<div class="edo-content edo-content--<?php echo esc_attr( $view ); ?>">
					<?php
					if ( file_exists( $edo_view_file ) ) {
						edo_get_template( 'views/' . $view );
					} else {
						edo_get_template( 'views/placeholder', array( 'title' => $title ) );
					}
					?>
				</div>
			</div>

			<?php edo_get_template( 'partials/mobile-tabs', array( 'view' => $view ) ); ?>

		</div>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
<?php
