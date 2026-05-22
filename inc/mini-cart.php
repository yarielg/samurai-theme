<?php
/**
 * Mini Cart — drawer HTML, WC fragments, and AJAX handlers.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

// ---------------------------------------------------------------------------
// Cart badge outer HTML (fragment-replaceable)
// ---------------------------------------------------------------------------
function samurai_cart_badge_html(): string {
	$count = ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
	if ( $count > 0 ) {
		return '<span class="sf-cart-badge js-cart-count" aria-hidden="false">' . absint( $count ) . '</span>';
	}
	return '<span class="sf-cart-badge js-cart-count sf-cart-badge--empty" aria-hidden="true" style="display:none"></span>';
}

// ---------------------------------------------------------------------------
// Full drawer HTML (fragment-replaceable)
// ---------------------------------------------------------------------------
function samurai_mini_cart_html(): string {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return '';
	}

	$cart         = WC()->cart;
	$items        = $cart->get_cart();
	$count        = (int) $cart->get_cart_contents_count();
	$subtotal     = $cart->get_cart_subtotal();
	$cart_url     = wc_get_cart_url();
	$checkout_url = wc_get_checkout_url();

	// Free shipping progress bar
	$threshold  = (float) get_option( 'samurai_free_shipping_threshold', 0 );
	$cart_total = (float) $cart->get_cart_contents_total();
	$show_bar   = $threshold > 0;
	$pct        = $show_bar ? min( 100, (int) round( ( $cart_total / $threshold ) * 100 ) ) : 0;
	$remaining  = $show_bar ? max( 0.0, $threshold - $cart_total ) : 0.0;
	$unlocked   = $show_bar && $remaining <= 0;

	ob_start();
	?>
	<div class="sf-minicart" id="sf-minicart" role="dialog" aria-modal="true"
	     aria-label="<?php esc_attr_e( 'Shopping cart', 'samurai' ); ?>">
		<div class="sf-minicart__inner">

			<!-- ── Head ──────────────────────────────────────────────────── -->
			<div class="sf-minicart__head">
				<div class="sf-minicart__head-left">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
					<h2 class="sf-minicart__title">
						<?php esc_html_e( 'Your Cart', 'samurai' ); ?>
						<?php if ( $count > 0 ) : ?><span class="sf-minicart__item-count">(<?php echo absint( $count ); ?>)</span><?php endif; ?>
					</h2>
				</div>
				<button type="button" class="sf-minicart__close js-minicart-close"
				        aria-label="<?php esc_attr_e( 'Close cart', 'samurai' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
				</button>
			</div>

			<?php if ( $show_bar ) : ?>
			<!-- ── Free Shipping Bar ──────────────────────────────────── -->
			<div class="sf-minicart__shipping<?php echo $unlocked ? ' is-unlocked' : ''; ?>">
				<?php if ( $unlocked ) : ?>
				<p class="sf-minicart__shipping-msg">
					<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
					<?php esc_html_e( "You've unlocked free shipping!", 'samurai' ); ?>
				</p>
				<?php else : ?>
				<p class="sf-minicart__shipping-msg">
					<?php printf(
						/* translators: %s: currency amount */
						wp_kses( __( 'Add %s more for <strong>free shipping</strong>', 'samurai' ), [ 'strong' => [] ] ),
						wc_price( $remaining )
					); ?>
				</p>
				<?php endif; ?>
				<div class="sf-minicart__shipping-track" role="progressbar"
				     aria-valuenow="<?php echo absint( $pct ); ?>" aria-valuemin="0" aria-valuemax="100">
					<div class="sf-minicart__shipping-fill" style="width:<?php echo absint( $pct ); ?>%"></div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>

			<!-- ── Items ──────────────────────────────────────────────── -->
			<ul class="sf-minicart__items" role="list">
				<?php foreach ( $items as $key => $item ) :
					/** @var WC_Product $product */
					$product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );
					if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) continue;

					$link       = apply_filters( 'woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink( $item ) : '', $item, $key );
					$img        = apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image( [ 80, 80 ], [ 'class' => 'sf-minicart__img' ] ), $item, $key );
					$name       = apply_filters( 'woocommerce_cart_item_name', $product->get_name(), $item, $key );
					$line_price = apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $product, $item['quantity'] ), $item, $key );
					$qty        = absint( $item['quantity'] );
					$nonce      = wp_create_nonce( 'sf-minicart-nonce' );

					// Variation attributes
					$attr_parts = [];
					if ( ! empty( $item['variation'] ) ) {
						foreach ( $item['variation'] as $attr_key => $attr_val ) {
							if ( '' === $attr_val ) continue;
							$label   = wc_attribute_label( str_replace( 'attribute_', '', $attr_key ), $product );
							$display = apply_filters( 'woocommerce_variation_option_name', $attr_val, null, str_replace( 'attribute_', '', $attr_key ), $product );
							$attr_parts[] = esc_html( $label ) . ': ' . esc_html( $display );
						}
					}
				?>
				<li class="sf-minicart__item" data-cart-key="<?php echo esc_attr( $key ); ?>">
					<?php if ( $link ) : ?>
					<a href="<?php echo esc_url( $link ); ?>" class="sf-minicart__item-media" tabindex="-1" aria-hidden="true">
						<?php echo $img; // phpcs:ignore ?>
					</a>
					<?php else : ?>
					<div class="sf-minicart__item-media" aria-hidden="true"><?php echo $img; // phpcs:ignore ?></div>
					<?php endif; ?>

					<div class="sf-minicart__item-body">
						<a href="<?php echo esc_url( $link ); ?>" class="sf-minicart__item-name"><?php echo esc_html( $name ); ?></a>

						<?php if ( $attr_parts ) : ?>
						<p class="sf-minicart__item-meta"><?php echo implode( ' &middot; ', $attr_parts ); // phpcs:ignore ?></p>
						<?php endif; ?>

						<div class="sf-minicart__item-foot">
							<div class="sf-minicart__qty js-mc-qty"
							     data-cart-key="<?php echo esc_attr( $key ); ?>"
							     data-nonce="<?php echo esc_attr( $nonce ); ?>">
								<button type="button" class="sf-minicart__qty-btn js-mc-minus"
								        aria-label="<?php esc_attr_e( 'Decrease quantity', 'samurai' ); ?>">
									<svg width="10" height="2" viewBox="0 0 10 2" fill="currentColor" aria-hidden="true"><rect width="10" height="2" rx="1"/></svg>
								</button>
								<span class="sf-minicart__qty-val"><?php echo $qty; ?></span>
								<button type="button" class="sf-minicart__qty-btn js-mc-plus"
								        aria-label="<?php esc_attr_e( 'Increase quantity', 'samurai' ); ?>">
									<svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true"><rect x="4" width="2" height="10" rx="1"/><rect y="4" width="10" height="2" rx="1"/></svg>
								</button>
							</div>
							<span class="sf-minicart__item-price"><?php echo $line_price; // phpcs:ignore ?></span>
							<button type="button" class="sf-minicart__item-remove js-mc-remove"
							        data-cart-key="<?php echo esc_attr( $key ); ?>"
							        data-nonce="<?php echo esc_attr( $nonce ); ?>"
							        aria-label="<?php printf( esc_attr__( 'Remove %s from cart', 'samurai' ), $name ); ?>">
								<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m5 0V4a1 1 0 011-1h2a1 1 0 011 1v2"/></svg>
							</button>
						</div>
					</div>
				</li>
				<?php endforeach; ?>
			</ul>

			<!-- ── Foot ────────────────────────────────────────────────── -->
			<div class="sf-minicart__foot">
				<div class="sf-minicart__subtotal">
					<span class="sf-minicart__subtotal-label"><?php esc_html_e( 'Subtotal', 'samurai' ); ?></span>
					<span class="sf-minicart__subtotal-val"><?php echo $subtotal; // phpcs:ignore ?></span>
				</div>
				<p class="sf-minicart__tax-note"><?php esc_html_e( 'Taxes and shipping calculated at checkout', 'samurai' ); ?></p>
				<a href="<?php echo esc_url( $checkout_url ); ?>" class="sf-minicart__checkout-btn">
					<?php esc_html_e( 'Checkout', 'samurai' ); ?>
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
				</a>
				<a href="<?php echo esc_url( $cart_url ); ?>" class="sf-minicart__view-cart">
					<?php esc_html_e( 'View Full Cart', 'samurai' ); ?>
				</a>
			</div>

			<?php else : ?>

			<!-- ── Empty ────────────────────────────────────────────────── -->
			<div class="sf-minicart__empty">
				<span class="sf-minicart__empty-icon" aria-hidden="true">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
				</span>
				<p class="sf-minicart__empty-title"><?php esc_html_e( 'Your cart is empty', 'samurai' ); ?></p>
				<p class="sf-minicart__empty-sub"><?php esc_html_e( "You haven't added any products yet.", 'samurai' ); ?></p>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sf-minicart__empty-cta">
					<?php esc_html_e( 'Start Shopping', 'samurai' ); ?>
				</a>
			</div>

			<?php endif; ?>

		</div>
	</div>
	<?php
	return ob_get_clean();
}

