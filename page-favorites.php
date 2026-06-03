<?php
/**
 * Favorites page template.
 *
 * Used automatically for the page with slug "favorites".
 *
 * @package garnernewtheme
 */

get_header();

$favorite_product_ids = array_reverse( garnernewtheme_get_favorite_product_ids() );

$favorites_query = new WP_Query(
	array(
		'post_type'           => 'product',
		'post_status'         => 'publish',
		'posts_per_page'      => 24,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
		'post__in'            => ! empty( $favorite_product_ids ) ? $favorite_product_ids : array( 0 ),
		'orderby'             => 'post__in',
	)
);
?>

<main id="main-content">
	<section class="featured-plans" data-purpose="favorites-page">
		<div class="container">
			<header class="section-header">
				<span class="section-kicker"><?php esc_html_e( 'Saved Plans', 'garnernewtheme' ); ?></span>
				<h1 class="section-title"><?php esc_html_e( 'Your Favorites', 'garnernewtheme' ); ?></h1>
				<p class="section-subtitle"><?php esc_html_e( 'Products you saved from the heart icon appear here for this browser session.', 'garnernewtheme' ); ?></p>
			</header>

			<div class="cards-4">
				<?php if ( $favorites_query->have_posts() ) : ?>
					<?php while ( $favorites_query->have_posts() ) : ?>
						<?php
						$favorites_query->the_post();
						$product_id = get_the_ID();
						$image_url  = get_the_post_thumbnail_url( $product_id, 'large' );

						if ( ! $image_url ) {
							$image_url = get_theme_file_uri( '/assets/images/plan-greenwood.svg' );
						}
						?>
						<article class="plan-card">
							<?php echo garnernewtheme_render_favorite_button( $product_id, 'grid' ); ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>">
							<div>
								<h3><?php the_title(); ?></h3>
								<a href="<?php the_permalink(); ?>"><?php esc_html_e( 'View Plan', 'garnernewtheme' ); ?></a>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				<?php else : ?>
					<article class="plan-card">
						<img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/plan-greenwood.svg' ) ); ?>" alt="<?php esc_attr_e( 'Plan placeholder', 'garnernewtheme' ); ?>">
						<div>
							<h3><?php esc_html_e( 'No Saved Plans Yet', 'garnernewtheme' ); ?></h3>
							<p><?php esc_html_e( 'Tap the heart icon on any product card or product page to save it here.', 'garnernewtheme' ); ?></p>
							<a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>"><?php esc_html_e( 'Browse Plans', 'garnernewtheme' ); ?></a>
						</div>
					</article>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
