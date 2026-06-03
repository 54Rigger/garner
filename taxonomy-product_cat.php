<?php

/**
 * Product category archive template.
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

$current_term = get_queried_object();

$breadcrumb_items = array(
	array(
		'label' => __('Home', 'garnernewtheme'),
		'url'   => home_url('/'),
	),
	array(
		'label' => __('House Plans', 'garnernewtheme'),
		'url'   => get_post_type_archive_link('product'),
	),
);

$child_category_terms = array();
$selector_mode        = 'children';

if ($current_term instanceof WP_Term) {
	$ancestor_ids = array_reverse(get_ancestors($current_term->term_id, $current_term->taxonomy));
	foreach ($ancestor_ids as $ancestor_id) {
		$ancestor_term = get_term($ancestor_id, $current_term->taxonomy);
		if ($ancestor_term instanceof WP_Term) {
			$ancestor_link = get_term_link($ancestor_term);
			$breadcrumb_items[] = array(
				'label' => $ancestor_term->name,
				'url'   => is_wp_error($ancestor_link) ? '' : $ancestor_link,
			);
		}
	}

	$breadcrumb_items[] = array(
		'label' => $current_term->name,
		'url'   => '',
	);

	$child_category_terms = get_terms(
		array(
			'taxonomy'   => $current_term->taxonomy,
			'parent'     => $current_term->term_id,
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if (! is_wp_error($child_category_terms) && empty($child_category_terms) && $current_term->parent > 0) {
		$selector_mode       = 'siblings';
		$child_category_terms = get_terms(
			array(
				'taxonomy'   => $current_term->taxonomy,
				'parent'     => $current_term->parent,
				'hide_empty' => false,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'exclude'    => array($current_term->term_id),
			)
		);
	}
}
?>

<main id="main-content">
	<section class="featured-plans" data-purpose="product-category-archive">
		<div class="container">
			<nav class="archive-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'garnernewtheme'); ?>">
				<ol>
					<?php foreach ($breadcrumb_items as $breadcrumb_index => $breadcrumb_item) : ?>
						<li>
							<?php if (! empty($breadcrumb_item['url']) && $breadcrumb_index < count($breadcrumb_items) - 1) : ?>
								<a href="<?php echo esc_url($breadcrumb_item['url']); ?>"><?php echo esc_html($breadcrumb_item['label']); ?></a>
							<?php else : ?>
								<span><?php echo esc_html($breadcrumb_item['label']); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</nav>

			<?php if (! is_wp_error($child_category_terms) && ! empty($child_category_terms)) : ?>
				<div class="archive-child-category-jump">
					<label for="archive-child-category-select">
						<?php echo esc_html('siblings' === $selector_mode ? __('Jump to sibling plan category', 'garnernewtheme') : __('Jump to child plan category', 'garnernewtheme')); ?>
					</label>
					<select id="archive-child-category-select" onchange="if (this.value) { window.location.href = this.value; }">
						<option value=""><?php esc_html_e('Select a category', 'garnernewtheme'); ?></option>
						<?php foreach ($child_category_terms as $child_category_term) : ?>
							<?php $child_term_link = get_term_link($child_category_term); ?>
							<?php if (! is_wp_error($child_term_link)) : ?>
								<option value="<?php echo esc_url($child_term_link); ?>"><?php echo esc_html($child_category_term->name); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<header class="section-header">
				<span class="section-kicker"><?php esc_html_e('House Plans', 'garnernewtheme'); ?></span>
				<h1 class="section-title"><?php single_term_title(); ?></h1>
				<?php if (term_description()) : ?>
					<p class="section-subtitle"><?php echo esc_html(wp_strip_all_tags(term_description())); ?></p>
				<?php endif; ?>
			</header>

			<div class="cards-4">
				<?php if (have_posts()) : ?>
					<?php while (have_posts()) : ?>
						<?php
						the_post();
						$product_id = get_the_ID();
						$product    = function_exists('wc_get_product') ? wc_get_product($product_id) : null;

						$image_id  = get_post_thumbnail_id($product_id);
						$image_url = '';
						if ($image_id) {
							$featured_image_url = wp_get_attachment_image_url($image_id, 'large');
							if ($featured_image_url) {
								$image_url = esc_url($featured_image_url);
							}
						}

						if (! $image_url) {
							$image_url = $get_acf_image_url($product_id, array('feature_image', 'plan_image', 'plan_image_1', 'plan_image_2', 'plan_image_3', 'mf-feature_image', 'mf-plan_image_1', 'mf-plan_image_2', 'mf-plan_image_3'));
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
				<?php else : ?>
					<article class="plan-card">
						<img src="<?php echo $img('plan-greenwood.svg'); ?>" alt="<?php esc_attr_e('Plan placeholder', 'garnernewtheme'); ?>">
						<div>
							<h3><?php esc_html_e('No Products Found', 'garnernewtheme'); ?></h3>
							<p><?php esc_html_e('No products are available in this category yet.', 'garnernewtheme'); ?></p>
						</div>
					</article>
				<?php endif; ?>
			</div>

			<?php if (have_posts()) : ?>
				<div class="section-cta-wrap">
					<?php
					the_posts_pagination(
						array(
							'mid_size'           => 1,
							'prev_text'          => __('Previous', 'garnernewtheme'),
							'next_text'          => __('Next', 'garnernewtheme'),
							'screen_reader_text' => __('Products navigation', 'garnernewtheme'),
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
