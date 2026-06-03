<?php

/**
 * Front page template.
 *
 * @package garnernewtheme
 */

get_header();

$img = static function ($file) {
	return esc_url(get_theme_file_uri('/assets/images/' . $file));
};

$hero_image_ids = array_filter(
	array_map(
		'absint',
		explode(',', (string) get_theme_mod('garnernewtheme_hero_carousel_images', ''))
	)
);

$hero_image_urls = array();
foreach ($hero_image_ids as $hero_image_id) {
	$hero_image_url = wp_get_attachment_image_url($hero_image_id, 'full');
	if ($hero_image_url) {
		$hero_image_urls[] = esc_url($hero_image_url);
	}
}

if (empty($hero_image_urls)) {
	$hero_image_urls[] = $img('hero-home.svg');
}

$narrative_heading      = (string) get_theme_mod('garnernewtheme_narrative_heading', 'Rooted in Experience. Focused on You.');
$narrative_paragraph    = (string) get_theme_mod('garnernewtheme_narrative_paragraph', 'For over three decades, Garner Drake Residential Design has created homes that reflect the beauty of Southern architecture and the way families truly live.');
$narrative_button_text  = (string) get_theme_mod('garnernewtheme_narrative_button_text', 'Our Story');
$narrative_button_url   = (string) get_theme_mod('garnernewtheme_narrative_button_url', '#');
$narrative_image_id     = absint(get_theme_mod('garnernewtheme_narrative_image_id', 0));
$narrative_image_url    = $img('narrative-designer.svg');
$narrative_image_alt    = __('Architect sketching at desk', 'garnernewtheme');

if ($narrative_image_id) {
	$custom_narrative_image_url = wp_get_attachment_image_url($narrative_image_id, 'large');
	if ($custom_narrative_image_url) {
		$narrative_image_url = esc_url($custom_narrative_image_url);
	}

	$custom_narrative_image_alt = get_post_meta($narrative_image_id, '_wp_attachment_image_alt', true);
	if (is_string($custom_narrative_image_alt) && '' !== trim($custom_narrative_image_alt)) {
		$narrative_image_alt = $custom_narrative_image_alt;
	}
}
?>

