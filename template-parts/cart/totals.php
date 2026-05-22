<?php
/**
 * Cart order summary panel (right column).
 *
 * Accessed via get_template_part( 'template-parts/cart/totals' ) from
 * woocommerce/cart/cart.php and refreshed via AJAX.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_cart = WC()->cart;

// Free-shipping threshold.
$sf_threshold = (float) get_option( 'sf_free_shipping_threshold', 0 );
$sf_subtotal_raw = (float) $sf_cart->subtotal;

// Applied coupons.
$sf_coupons = $sf_cart->get_coupons();

// Support message via ACF-backed helper.
$sf_support_message = function_exists( 'samurai_option_text' )
	? samurai_option_text( 'cart_support_message', '' )
	: '';

// Cart totals helper values.
$sf_needs_shipping  = $sf_cart->needs_shipping();
$sf_checkout_url    = wc_get_checkout_url();
$sf_shop_url        = wc_get_page_permalink( 'shop' );
$sf_remove_nonce    = wp_create_nonce( 'remove-coupon' );
$sf_coupon_nonce    = wp_create_nonce( 'apply-coupon' );

// Item count for sticky bar.
$sf_item_count = $sf_cart->get_cart_contents_count();
?>
<div class="sf-order-summary">
	<div class="sf-order-summary__panel">

		<!-- Title -->
		<h2 class="sf-order-summary__title"><?php esc_html_e( 'Order Summary', 'samurai' ); ?></h2>

		<?php if ( $sf_threshold > 0 ) : ?>
		<!-- Shipping progress bar (visible on mobile only — CSS hides on desktop) -->
		<div class="sf-order-summary__shipping-bar">
			<?php
			$sf_pct_inner = $sf_threshold > 0 ? min( 100, round( ( $sf_subtotal_raw / $sf_threshold ) * 100 ) ) : 0;
			$sf_remaining = max( 0, $sf_threshold - $sf_subtotal_raw );
			$sf_bar_unlocked = ( $sf_subtotal_raw >= $sf_threshold );
			?>
			<div class="sf-cart-shipping-bar<?php echo $sf_bar_unlocked ? ' is-unlocked' : ''; ?>">
				<p class="sf-cart-shipping-bar__msg">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<rect x="1" y="3" width="15" height="13" rx="1"/>
						<path d="M16 8h4l3 5v3h-7V8z"/>
						<circle cx="5.5" cy="18.5" r="2.5"/>
						<circle cx="18.5" cy="18.5" r="2.5"/>
					</svg>
					<?php if ( $sf_bar_unlocked ) : ?>
						<?php esc_html_e( 'You\'ve unlocked free shipping!', 'samurai' ); ?>
					<?php else : ?>
						<?php
						printf(
							/* translators: %s: formatted currency amount */
							esc_html__( 'Add %s more for free shipping', 'samurai' ),
							wp_kses_post( wc_price( $sf_remaining ) )
						);
						?>
					<?php endif; ?>
				</p>
				<div class="sf-cart-shipping-bar__track" role="progressbar" aria-valuenow="<?php echo absint( $sf_pct_inner ); ?>" aria-valuemin="0" aria-valuemax="100">
					<div class="sf-cart-shipping-bar__fill" style="width:<?php echo absint( $sf_pct_inner ); ?>%"></div>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Totals -->
		<div class="sf-order-summary__totals">

			<!-- Subtotal -->
			<div class="sf-order-summary__row">
				<span class="sf-order-summary__label"><?php esc_html_e( 'Subtotal', 'samurai' ); ?></span>
				<span class="sf-order-summary__val"><?php echo wp_kses_post( $sf_cart->get_cart_subtotal() ); ?></span>
			</div>

			<!-- Applied coupons -->
			<?php foreach ( $sf_coupons as $sf_coupon_code => $sf_coupon_obj ) :
				$sf_discount_amount = $sf_cart->get_coupon_discount_amount( $sf_coupon_code, $sf_cart->display_cart_ex_tax );
			?>
			<div class="sf-order-summary__row">
				<span class="sf-order-summary__label sf-order-summary__coupon-label">
					<?php
					printf(
						/* translators: %s: coupon code */
						esc_html__( 'Coupon: %s', 'samurai' ),
						'<strong>' . esc_html( strtoupper( $sf_coupon_code ) ) . '</strong>'
					);
					?>
					<button type="button"
					        class="sf-coupon-remove js-remove-coupon"
					        data-coupon="<?php echo esc_attr( $sf_coupon_code ); ?>"
					        data-nonce="<?php echo esc_attr( $sf_remove_nonce ); ?>"
					        aria-label="<?php printf( esc_attr__( 'Remove coupon %s', 'samurai' ), esc_attr( strtoupper( $sf_coupon_code ) ) ); ?>">
						<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
							<line x1="18" y1="6" x2="6" y2="18"/>
							<line x1="6" y1="6" x2="18" y2="18"/>
						</svg>
					</button>
				</span>
				<span class="sf-order-summary__val sf-order-summary__val--discount">
					&minus;<?php echo wp_kses_post( wc_price( $sf_discount_amount ) ); ?>
				</span>
			</div>
			<?php endforeach; ?>

			<!-- Shipping -->
			<div class="sf-order-summary__row">
				<span class="sf-order-summary__label"><?php esc_html_e( 'Shipping', 'samurai' ); ?></span>
				<span class="sf-order-summary__val sf-order-summary__val--muted">
					<?php if ( $sf_needs_shipping ) : ?>
						<?php esc_html_e( 'Calculated at checkout', 'samurai' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'N/A', 'samurai' ); ?>
					<?php endif; ?>
				</span>
			</div>

			<!-- Taxes -->
			<?php if ( wc_tax_enabled() && ! $sf_cart->display_prices_including_tax() ) :
				$sf_tax_totals = $sf_cart->get_tax_totals();
				if ( ! empty( $sf_tax_totals ) ) :
					foreach ( $sf_tax_totals as $sf_tax ) : ?>
			<div class="sf-order-summary__row">
				<span class="sf-order-summary__label"><?php echo esc_html( $sf_tax->label ); ?></span>
				<span class="sf-order-summary__val"><?php echo wp_kses_post( $sf_tax->formatted_amount ); ?></span>
			</div>
				<?php endforeach;
				else : ?>
			<div class="sf-order-summary__row">
				<span class="sf-order-summary__label"><?php esc_html_e( 'Tax', 'samurai' ); ?></span>
				<span class="sf-order-summary__val sf-order-summary__val--muted"><?php esc_html_e( 'Calculated at checkout', 'samurai' ); ?></span>
			</div>
				<?php endif;
			endif; ?>

			<!-- Estimated total = items (ex-tax) − coupon discounts + taxes -->
			<?php
			$sf_est_total = (float) $sf_cart->subtotal_ex_tax
			              - (float) $sf_cart->get_discount_total()
			              + ( wc_tax_enabled() ? (float) $sf_cart->get_taxes_total() : 0.0 );
			?>
			<div class="sf-order-summary__row sf-order-summary__row--total">
				<span class="sf-order-summary__label"><?php esc_html_e( 'Estimated Total', 'samurai' ); ?></span>
				<span class="sf-order-summary__val js-cart-total">
					<?php echo wp_kses_post( wc_price( $sf_est_total ) ); ?>
				</span>
			</div>

		</div><!-- /.sf-order-summary__totals -->

		<!-- Checkout CTA -->
		<div class="sf-order-summary__cta-wrap">
			<a href="<?php echo esc_url( $sf_checkout_url ); ?>"
			   class="sf-cart-checkout-cta sf-btn sf-btn--primary sf-btn--block">
				<?php esc_html_e( 'Proceed to Checkout', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="5" y1="12" x2="19" y2="12"/>
					<polyline points="12 5 19 12 12 19"/>
				</svg>
			</a>
			<a href="<?php echo esc_url( $sf_shop_url ); ?>"
			   class="sf-cart-continue-link">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="19" y1="12" x2="5" y2="12"/>
					<polyline points="12 19 5 12 12 5"/>
				</svg>
				<?php esc_html_e( 'Continue Shopping', 'samurai' ); ?>
			</a>
		</div>

		<!-- Coupon area -->
		<div class="sf-coupon-area">
			<button type="button"
			        class="sf-coupon-toggle js-coupon-toggle"
			        aria-expanded="false"
			        aria-controls="sf-coupon-drawer">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
					<line x1="7" y1="7" x2="7.01" y2="7"/>
				</svg>
				<?php esc_html_e( 'Have a coupon?', 'samurai' ); ?>
				<svg class="sf-coupon-toggle__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<polyline points="6 9 12 15 18 9"/>
				</svg>
			</button>

			<div class="sf-coupon-drawer"
			     id="sf-coupon-drawer"
			     aria-hidden="true">
				<form class="sf-coupon-form js-coupon-form" novalidate>
					<input type="hidden"
					       name="apply-coupon-nonce"
					       value="<?php echo esc_attr( $sf_coupon_nonce ); ?>">
					<input type="text"
					       class="sf-coupon-form__input js-coupon-input"
					       name="coupon_code"
					       id="coupon_code"
					       placeholder="<?php esc_attr_e( 'Enter coupon code', 'samurai' ); ?>"
					       autocomplete="off"
					       aria-label="<?php esc_attr_e( 'Coupon code', 'samurai' ); ?>">
					<button type="submit"
					        class="sf-coupon-form__btn js-coupon-submit">
						<?php esc_html_e( 'Apply', 'samurai' ); ?>
					</button>
				</form>
			</div>
		</div><!-- /.sf-coupon-area -->

		<!-- Trust panel -->
		<div class="sf-cart-trust" aria-label="<?php esc_attr_e( 'Why shop with us', 'samurai' ); ?>">


			<div class="sf-cart-trust__item">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
					<path d="M7 11V7a5 5 0 0110 0v4"/>
				</svg>
				<span><?php esc_html_e( 'Secure checkout — SSL encrypted', 'samurai' ); ?></span>
			</div>

			<div class="sf-cart-trust__item">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 01.13 1.18 2 2 0 012.1 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/>
				</svg>
				<span><?php esc_html_e( 'Questions? Call or text us anytime', 'samurai' ); ?></span>
			</div>

			<?php if ( $sf_support_message ) : ?>
			<div class="sf-cart-trust__item sf-cart-trust__item--note">
				<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="12" cy="12" r="10"/>
					<line x1="12" y1="8" x2="12" y2="12"/>
					<line x1="12" y1="16" x2="12.01" y2="16"/>
				</svg>
				<span><?php echo esc_html( $sf_support_message ); ?></span>
			</div>
			<?php endif; ?>

		</div><!-- /.sf-cart-trust -->

	</div><!-- /.sf-order-summary__panel -->
</div><!-- /.sf-order-summary -->

<!-- Sticky mobile checkout bar -->
<div class="sf-cart-sticky-bar js-sticky-bar" role="complementary" aria-label="<?php esc_attr_e( 'Quick checkout', 'samurai' ); ?>">
	<div class="sf-cart-sticky-bar__info">
		<span class="sf-cart-sticky-bar__count">
			<?php
			printf(
				/* translators: %d: number of items in cart */
				esc_html( _n( '%d item', '%d items', $sf_item_count, 'samurai' ) ),
				absint( $sf_item_count )
			);
			?>
		</span>
		<span class="sf-cart-sticky-bar__total js-sticky-total">
			<?php echo wp_kses_post( $sf_cart->get_cart_total() ); ?>
		</span>
	</div>
	<a href="<?php echo esc_url( $sf_checkout_url ); ?>"
	   class="sf-cart-sticky-bar__btn">
		<?php esc_html_e( 'Checkout', 'samurai' ); ?>
		<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<line x1="5" y1="12" x2="19" y2="12"/>
			<polyline points="12 5 19 12 12 19"/>
		</svg>
	</a>
</div>
