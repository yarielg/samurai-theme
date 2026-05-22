<?php
/**
 * Homepage — Fire Deals section.
 *
 * Queries products that belong to any term in the `fire-deal` taxonomy.
 * Renders with an orange/fire-themed treatment.
 * Respects WooCommerce prices and visibility rules — never calculates
 * or displays discounts manually.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_product' ) || ! taxonomy_exists( 'fire-deal' ) ) {
	return;
}

$sf_heading = samurai_option_text( 'home_deals_heading', __( '🔥 Fire Deals', 'samurai' ), false );
$sf_subline = samurai_option_text( 'home_deals_subline', __( 'Limited-time deals on our best sellers.', 'samurai' ), false );

$sf_deals_query = new WP_Query( [
	'post_type'           => 'product',
	'posts_per_page'      => 6,
	'post_status'         => 'publish',
	'ignore_sticky_posts' => true,
	'tax_query'           => [ // phpcs:ignore WordPress.DB.SlowDBQuery
		[
			'taxonomy' => 'fire-deal',
			'operator' => 'EXISTS',
		],
	],
] );

if ( ! $sf_deals_query->have_posts() ) {
	return;
}

// Get the archive URL for the fire-deal taxonomy.
$sf_deals_url = samurai_tax_archive_url( 'fire-deal' );
?>
<section class="sf-home-section sf-deals-section" aria-label="<?php esc_attr_e( 'Fire Deals', 'samurai' ); ?>">

	<div class="sf-deals-section__header">
		<div class="sf-container sf-deals-section__header-inner">
			<div class="sf-deals-section__title-wrap">
				<h2 class="sf-deals-section__heading"><?php echo esc_html( $sf_heading ); ?></h2>
				<?php if ( $sf_subline ) : ?>
				<p class="sf-deals-section__subline"><?php echo esc_html( $sf_subline ); ?></p>
				<?php endif; ?>
			</div>
			<a href="<?php echo esc_url( $sf_deals_url ); ?>" class="sf-btn sf-btn--secondary sf-deals-section__cta">
				<?php esc_html_e( 'All Fire Deals', 'samurai' ); ?>
			</a>
		</div>
	</div>

	<div class="sf-container">
		<div class="sf-product-grid sf-product-grid--3">
			<?php
			while ( $sf_deals_query->have_posts() ) :
				$sf_deals_query->the_post();
				$sf_product = wc_get_product( get_the_ID() );
				if ( ! $sf_product || ! $sf_product->is_visible() ) {
					continue;
				}
				get_template_part( 'template-parts/product/product-card', null, [
					'product'    => $sf_product,
					'show_brand' => false,
					'card_class' => 'sf-product-card--deals',
				] );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>

</section>
