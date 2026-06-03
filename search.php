<?php
/**
 * Search results template.
 *
 * @package garnernewtheme
 */

get_header();
?>
<main id="main-content" class="content-shell container content-shell--default">
	<header class="archive-header">
		<h1>
			<?php
			printf(
				esc_html__( 'Search Results for: %s', 'garnernewtheme' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'entry-card' ); ?>>
				<h2 class="entry-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry-card__excerpt"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No matching results.', 'garnernewtheme' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
