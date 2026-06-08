<?php

function acf_field_display()
{
	if (! function_exists('get_field_objects') || ! is_singular('product')) {
		return '';
	}

	$field_objects = get_field_objects(get_the_ID());
	if (empty($field_objects) || ! is_array($field_objects)) {
		return '';
	}

	$clean_dimension_label = static function ($label) {
		$label = trim((string) $label);
		$label = preg_replace('/\s*(Feet|Inches)\s*$/i', '', $label);

		return trim((string) $label);
	};

	$normalize_field_key = static function ($value) {
		$value = strtolower(trim((string) $value));
		$value = preg_replace('/[^a-z0-9]+/', '_', $value);
		$value = trim((string) $value, '_');

		return $value;
	};

	$is_excluded_field = static function ($field_name, $field_label) use ($normalize_field_key) {
		$normalized_name  = $normalize_field_key($field_name);
		$normalized_label = $normalize_field_key($field_label);
		$excluded_keys    = array('plan_number', 'index_plan_number', 'sd_id', 'plantype', 'approved', 'c');

		if (in_array($normalized_name, $excluded_keys, true) || in_array($normalized_label, $excluded_keys, true)) {
			return true;
		}

		return false;
	};

	$is_designer_plan_number_field = static function ($field_name, $field_label) use ($normalize_field_key) {
		$normalized_name  = $normalize_field_key($field_name);
		$normalized_label = $normalize_field_key($field_label);

		return in_array('designer_plan_number', array($normalized_name, $normalized_label), true);
	};

	$format_dimension_value = static function ($feet, $inches) {
		$feet   = trim((string) $feet);
		$inches = trim((string) $inches);

		if (preg_match('/^[\'\"]?-+[\'\"]?$/', $feet) || in_array(strtolower($feet), array('n/a', 'na', 'none'), true)) {
			$feet = '';
		}

		if (preg_match('/^[\'\"]?-+[\'\"]?$/', $inches) || in_array(strtolower($inches), array('n/a', 'na', 'none'), true)) {
			$inches = '';
		}

		if ('' !== $feet && '' !== $inches) {
			return $feet . "' " . $inches . '"';
		}

		if ('' !== $feet) {
			return $feet . "'";
		}

		if ('' !== $inches) {
			return $inches . '"';
		}

		return '';
	};

	$valid_fields = array();
	$garage_type_is_none = false;

	foreach ($field_objects as $field_object) {
		if (! is_array($field_object)) {
			continue;
		}

		$field_name  = isset($field_object['name']) ? trim((string) $field_object['name']) : '';
		$field_type  = isset($field_object['type']) ? (string) $field_object['type'] : '';
		$field_label = isset($field_object['label']) ? trim((string) $field_object['label']) : '';
		$field_value = array_key_exists('value', $field_object) ? $field_object['value'] : null;

		if ('' === $field_name || '' === $field_label) {
			continue;
		}

		if ($is_excluded_field($field_name, $field_label)) {
			continue;
		}

		if (null === $field_value || '' === $field_value || '-' === $field_value) {
			continue;
		}

		if (is_array($field_value) || is_object($field_value)) {
			continue;
		}

		if (in_array($field_type, array('image', 'gallery'), true)) {
			continue;
		}

		if (! is_scalar($field_value)) {
			continue;
		}

		$display_value = trim((string) $field_value);
		if ('' === $display_value) {
			continue;
		}

		if ('Y:Yes' === $display_value) {
			$display_value = 'Yes';
		} elseif ('N:No' === $display_value) {
			$display_value = 'No';
		} elseif ('1:Yes' === $display_value) {
			$display_value = 'Yes';
		} elseif ('0:No' === $display_value) {
			$display_value = 'No';
		} elseif ('Y' === strtoupper($display_value)) {
			$display_value = 'yes';
		} elseif ('N' === strtoupper($display_value)) {
			$display_value = 'no';
		}

		$is_garage_type_field = (false !== stripos($field_name, 'garage_type')) || (false !== stripos($field_name, 'garage-type')) || (false !== stripos($field_label, 'garage type'));
		if ($is_garage_type_field && 'none' === strtolower($display_value)) {
			$garage_type_is_none = true;
			continue;
		}

		if (preg_match('/^[\'\"]?-+[\'\"]?$/', $display_value) || in_array(strtolower($display_value), array('n/a', 'na', 'none'), true)) {
			continue;
		}

		$is_area_field = (false !== stripos($field_name, 'area')) || (false !== stripos($field_label, 'area'));
		if ($is_area_field && is_numeric($display_value) && (float) $display_value <= 0) {
			continue;
		}

		$valid_fields[strtolower($field_name)] = array(
			'name'  => $field_name,
			'label' => $field_label,
			'value' => $display_value,
		);
	}

	if (empty($valid_fields)) {
		return '';
	}

	$rows = array();
	$processed_fields = array();

	foreach ($valid_fields as $field_key => $field_data) {
		if ($is_designer_plan_number_field($field_data['name'], $field_data['label'])) {
			$rows[] = array(
				'label' => $field_data['label'],
				'value' => $field_data['value'],
			);
			$processed_fields[$field_key] = true;
			break;
		}
	}

	foreach ($valid_fields as $field_key => $field_data) {
		if (isset($processed_fields[$field_key])) {
			continue;
		}

		$field_name = isset($field_data['name']) ? (string) $field_data['name'] : '';
		$field_label = isset($field_data['label']) ? (string) $field_data['label'] : '';
		$is_garage_bays_field = (false !== stripos($field_name, 'garage_bays')) || (false !== stripos($field_name, 'garage-bays')) || (false !== stripos($field_name, 'per_unit_garage_bays')) || (false !== stripos($field_name, 'per-unit-garage-bays')) || (false !== stripos($field_label, 'garage bays'));
		$is_garage_type_field = (false !== stripos($field_name, 'garage_type')) || (false !== stripos($field_name, 'garage-type')) || (false !== stripos($field_label, 'garage type'));
		if ($garage_type_is_none && ($is_garage_type_field || $is_garage_bays_field)) {
			continue;
		}
		if (preg_match('/^(.*?)([-_])(feet|inches)$/i', $field_name, $matches)) {
			$field_stem         = trim((string) $matches[1]);
			$field_separator    = $matches[2];
			$field_suffix       = strtolower((string) $matches[3]);
			$partner_suffix     = 'feet' === $field_suffix ? 'inches' : 'feet';
			$partner_field_key  = strtolower($field_stem . $field_separator . $partner_suffix);
			$partner_field_data = isset($valid_fields[$partner_field_key]) ? $valid_fields[$partner_field_key] : null;
			$combined_label     = $clean_dimension_label(isset($field_data['label']) ? $field_data['label'] : '');

			if ('' === $combined_label && $partner_field_data) {
				$combined_label = $clean_dimension_label(isset($partner_field_data['label']) ? $partner_field_data['label'] : '');
			}

			if ($partner_field_data) {
				$feet_value = 'feet' === $field_suffix ? $field_data['value'] : $partner_field_data['value'];
				$inch_value = 'feet' === $field_suffix ? $partner_field_data['value'] : $field_data['value'];
				$combined_value = $format_dimension_value($feet_value, $inch_value);

				if ('' !== $combined_value) {
					$rows[] = array(
						'label' => '' !== $combined_label ? $combined_label : $field_stem,
						'value' => $combined_value,
					);
				}

				$processed_fields[$field_key] = true;
				$processed_fields[$partner_field_key] = true;
				continue;
			}

			$single_value = $format_dimension_value(
				'feet' === $field_suffix ? $field_data['value'] : '',
				'inches' === $field_suffix ? $field_data['value'] : ''
			);

			if ('' !== $single_value) {
				$rows[] = array(
					'label' => '' !== $combined_label ? $combined_label : $field_stem,
					'value' => $single_value,
				);
			}

			$processed_fields[$field_key] = true;
			continue;
		}

		$rows[] = array(
			'label' => $field_data['label'],
			'value' => $field_data['value'],
		);
		$processed_fields[$field_key] = true;
	}

	if (empty($rows)) {
		return '';
	}

	$halfway_index = (int) ceil(count($rows) / 2);
	$left_rows     = array_slice($rows, 0, $halfway_index);
	$right_rows    = array_slice($rows, $halfway_index);

	ob_start();
	?>
	<section class="product-acf-fields" aria-label="<?php esc_attr_e('Product details', 'garnernewtheme'); ?>">
		<div class="product-acf-fields__grid">
			<table class="product-acf-fields__table">
				<tbody>
					<?php foreach ($left_rows as $row) : ?>
						<tr>
							<th scope="row"><?php echo esc_html($row['label']); ?></th>
							<td><?php echo esc_html($row['value']); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<table class="product-acf-fields__table">
				<tbody>
					<?php foreach ($right_rows as $row) : ?>
						<tr>
							<th scope="row"><?php echo esc_html($row['label']); ?></th>
							<td><?php echo esc_html($row['value']); ?></td>
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
function garnernewtheme_normalize_acf_image_field($field_value, $fallback_alt = '')
{
	$image = array(
		'url'   => '',
		'alt'   => '',
		'label' => '',
	);

	if (is_array($field_value)) {
		if (! empty($field_value['url'])) {
			$image['url'] = esc_url_raw($field_value['url']);
		}

		if (! empty($field_value['alt'])) {
			$image['alt'] = sanitize_text_field($field_value['alt']);
		}

		if (! empty($field_value['title'])) {
			$image['label'] = sanitize_text_field($field_value['title']);
		}

		if (! $image['url'] && ! empty($field_value['ID'])) {
			$url = wp_get_attachment_image_url(absint($field_value['ID']), 'large');
			if ($url) {
				$image['url'] = esc_url_raw($url);
			}

			$alt = get_post_meta(absint($field_value['ID']), '_wp_attachment_image_alt', true);
			if ($alt) {
				$image['alt'] = sanitize_text_field($alt);
			}
		}
	} elseif (is_numeric($field_value)) {
		$url = wp_get_attachment_image_url(absint($field_value), 'large');
		if ($url) {
			$image['url'] = esc_url_raw($url);
		}

		$alt = get_post_meta(absint($field_value), '_wp_attachment_image_alt', true);
		if ($alt) {
			$image['alt'] = sanitize_text_field($alt);
		}
	} elseif (is_string($field_value) && filter_var($field_value, FILTER_VALIDATE_URL)) {
		$image['url'] = esc_url_raw($field_value);
	}

	if ('' === $image['alt']) {
		$image['alt'] = sanitize_text_field($fallback_alt);
	}

	return $image;
}

/**
 * Get the floorplan image fields for a product.
 *
 * @param int $product_id Product ID.
 * @return array<int, array<string, string>>
 */
function garnernewtheme_get_floorplan_images($product_id)
{
	if (! function_exists('get_field')) {
		return array();
	}

	$field_candidates = array(
		array('plan_image_1', 'plan-image-1', 'plan_image1', 'mf-plan_image_1', 'mf-plan-image-1', 'mf_plan_image_1'),
		array('plan_image_2', 'plan-image-2', 'plan_image2', 'mf-plan_image_2', 'mf-plan-image-2', 'mf_plan_image_2'),
		array('plan_image_3', 'plan-image-3', 'plan_image3', 'mf-plan_image_3', 'mf-plan-image-3', 'mf_plan_image_3'),
	);
	$images = array();
	$seen_urls = array();

	foreach ($field_candidates as $index => $candidate_group) {
		$image = array();

		foreach ($candidate_group as $field_name) {
			$field_value = get_field($field_name, $product_id);
			$image       = garnernewtheme_normalize_acf_image_field($field_value, sprintf(__('Floorplan %d', 'garnernewtheme'), $index + 1));

			if (! empty($image['url'])) {
				break;
			}
		}

		if (! empty($image['url'])) {
			if (isset($seen_urls[$image['url']])) {
				continue;
			}

			$seen_urls[$image['url']] = true;
			$image['label'] = sprintf(__('Floorplan %d', 'garnernewtheme'), $index + 1);
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
function garnernewtheme_render_floorplans_tab_content()
{
	$images = garnernewtheme_get_floorplan_images(get_the_ID());

	if (empty($images)) {
		return '';
	}

	$product_title = get_the_title();

	ob_start();
?>
	<section class="product-floorplans" data-carousel="floorplans" aria-label="<?php esc_attr_e('Floorplans', 'garnernewtheme'); ?>">
		<div class="product-floorplans__carousel">
			<div class="product-floorplans__viewport">
				<?php foreach ($images as $index => $image) : ?>
					<button
						type="button"
						class="product-floorplans__slide<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-floorplans-slide
						data-floorplans-index="<?php echo esc_attr((string) $index); ?>"
						data-floorplans-full="<?php echo esc_url($image['url']); ?>"
						data-floorplans-caption="<?php echo esc_attr($image['label']); ?>"
						aria-label="<?php echo esc_attr(sprintf(__('Open %s in lightbox', 'garnernewtheme'), $image['label'])); ?>">
						<img src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>">
					</button>
				<?php endforeach; ?>
			</div>

			<?php if (count($images) > 1) : ?>
				<button class="product-floorplans__nav product-floorplans__nav--prev" type="button" data-floorplans-prev aria-label="<?php esc_attr_e('Previous floorplan', 'garnernewtheme'); ?>">&larr;</button>
				<button class="product-floorplans__nav product-floorplans__nav--next" type="button" data-floorplans-next aria-label="<?php esc_attr_e('Next floorplan', 'garnernewtheme'); ?>">&rarr;</button>
			<?php endif; ?>
		</div>

		<?php if (count($images) > 1) : ?>
			<div class="product-floorplans__dots" aria-label="<?php esc_attr_e('Floorplan carousel pagination', 'garnernewtheme'); ?>">
				<?php foreach ($images as $index => $image) : ?>
					<button
						type="button"
						class="product-floorplans__dot<?php echo 0 === $index ? ' is-active' : ''; ?>"
						data-floorplans-dot
						data-floorplans-index="<?php echo esc_attr((string) $index); ?>"
						aria-label="<?php echo esc_attr(sprintf(__('View floorplan %d', 'garnernewtheme'), $index + 1)); ?>"
						aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="product-floorplans__caption">
			<strong><?php echo esc_html($product_title); ?></strong>
			<span data-floorplans-caption-text><?php echo esc_html($images[0]['label']); ?></span>
		</div>

		<div class="product-floorplans__lightbox" data-floorplans-dialog hidden>
			<div class="product-floorplans__lightbox-backdrop" data-floorplans-close></div>
			<div class="product-floorplans__lightbox-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Floorplan lightbox', 'garnernewtheme'); ?>">
				<button class="product-floorplans__lightbox-close" type="button" data-floorplans-close aria-label="<?php esc_attr_e('Close lightbox', 'garnernewtheme'); ?>">&times;</button>
				<div class="product-floorplans__lightbox-frame">
					<img data-floorplans-lightbox-image src="<?php echo esc_url($images[0]['url']); ?>" alt="<?php echo esc_attr($images[0]['alt']); ?>">
				</div>
				<p class="product-floorplans__lightbox-caption" data-floorplans-lightbox-caption><?php echo esc_html($images[0]['label']); ?></p>
				<div class="product-floorplans__lightbox-nav">
					<button class="product-floorplans__nav" type="button" data-floorplans-lightbox-prev aria-label="<?php esc_attr_e('Previous floorplan image', 'garnernewtheme'); ?>">&larr;</button>
					<button class="product-floorplans__nav" type="button" data-floorplans-lightbox-next aria-label="<?php esc_attr_e('Next floorplan image', 'garnernewtheme'); ?>">&rarr;</button>
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
function garnernewtheme_add_acf_product_tab($tabs)
{
	$acf_content = acf_field_display();
	$floorplans_content = garnernewtheme_render_floorplans_tab_content();

	if ('' === $acf_content && '' === $floorplans_content) {
		return $tabs;
	}

	if ('' !== $floorplans_content) {
		$tabs['acf_product_floorplans'] = array(
			'title'    => __('Floorplans', 'garnernewtheme'),
			'priority' => 26,
			'callback' => 'garnernewtheme_render_floorplans_tab',
		);
	}

	$tabs['acf_product_details'] = array(
		'title'    => __('Plan Details', 'garnernewtheme'),
		'priority' => 25,
		'callback' => 'garnernewtheme_render_acf_product_tab',
	);

	return $tabs;
}
add_filter('woocommerce_product_tabs', 'garnernewtheme_add_acf_product_tab', 20);

/**
 * Render the ACF product details tab.
 *
 * @return void
 */
function garnernewtheme_render_acf_product_tab()
{
	echo acf_field_display();
}

/**
 * Render the floorplans product tab.
 *
 * @return void
 */
function garnernewtheme_render_floorplans_tab()
{
	echo garnernewtheme_render_floorplans_tab_content();
}

/**
 * Get the cookie name for guest product favorites.
 *
 * @return string
 */
