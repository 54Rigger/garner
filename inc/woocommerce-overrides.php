<?php

function garnernewtheme_redirect_logged_out_front_page()
{
	if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) {
		return;
	}

	if (is_user_logged_in() || ! is_front_page() || is_page('coming-soon')) {
		return;
	}

	$coming_soon_page = get_page_by_path('coming-soon');
	if (! ($coming_soon_page instanceof WP_Post)) {
		return;
	}

	if ((int) get_queried_object_id() === (int) $coming_soon_page->ID) {
		return;
	}

	$coming_soon_url = get_permalink($coming_soon_page);
	if ($coming_soon_url) {
		wp_safe_redirect($coming_soon_url, 302);
		exit;
	}
}
add_action('template_redirect', 'garnernewtheme_redirect_logged_out_front_page', 1);

/**
 * Ensure WooCommerce product category archives use the theme template.
 *
 * This avoids block/template-loader fallbacks bypassing taxonomy-product_cat.php.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function garnernewtheme_force_product_cat_template($template)
{
	if (! is_tax('product_cat')) {
		return $template;
	}

	$custom_template = get_theme_file_path('taxonomy-product_cat.php');
	if (file_exists($custom_template)) {
		return $custom_template;
	}

	return $template;
}
add_filter('template_include', 'garnernewtheme_force_product_cat_template', 99);

/**
 * Prefer classic PHP templates for WooCommerce product archives/categories.
 *
 * @param bool   $has_template  Whether WooCommerce sees a block template.
 * @param string $template_name Block template slug.
 * @return bool
 */
function garnernewtheme_disable_woo_block_templates_for_archives($has_template, $template_name)
{
	if (in_array($template_name, array('archive-product', 'taxonomy-product_cat', 'taxonomy-product_tag'), true)) {
		return false;
	}

	return $has_template;
}
add_filter('woocommerce_has_block_template', 'garnernewtheme_disable_woo_block_templates_for_archives', 10, 2);

/**
 * Hard-override rendering for product category archives.
 *
 * This guarantees taxonomy-product_cat.php is used even when block template
 * resolution or third-party template loaders interfere.
 */
function garnernewtheme_render_product_cat_template_directly()
{
	if (is_admin() || wp_doing_ajax() || is_feed() || is_embed()) {
		return;
	}

	if (! is_tax('product_cat')) {
		return;
	}

	$custom_template = get_theme_file_path('taxonomy-product_cat.php');
	if (file_exists($custom_template)) {
		include $custom_template;
		exit;
	}
}
add_action('template_redirect', 'garnernewtheme_render_product_cat_template_directly', 0);

/**
 * Remove the WooCommerce product meta block from the single product summary.
 */
function garnernewtheme_remove_single_product_meta()
{
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
}
add_action('wp', 'garnernewtheme_remove_single_product_meta');

/**
 * Remove the related products carousel from single product pages.
 */
function garnernewtheme_remove_related_products_carousel()
{
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
}
add_action('wp', 'garnernewtheme_remove_related_products_carousel');

/**
 * Keep the main product image but remove gallery thumbnails.
 */
function garnernewtheme_remove_single_product_gallery_thumbnails()
{
	remove_action('woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20);
}
add_action('wp', 'garnernewtheme_remove_single_product_gallery_thumbnails');

/**
 * Render a table of ACF fields for the current product.
 *
 * Skips empty, placeholder, array, object, and image/gallery fields.
 *
 * @return string
 */
