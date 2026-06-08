<?php

function garnernewtheme_sanitize_attachment_ids($input)
{
	$ids = array_filter(
		array_map(
			'absint',
			explode(',', (string) $input)
		)
	);

	return implode(',', $ids);
}

/**
 * Build product category choices for Customizer selects.
 *
 * @return array<string, string>
 */
function garnernewtheme_get_product_category_choices()
{
	$choices = array(
		'' => __('Select a product category', 'garnernewtheme'),
	);

	if (! taxonomy_exists('product_cat')) {
		return $choices;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if (is_wp_error($terms) || empty($terms)) {
		return $choices;
	}

	foreach ($terms as $term) {
		$choices[$term->slug] = $term->name;
	}

	return $choices;
}

/**
 * Sanitize featured collection category slug.
 *
 * @param string $input Raw category slug.
 * @return string
 */
function garnernewtheme_sanitize_product_category_slug($input)
{
	$input = sanitize_title((string) $input);

	if ('' === $input) {
		return '';
	}

	if (! taxonomy_exists('product_cat')) {
		return $input;
	}

	$term = get_term_by('slug', $input, 'product_cat');
	if ($term instanceof WP_Term) {
		return $term->slug;
	}

	return '';
}

/**
 * Sanitize social profile URL values from the Customizer.
 *
 * @param string $input Raw URL.
 * @return string
 */
function garnernewtheme_sanitize_social_url($input)
{
	$input = trim((string) $input);

	if ('' === $input || '#' === $input) {
		return '#';
	}

	$url = esc_url_raw($input);

	return $url ? $url : '#';
}

/**
 * Return supported social networks for footer links.
 *
 * @return array<string, string>
 */
function garnernewtheme_get_social_networks()
{
	return array(
		'youtube'   => __('YouTube', 'garnernewtheme'),
		'facebook'  => __('Facebook', 'garnernewtheme'),
		'instagram' => __('Instagram', 'garnernewtheme'),
		'whatsapp'  => __('WhatsApp', 'garnernewtheme'),
		'tiktok'    => __('TikTok', 'garnernewtheme'),
		'pinterest' => __('Pinterest', 'garnernewtheme'),
		'snapchat'  => __('Snapchat', 'garnernewtheme'),
		'threads'   => __('Threads', 'garnernewtheme'),
		'twitter'   => __('Twitter', 'garnernewtheme'),
		'reddit'    => __('Reddit', 'garnernewtheme'),
		'tumblr'    => __('Tumblr', 'garnernewtheme'),
		'linkedin'  => __('LinkedIn', 'garnernewtheme'),
		'discord'   => __('Discord', 'garnernewtheme'),
	);
}

/**
 * Sanitize optional social profile URL values.
 *
 * @param string $input Raw URL.
 * @return string
 */
function garnernewtheme_sanitize_optional_social_url($input)
{
	$input = trim((string) $input);

	if ('' === $input || '#' === $input) {
		return '';
	}

	$url = esc_url_raw($input);

	return $url ? $url : '';
}

/**
 * Sanitize comma-separated social network order list.
 *
 * @param string $input Raw order value.
 * @return string
 */
function garnernewtheme_sanitize_social_order($input)
{
	$networks = array_keys(garnernewtheme_get_social_networks());
	$raw      = array_filter(array_map('sanitize_key', explode(',', (string) $input)));
	$ordered  = array();

	foreach ($raw as $network) {
		if (in_array($network, $networks, true) && ! in_array($network, $ordered, true)) {
			$ordered[] = $network;
		}
	}

	foreach ($networks as $network) {
		if (! in_array($network, $ordered, true)) {
			$ordered[] = $network;
		}
	}

	return implode(',', $ordered);
}

/**
 * Return the current social network order from theme mods.
 *
 * @return array<int, string>
 */
function garnernewtheme_get_social_ordered_networks()
{
	$default_order = implode(',', array_keys(garnernewtheme_get_social_networks()));
	$order_raw     = (string) get_theme_mod('social_links_order', $default_order);
	$order_clean   = garnernewtheme_sanitize_social_order($order_raw);

	return array_filter(explode(',', $order_clean));
}

/**
 * Return SVG icon markup for a social network slug.
 *
 * @param string $network Network slug.
 * @return string
 */
function garnernewtheme_get_social_icon_svg($network)
{
	$icons = array(
		'youtube'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="3" y="6" width="18" height="12" rx="4"></rect><polygon points="11,10 16,12 11,14" fill="none"></polygon></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M14.5 8H16V4.6c-.27-.04-1.2-.1-2.3-.1-2.28 0-3.84 1.42-3.84 4.02V11H7v4h2.86v5h3.51v-5H16l.4-4h-2.99V8.95c0-1.15.31-1.95 1.72-1.95z"></path></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5" ry="5"></rect><circle cx="12" cy="12" r="4.2"></circle><circle cx="17.5" cy="6.5" r="1"></circle></svg>',
		'whatsapp'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M20 11.7A8 8 0 0 1 8.6 18.8L4 20l1.3-4.4A8 8 0 1 1 20 11.7z"></path><path d="M10 9.5c.4 1.4 1.1 2.6 2.2 3.7 1.1 1.1 2.3 1.8 3.7 2.2"></path></svg>',
		'tiktok'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M14 5c.8 1.5 2.2 2.7 4 3.1"></path><path d="M10 11.3a3.7 3.7 0 1 0 3.6 3.7V5"></path></svg>',
		'pinterest' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8"></circle><path d="M9.8 12.2c0-2 1.2-3.4 3-3.4 1.5 0 2.5 1.1 2.5 2.5 0 2-1 3.3-2.6 3.3-.6 0-1.1-.3-1.3-.7l-.6 2.4"></path></svg>',
		'snapchat'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M12 4.5c2.1 0 3.8 1.7 3.8 3.8v1.1c0 .6.3 1.2.8 1.5.4.2.7.5.7.9 0 .6-.6.9-1.2 1.1-.4.1-.6.5-.5.9.1.5.3 1 .7 1.4-.6.3-1.3.4-2 .4-.5 0-1 .3-1.3.8l-.9 1.4-.9-1.4c-.3-.5-.8-.8-1.3-.8-.7 0-1.4-.1-2-.4.4-.4.6-.9.7-1.4.1-.4-.1-.8-.5-.9-.6-.2-1.2-.5-1.2-1.1 0-.4.3-.7.7-.9.5-.3.8-.9.8-1.5V8.3c0-2.1 1.7-3.8 3.8-3.8z"></path></svg>',
		'threads'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M12.4 7.1c2.8 0 4.6 1.4 4.6 4 0 2.5-1.8 4.2-4.6 4.2-2.5 0-4.2-1.4-4.2-3.5 0-1.9 1.5-3.2 3.8-3.2 2.4 0 3.9 1.5 4.3 4.2"></path><path d="M14.5 16.2c-.8 1.6-2.4 2.4-4.5 2.4-3.3 0-5.8-2.6-5.8-6.3 0-4.2 2.9-7.1 7.2-7.1 4.4 0 7.4 2.8 7.4 7.2 0 4.1-2.5 6.8-6.3 6.8"></path></svg>',
		'twitter'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M4 4l16 16"></path><path d="M20 4l-6.4 7.1"></path><path d="M10.4 12.9L4 20"></path></svg>',
		'reddit'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><circle cx="12" cy="13" r="4.8"></circle><circle cx="9.8" cy="12.5" r="0.7"></circle><circle cx="14.2" cy="12.5" r="0.7"></circle><path d="M10 15c.5.5 1.2.8 2 .8s1.5-.3 2-.8"></path><circle cx="7.1" cy="11.7" r="1.2"></circle><circle cx="16.9" cy="11.7" r="1.2"></circle><path d="M12.9 7.7l.9-3 2.6.6"></path><circle cx="16.9" cy="5.5" r="1"></circle></svg>',
		'tumblr'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M15.5 18.6c-.7.4-1.6.6-2.5.6-2.1 0-3.2-1.3-3.2-3.7V11H7.4V8.6c1.7-.7 2.8-2.3 3-4.2h2.4v3.5h2.9V11h-2.9v4.3c0 1 .5 1.4 1.2 1.4.5 0 .9-.1 1.3-.4z"></path></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><rect x="4.2" y="9.2" width="3.2" height="10"></rect><circle cx="5.8" cy="5.8" r="1.6"></circle><path d="M10.2 9.2v10"></path><path d="M10.2 13.2c0-2.2 1.4-3.8 3.4-3.8s3.2 1.4 3.2 3.8v6"></path></svg>',
		'discord'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true" focusable="false"><path d="M7.5 8.2c2.6-1.2 6.3-1.2 9 0 .9 1.5 1.5 3.1 1.7 4.8-.9.7-1.9 1.2-3 1.5l-.7-1.1c-.5.1-1 .2-1.5.2s-1 0-1.5-.2l-.7 1.1c-1.1-.3-2.1-.8-3-1.5.2-1.7.8-3.3 1.7-4.8z"></path><circle cx="10" cy="12.1" r="0.8"></circle><circle cx="14" cy="12.1" r="0.8"></circle></svg>',
	);

	return $icons[$network] ?? '';
}

/**
 * Register Customizer options for homepage hero.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function garnernewtheme_customize_register($wp_customize)
{
	$wp_customize->add_section(
		'garnernewtheme_homepage',
		array(
			'title'       => __('Homepage', 'garnernewtheme'),
			'priority'    => 30,
			'description' => __('Homepage hero and section settings.', 'garnernewtheme'),
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_hero_carousel_images',
		array(
			'default'           => '',
			'sanitize_callback' => 'garnernewtheme_sanitize_attachment_ids',
		)
	);

	if (class_exists('WP_Customize_Control') && ! class_exists('GarnerNewTheme_Multi_Image_Control')) {
		class GarnerNewTheme_Multi_Image_Control extends WP_Customize_Control
		{
			public $type = 'garnernewtheme_multi_image';

			public function render_content()
			{
				$input_id = '_customize-input-' . $this->id;
				?>
				<label>
					<?php if (! empty($this->label)) : ?>
						<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
					<?php endif; ?>
					<?php if (! empty($this->description)) : ?>
						<span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
					<?php endif; ?>
				</label>

				<input
					type="hidden"
					id="<?php echo esc_attr($input_id); ?>"
					class="garner-multi-image-input"
					value="<?php echo esc_attr($this->value()); ?>"
					<?php $this->link(); ?> />

				<div class="garner-multi-image-preview"></div>

				<p>
					<button type="button" class="button garner-multi-image-select"><?php esc_html_e('Select Carousel Images', 'garnernewtheme'); ?></button>
					<button type="button" class="button-link garner-multi-image-clear"><?php esc_html_e('Clear', 'garnernewtheme'); ?></button>
				</p>
				<?php
			}
		}
	}

	if (class_exists('WP_Customize_Control') && ! class_exists('GarnerNewTheme_Social_Order_Control')) {
		class GarnerNewTheme_Social_Order_Control extends WP_Customize_Control
		{
			public $type = 'garnernewtheme_social_order';

			public function render_content()
			{
				$input_id = '_customize-input-' . $this->id;
				$networks = garnernewtheme_get_social_networks();
				$order    = explode(',', garnernewtheme_sanitize_social_order((string) $this->value()));
				$ordered  = array();

				foreach ($order as $slug) {
					if (isset($networks[$slug])) {
						$ordered[$slug] = $networks[$slug];
					}
				}

				foreach ($networks as $slug => $label) {
					if (! isset($ordered[$slug])) {
						$ordered[$slug] = $label;
					}
				}
				?>
				<label>
					<?php if (! empty($this->label)) : ?>
						<span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
					<?php endif; ?>
					<?php if (! empty($this->description)) : ?>
						<span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
					<?php endif; ?>
				</label>

				<input
					type="hidden"
					id="<?php echo esc_attr($input_id); ?>"
					class="garner-social-order-input"
					value="<?php echo esc_attr(implode(',', array_keys($ordered))); ?>"
					<?php $this->link(); ?> />

				<ul class="garner-social-order-list" style="margin: 0; padding: 0;">
					<?php foreach ($ordered as $slug => $label) : ?>
						<li class="garner-social-order-item" data-network="<?php echo esc_attr($slug); ?>" style="list-style: none; border: 1px solid #dcdcde; padding: 8px 10px; margin: 0 0 6px; background: #fff; cursor: move; display: flex; align-items: center; gap: 8px;">
							<span class="dashicons dashicons-move" aria-hidden="true"></span>
							<span><?php echo esc_html($label); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
				<?php
			}
		}
	}

	$wp_customize->add_control(
		new GarnerNewTheme_Multi_Image_Control(
			$wp_customize,
			'garnernewtheme_hero_carousel_images',
			array(
				'label'       => __('Hero Carousel Images', 'garnernewtheme'),
				'description' => __('Select multiple images from the media library for the hero slideshow.', 'garnernewtheme'),
				'section'     => 'garnernewtheme_homepage',
			)
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_featured_collection_category',
		array(
			'default'           => '',
			'sanitize_callback' => 'garnernewtheme_sanitize_product_category_slug',
		)
	);

	$wp_customize->add_control(
		'garnernewtheme_featured_collection_category',
		array(
			'label'       => __('Featured Collection Category', 'garnernewtheme'),
			'description' => __('Choose one WooCommerce product category for the Featured Collection section.', 'garnernewtheme'),
			'section'     => 'garnernewtheme_homepage',
			'type'        => 'select',
			'choices'     => garnernewtheme_get_product_category_choices(),
		)
	);

	$wp_customize->add_section(
		'garnernewtheme_narrative',
		array(
			'title'       => __('Narrative', 'garnernewtheme'),
			'priority'    => 32,
			'description' => __('Controls for the homepage narrative section.', 'garnernewtheme'),
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_narrative_heading',
		array(
			'default'           => 'Rooted in Experience. Focused on You.',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'garnernewtheme_narrative_heading',
		array(
			'label'   => __('Narrative Heading', 'garnernewtheme'),
			'section' => 'garnernewtheme_narrative',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_narrative_paragraph',
		array(
			'default'           => 'For over three decades, Garner Drake Residential Design has created homes that reflect the beauty of Southern architecture and the way families truly live.',
			'sanitize_callback' => 'sanitize_textarea_field',
		)
	);

	$wp_customize->add_control(
		'garnernewtheme_narrative_paragraph',
		array(
			'label'   => __('Narrative Paragraph', 'garnernewtheme'),
			'section' => 'garnernewtheme_narrative',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_narrative_button_text',
		array(
			'default'           => 'Our Story',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);

	$wp_customize->add_control(
		'garnernewtheme_narrative_button_text',
		array(
			'label'   => __('Narrative Button Text', 'garnernewtheme'),
			'section' => 'garnernewtheme_narrative',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_narrative_button_url',
		array(
			'default'           => '#',
			'sanitize_callback' => 'garnernewtheme_sanitize_social_url',
		)
	);

	$wp_customize->add_control(
		'garnernewtheme_narrative_button_url',
		array(
			'label'   => __('Narrative Button URL', 'garnernewtheme'),
			'section' => 'garnernewtheme_narrative',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_narrative_image_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);

	if (class_exists('WP_Customize_Media_Control')) {
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'garnernewtheme_narrative_image_id',
				array(
					'label'      => __('Narrative Right Image', 'garnernewtheme'),
					'section'    => 'garnernewtheme_narrative',
					'mime_type'  => 'image',
				)
			)
		);
	}

	$wp_customize->add_section(
		'garnernewtheme_social_links',
		array(
			'title'       => __('Social Links', 'garnernewtheme'),
			'priority'    => 35,
			'description' => __('Set footer social profile URLs and drag to reorder icons.', 'garnernewtheme'),
		)
	);

	$social_networks = garnernewtheme_get_social_networks();

	$wp_customize->add_setting(
		'social_links_order',
		array(
			'default'           => implode(',', array_keys($social_networks)),
			'sanitize_callback' => 'garnernewtheme_sanitize_social_order',
		)
	);

	$wp_customize->add_control(
		new GarnerNewTheme_Social_Order_Control(
			$wp_customize,
			'social_links_order',
			array(
				'label'       => __('Social Icon Order', 'garnernewtheme'),
				'description' => __('Drag and drop to reorder how footer social icons appear.', 'garnernewtheme'),
				'section'     => 'garnernewtheme_social_links',
			)
		)
	);

	foreach ($social_networks as $network_slug => $network_label) {
		$setting_id = 'social_' . $network_slug . '_url';

		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => '',
				'sanitize_callback' => 'garnernewtheme_sanitize_optional_social_url',
			)
		);

		$wp_customize->add_control(
			$setting_id,
			array(
				'label'       => sprintf(__('%s URL', 'garnernewtheme'), $network_label),
				'description' => __('Leave blank to hide this network icon in the footer.', 'garnernewtheme'),
				'section'     => 'garnernewtheme_social_links',
				'type'        => 'url',
			)
		);
	}
}
add_action('customize_register', 'garnernewtheme_customize_register');

/**
 * Enqueue Customizer control scripts for multi-image selection UI.
 */
function garnernewtheme_customize_controls_assets()
{
	wp_enqueue_media();

	wp_enqueue_script(
		'garnernewtheme-customizer-controls',
		get_theme_file_uri('/assets/js/customizer-controls.js'),
		array('customize-controls', 'jquery', 'jquery-ui-sortable'),
		wp_get_theme()->get('Version'),
		true
	);
}
add_action('customize_controls_enqueue_scripts', 'garnernewtheme_customize_controls_assets');

/**
 * Show the coming soon page to logged-out visitors on the front page.
 */
