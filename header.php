<?php
/**
 * Site header.
 *
 * @package garnernewtheme
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'garnernewtheme' ); ?></a>

<header class="site-header" data-purpose="site-header">
	<nav class="site-header__inner container">
		<div class="brand-mark">
			<a class="brand-mark__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
			<span class="brand-mark__subtitle"><?php esc_html_e( 'Residential Design', 'garnernewtheme' ); ?></span>
		</div>

		<div class="site-header__actions">
			<button class="site-header__menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu-panel">
				<span><?php esc_html_e( 'Menu', 'garnernewtheme' ); ?></span>
			</button>

			<button class="site-header__favorite" type="button" aria-label="<?php esc_attr_e( 'Saved plans', 'garnernewtheme' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
					<path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
			</button>
		</div>

		<div id="primary-menu-panel" class="site-header__menu-panel">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'primary-menu',
					'fallback_cb'    => 'garnernewtheme_primary_fallback_menu',
				)
			);
			?>
		</div>

	</nav>
</header>
