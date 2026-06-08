<?php

function garnernewtheme_get_favorites_cookie_name()
{
	return 'garnernewtheme_favorites';
}

/**
 * Get the user meta key for logged-in favorites storage.
 *
 * @return string
 */
function garnernewtheme_get_favorites_user_meta_key()
{
	return '_garnernewtheme_favorites';
}

/**
 * Normalize, deduplicate, and validate favorite product IDs.
 *
 * @param array<int|string> $favorite_ids Raw favorite IDs.
 * @return array<int>
 */
function garnernewtheme_normalize_favorite_product_ids($favorite_ids)
{
	$favorite_ids = array_slice(array_unique(array_filter(array_map('absint', (array) $favorite_ids))), 0, 200);

	if (empty($favorite_ids)) {
		return array();
	}

	$product_ids = array();
	foreach ($favorite_ids as $favorite_id) {
		if ('product' === get_post_type($favorite_id)) {
			$product_ids[] = $favorite_id;
		}
	}

	return $product_ids;
}

/**
 * Read and sanitize favorite product IDs from cookie storage.
 *
 * @return array<int>
 */
function garnernewtheme_get_favorite_product_ids_from_cookie()
{
	$cookie_name = garnernewtheme_get_favorites_cookie_name();
	if (empty($_COOKIE[$cookie_name])) {
		return array();
	}

	$raw_cookie_value = wp_unslash((string) $_COOKIE[$cookie_name]);
	$decoded_ids      = json_decode($raw_cookie_value, true);

	if (! is_array($decoded_ids)) {
		return array();
	}

	return garnernewtheme_normalize_favorite_product_ids($decoded_ids);
}

/**
 * Clear guest favorites cookie value.
 *
 * @return void
 */
function garnernewtheme_clear_favorite_product_ids_cookie()
{
	$cookie_name = garnernewtheme_get_favorites_cookie_name();
	$cookie_path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';

	setcookie(
		$cookie_name,
		'',
		array(
			'expires'  => time() - HOUR_IN_SECONDS,
			'path'     => $cookie_path,
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	unset($_COOKIE[$cookie_name]);
}

/**
 * Read and sanitize favorite product IDs from the active storage source.
 *
 * Logged-out visitors use session cookies. Logged-in users use user meta.
 *
 * @return array<int>
 */
function garnernewtheme_get_favorite_product_ids()
{
	if (! is_user_logged_in()) {
		return garnernewtheme_get_favorite_product_ids_from_cookie();
	}

	$user_id = get_current_user_id();
	if (! $user_id) {
		return array();
	}

	$meta_key      = garnernewtheme_get_favorites_user_meta_key();
	$meta_favorites = get_user_meta($user_id, $meta_key, true);
	$meta_favorites = is_array($meta_favorites) ? $meta_favorites : array();
	$meta_favorites = garnernewtheme_normalize_favorite_product_ids($meta_favorites);

	$cookie_favorites = garnernewtheme_get_favorite_product_ids_from_cookie();
	if (! empty($cookie_favorites)) {
		$merged_favorites = garnernewtheme_normalize_favorite_product_ids(array_merge($meta_favorites, $cookie_favorites));

		if ($merged_favorites !== $meta_favorites) {
			update_user_meta($user_id, $meta_key, $merged_favorites);
			$meta_favorites = $merged_favorites;
		}

		garnernewtheme_clear_favorite_product_ids_cookie();
	}

	return $meta_favorites;
}

/**
 * Persist favorite product IDs to active storage.
 *
 * @param array<int> $favorite_ids Product IDs.
 * @return void
 */
function garnernewtheme_set_favorite_product_ids($favorite_ids)
{
	$favorite_ids = garnernewtheme_normalize_favorite_product_ids($favorite_ids);

	if (is_user_logged_in()) {
		$user_id = get_current_user_id();
		if ($user_id) {
			update_user_meta($user_id, garnernewtheme_get_favorites_user_meta_key(), $favorite_ids);
		}

		garnernewtheme_clear_favorite_product_ids_cookie();
		return;
	}

	$cookie_name = garnernewtheme_get_favorites_cookie_name();
	$cookie_path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
	$cookie_data = wp_json_encode(array_values($favorite_ids));

	if (! is_string($cookie_data)) {
		return;
	}

	setcookie(
		$cookie_name,
		$cookie_data,
		array(
			'expires'  => 0,
			'path'     => $cookie_path,
			'domain'   => COOKIE_DOMAIN,
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		)
	);

	$_COOKIE[$cookie_name] = $cookie_data;
}

/**
 * Determine whether a product is currently favorited.
 *
 * @param int $product_id Product ID.
 * @return bool
 */
function garnernewtheme_is_product_favorited($product_id)
{
	$product_id = absint($product_id);
	if (! $product_id) {
		return false;
	}

	return in_array($product_id, garnernewtheme_get_favorite_product_ids(), true);
}

/**
 * Get current favorite product count.
 *
 * @return int
 */
function garnernewtheme_get_favorite_products_count()
{
	return count(garnernewtheme_get_favorite_product_ids());
}

/**
 * Resolve the URL for the full favorites page.
 *
 * @return string
 */
function garnernewtheme_get_favorites_page_url()
{
	$favorites_page = get_page_by_path('favorites');

	if (! ($favorites_page instanceof WP_Post)) {
		$favorites_page = get_page_by_path('saved-plans');
	}

	if ($favorites_page instanceof WP_Post) {
		$url = get_permalink($favorites_page);
		if ($url) {
			return $url;
		}
	}

	return home_url('/favorites/');
}

/**
 * Render the header favorites grid/list markup.
 *
 * @return string
 */
function garnernewtheme_render_header_favorites_grid()
{
	$favorites = array_reverse(garnernewtheme_get_favorite_product_ids());

	if (empty($favorites)) {
		return '<p class="site-header__favorites-empty">' . esc_html__('No saved plans yet.', 'garnernewtheme') . '</p>';
	}

	$favorites_query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 24,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'post__in'            => $favorites,
			'orderby'             => 'post__in',
		)
	);

	if (! $favorites_query->have_posts()) {
		wp_reset_postdata();
		return '<p class="site-header__favorites-empty">' . esc_html__('No saved plans yet.', 'garnernewtheme') . '</p>';
	}

	ob_start();
?>
	<ul class="site-header__favorites-grid">
		<?php while ($favorites_query->have_posts()) : ?>
			<?php
			$favorites_query->the_post();
			$product_id = get_the_ID();
			$image_url  = get_the_post_thumbnail_url($product_id, 'woocommerce_thumbnail');

			if (! $image_url) {
				$image_url = get_theme_file_uri('/assets/images/plan-greenwood.svg');
			}
			?>
			<li class="site-header__favorites-item">
				<a href="<?php the_permalink(); ?>" class="site-header__favorites-link">
					<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
					<span><?php the_title(); ?></span>
				</a>
			</li>
		<?php endwhile; ?>
	</ul>
<?php
	wp_reset_postdata();

	return ob_get_clean();
}

