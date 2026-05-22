<?php
/**
 * Cart item cards — rendered inline on page load and refreshed via AJAX.
 *
 * Accessed via get_template_part( 'template-parts/cart/items' ) both from
 * woocommerce/cart/cart.php and from the sf_cart_update AJAX handler.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_cart  = WC()->cart;
$sf_items = $sf_cart->get_cart();

if ( empty( $sf_items ) ) {
	return;
}

foreach ( $sf_items as $sf_key => $sf_item ) :
	/** @var WC_Product $sf_product */
	$sf_product = apply_filters( 'woocommerce_cart_item_product', $sf_item['data'], $sf_item, $sf_key );

	if ( ! $sf_product || ! $sf_product->exists() || 0 >= $sf_item['quantity'] ) {
		continue;
	}
	if ( ! apply_filters( 'woocommerce_cart_item_visible', true, $sf_item, $sf_key ) ) {
		continue;
	}

	$sf_permalink  = apply_filters( 'woocommerce_cart_item_permalink', $sf_product->is_visible() ? $sf_product->get_permalink( $sf_item ) : '', $sf_item, $sf_key );
	$sf_thumbnail  = apply_filters(
		'woocommerce_cart_item_thumbnail',
		$sf_product->get_image(
			'woocommerce_thumbnail',
			[
				'class'   => 'sf-cart-item__img',
				'loading' => 'lazy',
				'alt'     => esc_attr( $sf_product->get_name() ),
			]
		),
		$sf_item,
		$sf_key
	);
	$sf_name       = apply_filters( 'woocommerce_cart_item_name', $sf_product->get_name(), $sf_item, $sf_key );
	$sf_qty        = (int) $sf_item['quantity'];
	$sf_max_qty    = $sf_product->get_max_purchase_quantity();
	$sf_unit_price = apply_filters( 'woocommerce_cart_item_price', $sf_cart->get_product_price( $sf_product ), $sf_item, $sf_key );
	$sf_subtotal   = apply_filters( 'woocommerce_cart_item_subtotal', $sf_cart->get_product_subtotal( $sf_product, $sf_qty ), $sf_item, $sf_key );
	$sf_item_data  = wc_get_formatted_cart_item_data( $sf_item );

	// Brand taxonomy.
	$sf_brands     = get_the_terms( $sf_product->get_id(), 'brand' );
	$sf_brand_name = ( ! is_wp_error( $sf_brands ) && ! empty( $sf_brands ) ) ? $sf_brands[0]->name : '';

	// Stock status.
	$sf_stock_qty = $sf_product->managing_stock() ? $sf_product->get_stock_quantity() : null;
	$sf_is_low    = null !== $sf_stock_qty && $sf_stock_qty > 0 && $sf_stock_qty <= 5;
	$sf_is_oos    = ! $sf_product->is_in_stock();

	// Fire deal badge.
	$sf_is_deal = has_term( '', 'fire-deal', $sf_product->get_id() );

	// Item classes.
	$sf_item_class = trim( 'sf-cart-item ' . ( apply_filters( 'woocommerce_cart_item_class', '', $sf_item, $sf_key ) ?: '' ) );
	if ( $sf_is_oos ) {
		$sf_item_class .= ' sf-cart-item--oos';
	}
