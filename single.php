<?php
/**
 * Single post template.
 *
 * @package garnernewtheme
 */

get_header();
?>
<main id="main-content" class="content-shell container content-shell--default">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'entry-content' ); ?>>
			<h1><?php the_title(); ?></h1>
			<div class="entry-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
			<?php the_content(); ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