/**
 * Render a favorite toggle heart button.
 *
 * @param int    $product_id Product ID.
 * @param string $context    Optional display context modifier.
 * @return string
 */
function garnernewtheme_render_favorite_button($product_id, $context = 'grid')
{
	$product_id = absint($product_id);
	if (! $product_id || 'product' !== get_post_type($product_id)) {
		return '';
	}

	$is_favorited    = garnernewtheme_is_product_favorited($product_id);
	$button_classes  = array('favorite-toggle');
	$button_classes[] = 'favorite-toggle--' . sanitize_html_class((string) $context);

	if ($is_favorited) {
		$button_classes[] = 'is-active';
	}

	$button_label = $is_favorited ? __('Remove from favorites', 'garnernewtheme') : __('Add to favorites', 'garnernewtheme');

	ob_start();
?>
	<button
		type="button"
		class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
		data-favorite-toggle
		data-product-id="<?php echo esc_attr((string) $product_id); ?>"
		data-add-label="<?php esc_attr_e('Add to favorites', 'garnernewtheme'); ?>"
		data-remove-label="<?php esc_attr_e('Remove from favorites', 'garnernewtheme'); ?>"
		aria-pressed="<?php echo $is_favorited ? 'true' : 'false'; ?>"
		aria-label="<?php echo esc_attr($button_label); ?>">
		<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
			<path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
		</svg>
		<span class="screen-reader-text"><?php echo esc_html($button_label); ?></span>
	</button>
<?php

	return ob_get_clean();
}

/**
 * AJAX callback for toggling product favorites.
 *
 * @return void
 */
function garnernewtheme_toggle_favorite_product()
{
	check_ajax_referer('garnernewtheme_favorites_nonce', 'nonce');

	$product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
	if (! $product_id || 'product' !== get_post_type($product_id)) {
		wp_send_json_error(array('message' => __('Invalid product.', 'garnernewtheme')), 400);
	}

	$favorites    = garnernewtheme_get_favorite_product_ids();
	$existing_key = array_search($product_id, $favorites, true);

	if (false !== $existing_key) {
		unset($favorites[$existing_key]);
		$is_favorited = false;
	} else {
		$favorites[]  = $product_id;
		$is_favorited = true;
	}

	$favorites = array_values(array_unique(array_filter(array_map('absint', $favorites))));
	garnernewtheme_set_favorite_product_ids($favorites);

	wp_send_json_success(
		array(
			'product_id'   => $product_id,
			'is_favorited' => $is_favorited,
			'favorites'    => $favorites,
			'count'        => count($favorites),
		)
	);
}
add_action('wp_ajax_garnernewtheme_toggle_favorite_product', 'garnernewtheme_toggle_favorite_product');
add_action('wp_ajax_nopriv_garnernewtheme_toggle_favorite_product', 'garnernewtheme_toggle_favorite_product');

/**
 * AJAX callback to return header favorites grid HTML.
 *
 * @return void
 */
function garnernewtheme_get_favorite_products_grid()
{
	check_ajax_referer('garnernewtheme_favorites_nonce', 'nonce');

	wp_send_json_success(
		array(
			'html'  => garnernewtheme_render_header_favorites_grid(),
			'count' => garnernewtheme_get_favorite_products_count(),
		)
	);
}
add_action('wp_ajax_garnernewtheme_get_favorite_products_grid', 'garnernewtheme_get_favorite_products_grid');
add_action('wp_ajax_nopriv_garnernewtheme_get_favorite_products_grid', 'garnernewtheme_get_favorite_products_grid');

/**
 * Show a favorites heart on single product pages.
 *
 * @return void
 */
function garnernewtheme_render_single_product_favorite_button()
{
	if (! is_singular('product')) {
		return;
	}

	echo '<div class="single-product-favorite">' . garnernewtheme_render_favorite_button(get_the_ID(), 'single') . '</div>';
}
add_action('woocommerce_single_product_summary', 'garnernewtheme_render_single_product_favorite_button', 6);
