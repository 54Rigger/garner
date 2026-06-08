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
					<?php
					$social_networks = garnernewtheme_get_social_networks();
					$social_order    = garnernewtheme_get_social_ordered_networks();

					foreach ($social_order as $network_slug) {
						if (! isset($social_networks[$network_slug])) {
							continue;
						}

						$url  = trim((string) get_theme_mod('social_' . $network_slug . '_url', ''));
						$icon = garnernewtheme_get_social_icon_svg($network_slug);

						if ('' === $url || '' === $icon) {
							continue;
						}
						?>
						<a href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($social_networks[$network_slug]); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo $icon; ?>
						</a>
						<?php
					}
					?>
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
