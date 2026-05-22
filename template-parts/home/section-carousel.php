<?php
/**
 * Homepage — Category Product Carousel.
 * Configure in Theme Settings → Homepage.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_heading = samurai_option_text( 'home_carousel_heading', __( 'Shop the Collection', 'samurai' ), false );
$sf_cat_id  = function_exists( 'get_field' ) ? absint( get_field( 'home_carousel_category', 'option' ) ) : 0;
$sf_count   = function_exists( 'get_field' ) ? absint( get_field( 'home_carousel_count', 'option' ) ) : 12;
$sf_count   = ( $sf_count >= 4 && $sf_count <= 24 ) ? $sf_count : 12;

$sf_cat_term = $sf_cat_id ? get_term( $sf_cat_id, 'product_cat' ) : null;
$sf_cat_name = ( $sf_cat_term && ! is_wp_error( $sf_cat_term ) ) ? $sf_cat_term->name : '';
$sf_cat_link = '';
if ( $sf_cat_id ) {
	$sf_link = get_term_link( $sf_cat_id, 'product_cat' );
	if ( ! is_wp_error( $sf_link ) ) {
		$sf_cat_link = $sf_link;
	}
}

$sf_args = [
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => $sf_count,
	'orderby'        => 'date',
	'order'          => 'DESC',
];

if ( $sf_cat_id ) {
	$sf_args['tax_query'] = [
		[
			'taxonomy'         => 'product_cat',
			'field'            => 'term_id',
			'terms'            => $sf_cat_id,
			'include_children' => true,
		],
	];
}

$sf_loop = new WP_Query( $sf_args );
if ( ! $sf_loop->have_posts() ) {
	return;
}
?>
<section class="sf-home-section sf-carousel-section" aria-label="<?php echo esc_attr( $sf_heading ); ?>">
	<div class="sf-container sf-carousel-container">

		<div class="sf-section-header sf-carousel__header">
			<div class="sf-carousel__title-group">
				<?php if ( $sf_cat_name ) : ?>
				<p class="sf-carousel__eyebrow">
					<span class="sf-carousel__eyebrow-line" aria-hidden="true"></span>
					<?php echo esc_html( $sf_cat_name ); ?>
				</p>
				<?php endif; ?>
				<h2 class="sf-section-heading sf-carousel__heading"><?php echo esc_html( $sf_heading ); ?></h2>
			</div>
			<?php if ( $sf_cat_link ) : ?>
			<a href="<?php echo esc_url( $sf_cat_link ); ?>" class="sf-section-link sf-carousel__view-all">
				<?php esc_html_e( 'View All', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
			<?php endif; ?>
		</div>

		<div class="sf-carousel-wrap js-carousel">

			<button type="button"
			        class="sf-carousel-arrow sf-carousel-arrow--prev js-carousel-prev"
			        aria-label="<?php esc_attr_e( 'Scroll left', 'samurai' ); ?>"
			        hidden>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
			</button>

			<div class="sf-carousel-track" role="list">
				<?php while ( $sf_loop->have_posts() ) :
					$sf_loop->the_post();
					global $product;
					if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
						$product = wc_get_product( get_the_ID() );
					}
					if ( ! $product ) continue;
					?>
				<div class="sf-carousel-item" role="listitem">
					<?php get_template_part( 'template-parts/product/product-card', null, [ 'product' => $product ] ); ?>
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