?>
<article class="<?php echo esc_attr( $sf_item_class ); ?>"
         data-cart-key="<?php echo esc_attr( $sf_key ); ?>">

	<!-- Thumbnail -->
	<div class="sf-cart-item__thumb">
		<?php if ( $sf_permalink ) : ?>
		<a href="<?php echo esc_url( $sf_permalink ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo $sf_thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
		<?php else : ?>
		<div><?php echo $sf_thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php endif; ?>

		<?php if ( $sf_product->is_on_sale() ) : ?>
		<span class="sf-cart-item__badge sf-cart-item__badge--sale"><?php esc_html_e( 'Sale', 'samurai' ); ?></span>
		<?php elseif ( $sf_is_deal ) : ?>
		<span class="sf-cart-item__badge sf-cart-item__badge--deal" aria-label="<?php esc_attr_e( 'Fire deal', 'samurai' ); ?>">🔥</span>
		<?php endif; ?>
	</div>

	<!-- Body -->
	<div class="sf-cart-item__body">

		<!-- Info -->
		<div class="sf-cart-item__info">
			<?php if ( $sf_brand_name ) : ?>
			<span class="sf-cart-item__brand"><?php echo esc_html( $sf_brand_name ); ?></span>
			<?php endif; ?>

			<h2 class="sf-cart-item__name">
				<?php if ( $sf_permalink ) : ?>
				<a href="<?php echo esc_url( $sf_permalink ); ?>"><?php echo esc_html( $sf_name ); ?></a>
				<?php else : ?>
				<?php echo esc_html( $sf_name ); ?>
				<?php endif; ?>
			</h2>

			<?php if ( $sf_item_data ) : ?>
			<div class="sf-cart-item__meta"><?php echo $sf_item_data; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — WC escapes this ?></div>
			<?php endif; ?>

			<div class="sf-cart-item__unit-price"><?php echo $sf_unit_price; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>

			<?php if ( $sf_is_oos ) : ?>
			<p class="sf-cart-item__stock sf-cart-item__stock--oos" role="alert">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
					<circle cx="12" cy="12" r="10"/>
					<line x1="12" y1="8" x2="12" y2="12"/>
					<line x1="12" y1="16" x2="12.01" y2="16"/>
				</svg>
				<?php esc_html_e( 'Out of stock', 'samurai' ); ?>
			</p>
			<?php elseif ( $sf_is_low ) : ?>
			<p class="sf-cart-item__stock sf-cart-item__stock--low">
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
					<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
					<line x1="12" y1="9" x2="12" y2="13"/>
					<line x1="12" y1="17" x2="12.01" y2="17"/>
				</svg>
				<?php printf( esc_html__( 'Only %d left!', 'samurai' ), absint( $sf_stock_qty ) ); ?>
			</p>
			<?php endif; ?>
		</div>

		<!-- Footer: qty + total + remove -->
		<div class="sf-cart-item__foot">
			<div class="sf-cart-item__qty-group" role="group"
			     aria-label="<?php printf( esc_attr__( 'Quantity for %s', 'samurai' ), esc_attr( $sf_name ) ); ?>">
				<button type="button"
				        class="sf-cart-item__qty-btn js-cart-minus"
				        aria-label="<?php esc_attr_e( 'Decrease quantity', 'samurai' ); ?>"
				        data-key="<?php echo esc_attr( $sf_key ); ?>"
				        <?php disabled( $sf_qty <= 1 ); ?>>
					<svg width="10" height="2" viewBox="0 0 10 2" fill="currentColor" aria-hidden="true">
						<rect width="10" height="2" rx="1"/>
					</svg>
				</button>
				<input type="number"
				       class="sf-cart-item__qty-input js-cart-qty"
				       value="<?php echo absint( $sf_qty ); ?>"
				       min="1"
				       <?php echo ( $sf_max_qty > 0 ) ? 'max="' . absint( $sf_max_qty ) . '"' : ''; ?>
				       step="1"
				       inputmode="numeric"
				       name="cart[<?php echo esc_attr( $sf_key ); ?>][qty]"
				       data-key="<?php echo esc_attr( $sf_key ); ?>"
				       data-original="<?php echo absint( $sf_qty ); ?>"
				       aria-label="<?php printf( esc_attr__( 'Quantity for %s', 'samurai' ), esc_attr( $sf_name ) ); ?>">
				<button type="button"
				        class="sf-cart-item__qty-btn js-cart-plus"
				        aria-label="<?php esc_attr_e( 'Increase quantity', 'samurai' ); ?>"
				        data-key="<?php echo esc_attr( $sf_key ); ?>"
				        <?php echo ( $sf_max_qty > 0 && $sf_qty >= $sf_max_qty ) ? 'disabled' : ''; ?>>
					<svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true">
						<rect x="4" width="2" height="10" rx="1"/>
						<rect y="4" width="10" height="2" rx="1"/>
					</svg>
				</button>
			</div>

			<div class="sf-cart-item__line-total js-cart-item-total"
			     data-key="<?php echo esc_attr( $sf_key ); ?>">
				<?php echo $sf_subtotal; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<button type="button"
			        class="sf-cart-item__remove js-cart-remove"
			        data-key="<?php echo esc_attr( $sf_key ); ?>"
			        aria-label="<?php printf( esc_attr__( 'Remove %s from cart', 'samurai' ), esc_attr( $sf_name ) ); ?>">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
					<polyline points="3 6 5 6 21 6"/>
					<path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m5 0V4a1 1 0 011-1h2a1 1 0 011 1v2"/>
				</svg>
			</button>
		</div>

	</div>
</article>
<?php endforeach; ?>
