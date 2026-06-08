<?php

/**
 * Theme setup and asset loading.
 *
 * @package garnernewtheme
 */

if (! defined('ABSPATH')) {
	exit;
}

function garnernewtheme_setup()
{
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('woocommerce');
	add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
	add_theme_support('custom-logo', array('height' => 96, 'width' => 320, 'flex-height' => true, 'flex-width' => true));

	register_nav_menus(
		array(
			'primary' => __('Primary Menu', 'garnernewtheme'),
			'footer_explore' => __('Footer Explore Menu', 'garnernewtheme'),
			'footer_company' => __('Footer Company Menu', 'garnernewtheme'),
			'footer_support' => __('Footer Support Menu', 'garnernewtheme'),
		)
	);
}
add_action('after_setup_theme', 'garnernewtheme_setup');

function garnernewtheme_widgets_init()
{
	register_sidebar(
		array(
			'name'          => __('Footer Brand Area', 'garnernewtheme'),
			'id'            => 'footer-brand',
			'description'   => __('Widgets in this area appear in the footer brand column.', 'garnernewtheme'),
			'before_widget' => '<section class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action('widgets_init', 'garnernewtheme_widgets_init');

/**
 * Fallback menu for primary navigation when no menu is assigned.
 */
function garnernewtheme_primary_fallback_menu()
{
	$items = array(
		__('Home', 'garnernewtheme')          => home_url('/'),
		__('Stock Plans', 'garnernewtheme')   => '#',
		__('Custom Design', 'garnernewtheme') => '#',
		__('Multi-Family', 'garnernewtheme')  => '#',
		__('About', 'garnernewtheme')         => '#',
		__('Blog', 'garnernewtheme')          => '#',
		__('Contact', 'garnernewtheme')       => '#',
	);

	echo '<ul class="primary-menu">';
	foreach ($items as $label => $url) {
		echo '<li><a href="' . esc_url($url) . '">' . esc_html($label) . '</a></li>';
	}
	echo '</ul>';
}

function garnernewtheme_assets()
{
	$theme_version = wp_get_theme()->get('Version');

	wp_enqueue_style(
		'garnernewtheme-google-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'garnernewtheme-main',
		get_theme_file_uri('/assets/css/main.css'),
		array('garnernewtheme-google-fonts'),
		$theme_version
	);

	wp_enqueue_script(
		'garnernewtheme-theme',
		get_theme_file_uri('/assets/js/theme.js'),
		array(),
		$theme_version,
		true
	);

	wp_localize_script(
		'garnernewtheme-theme',
		'garnernewthemeFavorites',
		array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('garnernewtheme_favorites_nonce'),
		)
	);
}
add_action('wp_enqueue_scripts', 'garnernewtheme_assets');
