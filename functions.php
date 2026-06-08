<?php

/**
 * Theme bootstrap loader.
 *
 * @package garnernewtheme
 */

if (! defined('ABSPATH')) {
	exit;
}

$garnernewtheme_includes = array(
	'inc/setup.php',
	'inc/customizer.php',
	'inc/woocommerce-overrides.php',
	'inc/product-tabs.php',
	'inc/favorites.php',
);

foreach ($garnernewtheme_includes as $garnernewtheme_include) {
	$garnernewtheme_file = get_theme_file_path($garnernewtheme_include);

	if (file_exists($garnernewtheme_file)) {
		require_once $garnernewtheme_file;
	}
}
add_filter('woocommerce_order_email_verification_required', '__return_false');
