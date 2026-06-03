<?php
/**
 * 404 template.
 *
 * @package garnernewtheme
 */

get_header();
?>
<main id="main-content" class="content-shell container content-shell--default">
	<section class="error-404">
		<h1><?php esc_html_e( 'Page Not Found', 'garnernewtheme' ); ?></h1>
		<p><?php esc_html_e( 'The page you are looking for does not exist or has moved.', 'garnernewtheme' ); ?></p>
		<a class="btn btn--dark" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return Home', 'garnernewtheme' ); ?></a>
	</section>
</main>
<?php
get_footer();
