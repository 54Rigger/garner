<?php
/**
 * Theme setup and asset loading.
 *
 * @package garnernewtheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function garnernewtheme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array( 'height' => 96, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'garnernewtheme' ),
			'footer_explore' => __( 'Footer Explore Menu', 'garnernewtheme' ),
			'footer_company' => __( 'Footer Company Menu', 'garnernewtheme' ),
			'footer_support' => __( 'Footer Support Menu', 'garnernewtheme' ),
		)
	);
}
add_action( 'after_setup_theme', 'garnernewtheme_setup' );

function garnernewtheme_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Footer Brand Area', 'garnernewtheme' ),
			'id'            => 'footer-brand',
			'description'   => __( 'Widgets in this area appear in the footer brand column.', 'garnernewtheme' ),
			'before_widget' => '<section class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'garnernewtheme_widgets_init' );

/**
 * Fallback menu for primary navigation when no menu is assigned.
 */
function garnernewtheme_primary_fallback_menu() {
	$items = array(
		__( 'Home', 'garnernewtheme' )          => home_url( '/' ),
		__( 'Stock Plans', 'garnernewtheme' )   => '#',
		__( 'Custom Design', 'garnernewtheme' ) => '#',
		__( 'Multi-Family', 'garnernewtheme' )  => '#',
		__( 'About', 'garnernewtheme' )         => '#',
		__( 'Blog', 'garnernewtheme' )          => '#',
		__( 'Contact', 'garnernewtheme' )       => '#',
	);

	echo '<ul class="primary-menu">';
	foreach ( $items as $label => $url ) {
		echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
	}
	echo '</ul>';
}

function garnernewtheme_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'garnernewtheme-google-fonts',
		'https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'garnernewtheme-main',
		get_theme_file_uri( '/assets/css/main.css' ),
		array( 'garnernewtheme-google-fonts' ),
		$theme_version
	);

	wp_enqueue_script(
		'garnernewtheme-theme',
		get_theme_file_uri( '/assets/js/theme.js' ),
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'garnernewtheme_assets' );

/**
 * Sanitize comma-separated list of attachment IDs.
 *
 * @param string $input Raw Customizer input.
 * @return string
 */
function garnernewtheme_sanitize_attachment_ids( $input ) {
	$ids = array_filter(
		array_map(
		'absint',
		explode( ',', (string) $input )
		)
	);

	return implode( ',', $ids );
}

/**
 * Build product category choices for Customizer selects.
 *
 * @return array<string, string>
 */
function garnernewtheme_get_product_category_choices() {
	$choices = array(
		'' => __( 'Select a product category', 'garnernewtheme' ),
	);

	if ( ! taxonomy_exists( 'product_cat' ) ) {
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

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $choices;
	}

	foreach ( $terms as $term ) {
		$choices[ $term->slug ] = $term->name;
	}

	return $choices;
}

/**
 * Sanitize featured collection category slug.
 *
 * @param string $input Raw category slug.
 * @return string
 */
