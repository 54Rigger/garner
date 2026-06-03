<?php
/**
 * Site footer.
 *
 * @package garnernewtheme
 */
?>

<footer class="main-footer" data-purpose="main-footer">
	<div class="container">
		<div class="main-footer__grid">
			<div class="main-footer__brand">
				<div class="brand-mark brand-mark--footer">
					<a class="brand-mark__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
					<span class="brand-mark__subtitle"><?php esc_html_e( 'Residential Design', 'garnernewtheme' ); ?></span>
				</div>
				<div class="social-links">
					<a href="<?php echo esc_url( get_theme_mod( 'social_instagram_url', '#' ) ); ?>" aria-label="Instagram">IG</a>
					<a href="<?php echo esc_url( get_theme_mod( 'social_facebook_url', '#' ) ); ?>" aria-label="Facebook">FB</a>
					<a href="<?php echo esc_url( get_theme_mod( 'social_pinterest_url', '#' ) ); ?>" aria-label="Pinterest">PI</a>
				</div>
			</div>

			<div class="main-footer__links">
				<h4><?php esc_html_e( 'Explore', 'garnernewtheme' ); ?></h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_explore',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'fallback_cb'    => false,
					)
				);
				?>
			</div>

			<div class="main-footer__links">
				<h4><?php esc_html_e( 'Company', 'garnernewtheme' ); ?></h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_company',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'fallback_cb'    => false,
					)
				);
				?>
			</div>

			<div class="main-footer__links">
				<h4><?php esc_html_e( 'Support', 'garnernewtheme' ); ?></h4>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_support',
						'container'      => false,
						'menu_class'     => 'footer-menu',
						'fallback_cb'    => false,
					)
				);
				?>
			</div>
		</div>

		<div class="main-footer__meta">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All Rights Reserved.', 'garnernewtheme' ); ?></p>
			<div class="main-footer__legal">
				<a href="#"><?php esc_html_e( 'Privacy Policy', 'garnernewtheme' ); ?></a>
				<a href="#"><?php esc_html_e( 'Terms of Service', 'garnernewtheme' ); ?></a>
			</div>
		</div>
	</div>
</footer>

<button id="scroll-to-top" class="scroll-to-top" aria-label="<?php esc_attr_e( 'Scroll to top', 'garnernewtheme' ); ?>">
	<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
		<path d="M12 5l7 7m-7-7-7 7m7-7v14" stroke-linecap="round" stroke-linejoin="round" />
	</svg>
</button>

<?php wp_footer(); ?>
</body>
</html>