<main id="main-content">
	<section class="hero" data-purpose="hero">
		<div class="hero__carousel" data-carousel="hero" data-interval="5000">
			<?php foreach ($hero_image_urls as $index => $hero_image_url) : ?>
				<img
					class="hero__bg<?php echo 0 === $index ? ' is-active' : ''; ?>"
					src="<?php echo esc_url($hero_image_url); ?>"
					alt="<?php esc_attr_e('Hero carousel image', 'garnernewtheme'); ?>">
			<?php endforeach; ?>

			<?php if (count($hero_image_urls) > 1) : ?>
				<button class="hero__control hero__control--prev" type="button" aria-label="<?php esc_attr_e('Previous slide', 'garnernewtheme'); ?>" data-carousel-prev>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
						<path d="M15.5 4.5L8 12l7.5 7.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
				<button class="hero__control hero__control--next" type="button" aria-label="<?php esc_attr_e('Next slide', 'garnernewtheme'); ?>" data-carousel-next>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
						<path d="M8.5 4.5L16 12l-7.5 7.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
				<div class="hero__dots" aria-label="<?php esc_attr_e('Hero carousel pagination', 'garnernewtheme'); ?>">
					<?php foreach ($hero_image_urls as $index => $_hero_image_url) : ?>
						<button
							type="button"
							class="hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
							data-carousel-dot="<?php echo esc_attr((string) $index); ?>"
							aria-label="<?php echo esc_attr(sprintf(__('Go to slide %d', 'garnernewtheme'), $index + 1)); ?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="hero__overlay"></div>
		<div class="container hero__content">
			<h1>Timeless Home Designs<br>for the Way You Live</h1>
			<p>Custom Homes • Stock Plans<br>Multi-Family Design</p>
			<div class="hero__actions">
				<a href="/shop" class="btn btn--ghost"><?php esc_html_e('Browse House Plans', 'garnernewtheme'); ?></a>
				<a href="#contact" class="btn btn--solid"><?php esc_html_e('Start a Custom Design', 'garnernewtheme'); ?></a>
			</div>
		</div>
	</section>

	<section class="plan-search" data-purpose="plan-search">
		<div class="container plan-search__row">
			<div class="plan-search__input-wrap">
				<input type="text" placeholder="Search by plan name, square footage, bedrooms, style..." aria-label="<?php esc_attr_e('Search house plans', 'garnernewtheme'); ?>">
			</div>
			<button type="button" class="plan-search__saved"><?php esc_html_e('Saved Plans', 'garnernewtheme'); ?></button>
		</div>
	</section>

	<?php
	$collection_category_slug = (string) get_theme_mod('garnernewtheme_featured_collection_category', '');
	$collection_category      = $collection_category_slug ? get_term_by('slug', $collection_category_slug, 'product_cat') : null;

	$collection_tax_query = array();

	if ($collection_category instanceof WP_Term) {
		$collection_tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => array($collection_category->slug),
		);
	}

	$collection_query = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'orderby'             => 'rand',
			'tax_query'           => $collection_tax_query,
		)
	);

	$collection_archive_url = $collection_category instanceof WP_Term ? get_term_link($collection_category) : get_post_type_archive_link('product');
	if (is_wp_error($collection_archive_url) || ! $collection_archive_url) {
		$collection_archive_url = home_url('/');
	}
	?>
	<section class="featured-collection" data-purpose="featured-collection">
		<div class="container">
			<span class="section-kicker"><?php esc_html_e('Featured Collection', 'garnernewtheme'); ?></span>
			<h2 class="section-title">
				<?php
				echo esc_html(
					$collection_category instanceof WP_Term
						? sprintf(__('The %s Collection', 'garnernewtheme'), $collection_category->name)
						: __('Featured Product Collection', 'garnernewtheme')
				);
				?>
			</h2>
			<p class="section-subtitle">
				<?php
				echo esc_html(
					$collection_category instanceof WP_Term && ! empty($collection_category->description)
						? wp_strip_all_tags($collection_category->description)
						: __('A curated selection of plans from our collection.', 'garnernewtheme')
				);
				?>
			</p>

			<div class="collection-grid">
				<?php if ($collection_query->have_posts()) : ?>
					<?php while ($collection_query->have_posts()) : ?>
						<?php
						$collection_query->the_post();
						$collection_image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
						if (! $collection_image_url) {
							$collection_image_url = $img('plan-greenwood.svg');
						}
						?>
						<a class="collection-grid__item" href="<?php the_permalink(); ?>">
							<img src="<?php echo esc_url($collection_image_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
							<span class="collection-grid__title"><?php the_title(); ?></span>
						</a>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<div class="collection-grid__item">
						<img src="<?php echo $img('collection-1.svg'); ?>" alt="<?php esc_attr_e('Featured collection placeholder one', 'garnernewtheme'); ?>">
					</div>
					<div class="collection-grid__item">
						<img src="<?php echo $img('collection-2.svg'); ?>" alt="<?php esc_attr_e('Featured collection placeholder two', 'garnernewtheme'); ?>">
					</div>
					<div class="collection-grid__item">
						<img src="<?php echo $img('collection-3.svg'); ?>" alt="<?php esc_attr_e('Featured collection placeholder three', 'garnernewtheme'); ?>">
					</div>
				<?php endif; ?>
			</div>

			<div class="section-cta-wrap">
				<a class="btn btn--outline" href="<?php echo esc_url($collection_archive_url); ?>">
					<?php
					echo esc_html(
						$collection_category instanceof WP_Term
							? sprintf(__('View The %s Collection', 'garnernewtheme'), $collection_category->name)
							: __('Browse Featured Products', 'garnernewtheme')
					);
					?>
				</a>
			</div>
		</div>
	</section>

	<section class="categories" data-purpose="categories">
		<div class="container cards-3">
			<?php
			$single_family_term = get_term_by('slug', 'single-family-house-plans', 'product_cat');
			if (! ($single_family_term instanceof WP_Term)) {
				$single_family_term = get_term_by('name', 'Single Family House Plans', 'product_cat');
			}
			$single_family_term_archive_url = '#';
			if ($single_family_term instanceof WP_Term) {
				$maybe_archive_url = get_term_link($single_family_term);
				if (! is_wp_error($maybe_archive_url)) {
					$single_family_term_archive_url = $maybe_archive_url;
				}
			}
			$single_family_term_image_url = $img('category-singlefamily.svg');
			if ($single_family_term instanceof WP_Term) {
				$single_family_query = new WP_Query(
					array(
						'post_type'           => 'product',
						'post_status'         => 'publish',
						'posts_per_page'      => 1,
						'ignore_sticky_posts' => true,
						'orderby'             => 'rand',
						'tax_query'           => array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => array((int) $single_family_term->term_id),
							),
						),
					)
				);

				if ($single_family_query->have_posts()) {
					$single_family_query->the_post();
					$random_single_family_image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
					if ($random_single_family_image_url) {
						$single_family_term_image_url = esc_url($random_single_family_image_url);
					}
					wp_reset_postdata();
				}
			}
			?>
			<article class="category-card">
				<img src="<?php echo esc_url($single_family_term_image_url); ?>" alt="Stock plans">
				<h3><?php esc_html_e('Stock Plans', 'garnernewtheme'); ?></h3>
				<p><?php esc_html_e('Professionally designed homes ready to build.', 'garnernewtheme'); ?></p>
				<a href="<?php echo esc_url($single_family_term_archive_url); ?>"><?php esc_html_e('Browse Plans', 'garnernewtheme'); ?></a>
			</article>

			<article class="category-card">
				<img src="/wp-content/uploads/2026/05/pexels-photo-6614837-scaled.jpeg" alt="Custom design">
				<h3><?php esc_html_e('Custom Design', 'garnernewtheme'); ?></h3>
				<p><?php esc_html_e('Create a home tailored to your land, lifestyle, and vision.', 'garnernewtheme'); ?></p>
				<a href="#"><?php esc_html_e('Learn More', 'garnernewtheme'); ?></a>
			</article>
			<?php
			$multi_family_term = get_term_by('slug', 'multi-family-plans', 'product_cat');
			if (! ($multi_family_term instanceof WP_Term)) {
				$multi_family_term = get_term_by('name', 'Multi-Family Plans', 'product_cat');
			}

			$multi_family_archive_url = '#';
			if ($multi_family_term instanceof WP_Term) {
				$maybe_archive_url = get_term_link($multi_family_term);
				if (! is_wp_error($maybe_archive_url)) {
					$multi_family_archive_url = $maybe_archive_url;
				}
			}

			$multi_family_image_url = $img('category-multifamily.svg');
			if ($multi_family_term instanceof WP_Term) {
				$multi_family_query = new WP_Query(
					array(
						'post_type'           => 'product',
						'post_status'         => 'publish',
						'posts_per_page'      => 1,
						'ignore_sticky_posts' => true,
						'orderby'             => 'rand',
						'tax_query'           => array(
							array(
								'taxonomy' => 'product_cat',
								'field'    => 'term_id',
								'terms'    => array((int) $multi_family_term->term_id),
							),
						),
					)
				);

				if ($multi_family_query->have_posts()) {
					$multi_family_query->the_post();
					$random_multi_family_image_url = get_the_post_thumbnail_url(get_the_ID(), 'large');
					if ($random_multi_family_image_url) {
						$multi_family_image_url = esc_url($random_multi_family_image_url);
					}
					wp_reset_postdata();
				}
			}
			?>
			<article class="category-card">
				<img src="<?php echo esc_url($multi_family_image_url); ?>" alt="<?php esc_attr_e('Multi-family plan', 'garnernewtheme'); ?>">
				<h3><?php esc_html_e('Multi-Family', 'garnernewtheme'); ?></h3>
				<p><?php esc_html_e('Duplex, townhome, and multi-family designs for builders.', 'garnernewtheme'); ?></p>
				<a href="<?php echo esc_url($multi_family_archive_url); ?>"><?php esc_html_e('Explore Designs', 'garnernewtheme'); ?></a>
			</article>
		</div>
	</section>

	<section class="featured-plans" id="plans" data-purpose="featured-plans">
		<div class="container">
			<div class="section-header">
				<span class="section-kicker"><?php esc_html_e('House Plans', 'garnernewtheme'); ?></span>
				<h2 class="section-title"><?php esc_html_e('Featured Plans', 'garnernewtheme'); ?></h2>
			</div>

			<?php
			$featured_ids = array();
			if (function_exists('wc_get_featured_product_ids')) {
				$featured_ids = array_filter(array_map('absint', wc_get_featured_product_ids()));
			}

			$product_args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'posts_per_page'      => 4,
				'ignore_sticky_posts' => true,
			);

			if (! empty($featured_ids)) {
				$product_args['post__in'] = $featured_ids;
				$product_args['orderby']  = 'post__in';
			}

			$featured_products_query = new WP_Query($product_args);

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
			?>

			<div class="cards-4">
				<?php if ($featured_products_query->have_posts()) : ?>
					<?php while ($featured_products_query->have_posts()) : ?>
						<?php
						$featured_products_query->the_post();
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
						<img src="<?php echo $img('plan-greenwood.svg'); ?>" alt="<?php esc_attr_e('Featured plan placeholder', 'garnernewtheme'); ?>">
						<div>
							<h3><?php esc_html_e('No Products Found', 'garnernewtheme'); ?></h3>
							<p><?php esc_html_e('Add WooCommerce products with ACF plan fields to populate this section.', 'garnernewtheme'); ?></p>
						</div>
					</article>
				<?php endif; ?>
			</div>

			<div class="section-cta-wrap">
				<a class="btn btn--outline" href="#"><?php esc_html_e('Browse All Plans', 'garnernewtheme'); ?></a>
			</div>
		</div>
	</section>

	<section class="narrative" data-purpose="narrative">
		<div class="narrative__left">
			<span class="section-kicker"><?php esc_html_e('A Legacy of Design', 'garnernewtheme'); ?></span>
			<h2><?php echo esc_html($narrative_heading); ?></h2>
			<p><?php echo esc_html($narrative_paragraph); ?></p>
			<a class="btn btn--dark" href="<?php echo esc_url($narrative_button_url); ?>"><?php echo esc_html($narrative_button_text); ?></a>
		</div>
		<div class="narrative__right">
			<img src="<?php echo esc_url($narrative_image_url); ?>" alt="<?php echo esc_attr($narrative_image_alt); ?>">
		</div>
	</section>

	<section class="social-proof" data-purpose="social-proof">
		<div class="container social-proof__grid">
			<div class="testimonial-card">
				<span class="section-kicker"><?php esc_html_e('What Our Clients Say', 'garnernewtheme'); ?></span>
				<p class="testimonial-card__quote">"Garner Drake designed our dream home that fits our property perfectly. The attention to detail and timeless style is unmatched."</p>
				<p class="testimonial-card__author">— Jessica &amp; Matt H. | Madison, MS</p>
			</div>
			<div>
				<span class="section-kicker"><?php esc_html_e('Follow Along On Instagram', 'garnernewtheme'); ?></span>
				<div class="instagram-grid">
					<img src="<?php echo $img('ig-1.svg'); ?>" alt="Instagram post one">
					<img src="<?php echo $img('ig-2.svg'); ?>" alt="Instagram post two">
					<img src="<?php echo $img('ig-3.svg'); ?>" alt="Instagram post three">
					<img src="<?php echo $img('ig-4.svg'); ?>" alt="Instagram post four">
					<img src="<?php echo $img('ig-5.svg'); ?>" alt="Instagram post five">
					<img src="<?php echo $img('ig-6.svg'); ?>" alt="Instagram post six">
				</div>
				<a class="instagram-handle" href="#">@GarnerDrakeResidential</a>
			</div>
		</div>
	</section>

	<section class="newsletter-banner" id="contact">
		<div class="container newsletter-banner__inner">
			<div>
				<h2><?php esc_html_e('Build Smarter. Design Better.', 'garnernewtheme'); ?></h2>
				<p><?php esc_html_e('Get new house plans, design inspiration, and featured collections delivered to your inbox.', 'garnernewtheme'); ?></p>
			</div>
			<form class="newsletter-form" action="#" method="post">
				<label class="screen-reader-text" for="newsletter-email"><?php esc_html_e('Email Address', 'garnernewtheme'); ?></label>
				<input id="newsletter-email" type="email" placeholder="Email Address">
				<button type="submit"><?php esc_html_e('Join The List', 'garnernewtheme'); ?></button>
			</form>
		</div>
	</section>

	<section class="footer-cta" data-purpose="footer-cta">
		<img class="footer-cta__bg" src="<?php echo $img('footer-cta-bg.svg'); ?>" alt="Draft background texture">
		<div class="footer-cta__overlay"></div>
		<div class="container footer-cta__content">
			<h2><?php esc_html_e('Ready to Build Something Timeless?', 'garnernewtheme'); ?></h2>
			<p><?php esc_html_e('Let\'s create a home that reflects your life, your land, and your legacy.', 'garnernewtheme'); ?></p>
			<div class="footer-cta__actions">
				<a class="btn btn--solid" href="#"><?php esc_html_e('Explore House Plans', 'garnernewtheme'); ?></a>
				<a class="btn btn--ghost" href="#"><?php esc_html_e('Start Your Custom Design', 'garnernewtheme'); ?></a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
