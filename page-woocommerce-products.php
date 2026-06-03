<?php

/**
 * Template Name: WooCommerce Products
 * Template Post Type: page
 *
 * Displays a paginated WooCommerce product archive using the theme's
 * existing featured plans card layout.
 *
 * @package garnernewtheme
 */

get_header();

$img = static function ($file) {
	return esc_url(get_theme_file_uri('/assets/images/' . $file));
};

$get_acf_value = static function ($post_id, $keys) {
	if (! function_exists('get_field')) {
		return '';
	}

	foreach ((array) $keys as $key) {
		$value = get_field($key, $post_id);
		if ('' !== $value && null !== $value) {
			return $value;
		}
	}

	return '';
};

$get_acf_image_url = static function ($post_id, $keys) {
	if (! function_exists('get_field')) {
		return '';
	}

	foreach ((array) $keys as $key) {
		$value = get_field($key, $post_id);

		if (is_array($value)) {
			if (! empty($value['url'])) {
				return esc_url($value['url']);
			}

			if (! empty($value['ID'])) {
				$url = wp_get_attachment_image_url(absint($value['ID']), 'large');
				if ($url) {
					return esc_url($url);
				}
			}

			if (! empty($value[0])) {
				$gallery_item = $value[0];

				if (is_array($gallery_item) && ! empty($gallery_item['url'])) {
					return esc_url($gallery_item['url']);
				}

				if (is_numeric($gallery_item)) {
					$url = wp_get_attachment_image_url(absint($gallery_item), 'large');
					if ($url) {
						return esc_url($url);
					}
				}
			}
		}

		if (is_numeric($value)) {
			$url = wp_get_attachment_image_url(absint($value), 'large');
			if ($url) {
				return esc_url($url);
			}
		}

		if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
			return esc_url($value);
		}
	}

	return '';
};

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

$products_query = new WP_Query(
	array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 12,
		'ignore_sticky_posts' => true,
		'paged'               => $paged,
	)
);
?>

<main id="main-content">
	<section class="featured-plans" data-purpose="woocommerce-products-page">
		<div class="container">
			<header class="section-header">
				<span class="section-kicker"><?php esc_html_e('House Plans', 'garnernewtheme'); ?></span>
				<h1 class="section-title"><?php the_title(); ?></h1>
				<?php if (has_excerpt()) : ?>
					<p class="section-subtitle"><?php echo esc_html(get_the_excerpt()); ?></p>
				<?php endif; ?>
			</header>

			<div class="cards-4">
				<?php if ($products_query->have_posts()) : ?>
					<?php while ($products_query->have_posts()) : ?>
						<?php
						$products_query->the_post();
						$product_id = get_the_ID();
						$product    = function_exists('wc_get_product') ? wc_get_product($product_id) : null;

						$image_url = $get_acf_image_url($product_id, array('feature_image', 'plan_image', 'plan_image_1', 'plan_image_2', 'plan_image_3', 'mf-feature_image', 'mf-plan_image_1', 'mf-plan_image_2', 'mf-plan_image_3'));
						if (! $image_url) {
							$image_id = get_post_thumbnail_id($product_id);
							if ($image_id) {
								$fallback_image_url = wp_get_attachment_image_url($image_id, 'large');
								if ($fallback_image_url) {
									$image_url = esc_url($fallback_image_url);
								}
							}
						}

						if (! $image_url) {
							$image_url = $img('plan-greenwood.svg');
						}

						$sq_ft = $get_acf_value($product_id, array('total-living-area', 'total-area', 'area_heated', 'mf-total-living-area', 'mf-total-area', 'mf-area_heated'));
						$beds  = $get_acf_value($product_id, array('bedrooms', 'MF-unit-bedrooms'));
						$baths = $get_acf_value($product_id, array('bathrooms', 'MF-unit-bathrooms'));

						if (! $beds && is_object($product) && method_exists($product, 'get_attribute')) {
							$beds = $product->get_attribute('pa_bedrooms');
						}
						if (! $baths && is_object($product) && method_exists($product, 'get_attribute')) {
							$baths = $product->get_attribute('pa_bathrooms');
						}

						$specs = array();
						if ($sq_ft) {
							$specs[] = strtoupper(wp_strip_all_tags((string) $sq_ft)) . ' SQ FT';
						}
						if ($beds) {
							$specs[] = strtoupper(wp_strip_all_tags((string) $beds)) . ' BED';
						}
						if ($baths) {
							$specs[] = strtoupper(wp_strip_all_tags((string) $baths)) . ' BATH';
						}
						?>
						<article class="plan-card">
							<?php echo garnernewtheme_render_favorite_button($product_id, 'grid'); ?>
							<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
							<div>
								<h3><?php the_title(); ?></h3>
								<?php if (! empty($specs)) : ?>
									<p><?php echo esc_html(implode(' | ', $specs)); ?></p>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>"><?php esc_html_e('View Plan', 'garnernewtheme'); ?></a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<article class="plan-card">
						<img src="<?php echo $img('plan-greenwood.svg'); ?>" alt="<?php esc_attr_e('Plan placeholder', 'garnernewtheme'); ?>">
						<div>
							<h3><?php esc_html_e('No Products Found', 'garnernewtheme'); ?></h3>
							<p><?php esc_html_e('No WooCommerce products are available yet.', 'garnernewtheme'); ?></p>
						</div>
					</article>
				<?php endif; ?>
			</div>

			<?php if ($products_query->have_posts()) : ?>
				<div class="section-cta-wrap">
					<?php
					echo paginate_links(
						array(
							'total'     => (int) $products_query->max_num_pages,
							'current'   => $paged,
							'prev_text' => __('Previous', 'garnernewtheme'),
							'next_text' => __('Next', 'garnernewtheme'),
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
