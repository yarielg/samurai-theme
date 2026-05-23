<?php
/**
 * Homepage — Shop by Category section.
 *
 * Queries top-level WooCommerce product categories and displays them
 * as image cards linking to their archive pages.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_selected_ids = function_exists( 'get_field' ) ? get_field( 'home_featured_categories', 'option' ) : [];
$sf_selected_ids = ! empty( $sf_selected_ids ) ? array_map( 'absint', (array) $sf_selected_ids ) : [];

if ( ! empty( $sf_selected_ids ) ) {
	// Admin-picked categories — preserve the chosen order.
	$sf_cats = get_terms( [
		'taxonomy'   => 'product_cat',
		'include'    => $sf_selected_ids,
		'hide_empty' => false,
		'orderby'    => 'include',
	] );
} else {
	// Fallback: all top-level categories by menu order.
	$sf_cats = get_terms( [
		'taxonomy'   => 'product_cat',
		'parent'     => 0,
		'hide_empty' => true,
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'number'     => 8,
	] );
	$sf_cats = array_filter( $sf_cats, function ( $term ) {
		return 'uncategorized' !== $term->slug;
	} );
}

if ( empty( $sf_cats ) || is_wp_error( $sf_cats ) ) {
	return;
}

?>
<section class="sf-home-section sf-categories-section" aria-label="<?php esc_attr_e( 'Shop by Category', 'samurai' ); ?>">
	<div class="sf-container">

		<div class="sf-section-header">
			<h2 class="sf-section-heading"><?php echo esc_html( samurai_option_text( 'home_categories_heading', __( 'Shop by Category', 'samurai' ), false ) ); ?></h2>
			<a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="sf-section-link">
				<?php esc_html_e( 'All Categories', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>

		<div class="sf-cat-grid">
			<?php foreach ( $sf_cats as $sf_cat ) :
				$sf_cat_link     = get_term_link( $sf_cat );
				$sf_thumbnail_id = get_term_meta( $sf_cat->term_id, 'thumbnail_id', true );
				$sf_cat_name     = $sf_cat->name;
				$sf_count        = $sf_cat->count;
				?>
			<a href="<?php echo esc_url( $sf_cat_link ); ?>"
			   class="sf-cat-card"
			   aria-label="<?php echo esc_attr( sprintf( /* translators: %s: category name */ __( 'Shop %s', 'samurai' ), $sf_cat_name ) ); ?>">

				<div class="sf-cat-card__media">
					<?php if ( $sf_thumbnail_id ) : ?>
						<?php echo wp_get_attachment_image( $sf_thumbnail_id, 'samurai-taxonomy', false, [
							'class'   => 'sf-cat-card__img',
							'loading' => 'lazy',
							'alt'     => esc_attr( $sf_cat_name ),
						] ); ?>
					<?php else : ?>
						<div class="sf-cat-card__placeholder" aria-hidden="true">
							<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
						</div>
					<?php endif; ?>
					<div class="sf-cat-card__overlay" aria-hidden="true"></div>
				</div>

				<div class="sf-cat-card__info">
					<span class="sf-cat-card__name"><?php echo esc_html( $sf_cat_name ); ?></span>
					<?php if ( $sf_count > 0 ) : ?>
					<span class="sf-cat-card__count">
						<?php echo esc_html( sprintf( /* translators: %d: product count */ _n( '%d item', '%d items', $sf_count, 'samurai' ), $sf_count ) ); ?>
					</span>
					<?php endif; ?>
				</div>

			</a>
			<?php endforeach; ?>
		</div>

	</div>
</section>
