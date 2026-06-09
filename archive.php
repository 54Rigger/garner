<?php

/**
 * Archive template.
 *
 * @package garnernewtheme
 */

get_header();

$queried_object        = get_queried_object();
$breadcrumb_items      = array();
$child_category_terms  = array();
$child_selector_label  = __('Jump to child category', 'garnernewtheme');

$breadcrumb_items[] = array(
	'label' => __('Home', 'garnernewtheme'),
	'url'   => home_url('/'),
);

if (is_tax() || is_category()) {
	$term = $queried_object instanceof WP_Term ? $queried_object : null;

	if ($term instanceof WP_Term) {
		$taxonomy_object = get_taxonomy($term->taxonomy);

		if ($taxonomy_object && ! empty($taxonomy_object->labels->name)) {
			$taxonomy_archive_link = get_post_type_archive_link('product');
			if ('category' === $term->taxonomy) {
				$taxonomy_archive_link = get_permalink(get_option('page_for_posts'));
			}

			$breadcrumb_items[] = array(
				'label' => $taxonomy_object->labels->name,
				'url'   => $taxonomy_archive_link,
			);
		}

		$ancestor_ids = array_reverse(get_ancestors($term->term_id, $term->taxonomy));
		foreach ($ancestor_ids as $ancestor_id) {
			$ancestor_term = get_term($ancestor_id, $term->taxonomy);
			if ($ancestor_term instanceof WP_Term) {
				$breadcrumb_items[] = array(
					'label' => $ancestor_term->name,
					'url'   => get_term_link($ancestor_term),
				);
			}
		}

		$breadcrumb_items[] = array(
			'label' => single_term_title('', false),
			'url'   => '',
		);

		if ($taxonomy_object && ! empty($taxonomy_object->hierarchical)) {
			$child_category_terms = get_terms(
				array(
					'taxonomy'   => $term->taxonomy,
					'parent'     => $term->term_id,
					'hide_empty' => false,
					'orderby'    => 'name',
					'order'      => 'ASC',
				)
			);

			if ('product_cat' === $term->taxonomy) {
				$child_selector_label = __('Jump to child plan category', 'garnernewtheme');
			}
		}
	}
} else {
	$breadcrumb_items[] = array(
		'label' => __('Archive', 'garnernewtheme'),
		'url'   => '',
	);
}
?>
<main id="main-content" class="content-shell container content-shell--default">
	<nav class="archive-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'garnernewtheme'); ?>">
		<ol>
			<?php foreach ($breadcrumb_items as $breadcrumb_index => $breadcrumb_item) : ?>
				<li>
					<?php if (! empty($breadcrumb_item['url']) && $breadcrumb_index < count($breadcrumb_items) - 1) : ?>
						<a href="<?php echo esc_url($breadcrumb_item['url']); ?>"><?php echo esc_html($breadcrumb_item['label']); ?></a>
					<?php else : ?>
						<span><?php echo esc_html($breadcrumb_item['label']); ?></span>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>

	<?php if (! is_wp_error($child_category_terms) && ! empty($child_category_terms)) : ?>
		<div class="archive-child-category-jump">
			<label for="archive-child-category-select"><?php echo esc_html($child_selector_label); ?></label>
			<select id="archive-child-category-select" onchange="if (this.value) { window.location.href = this.value; }">
				<option value=""><?php esc_html_e('Select a category', 'garnernewtheme'); ?></option>
				<?php foreach ($child_category_terms as $child_category_term) : ?>
					<?php $child_term_link = get_term_link($child_category_term); ?>
					<?php if (! is_wp_error($child_term_link)) : ?>
						<option value="<?php echo esc_url($child_term_link); ?>"><?php echo esc_html($child_category_term->name); ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>
	<?php endif; ?>

	<header class="archive-header">
		<h1><?php the_archive_title(); ?></h1>
		<?php the_archive_description('<div class="archive-description">', '</div>'); ?>
	</header>

	<?php if (have_posts()) : ?>
		<?php while (have_posts()) : the_post(); ?>
			<article <?php post_class('entry-card'); ?>>
				<h2 class="entry-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<div class="entry-card__excerpt"><?php the_excerpt(); ?></div>
			</article>
		<?php endwhile; ?>
		<?php
		the_posts_pagination(
			array(
				'mid_size'  => 1,
				'prev_text' => '&#171;',
				'next_text' => '&#187;',
			)
		);
		?>
	<?php else : ?>
		<p><?php esc_html_e('No content found.', 'garnernewtheme'); ?></p>
	<?php endif; ?>
</main>
<?php
get_footer();
