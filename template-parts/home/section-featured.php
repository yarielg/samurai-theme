<?php
/**
 * Homepage — Trending Now product carousel.
 *
 * Priority order:
 *   A. ACF manual override (home_featured_ids option — post_object field)
 *   B. WooCommerce "featured" products
 *   C. Latest 8 products (fallback so the section never appears empty)
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_product' ) ) {
	return;
}

$sf_heading    = samurai_option_text( 'home_featured_heading', __( 'Trending Now', 'samurai' ), false );
$sf_subheading = samurai_option_text( 'home_featured_subheading', __( "Miami's hottest picks — flying off the shelves.", 'samurai' ), false );

// ── Query products ─────────────────────────────────────────────────────────

$sf_override_ids = function_exists( 'get_field' ) ? get_field( 'home_featured_ids', 'option' ) : null;
if ( ! empty( $sf_override_ids ) ) {
	if ( ! is_array( $sf_override_ids ) ) {
		$sf_override_ids = [ $sf_override_ids ];
	}
	$sf_query_args = [
		'post_type'      => 'product',
		'posts_per_page' => 12,
		'post__in'       => array_map( 'absint', $sf_override_ids ),
		'orderby'        => 'post__in',
		'post_status'    => 'publish',
	];
} else {
	$sf_query_args = [
		'post_type'           => 'product',
		'posts_per_page'      => 12,
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'tax_query'           => [ // phpcs:ignore WordPress.DB.SlowDBQuery
			[
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			],
		],
	];
}

$sf_query = new WP_Query( $sf_query_args );

// Fallback: latest products so the section always renders.
if ( ! $sf_query->have_posts() ) {
	$sf_query = new WP_Query( [
		'post_type'           => 'product',
		'posts_per_page'      => 12,
		'post_status'         => 'publish',
		'orderby'             => 'date',
		'order'               => 'DESC',
		'ignore_sticky_posts' => true,
	] );
}

if ( ! $sf_query->have_posts() ) {
	return;
}

$sf_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<section class="sf-home-section sf-featured-section" aria-label="<?php echo esc_attr( $sf_heading ); ?>">
	<div class="sf-container sf-carousel-container">

		<div class="sf-section-header sf-carousel__header">
			<div class="sf-carousel__title-group">
				<p class="sf-carousel__eyebrow">
					<span class="sf-carousel__eyebrow-line" aria-hidden="true"></span>
					<?php esc_html_e( 'Hot Right Now', 'samurai' ); ?>
				</p>
				<h2 class="sf-section-heading sf-carousel__heading"><?php echo esc_html( $sf_heading ); ?></h2>
				<?php if ( $sf_subheading ) : ?>
				<p class="sf-featured__subheading"><?php echo esc_html( $sf_subheading ); ?></p>
				<?php endif; ?>
			</div>
			<a href="<?php echo esc_url( $sf_shop_url ); ?>" class="sf-section-link sf-carousel__view-all">
				<?php esc_html_e( 'Shop All', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>

		<div class="sf-carousel-wrap js-carousel">

			<button type="button"
			        class="sf-carousel-arrow sf-carousel-arrow--prev js-carousel-prev"
			        aria-label="<?php esc_attr_e( 'Scroll left', 'samurai' ); ?>"
			        hidden>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
			</button>

			<div class="sf-carousel-track" role="list">
				<?php while ( $sf_query->have_posts() ) :
					$sf_query->the_post();
					$sf_product = wc_get_product( get_the_ID() );
					if ( ! $sf_product || ! $sf_product->is_visible() ) {
						continue;
					}
					?>
				<div class="sf-carousel-item" role="listitem">
					<?php get_template_part( 'template-parts/product/product-card', null, [ 'product' => $sf_product ] ); ?>
				</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<button type="button"
			        class="sf-carousel-arrow sf-carousel-arrow--next js-carousel-next"
			        aria-label="<?php esc_attr_e( 'Scroll right', 'samurai' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
			</button>

		</div>

		<div class="sf-carousel-progress-wrap" aria-hidden="true">
			<div class="sf-carousel-progress-bar js-carousel-bar"></div>
		</div>

	</div>
</section>