function garnernewtheme_sanitize_product_category_slug( $input ) {
	$input = sanitize_title( (string) $input );

	if ( '' === $input ) {
		return '';
	}

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $input;
	}

	$term = get_term_by( 'slug', $input, 'product_cat' );
	if ( $term instanceof WP_Term ) {
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
function garnernewtheme_sanitize_social_url( $input ) {
	$input = trim( (string) $input );

	if ( '' === $input || '#' === $input ) {
		return '#';
	}

	$url = esc_url_raw( $input );

	return $url ? $url : '#';
}

/**
 * Register Customizer options for homepage hero.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager instance.
 */
function garnernewtheme_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'garnernewtheme_homepage',
		array(
			'title'       => __( 'Homepage', 'garnernewtheme' ),
			'priority'    => 30,
			'description' => __( 'Homepage hero and section settings.', 'garnernewtheme' ),
		)
	);

	$wp_customize->add_setting(
		'garnernewtheme_hero_carousel_images',
		array(
			'default'           => '',
			'sanitize_callback' => 'garnernewtheme_sanitize_attachment_ids',
		)
	);

	if ( class_exists( 'WP_Customize_Control' ) && ! class_exists( 'GarnerNewTheme_Multi_Image_Control' ) ) {
		class GarnerNewTheme_Multi_Image_Control extends WP_Customize_Control {
			public $type = 'garnernewtheme_multi_image';

			public function render_content() {
				$input_id = '_customize-input-' . $this->id;
				?>
				<label>
					<?php if ( ! empty( $this->label ) ) : ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $this->description ) ) : ?>
						<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
					<?php endif; ?>
				</label>

				<input
					type="hidden"
					id="<?php echo esc_attr( $input_id ); ?>"
					class="garner-multi-image-input"
					value="<?php echo esc_attr( $this->value() ); ?>"
					<?php $this->link(); ?>
				/>

				<div class="garner-multi-image-preview"></div>

				<p>
					<button type="button" class="button garner-multi-image-select"><?php esc_html_e( 'Select Carousel Images', 'garnernewtheme' ); ?></button>
					<button type="button" class="button-link garner-multi-image-clear"><?php esc_html_e( 'Clear', 'garnernewtheme' ); ?></button>
				</p>
				<?php
			}
		}
	}

	$wp_customize->add_control(
		new GarnerNewTheme_Multi_Image_Control(
			$wp_customize,
			'garnernewtheme_hero_carousel_images',
			array(
				'label'       => __( 'Hero Carousel Images', 'garnernewtheme' ),
				'description' => __( 'Select multiple images from the media library for the hero slideshow.', 'garnernewtheme' ),
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
			'label'       => __( 'Featured Collection Category', 'garnernewtheme' ),
			'description' => __( 'Choose one WooCommerce product category for the Featured Collection section.', 'garnernewtheme' ),
			'section'     => 'garnernewtheme_homepage',
			'type'        => 'select',
			'choices'     => garnernewtheme_get_product_category_choices(),
		)
	);

	$wp_customize->add_section(
		'garnernewtheme_social_links',
		array(
			'title'       => __( 'Social Links', 'garnernewtheme' ),
			'priority'    => 35,
			'description' => __( 'Set footer social profile URLs.', 'garnernewtheme' ),
		)
	);

	$wp_customize->add_setting(
		'social_instagram_url',
		array(
			'default'           => '#',
			'sanitize_callback' => 'garnernewtheme_sanitize_social_url',
		)
	);

	$wp_customize->add_control(
		'social_instagram_url',
		array(
			'label'   => __( 'Instagram URL', 'garnernewtheme' ),
			'section' => 'garnernewtheme_social_links',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'social_facebook_url',
		array(
			'default'           => '#',
			'sanitize_callback' => 'garnernewtheme_sanitize_social_url',
		)
	);

	$wp_customize->add_control(
		'social_facebook_url',
		array(
			'label'   => __( 'Facebook URL', 'garnernewtheme' ),
			'section' => 'garnernewtheme_social_links',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'social_pinterest_url',
		array(
			'default'           => '#',
			'sanitize_callback' => 'garnernewtheme_sanitize_social_url',
		)
	);

	$wp_customize->add_control(
		'social_pinterest_url',
		array(
			'label'   => __( 'Pinterest URL', 'garnernewtheme' ),
			'section' => 'garnernewtheme_social_links',
			'type'    => 'url',
		)
	);
}
add_action( 'customize_register', 'garnernewtheme_customize_register' );

/**
 * Enqueue Customizer control scripts for multi-image selection UI.
 */
function garnernewtheme_customize_controls_assets() {
	wp_enqueue_media();

	wp_enqueue_script(
		'garnernewtheme-customizer-controls',
		get_theme_file_uri( '/assets/js/customizer-controls.js' ),
		array( 'customize-controls', 'jquery' ),
		wp_get_theme()->get( 'Version' ),
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'garnernewtheme_customize_controls_assets' );

/**
 * Show the coming soon page to logged-out visitors on the front page.
 */
function garnernewtheme_redirect_logged_out_front_page() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() ) {
		return;
	}

	if ( is_user_logged_in() || ! is_front_page() || is_page( 'coming-soon' ) ) {
		return;
	}

	$coming_soon_page = get_page_by_path( 'coming-soon' );
	if ( ! ( $coming_soon_page instanceof WP_Post ) ) {
		return;
	}

	if ( (int) get_queried_object_id() === (int) $coming_soon_page->ID ) {
		return;
	}

	$coming_soon_url = get_permalink( $coming_soon_page );
	if ( $coming_soon_url ) {
		wp_safe_redirect( $coming_soon_url, 302 );
		exit;
	}
}
add_action( 'template_redirect', 'garnernewtheme_redirect_logged_out_front_page', 1 );

/**
 * Ensure WooCommerce product category archives use the theme template.
 *
 * This avoids block/template-loader fallbacks bypassing taxonomy-product_cat.php.
 *
 * @param string $template Resolved template path.
 * @return string
 */
function garnernewtheme_force_product_cat_template( $template ) {
	if ( ! is_tax( 'product_cat' ) ) {
		return $template;
	}

	$custom_template = get_theme_file_path( 'taxonomy-product_cat.php' );
	if ( file_exists( $custom_template ) ) {
		return $custom_template;
	}

	return $template;
}
add_filter( 'template_include', 'garnernewtheme_force_product_cat_template', 99 );

/**
 * Prefer classic PHP templates for WooCommerce product archives/categories.
 *
 * @param bool   $has_template  Whether WooCommerce sees a block template.
 * @param string $template_name Block template slug.
 * @return bool
 */
function garnernewtheme_disable_woo_block_templates_for_archives( $has_template, $template_name ) {
	if ( in_array( $template_name, array( 'archive-product', 'taxonomy-product_cat', 'taxonomy-product_tag' ), true ) ) {
		return false;
	}

	return $has_template;
}
add_filter( 'woocommerce_has_block_template', 'garnernewtheme_disable_woo_block_templates_for_archives', 10, 2 );

/**
 * Hard-override rendering for product category archives.
 *
 * This guarantees taxonomy-product_cat.php is used even when block template
 * resolution or third-party template loaders interfere.
 */
function garnernewtheme_render_product_cat_template_directly() {
	if ( is_admin() || wp_doing_ajax() || is_feed() || is_embed() ) {
		return;
	}

	if ( ! is_tax( 'product_cat' ) ) {
		return;
	}

	$custom_template = get_theme_file_path( 'taxonomy-product_cat.php' );
	if ( file_exists( $custom_template ) ) {
		include $custom_template;
		exit;
	}
}
add_action( 'template_redirect', 'garnernewtheme_render_product_cat_template_directly', 0 );

/**
 * Remove the WooCommerce product meta block from the single product summary.
 */
function garnernewtheme_remove_single_product_meta() {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
}
add_action( 'wp', 'garnernewtheme_remove_single_product_meta' );

/**
 * Remove the related products carousel from single product pages.
 */
function garnernewtheme_remove_related_products_carousel() {
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}
add_action( 'wp', 'garnernewtheme_remove_related_products_carousel' );

/**
 * Keep the main product image but remove gallery thumbnails.
 */
function garnernewtheme_remove_single_product_gallery_thumbnails() {
	remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
}
add_action( 'wp', 'garnernewtheme_remove_single_product_gallery_thumbnails' );

/**
 * Render a table of ACF fields for the current product.
 *
 * Skips empty, placeholder, array, object, and image/gallery fields.
 *
 * @return string
 */
function acf_field_display() {
	if ( ! function_exists( 'get_field_objects' ) || ! is_singular( 'product' ) ) {
		return '';
	}

	$field_objects = get_field_objects( get_the_ID() );
	if ( empty( $field_objects ) || ! is_array( $field_objects ) ) {
		return '';
	}

	$clean_dimension_label = static function ( $label ) {
		$label = trim( (string) $label );
		$label = preg_replace( '/\s*(Feet|Inches)\s*$/i', '', $label );

		return trim( (string) $label );
	};

	$normalize_field_key = static function ( $value ) {
		$value = strtolower( trim( (string) $value ) );
		$value = preg_replace( '/[^a-z0-9]+/', '_', $value );
		$value = trim( (string) $value, '_' );

		return $value;
	};

	$is_excluded_field = static function ( $field_name, $field_label ) use ( $normalize_field_key ) {
		$normalized_name  = $normalize_field_key( $field_name );
		$normalized_label = $normalize_field_key( $field_label );
		$excluded_keys    = array( 'plan_number', 'index_plan_number', 'sd_id', 'plantype', 'approved', 'c' );

		if ( in_array( $normalized_name, $excluded_keys, true ) || in_array( $normalized_label, $excluded_keys, true ) ) {
			return true;
		}

		return false;
	};

	$is_designer_plan_number_field = static function ( $field_name, $field_label ) use ( $normalize_field_key ) {
		$normalized_name  = $normalize_field_key( $field_name );
		$normalized_label = $normalize_field_key( $field_label );

		return in_array( 'designer_plan_number', array( $normalized_name, $normalized_label ), true );
	};

	$format_dimension_value = static function ( $feet, $inches ) {
		$feet   = trim( (string) $feet );
		$inches = trim( (string) $inches );

		if ( preg_match( '/^[\'\"]?-+[\'\"]?$/', $feet ) || in_array( strtolower( $feet ), array( 'n/a', 'na', 'none' ), true ) ) {
			$feet = '';
		}

		if ( preg_match( '/^[\'\"]?-+[\'\"]?$/', $inches ) || in_array( strtolower( $inches ), array( 'n/a', 'na', 'none' ), true ) ) {
			$inches = '';
		}

		if ( '' !== $feet && '' !== $inches ) {
			return $feet . "' " . $inches . '"';
		}

		if ( '' !== $feet ) {
			return $feet . "'";
		}

		if ( '' !== $inches ) {
			return $inches . '"';
		}

		return '';
	};

	$valid_fields = array();
	$garage_type_is_none = false;

	foreach ( $field_objects as $field_object ) {
		if ( ! is_array( $field_object ) ) {
			continue;
		}

		$field_name  = isset( $field_object['name'] ) ? trim( (string) $field_object['name'] ) : '';
		$field_type  = isset( $field_object['type'] ) ? (string) $field_object['type'] : '';
		$field_label = isset( $field_object['label'] ) ? trim( (string) $field_object['label'] ) : '';
		$field_value = array_key_exists( 'value', $field_object ) ? $field_object['value'] : null;

		if ( '' === $field_name || '' === $field_label ) {
			continue;
		}

		if ( $is_excluded_field( $field_name, $field_label ) ) {
			continue;
		}

		if ( null === $field_value || '' === $field_value || '-' === $field_value ) {
			continue;
		}

		if ( is_array( $field_value ) || is_object( $field_value ) ) {
			continue;
		}

		if ( in_array( $field_type, array( 'image', 'gallery' ), true ) ) {
			continue;
		}

		if ( ! is_scalar( $field_value ) ) {
			continue;
		}

		$display_value = trim( (string) $field_value );
		if ( '' === $display_value ) {
			continue;
		}

		if ( 'Y:Yes' === $display_value ) {
			$display_value = 'Yes';
		} elseif ( 'N:No' === $display_value ) {
			$display_value = 'No';
		} elseif ( '1:Yes' === $display_value ) {
			$display_value = 'Yes';
		} elseif ( '0:No' === $display_value ) {
			$display_value = 'No';
		} elseif ( 'Y' === strtoupper( $display_value ) ) {
			$display_value = 'yes';
		} elseif ( 'N' === strtoupper( $display_value ) ) {
			$display_value = 'no';
		}

		$is_garage_type_field = ( false !== stripos( $field_name, 'garage_type' ) ) || ( false !== stripos( $field_name, 'garage-type' ) ) || ( false !== stripos( $field_label, 'garage type' ) );
		if ( $is_garage_type_field && 'none' === strtolower( $display_value ) ) {
			$garage_type_is_none = true;
			continue;
		}

		if ( preg_match( '/^[\'\"]?-+[\'\"]?$/', $display_value ) || in_array( strtolower( $display_value ), array( 'n/a', 'na', 'none' ), true ) ) {
			continue;
		}

		$is_area_field = ( false !== stripos( $field_name, 'area' ) ) || ( false !== stripos( $field_label, 'area' ) );
		if ( $is_area_field && is_numeric( $display_value ) && (float) $display_value <= 0 ) {
			continue;
		}

		$valid_fields[ strtolower( $field_name ) ] = array(
			'name'  => $field_name,
			'label' => $field_label,
			'value' => $display_value,
		);
	}

	if ( empty( $valid_fields ) ) {
		return '';
	}

	$rows = array();
	$processed_fields = array();

	foreach ( $valid_fields as $field_key => $field_data ) {
		if ( $is_designer_plan_number_field( $field_data['name'], $field_data['label'] ) ) {
			$rows[] = array(
				'label' => $field_data['label'],
				'value' => $field_data['value'],
			);
			$processed_fields[ $field_key ] = true;
			break;
		}
	}

	foreach ( $valid_fields as $field_key => $field_data ) {
		if ( isset( $processed_fields[ $field_key ] ) ) {
			continue;
		}

		$field_name = isset( $field_data['name'] ) ? (string) $field_data['name'] : '';
		$field_label = isset( $field_data['label'] ) ? (string) $field_data['label'] : '';
		$is_garage_bays_field = ( false !== stripos( $field_name, 'garage_bays' ) ) || ( false !== stripos( $field_name, 'garage-bays' ) ) || ( false !== stripos( $field_name, 'per_unit_garage_bays' ) ) || ( false !== stripos( $field_name, 'per-unit-garage-bays' ) ) || ( false !== stripos( $field_label, 'garage bays' ) );
		$is_garage_type_field = ( false !== stripos( $field_name, 'garage_type' ) ) || ( false !== stripos( $field_name, 'garage-type' ) ) || ( false !== stripos( $field_label, 'garage type' ) );
		if ( $garage_type_is_none && ( $is_garage_type_field || $is_garage_bays_field ) ) {
			continue;
		}
		if ( preg_match( '/^(.*?)([-_])(feet|inches)$/i', $field_name, $matches ) ) {
			$field_stem         = trim( (string) $matches[1] );
			$field_separator    = $matches[2];
			$field_suffix       = strtolower( (string) $matches[3] );
			$partner_suffix     = 'feet' === $field_suffix ? 'inches' : 'feet';
			$partner_field_key  = strtolower( $field_stem . $field_separator . $partner_suffix );
			$partner_field_data = isset( $valid_fields[ $partner_field_key ] ) ? $valid_fields[ $partner_field_key ] : null;
			$combined_label     = $clean_dimension_label( isset( $field_data['label'] ) ? $field_data['label'] : '' );

			if ( '' === $combined_label && $partner_field_data ) {
				$combined_label = $clean_dimension_label( isset( $partner_field_data['label'] ) ? $partner_field_data['label'] : '' );
			}

			if ( $partner_field_data ) {
				$feet_value = 'feet' === $field_suffix ? $field_data['value'] : $partner_field_data['value'];
				$inch_value = 'feet' === $field_suffix ? $partner_field_data['value'] : $field_data['value'];
				$combined_value = $format_dimension_value( $feet_value, $inch_value );

				if ( '' !== $combined_value ) {
					$rows[] = array(
						'label' => '' !== $combined_label ? $combined_label : $field_stem,
						'value' => $combined_value,
					);
				}

				$processed_fields[ $field_key ] = true;
				$processed_fields[ $partner_field_key ] = true;
				continue;
			}

			$single_value = $format_dimension_value(
				'feet' === $field_suffix ? $field_data['value'] : '',
				'inches' === $field_suffix ? $field_data['value'] : ''
			);

			if ( '' !== $single_value ) {
				$rows[] = array(
					'label' => '' !== $combined_label ? $combined_label : $field_stem,
					'value' => $single_value,
				);
			}

			$processed_fields[ $field_key ] = true;
			continue;
		}

		$rows[] = array(
			'label' => $field_data['label'],
			'value' => $field_data['value'],
		);
		$processed_fields[ $field_key ] = true;
	}

	if ( empty( $rows ) ) {
		return '';
	}

	$halfway_index = (int) ceil( count( $rows ) / 2 );
	$left_rows     = array_slice( $rows, 0, $halfway_index );
	$right_rows    = array_slice( $rows, $halfway_index );

	ob_start();
	?>
	<section class="product-acf-fields" aria-label="<?php esc_attr_e( 'Product details', 'garnernewtheme' ); ?>">
		<div class="product-acf-fields__grid">
			<table class="product-acf-fields__table">
				<tbody>
					<?php foreach ( $left_rows as $row ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
							<td><?php echo esc_html( $row['value'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<table class="product-acf-fields__table">
				<tbody>
					<?php foreach ( $right_rows as $row ) : ?>
						<tr>
							<th scope="row"><?php echo esc_html( $row['label'] ); ?></th>
							<td><?php echo esc_html( $row['value'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
	<?php

	return ob_get_clean();
}

/**
 * Normalize an ACF image field into a renderable image payload.
 *
 * @param mixed  $field_value ACF image field value.
 * @param string $fallback_alt Optional fallback alt text.
 * @return array<string, string>
 */
function garnernewtheme_normalize_acf_image_field( $field_value, $fallback_alt = '' ) {
	$image = array(
		'url'   => '',
		'alt'   => '',
		'label' => '',
	);

	if ( is_array( $field_value ) ) {
		if ( ! empty( $field_value['url'] ) ) {
			$image['url'] = esc_url_raw( $field_value['url'] );
		}

		if ( ! empty( $field_value['alt'] ) ) {
			$image['alt'] = sanitize_text_field( $field_value['alt'] );
		}

		if ( ! empty( $field_value['title'] ) ) {
			$image['label'] = sanitize_text_field( $field_value['title'] );
		}

		if ( ! $image['url'] && ! empty( $field_value['ID'] ) ) {
			$url = wp_get_attachment_image_url( absint( $field_value['ID'] ), 'large' );
			if ( $url ) {
				$image['url'] = esc_url_raw( $url );
			}

			$alt = get_post_meta( absint( $field_value['ID'] ), '_wp_attachment_image_alt', true );
			if ( $alt ) {
				$image['alt'] = sanitize_text_field( $alt );
			}
		}
	} elseif ( is_numeric( $field_value ) ) {
		$url = wp_get_attachment_image_url( absint( $field_value ), 'large' );
		if ( $url ) {
			$image['url'] = esc_url_raw( $url );
		}

		$alt = get_post_meta( absint( $field_value ), '_wp_attachment_image_alt', true );
		if ( $alt ) {
			$image['alt'] = sanitize_text_field( $alt );
		}
	} elseif ( is_string( $field_value ) && filter_var( $field_value, FILTER_VALIDATE_URL ) ) {
		$image['url'] = esc_url_raw( $field_value );
	}

	if ( '' === $image['alt'] ) {
		$image['alt'] = sanitize_text_field( $fallback_alt );
	}

	return $image;
}

/**
 * Get the floorplan image fields for a product.
 *
 * @param int $product_id Product ID.
 * @return array<int, array<string, string>>
 */
function garnernewtheme_get_floorplan_images( $product_id ) {
	if ( ! function_exists( 'get_field' ) ) {
		return array();
	}

	$field_candidates = array(
		array( 'plan_image_1', 'plan-image-1', 'plan_image1', 'mf-plan_image_1', 'mf-plan-image-1', 'mf_plan_image_1' ),
		array( 'plan_image_2', 'plan-image-2', 'plan_image2', 'mf-plan_image_2', 'mf-plan-image-2', 'mf_plan_image_2' ),
		array( 'plan_image_3', 'plan-image-3', 'plan_image3', 'mf-plan_image_3', 'mf-plan-image-3', 'mf_plan_image_3' ),
	);
	$images = array();
	$seen_urls = array();

	foreach ( $field_candidates as $index => $candidate_group ) {
		$image = array();

		foreach ( $candidate_group as $field_name ) {
			$field_value = get_field( $field_name, $product_id );
			$image       = garnernewtheme_normalize_acf_image_field( $field_value, sprintf( __( 'Floorplan %d', 'garnernewtheme' ), $index + 1 ) );

			if ( ! empty( $image['url'] ) ) {
				break;
			}
		}

		if ( ! empty( $image['url'] ) ) {
			if ( isset( $seen_urls[ $image['url'] ] ) ) {
				continue;
			}

			$seen_urls[ $image['url'] ] = true;
			$image['label'] = sprintf( __( 'Floorplan %d', 'garnernewtheme' ), $index + 1 );
			$images[]       = $image;
		}
	}

	return $images;
}

/**
 * Render a floorplans image carousel tab for the current product.
 *
 * @return string
 */
function garnernewtheme_render_floorplans_tab_content() {
	$images = garnernewtheme_get_floorplan_images( get_the_ID() );

	if ( empty( $images ) ) {
		return '';
	}

	$product_title = get_the_title();

	ob_start();
	?>
	<section class="product-floorplans" data-carousel="floorplans" aria-label="<?php esc_attr_e( 'Floorplans', 'garnernewtheme' ); ?>">
		<div class="product-floorplans__carousel">
			<div class="product-floorplans__viewport">
				<?php foreach ( $images as $index => $image ) : ?>
					<button
						type="button"
						class="product-floorplans__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-floorplans-slide
						data-floorplans-index="<?php echo esc_attr( (string) $index ); ?>"
						data-floorplans-full="<?php echo esc_url( $image['url'] ); ?>"
						data-floorplans-caption="<?php echo esc_attr( $image['label'] ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Open %s in lightbox', 'garnernewtheme' ), $image['label'] ) ); ?>"
					>
						<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
					</button>
				<?php endforeach; ?>
			</div>

			<?php if ( count( $images ) > 1 ) : ?>
				<button class="product-floorplans__nav product-floorplans__nav--prev" type="button" data-floorplans-prev aria-label="<?php esc_attr_e( 'Previous floorplan', 'garnernewtheme' ); ?>">&larr;</button>
				<button class="product-floorplans__nav product-floorplans__nav--next" type="button" data-floorplans-next aria-label="<?php esc_attr_e( 'Next floorplan', 'garnernewtheme' ); ?>">&rarr;</button>
			<?php endif; ?>
		</div>

		<?php if ( count( $images ) > 1 ) : ?>
			<div class="product-floorplans__dots" aria-label="<?php esc_attr_e( 'Floorplan carousel pagination', 'garnernewtheme' ); ?>">
				<?php foreach ( $images as $index => $image ) : ?>
					<button
						type="button"
						class="product-floorplans__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-floorplans-dot
						data-floorplans-index="<?php echo esc_attr( (string) $index ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'View floorplan %d', 'garnernewtheme' ), $index + 1 ) ); ?>"
						aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="product-floorplans__caption">
			<strong><?php echo esc_html( $product_title ); ?></strong>
			<span data-floorplans-caption-text><?php echo esc_html( $images[0]['label'] ); ?></span>
		</div>

		<div class="product-floorplans__lightbox" data-floorplans-dialog hidden>
			<div class="product-floorplans__lightbox-backdrop" data-floorplans-close></div>
			<div class="product-floorplans__lightbox-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Floorplan lightbox', 'garnernewtheme' ); ?>">
				<button class="product-floorplans__lightbox-close" type="button" data-floorplans-close aria-label="<?php esc_attr_e( 'Close lightbox', 'garnernewtheme' ); ?>">&times;</button>
				<div class="product-floorplans__lightbox-frame">
					<img data-floorplans-lightbox-image src="<?php echo esc_url( $images[0]['url'] ); ?>" alt="<?php echo esc_attr( $images[0]['alt'] ); ?>">
				</div>
				<p class="product-floorplans__lightbox-caption" data-floorplans-lightbox-caption><?php echo esc_html( $images[0]['label'] ); ?></p>
				<div class="product-floorplans__lightbox-nav">
					<button class="product-floorplans__nav" type="button" data-floorplans-lightbox-prev aria-label="<?php esc_attr_e( 'Previous floorplan image', 'garnernewtheme' ); ?>">&larr;</button>
					<button class="product-floorplans__nav" type="button" data-floorplans-lightbox-next aria-label="<?php esc_attr_e( 'Next floorplan image', 'garnernewtheme' ); ?>">&rarr;</button>
				</div>
			</div>
		</div>
	</section>
	<?php

	return ob_get_clean();
}

/**
 * Add a custom ACF details tab to product pages.
 *
 * @param array $tabs Existing WooCommerce tabs.
 * @return array
 */
function garnernewtheme_add_acf_product_tab( $tabs ) {
	$acf_content = acf_field_display();
	$floorplans_content = garnernewtheme_render_floorplans_tab_content();

	if ( '' === $acf_content && '' === $floorplans_content ) {
		return $tabs;
	}

	if ( '' !== $floorplans_content ) {
		$tabs['acf_product_floorplans'] = array(
			'title'    => __( 'Floorplans', 'garnernewtheme' ),
			'priority' => 26,
			'callback' => 'garnernewtheme_render_floorplans_tab',
		);
	}

	$tabs['acf_product_details'] = array(
		'title'    => __( 'Plan Details', 'garnernewtheme' ),
		'priority' => 25,
		'callback' => 'garnernewtheme_render_acf_product_tab',
	);

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'garnernewtheme_add_acf_product_tab', 20 );

/**
 * Render the ACF product details tab.
 *
 * @return void
 */
function garnernewtheme_render_acf_product_tab() {
	echo acf_field_display();
}

/**
 * Render the floorplans product tab.
 *
 * @return void
 */
function garnernewtheme_render_floorplans_tab() {
	echo garnernewtheme_render_floorplans_tab_content();
}
