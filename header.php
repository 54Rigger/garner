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
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<a class="screen-reader-text skip-link" href="#main-content"><?php esc_html_e('Skip to content', 'garnernewtheme'); ?></a>

	<header class="site-header" data-purpose="site-header">
		<nav class="site-header__inner container">
			<div class="brand-mark">
				<a class="brand-mark__title" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
				<span class="brand-mark__subtitle"><?php esc_html_e('Residential Design', 'garnernewtheme'); ?></span>
			</div>

			<div class="site-header__actions">
				<?php $favorite_count = garnernewtheme_get_favorite_products_count(); ?>
				<button class="site-header__menu-toggle" type="button" aria-expanded="false" aria-controls="primary-menu-panel">
					<span><?php esc_html_e('Menu', 'garnernewtheme'); ?></span>
				</button>

				<button
					class="site-header__favorite"
					type="button"
					aria-label="<?php esc_attr_e('Saved plans', 'garnernewtheme'); ?>"
					aria-expanded="false"
					aria-controls="header-favorites-panel"
					data-header-favorites-toggle>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
						<path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<span class="site-header__favorite-count<?php echo $favorite_count > 0 ? ' is-visible' : ''; ?>" data-header-favorites-count<?php echo $favorite_count > 0 ? '' : ' hidden'; ?>>
						<?php echo $favorite_count > 0 ? esc_html((string) $favorite_count) : ''; ?>
					</span>
				</button>

				<div id="header-favorites-panel" class="site-header__favorites-panel" data-header-favorites-panel hidden>
					<p class="site-header__favorites-title"><?php esc_html_e('Saved Plans', 'garnernewtheme'); ?></p>
					<div class="site-header__favorites-content" data-header-favorites-content>
						<?php echo garnernewtheme_render_header_favorites_grid(); ?>
					</div>
					<div class="site-header__favorites-footer">
						<a class="site-header__favorites-viewall" href="<?php echo esc_url(garnernewtheme_get_favorites_page_url()); ?>"><?php esc_html_e('View all favorites', 'garnernewtheme'); ?></a>
					</div>
				</div>
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