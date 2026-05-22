<?php
/**
 * Cart page template — two-column layout with AJAX updates.
 *
 * WC hooks fired (in order):
 *   woocommerce_before_cart
 *   woocommerce_before_cart_table
 *   woocommerce_before_cart_contents
 *   woocommerce_cart_actions
 *   woocommerce_after_cart_contents
 *   woocommerce_after_cart_table
 *   woocommerce_cart_collaterals (hidden — plugin hooks still fire)
 *   woocommerce_after_cart
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$sf_threshold  = (float) get_option( 'sf_free_shipping_threshold', 0 );
$sf_subtotal   = WC()->cart->subtotal;
$sf_pct        = $sf_threshold > 0 ? min( 100, round( ( $sf_subtotal / $sf_threshold ) * 100 ) ) : 0;
$sf_remaining  = max( 0, $sf_threshold - $sf_subtotal );
$sf_unlocked   = ( $sf_threshold > 0 && $sf_subtotal >= $sf_threshold );
$sf_item_count = WC()->cart->get_cart_contents_count();
?>
<div class="sf-cart-page sf-container">

	<!-- ── Step indicator (Cart = step 1) ────────────────────────────────── -->
	<?php get_template_part( 'template-parts/checkout/steps', null, [ 'step' => 1 ] ); ?>

	<!-- ── Page header ────────────────────────────────────────────────────── -->
	<div class="sf-cart-page__header">
		<div class="sf-cart-page__title-row">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="9" cy="21" r="1"/>
				<circle cx="20" cy="21" r="1"/>
				<path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-7.43H6"/>
			</svg>
			<h1 class="sf-cart-page__title"><?php esc_html_e( 'Your Cart', 'samurai' ); ?></h1>
			<span class="sf-cart-page__count js-cart-header-count">
				<?php
				printf(
					esc_html( _n( '%d item', '%d items', $sf_item_count, 'samurai' ) ),
					absint( $sf_item_count )
				);
				?>
			</span>
		</div>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sf-cart-page__continue-top">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="19" y1="12" x2="5" y2="12"/>
				<polyline points="12 19 5 12 12 5"/>
			</svg>
			<?php esc_html_e( 'Continue Shopping', 'samurai' ); ?>
		</a>
	</div>

	<!-- WC notices — seeds rendered here are harvested by toast.js -->
	<?php wc_print_notices(); ?>

	<?php if ( $sf_threshold > 0 ) : ?>
	<!-- Desktop-only free-shipping progress bar -->
	<div class="sf-cart-page-shipping-bar">
		<div class="sf-cart-shipping-bar<?php echo $sf_unlocked ? ' is-unlocked' : ''; ?>">
			<p class="sf-cart-shipping-bar__msg">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<rect x="1" y="3" width="15" height="13" rx="1"/>
					<path d="M16 8h4l3 5v3h-7V8z"/>
					<circle cx="5.5" cy="18.5" r="2.5"/>
					<circle cx="18.5" cy="18.5" r="2.5"/>
				</svg>
				<?php if ( $sf_unlocked ) : ?>
					<?php esc_html_e( "You've unlocked free shipping!", 'samurai' ); ?>
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
			<div class="sf-cart-shipping-bar__track"
			     role="progressbar"
			     aria-valuenow="<?php echo absint( $sf_pct ); ?>"
			     aria-valuemin="0"
			     aria-valuemax="100"
			     aria-label="<?php esc_attr_e( 'Free shipping progress', 'samurai' ); ?>">
				<div class="sf-cart-shipping-bar__fill" style="width:<?php echo absint( $sf_pct ); ?>%"></div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<!-- ── Two-column layout ──────────────────────────────────────────────── -->
	<div class="sf-cart-layout">

		<!-- Items column -->
		<div class="sf-cart-items-col">

			<form id="sf-cart-form"
			      class="woocommerce-cart-form"
			      action="<?php echo esc_url( wc_get_cart_url() ); ?>"
			      method="post">

				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<div class="sf-cart-items js-cart-items" aria-live="polite" aria-atomic="false">
					<?php get_template_part( 'template-parts/cart/items' ); ?>
				</div>

				<div class="sf-cart-form-actions">
					<?php do_action( 'woocommerce_cart_actions' ); ?>
					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
					<button type="submit"
					        class="sf-cart-update-btn"
					        name="update_cart"
					        value="<?php esc_attr_e( 'Update cart', 'samurai' ); ?>">
						<?php esc_html_e( 'Update Cart', 'samurai' ); ?>
					</button>
				</div>

				<?php do_action( 'woocommerce_after_cart_contents' ); ?>

			</form>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>

		</div><!-- /.sf-cart-items-col -->

		<!-- Order summary column -->
		<div class="sf-cart-summary-col js-cart-summary" aria-live="polite" aria-atomic="false">
			<?php get_template_part( 'template-parts/cart/totals' ); ?>
		</div>

	</div><!-- /.sf-cart-layout -->

	<!--
		Hidden collaterals wrapper: woocommerce_cart_collaterals is fired here
		so plugins that hook into it still work without injecting unwanted UI
		into our custom layout.
	-->
	<div style="display:none" aria-hidden="true">
		<?php do_action( 'woocommerce_cart_collaterals' ); ?>
	</div>

</div><!-- /.sf-cart-page -->

<?php do_action( 'woocommerce_after_cart' ); ?>
