<?php
/**
 * Reusable product card.
 *
 * Usage:
 *   get_template_part( 'template-parts/product/product-card', null, [ 'product' => $wc_product ] );
 *
 * @param array $args {
 *   @type WC_Product $product     Required. WC product object.
 *   @type bool       $show_brand  Optional. Show brand term below image. Default true.
 *   @type string     $card_class  Optional. Extra CSS class on the root <article>.
 * }
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

/** @var WC_Product|null $sf_product */
$sf_product = $args['product'] ?? null;
if ( ! $sf_product instanceof WC_Product ) {
	return;
}

// Skip products without a price — not available for sale.
if ( '' === $sf_product->get_price() ) return;

$sf_show_brand  = $args['show_brand'] ?? true;
$sf_card_class  = isset( $args['card_class'] ) ? ' ' . sanitize_html_class( $args['card_class'] ) : '';

$sf_product_id   = $sf_product->get_id();
$sf_permalink    = $sf_product->get_permalink();
$sf_title        = $sf_product->get_name();
$sf_price_html   = $sf_product->get_price_html();
$sf_is_on_sale   = $sf_product->is_on_sale();
$sf_in_stock     = $sf_product->is_in_stock();
$sf_product_type = $sf_product->get_type();

// Brand
$sf_brand = false;
if ( $sf_show_brand ) {
	$sf_brand_terms = get_the_terms( $sf_product_id, 'brand' );
	if ( $sf_brand_terms && ! is_wp_error( $sf_brand_terms ) ) {
		$sf_brand = reset( $sf_brand_terms );
	}
}

// Fire deal badge
$sf_is_fire_deal = has_term( '', 'fire-deal', $sf_product_id );

// Effect taxonomy terms (shown in hover panel)
$sf_effects     = get_the_terms( $sf_product_id, 'effect' );
$sf_effects     = ( $sf_effects && ! is_wp_error( $sf_effects ) ) ? array_slice( $sf_effects, 0, 3 ) : [];

// Product gallery images for color swatches (hover panel)
$sf_image_id     = $sf_product->get_image_id();
$sf_gallery_ids  = array_slice( $sf_product->get_gallery_image_ids(), 0, 4 );
$sf_has_swatches = ! empty( $sf_gallery_ids );

// Main image
if ( $sf_image_id ) {
	$sf_image = wp_get_attachment_image( $sf_image_id, 'woocommerce_thumbnail', false, [
		'class'   => 'sf-product-card__img',
		'loading' => 'lazy',
		'alt'     => esc_attr( $sf_title ),
	] );
} else {
	$sf_image = '<img src="' . esc_url( wc_placeholder_img_src( 'woocommerce_thumbnail' ) ) . '" class="sf-product-card__img" alt="" loading="lazy" />';
}

// Add-to-cart
$sf_atc_url   = $sf_product->add_to_cart_url();
$sf_atc_text  = $sf_product->add_to_cart_text();
$sf_ajax_class = ( 'simple' === $sf_product_type && $sf_in_stock ) ? ' ajax_add_to_cart' : '';

