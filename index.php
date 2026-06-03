<?php
/**
 * Main index template.
 *
 * @package garnernewtheme
 */

get_header();
?>
<main id="main-content" class="content-shell container content-shell--default">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'entry-card' ); ?>>
				<h2 class="entry-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
				<div class="entry-card__excerpt"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'No posts found.', 'garnernewtheme' ); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
