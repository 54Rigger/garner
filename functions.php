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

//add some custom text under the price on the product page. This is to inform customers that the price includes shipping and handling fees, which can help reduce cart abandonment and increase conversions.
add_filter('woocommerce_get_price_html', 'woo_add_text_under_price', 10, 2);

function woo_add_text_under_price($price,)
{
	// Add your custom text or HTML below the price
	$custom_text = '<div class="custom-price-text">
			<h3>PDF – Single Build</h3>
			<p>Complete printable PDF construction drawings with permission to build the design one time. Ideal for homeowners, builders, or developers constructing a single project.</p>
			</div>';

	return $custom_text . $price;
}

add_action('woocommerce_single_product_summary', 'customize_product_summary', 5);
function customize_product_summary()
{
	//remove the product excerpt from the product summary
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
	add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 7);
}
//remove the quantity field from the product page. This is to prevent customers from adding multiple quantities of the same product to their cart, which can help reduce cart abandonment and increase conversions.
add_filter('woocommerce_is_sold_individually', 'custom_remove_all_quantity_fields', 10, 2);
function custom_remove_all_quantity_fields($return, $product)
{
	return true;
}

add_action('woocommerce_product_addons_end', 'display_custom_text_after_addons', 10);
function display_custom_text_after_addons()
{
	echo '<div class="custom-addons-text">';
	echo '<h3>Not Sure Which Option Is Right for You? </h3>';
	echo '<p>Need help choosing the best file type or license for your project? View our detailed Format & Licensing Guide <a href="[linked to pricing page]">here</a> or <a href="mailto:[email address]">contact us</a> for assistance.</p>';
	echo '</div>';
}
