<?php
/**
 * Multi-step checkout template.
 *
 * Two panels on this page:
 *   Panel 1 (step=2 Shipping) — Contact info, delivery method, notes, coupon
 *   Panel 2 (step=3 Payment)  — Order summary toggle, payment methods, place order
 *
 * JS advances panels. WC handles all server-side validation and payment processing.
 * Billing address fields are included in panel 1 but shown/hidden based on
 * whether the selected shipping method requires an address.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

// Remove WC's built-in coupon notice — we have our own coupon UI in panel 1.
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_before_checkout_form', $checkout );
}

// Cart must not be empty.
if ( WC()->cart->is_empty() ) {
	wc_add_notice( __( 'Your cart is empty before placing an order.', 'samurai' ), 'error' );
	wp_safe_redirect( wc_get_cart_url() );
	exit;
}

// Ensure customer has a location so shipping methods can be calculated.
// Without a location, zone-based methods return no rates.
if ( ! WC()->customer->has_calculated_shipping() ) {
	WC()->customer->set_shipping_country( WC()->countries->get_base_country() );
	$sf_base_state = WC()->countries->get_base_state();
	if ( $sf_base_state ) {
		WC()->customer->set_shipping_state( $sf_base_state );
	}
}
// calculate_totals() calls calculate_shipping() internally and updates
// WC()->cart->total so the toggle button renders the correct grand total.
WC()->cart->calculate_totals();
$sf_packages = WC()->shipping()->get_packages();
?>
<div class="sf-checkout-page">

	<div class="sf-checkout-page__inner sf-container--sm">

		<h1 class="sf-checkout-title"><?php esc_html_e( 'Checkout', 'samurai' ); ?></h1>

		<!-- Step indicator (server renders step 2; JS updates to step 3 on panel advance) -->
		<?php get_template_part( 'template-parts/checkout/steps', null, [ 'step' => 2 ] ); ?>

		<!-- WC notices -->
		<?php woocommerce_output_all_notices(); ?>

		<form name="checkout"
		      id="sf-checkout-form"
		      class="woocommerce-checkout checkout"
		      method="post"
		      action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
		      novalidate>

			<?php if ( $checkout->get_checkout_fields() ) : ?>

			<!-- ================================================================
			     Panel 1 — Contact Information + Delivery Method
			     ================================================================ -->
			<div class="sf-co-panel js-co-panel" id="sf-co-panel-1" data-panel="1">

				<!-- Back to cart -->
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>"
				   class="sf-co-back-btn">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
					<?php esc_html_e( 'Back', 'samurai' ); ?>
				</a>

				<!-- Contact Info -->
				<div class="sf-co-section">
					<h3 class="sf-co-section__title"><?php esc_html_e( 'Contact Information', 'samurai' ); ?></h3>
					<div class="sf-co-fields">
						<?php
						$sf_contact_keys = [ 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone' ];
						$sf_billing      = $checkout->get_checkout_fields( 'billing' );
						foreach ( $sf_contact_keys as $sf_key ) {
							if ( empty( $sf_billing[ $sf_key ] ) ) continue;
							$sf_field = $sf_billing[ $sf_key ];
							if ( empty( $sf_field['placeholder'] ) && ! empty( $sf_field['label'] ) ) {
								$sf_field['placeholder'] = $sf_field['label'];
							}
							woocommerce_form_field( $sf_key, $sf_field, $checkout->get_value( $sf_key ) );
						}
						?>
					</div>
				</div>

				<!-- Delivery Method — rendered inline to avoid the WC cart-shipping.php
				     template (which outputs <tr> rows and expects a table context).
				     We bypass do_action('woocommerce_checkout_shipping') which outputs the
				     shipping ADDRESS form and triggers duplicate order notes. -->
				<?php if ( ! empty( $sf_packages ) ) : ?>
				<div class="sf-co-section">
					<h3 class="sf-co-section__title"><?php esc_html_e( 'Delivery Method', 'samurai' ); ?></h3>
					<div class="sf-co-shipping">
						<?php foreach ( $sf_packages as $sf_i => $sf_package ) :
							$sf_rates         = $sf_package['rates'];
							$sf_valid_ids     = array_keys( $sf_rates );
							$sf_chosen        = wc_get_chosen_shipping_method_for_package( $sf_i, $sf_package );
							$sf_single_method = 1 === count( $sf_rates );
							if ( empty( $sf_rates ) ) continue;

							// Always pre-select a method so form submission always has a value.
							if ( ! $sf_chosen || ! in_array( $sf_chosen, $sf_valid_ids, true ) ) {
								$sf_chosen = $sf_valid_ids[0];
								// Persist to session so WC validation finds it.
								$sf_session_chosen                = WC()->session->get( 'chosen_shipping_methods', [] );
								$sf_session_chosen[ $sf_i ]       = $sf_chosen;
								WC()->session->set( 'chosen_shipping_methods', $sf_session_chosen );
							}
						?>
						<ul id="shipping_method" class="woocommerce-shipping-methods sf-shipping-methods">
							<?php foreach ( $sf_rates as $sf_rate ) :
								$sf_rate_id  = esc_attr( $sf_rate->id );
								$sf_input_id = 'shipping_method_' . $sf_i . '_' . esc_attr( sanitize_title( $sf_rate->id ) );
								$sf_checked  = checked( $sf_rate->id, $sf_chosen, false );
							?>
							<li>
								<?php if ( $sf_single_method ) : ?>
								<input type="hidden"
								       name="shipping_method[<?php echo $sf_i; ?>]"
								       data-index="<?php echo $sf_i; ?>"
								       id="<?php echo $sf_input_id; ?>"
								       value="<?php echo $sf_rate_id; ?>"
								       class="shipping_method">
								<?php else : ?>
								<input type="radio"
								       name="shipping_method[<?php echo $sf_i; ?>]"
								       data-index="<?php echo $sf_i; ?>"
								       id="<?php echo $sf_input_id; ?>"
								       value="<?php echo $sf_rate_id; ?>"
								       class="shipping_method"
								       <?php echo $sf_checked; ?>>
								<?php endif; ?>
								<label for="<?php echo $sf_input_id; ?>">
									<?php echo wp_kses_post( wc_cart_totals_shipping_method_label( $sf_rate ) ); ?>
								</label>
								<?php do_action( 'woocommerce_after_shipping_rate', $sf_rate, $sf_i ); ?>
							</li>
							<?php endforeach; ?>
						</ul>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Billing address -->
				<div class="sf-co-section sf-co-address" id="sf-co-billing-address">
					<h3 class="sf-co-section__title"><?php esc_html_e( 'Billing Address', 'samurai' ); ?></h3>
					<div class="sf-co-fields">
						<?php
						$sf_skip   = [ 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone', 'billing_company' ];
						$sf_inline = [ 'billing_city', 'billing_state', 'billing_postcode' ];

						// Country + address lines
						foreach ( $sf_billing as $sf_key => $sf_field ) {
							if ( in_array( $sf_key, $sf_skip, true ) || in_array( $sf_key, $sf_inline, true ) ) continue;
							if ( empty( $sf_field['placeholder'] ) && ! empty( $sf_field['label'] ) ) {
								$sf_field['placeholder'] = $sf_field['label'];
							}
							woocommerce_form_field( $sf_key, $sf_field, $checkout->get_value( $sf_key ) );
						}
						?>

						<!-- City / State / ZIP on one row -->
						<div class="sf-co-fields-inline">
							<?php foreach ( $sf_inline as $sf_key ) :
								if ( empty( $sf_billing[ $sf_key ] ) ) continue;
								$sf_field = $sf_billing[ $sf_key ];
								if ( empty( $sf_field['placeholder'] ) && ! empty( $sf_field['label'] ) ) {
									$sf_field['placeholder'] = $sf_field['label'];
								}
							?>
							<?php woocommerce_form_field( $sf_key, $sf_field, $checkout->get_value( $sf_key ) ); ?>
							<?php endforeach; ?>
						</div>

					</div>
				</div>

				<!-- Order Notes (rendered once here; WC's auto-output is bypassed above) -->
				<?php
				$sf_order_fields = $checkout->get_checkout_fields( 'order' );
				if ( ! empty( $sf_order_fields ) ) : ?>
				<div class="sf-co-section">
					<h3 class="sf-co-section__title"><?php esc_html_e( 'Order Notes', 'samurai' ); ?><span class="sf-co-optional"><?php esc_html_e( 'Optional', 'samurai' ); ?></span></h3>
					<div class="sf-co-fields">
						<?php foreach ( $sf_order_fields as $sf_key => $sf_field ) :
							woocommerce_form_field( $sf_key, $sf_field, $checkout->get_value( $sf_key ) );
						endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Coupon -->
				<div class="sf-co-section sf-co-coupon-section">
					<button type="button"
					        class="sf-co-coupon-toggle js-coupon-toggle"
					        aria-expanded="false"
					        aria-controls="sf-co-coupon-drawer">
						<?php esc_html_e( 'Have a promo code?', 'samurai' ); ?>
						<svg class="sf-coupon-toggle__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
					</button>
					<div class="sf-coupon-drawer" id="sf-co-coupon-drawer" aria-hidden="true">
						<!-- div, not form — cannot nest <form> inside the checkout <form> -->
						<div class="sf-coupon-form js-coupon-form">
							<input type="hidden" class="js-coupon-nonce" value="<?php echo esc_attr( wp_create_nonce( 'sf-coupon' ) ); ?>">
							<input type="text"
							       class="sf-coupon-form__input js-coupon-input"
							       placeholder="<?php esc_attr_e( 'Enter code', 'samurai' ); ?>"
							       autocomplete="off"
							       aria-label="<?php esc_attr_e( 'Coupon code', 'samurai' ); ?>">
							<button type="button" class="sf-coupon-form__btn js-coupon-submit">
								<?php esc_html_e( 'Apply', 'samurai' ); ?>
							</button>
						</div>
						<div class="sf-coupon-feedback js-coupon-feedback" role="status" aria-live="polite"></div>
					</div>
				</div>

				<!-- Applied coupons (server-rendered; refreshed via WC fragment on change) -->
				<div class="sf-co-applied-coupons js-co-applied-coupons">
					<?php foreach ( WC()->cart->get_coupons() as $sf_code => $sf_coupon ) :
						$sf_discount = WC()->cart->get_coupon_discount_amount( $sf_code );
					?>
					<div class="sf-co-applied-coupon">
						<span class="sf-co-applied-coupon__code"><?php echo esc_html( strtoupper( $sf_code ) ); ?></span>
						<?php if ( $sf_discount ) : ?>
						<span class="sf-co-applied-coupon__discount">&minus;<?php echo wp_kses_post( wc_price( $sf_discount ) ); ?></span>
						<?php endif; ?>
						<button type="button"
						        class="sf-co-applied-coupon__remove js-coupon-remove"
						        data-coupon="<?php echo esc_attr( $sf_code ); ?>"
						        aria-label="<?php echo esc_attr( sprintf( __( 'Remove coupon %s', 'samurai' ), strtoupper( $sf_code ) ) ); ?>">
							&times;
						</button>
					</div>
					<?php endforeach; ?>
				</div>

				<!-- Fixed CTA — continues to panel 2 -->
				<div class="sf-co-sticky-cta">
					<button type="button" class="sf-co-cta-btn js-co-next" data-next="2">
						<?php esc_html_e( 'Continue to Payment', 'samurai' ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
					</button>
				</div>

			</div><!-- /#sf-co-panel-1 -->


			<!-- ================================================================
			     Panel 2 — Payment + Order Summary
			     Panel starts visible (no hidden attr) so payment gateway JS
			     (Square, PayPal) can initialize their iframes on page load.
			     CSS keeps it off-screen until JS calls showPanel(2).
			     ================================================================ -->
			<div class="sf-co-panel js-co-panel sf-co-panel--offscreen" id="sf-co-panel-2" data-panel="2" aria-hidden="true">

				<!-- Back button -->
				<button type="button" class="sf-co-back-btn js-co-back" data-back="1">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
					<?php esc_html_e( 'Back', 'samurai' ); ?>
				</button>

				<!-- Collapsible order summary -->
				<div class="sf-co-summary">
					<button type="button"
					        class="sf-co-summary__toggle js-co-summary-toggle"
					        aria-expanded="false"
					        aria-controls="sf-co-summary-body">
						<span class="sf-co-summary__left">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
							<?php esc_html_e( 'Show order summary', 'samurai' ); ?>
							<svg class="sf-coupon-toggle__chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</span>
						<span class="sf-co-summary__total">
							<?php echo wp_kses_post( wc_price( WC()->cart->total ) ); ?>
						</span>
					</button>
					<div class="sf-co-summary__body" id="sf-co-summary-body" aria-hidden="true">
						<?php woocommerce_order_review(); ?>
					</div>
				</div>

				<!-- Payment -->
				<div class="sf-co-section">
					<h3 class="sf-co-section__title"><?php esc_html_e( 'Payment', 'samurai' ); ?></h3>
					<?php
					// Must call the function directly — it is hooked to woocommerce_checkout_order_review,
					// NOT to the woocommerce_checkout_payment action hook.
					if ( function_exists( 'woocommerce_checkout_payment' ) ) {
						woocommerce_checkout_payment();
					}
					?>
				</div>

				<!-- Back button -->
				<button type="button" class="sf-co-back-btn js-co-back" data-back="1">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
					<?php esc_html_e( 'Back', 'samurai' ); ?>
				</button>

			</div><!-- /#sf-co-panel-2 -->

			<?php else : ?>

			<?php if ( function_exists( 'woocommerce_checkout_payment' ) ) woocommerce_checkout_payment(); ?>

			<?php endif; ?>

		</form>

	</div><!-- /.sf-checkout-page__inner -->

</div><!-- /.sf-checkout-page -->

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