// ---------------------------------------------------------------------------
// WC Fragments — auto-refresh drawer + badge on cart change (add to cart etc.)
// ---------------------------------------------------------------------------
add_filter( 'woocommerce_add_to_cart_fragments', static function ( array $fragments ): array {
	$fragments['div.sf-minicart']    = samurai_mini_cart_html();
	$fragments['span.sf-cart-badge'] = samurai_cart_badge_html();
	return $fragments;
} );

// ---------------------------------------------------------------------------
// AJAX — update qty or remove item (works on all pages, no WC fragment needed)
// ---------------------------------------------------------------------------
function samurai_ajax_mc_update(): void {
	check_ajax_referer( 'sf-minicart-nonce', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( [ 'message' => 'Cart unavailable' ], 503 );
	}

	$key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ?? '' ) );
	$qty = max( 0, (int) ( $_POST['quantity'] ?? 0 ) );

	if ( ! $key ) {
		wp_send_json_error( [ 'message' => 'Invalid key' ], 400 );
	}

	if ( $qty === 0 ) {
		WC()->cart->remove_cart_item( $key );
	} else {
		WC()->cart->set_quantity( $key, $qty, true );
	}
	WC()->cart->calculate_totals();

	wp_send_json_success( [
		'fragments' => [
			'div.sf-minicart'    => samurai_mini_cart_html(),
			'span.sf-cart-badge' => samurai_cart_badge_html(),
		],
		'cart_hash' => WC()->cart->get_cart_hash(),
		'count'     => WC()->cart->get_cart_contents_count(),
	] );
}
add_action( 'wp_ajax_sf_mc_update',        'samurai_ajax_mc_update' );
add_action( 'wp_ajax_nopriv_sf_mc_update', 'samurai_ajax_mc_update' );
