<?php
/**
 * Product archive template.
 *
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$img = static function ( $file ) {
	return esc_url( get_theme_file_uri( '/assets/images/' . $file ) );
};

$get_acf_value = static function ( $post_id, $keys ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	foreach ( (array) $keys as $key ) {
		$value = get_field( $key, $post_id );
		if ( '' !== $value && null !== $value ) {
			return $value;
		}
	}

	return '';
};

$get_acf_image_url = static function ( $post_id, $keys ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	foreach ( (array) $keys as $key ) {
		$value = get_field( $key, $post_id );

		if ( is_array( $value ) ) {
			if ( ! empty( $value['url'] ) ) {
				return esc_url( $value['url'] );
			}

			if ( ! empty( $value['ID'] ) ) {
				$url = wp_get_attachment_image_url( absint( $value['ID'] ), 'large' );
				if ( $url ) {
					return esc_url( $url );
				}
			}

			if ( ! empty( $value[0] ) ) {
				$gallery_item = $value[0];

				if ( is_array( $gallery_item ) && ! empty( $gallery_item['url'] ) ) {
					return esc_url( $gallery_item['url'] );
				}

				if ( is_numeric( $gallery_item ) ) {
					$url = wp_get_attachment_image_url( absint( $gallery_item ), 'large' );
					if ( $url ) {
						return esc_url( $url );
					}
				}
			}
		}

		if ( is_numeric( $value ) ) {
			$url = wp_get_attachment_image_url( absint( $value ), 'large' );
			if ( $url ) {
				return esc_url( $url );
			}
		}

		if ( is_string( $value ) && filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return esc_url( $value );
		}
	}

	return '';
};

$shop_breadcrumb = array(
	array(
		'label' => __( 'Home', 'garnernewtheme' ),
		'url'   => home_url( '/' ),
	),
	array(
		'label' => __( 'House Plans', 'garnernewtheme' ),
		'url'   => '',
	),
);

$shop_description = '';
if ( function_exists( 'get_the_archive_description' ) ) {
	$shop_description = get_the_archive_description();
}

$shop_category_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	)
);
?>

<main id="main-content">
	<section class="featured-plans" data-purpose="product-shop-archive">
		<div class="container">
			<nav class="archive-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'garnernewtheme' ); ?>">
				<ol>
					<?php foreach ( $shop_breadcrumb as $breadcrumb_index => $breadcrumb_item ) : ?>
						<li>
							<?php if ( ! empty( $breadcrumb_item['url'] ) && $breadcrumb_index < count( $shop_breadcrumb ) - 1 ) : ?>
								<a href="<?php echo esc_url( $breadcrumb_item['url'] ); ?>"><?php echo esc_html( $breadcrumb_item['label'] ); ?></a>
							<?php else : ?>
								<span><?php echo esc_html( $breadcrumb_item['label'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</nav>

			<?php if ( ! is_wp_error( $shop_category_terms ) && ! empty( $shop_category_terms ) ) : ?>
				<div class="archive-child-category-jump">
					<label for="archive-shop-category-select"><?php esc_html_e( 'Jump to plan category', 'garnernewtheme' ); ?></label>
					<select id="archive-shop-category-select" onchange="if (this.value) { window.location.href = this.value; }">
						<option value=""><?php esc_html_e( 'Select a category', 'garnernewtheme' ); ?></option>
						<?php foreach ( $shop_category_terms as $shop_category_term ) : ?>
							<?php $shop_term_link = get_term_link( $shop_category_term ); ?>
							<?php if ( ! is_wp_error( $shop_term_link ) ) : ?>
								<option value="<?php echo esc_url( $shop_term_link ); ?>"><?php echo esc_html( $shop_category_term->name ); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			<?php endif; ?>

			<header class="section-header">
				<span class="section-kicker"><?php esc_html_e( 'House Plans', 'garnernewtheme' ); ?></span>
				<h1 class="section-title"><?php woocommerce_page_title(); ?></h1>
				<?php if ( ! empty( $shop_description ) ) : ?>
					<p class="section-subtitle"><?php echo esc_html( wp_strip_all_tags( $shop_description ) ); ?></p>
				<?php endif; ?>
			</header>

			<div class="cards-4">
				<?php if ( have_posts() ) : ?>
					<?php while ( have_posts() ) : ?>
						<?php
						the_post();
						$product_id = get_the_ID();
						$product    = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;

						$image_url = $get_acf_image_url( $product_id, array( 'feature_image', 'plan_image', 'plan_image_1', 'plan_image_2', 'plan_image_3', 'mf-feature_image', 'mf-plan_image_1', 'mf-plan_image_2', 'mf-plan_image_3' ) );
						if ( ! $image_url ) {
							$image_id = get_post_thumbnail_id( $product_id );
							if ( $image_id ) {
								$fallback_image_url = wp_get_attachment_image_url( $image_id, 'large' );
								if ( $fallback_image_url ) {
									$image_url = esc_url( $fallback_image_url );
								}
							}
						}

						if ( ! $image_url ) {
							$image_url = $img( 'plan-greenwood.svg' );
						}

						$sq_ft = $get_acf_value( $product_id, array( 'total-living-area', 'total-area', 'area_heated', 'mf-total-living-area', 'mf-total-area', 'mf-area_heated' ) );
						$beds  = $get_acf_value( $product_id, array( 'bedrooms', 'MF-unit-bedrooms' ) );
						$baths = $get_acf_value( $product_id, array( 'bathrooms', 'MF-unit-bathrooms' ) );

						if ( ! $beds && is_object( $product ) && method_exists( $product, 'get_attribute' ) ) {
							$beds = $product->get_attribute( 'pa_bedrooms' );
						}
						if ( ! $baths && is_object( $product ) && method_exists( $product, 'get_attribute' ) ) {
							$baths = $product->get_attribute( 'pa_bathrooms' );
						}

						$specs = array();
						if ( $sq_ft ) {
							$specs[] = strtoupper( wp_strip_all_tags( (string) $sq_ft ) ) . ' SQ FT';
						}
						if ( $beds ) {
							$specs[] = strtoupper( wp_strip_all_tags( (string) $beds ) ) . ' BED';
						}
						if ( $baths ) {
							$specs[] = strtoupper( wp_strip_all_tags( (string) $baths ) ) . ' BATH';
						}
						?>
						<article class="plan-card">
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
							<div>
								<h3><?php the_title(); ?></h3>
								<?php if ( ! empty( $specs ) ) : ?>
									<p><?php echo esc_html( implode( ' | ', $specs ) ); ?></p>
								<?php endif; ?>
								<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View Plan', 'garnernewtheme' ); ?></a>
							</div>
						</article>
					<?php endwhile; ?>
				<?php else : ?>
					<article class="plan-card">
						<img src="<?php echo $img( 'plan-greenwood.svg' ); ?>" alt="<?php esc_attr_e( 'Plan placeholder', 'garnernewtheme' ); ?>">
						<div>
							<h3><?php esc_html_e( 'No Products Found', 'garnernewtheme' ); ?></h3>
							<p><?php esc_html_e( 'No products are available yet.', 'garnernewtheme' ); ?></p>
						</div>
					</article>
				<?php endif; ?>
			</div>

			<?php if ( have_posts() ) : ?>
				<div class="section-cta-wrap">
					<?php
					the_posts_pagination(
						array(
							'mid_size'           => 1,
							'prev_text'          => __( 'Previous', 'garnernewtheme' ),
							'next_text'          => __( 'Next', 'garnernewtheme' ),
							'screen_reader_text' => __( 'Products navigation', 'garnernewtheme' ),
						)
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer( 'shop' );
