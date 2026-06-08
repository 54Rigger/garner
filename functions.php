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

//Disable the feature where woocommerce asks to verify email address for guest checkout users. This is because we want to allow users to checkout as guests without creating an account or verifying their email address.
add_filter('woocommerce_checkout_registration_generate_password', '__return_false');
add_filter('woocommerce_checkout_registration_email_for_guest', '__return_false');