$sf_show_hover_panel = $sf_has_swatches || ! empty( $sf_effects );
?>
<article class="sf-product-card<?php echo esc_attr( $sf_card_class ); ?>" data-product-id="<?php echo esc_attr( $sf_product_id ); ?>">

	<?php /* ── Media / Image ── */ ?>
	<a href="<?php echo esc_url( $sf_permalink ); ?>" class="sf-product-card__media-link" tabindex="-1" aria-hidden="true">
		<div class="sf-product-card__media">
			<?php echo $sf_image; // phpcs:ignore ?>

			<?php if ( $sf_is_fire_deal || $sf_is_on_sale ) : ?>
			<div class="sf-product-card__badges" aria-hidden="true">
				<?php if ( $sf_is_fire_deal ) : ?>
					<span class="sf-badge sf-badge--fire"><?php esc_html_e( '🔥 Fire Deal', 'samurai' ); ?></span>
				<?php elseif ( $sf_is_on_sale ) : ?>
					<span class="sf-badge sf-badge--sale"><?php esc_html_e( 'Sale', 'samurai' ); ?></span>
				<?php endif; ?>
			</div>
			<?php endif; ?>

			<?php if ( ! $sf_in_stock ) : ?>
			<div class="sf-product-card__oos-overlay" aria-hidden="true">
				<span class="sf-badge sf-badge--oos"><?php esc_html_e( 'Sold Out', 'samurai' ); ?></span>
			</div>
			<?php endif; ?>

			<?php if ( $sf_show_hover_panel ) : ?>
			<div class="sf-product-card__hover-panel" aria-hidden="true">

				<?php if ( $sf_has_swatches ) : ?>
				<div class="sf-card-swatches">
					<?php foreach ( $sf_gallery_ids as $sf_gid ) :
						$sf_g_src    = wp_get_attachment_image_src( $sf_gid, [40, 40] );
						$sf_g_srcset = wp_get_attachment_image_srcset( $sf_gid, [40, 40] );
						if ( ! $sf_g_src ) continue;
						?>
					<button type="button"
					        class="sf-card-swatch js-card-swatch"
					        data-src="<?php echo esc_attr( $sf_g_src[0] ); ?>"
					        data-srcset="<?php echo esc_attr( $sf_g_srcset ?: $sf_g_src[0] ); ?>"
					        tabindex="-1"
					        aria-label="<?php esc_attr_e( 'View alternate image', 'samurai' ); ?>">
						<img src="<?php echo esc_url( $sf_g_src[0] ); ?>"
						     alt=""
						     width="40"
						     height="40"
						     loading="lazy">
					</button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if ( ! empty( $sf_effects ) ) : ?>
				<div class="sf-card-effects">
					<?php foreach ( $sf_effects as $sf_eff ) : ?>
					<span class="sf-card-effect-tag"><?php echo esc_html( $sf_eff->name ); ?></span>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

			</div>
			<?php endif; ?>

		</div>
	</a>

	<?php /* ── Body ── */ ?>
	<div class="sf-product-card__body">
		<?php if ( $sf_show_brand ) : ?>
		<div class="sf-product-card__brand">
			<?php if ( $sf_brand ) : ?>
			<a href="<?php echo esc_url( get_term_link( $sf_brand ) ); ?>" class="sf-product-card__brand-link" tabindex="-1">
				<?php echo esc_html( $sf_brand->name ); ?>
			</a>
			<?php else : ?>
			<span class="sf-product-card__brand-placeholder">Samurai</span>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<h3 class="sf-product-card__title">
			<a href="<?php echo esc_url( $sf_permalink ); ?>"><?php echo esc_html( $sf_title ); ?></a>
		</h3>

		<div class="sf-product-card__price">
			<?php echo $sf_price_html; // phpcs:ignore ?>
		</div>
	</div>

	<?php /* ── Footer / Add to Cart ── */ ?>
	<div class="sf-product-card__footer">
		<?php if ( $sf_in_stock ) : ?>
		<a href="<?php echo esc_url( $sf_atc_url ); ?>"
		   class="sf-product-card__atc add_to_cart_button<?php echo esc_attr( $sf_ajax_class ); ?>"
		   data-product_id="<?php echo esc_attr( $sf_product_id ); ?>"
		   data-product_sku="<?php echo esc_attr( $sf_product->get_sku() ); ?>"
		   data-quantity="1"
		   aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'Add %s to your cart', 'samurai' ), $sf_title ) ); ?>"
		   rel="nofollow">
			<svg class="sf-atc-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
			<span class="sf-atc-label"><?php echo esc_html( $sf_atc_text ); ?></span>
		</a>
		<?php else : ?>
		<a href="<?php echo esc_url( $sf_permalink ); ?>" class="sf-product-card__atc sf-product-card__atc--oos">
			<span class="sf-atc-label"><?php esc_html_e( 'View Product', 'samurai' ); ?></span>
		</a>
		<?php endif; ?>
	</div>

</article>
