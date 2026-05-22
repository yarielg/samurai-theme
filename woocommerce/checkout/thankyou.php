<?php
/**
 * Order Received / Thank You page
 *
 * @package samurai
 * @var WC_Order $order
 */
defined( 'ABSPATH' ) || exit;

// Remove WooCommerce's default order-details table + address output.
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
?>

<div class="sf-ty">

<?php if ( $order ) :
	do_action( 'woocommerce_before_thankyou', $order->get_id() );
?>

<?php if ( $order->has_status( 'failed' ) ) : ?>

	<!-- ── Failed order ──────────────────────────────────────────────────── -->
	<div class="sf-ty__failed sf-container--sm">
		<div class="sf-ty__failed-icon">
			<svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<circle cx="26" cy="26" r="25" stroke="currentColor" stroke-width="2"/>
				<line x1="17" y1="17" x2="35" y2="35" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
				<line x1="35" y1="17" x2="17" y2="35" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
			</svg>
		</div>
		<h1 class="sf-ty__failed-title"><?php esc_html_e( 'Payment Failed', 'samurai' ); ?></h1>
		<p class="sf-ty__failed-msg"><?php esc_html_e( 'Your payment could not be processed. Please try again or use a different payment method.', 'samurai' ); ?></p>
		<div class="sf-ty__failed-btns">
			<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="sf-btn sf-btn--primary">
				<?php esc_html_e( 'Try Again', 'samurai' ); ?>
			</a>
			<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="sf-btn sf-btn--outline">
				<?php esc_html_e( 'My Account', 'samurai' ); ?>
			</a>
			<?php endif; ?>
		</div>
	</div>

<?php else : ?>

	<!-- ── Hero ─────────────────────────────────────────────────────────── -->
	<div class="sf-ty__hero">
		<div class="sf-ty__check-wrap" aria-hidden="true">
			<svg class="sf-ty__check" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle class="sf-ty__check-circle" cx="26" cy="26" r="24" stroke="currentColor" stroke-width="2" fill="none"/>
				<polyline class="sf-ty__check-tick" points="14,27 22,35 38,17" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
			</svg>
		</div>
		<h1 class="sf-ty__hero-title"><?php esc_html_e( 'Order Confirmed!', 'samurai' ); ?></h1>
		<p class="sf-ty__hero-sub">
			<?php
			printf(
				/* translators: %s: customer first name */
				esc_html__( 'Thank you, %s! Your fireworks are on their way.', 'samurai' ),
				esc_html( $order->get_billing_first_name() )
			);
			?>
		</p>
		<?php if ( $order->get_billing_email() ) : ?>
		<p class="sf-ty__hero-email">
			<?php
			printf(
				/* translators: %s: email address */
				wp_kses( __( 'A confirmation email was sent to <strong>%s</strong>', 'samurai' ), [ 'strong' => [] ] ),
				esc_html( $order->get_billing_email() )
			);
			?>
		</p>
		<?php endif; ?>
	</div><!-- /.sf-ty__hero -->

	<!-- ── Order meta strip ──────────────────────────────────────────────── -->
	<div class="sf-ty__meta sf-container--sm">
		<div class="sf-ty__meta-item">
			<span class="sf-ty__meta-label"><?php esc_html_e( 'Order', 'samurai' ); ?></span>
			<span class="sf-ty__meta-val">#<?php echo esc_html( $order->get_order_number() ); ?></span>
		</div>
		<div class="sf-ty__meta-item">
			<span class="sf-ty__meta-label"><?php esc_html_e( 'Date', 'samurai' ); ?></span>
			<span class="sf-ty__meta-val"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
		</div>
		<div class="sf-ty__meta-item">
			<span class="sf-ty__meta-label"><?php esc_html_e( 'Total', 'samurai' ); ?></span>
			<span class="sf-ty__meta-val sf-ty__meta-val--total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
		</div>
		<?php if ( $order->get_payment_method_title() ) : ?>
		<div class="sf-ty__meta-item">
			<span class="sf-ty__meta-label"><?php esc_html_e( 'Payment', 'samurai' ); ?></span>
			<span class="sf-ty__meta-val"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
		</div>
		<?php endif; ?>
	</div>

	<!-- ── Main body ─────────────────────────────────────────────────────── -->
	<div class="sf-ty__body sf-container--sm">

		<!-- Order items + totals -->
		<div class="sf-ty__card">
			<h2 class="sf-ty__card-title"><?php esc_html_e( 'Order Summary', 'samurai' ); ?></h2>

			<div class="sf-ty__items">
				<?php foreach ( $order->get_items() as $item_id => $item ) :
					$product   = $item->get_product();
					$thumb_url = $product && $product->get_image_id()
						? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' )
						: wc_placeholder_img_src( 'thumbnail' );
				?>
				<div class="sf-ty__item">
					<div class="sf-ty__item-img">
						<img src="<?php echo esc_url( $thumb_url ); ?>"
						     alt="<?php echo esc_attr( $item->get_name() ); ?>"
						     width="56" height="56" loading="lazy">
						<span class="sf-ty__item-qty"><?php echo absint( $item->get_quantity() ); ?></span>
					</div>
					<div class="sf-ty__item-info">
						<span class="sf-ty__item-name"><?php echo esc_html( $item->get_name() ); ?></span>
						<?php
						$meta = wc_display_item_meta( $item, [ 'echo' => false ] );
						if ( $meta ) echo '<span class="sf-ty__item-meta">' . wp_kses_post( $meta ) . '</span>';
						?>
					</div>
					<span class="sf-ty__item-price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
				</div>
				<?php endforeach; ?>
			</div>

			<div class="sf-ty__totals">
				<div class="sf-ty__total-row">
					<span><?php esc_html_e( 'Subtotal', 'samurai' ); ?></span>
					<span><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></span>
				</div>
				<?php if ( $order->get_total_discount() > 0 ) : ?>
				<div class="sf-ty__total-row sf-ty__total-row--discount">
					<span><?php esc_html_e( 'Discount', 'samurai' ); ?></span>
					<span>&minus;<?php echo wp_kses_post( wc_price( $order->get_total_discount() ) ); ?></span>
				</div>
				<?php endif; ?>
				<div class="sf-ty__total-row">
					<span><?php esc_html_e( 'Shipping', 'samurai' ); ?></span>
					<span>
						<?php if ( (float) $order->get_shipping_total() > 0 ) :
							echo wp_kses_post( wc_price( $order->get_shipping_total() ) );
						else :
							esc_html_e( 'Free', 'samurai' );
						endif; ?>
					</span>
				</div>
				<?php if ( wc_tax_enabled() && $order->get_total_tax() > 0 ) : ?>
				<div class="sf-ty__total-row">
					<span><?php esc_html_e( 'Tax', 'samurai' ); ?></span>
					<span><?php echo wp_kses_post( wc_price( $order->get_total_tax() ) ); ?></span>
				</div>
				<?php endif; ?>
				<div class="sf-ty__total-row sf-ty__total-row--grand">
					<span><?php esc_html_e( 'Total', 'samurai' ); ?></span>
					<span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
				</div>
			</div>
		</div><!-- /.sf-ty__card (summary) -->

		<!-- What happens next -->
		<?php
		// Detect local pickup.
		$sf_is_pickup = false;
		foreach ( $order->get_shipping_methods() as $sf_sm ) {
			if ( strpos( $sf_sm->get_method_id(), 'local_pickup' ) !== false ) {
				$sf_is_pickup = true;
				break;
			}
		}

		// Map order status → current step index (0 = confirmed, always done).
		$sf_status = $order->get_status();
		if ( $sf_is_pickup ) {
			$sf_step_pos = match ( $sf_status ) {
				'ready-for-pickup' => 2,
				'completed'        => 3,
				default            => 1, // processing
			};
		} else {
			$sf_step_pos = match ( $sf_status ) {
				'shipped'   => 2,
				'completed' => 3,
				default     => 1, // processing
			};
		}

		// Returns the modifier class for a step by its index.
		$sf_step_class = function ( int $idx ) use ( $sf_step_pos ): string {
			if ( $idx < $sf_step_pos )  return 'sf-ty__step--done';
			if ( $idx === $sf_step_pos ) return 'sf-ty__step--active';
			return '';
		};

		$sf_check_icon = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
		?>
		<div class="sf-ty__card sf-ty__card--steps">
			<h3 class="sf-ty__card-title">
				<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
				</svg>
				<?php esc_html_e( 'What Happens Next?', 'samurai' ); ?>
			</h3>
			<ol class="sf-ty__steps">

				<!-- Step 0: always done -->
				<li class="sf-ty__step sf-ty__step--done">
					<span class="sf-ty__step-dot" aria-hidden="true"><?php echo $sf_check_icon; // phpcs:ignore ?></span>
					<div class="sf-ty__step-body">
						<strong><?php esc_html_e( 'Order Confirmed', 'samurai' ); ?></strong>
						<p><?php esc_html_e( "We've received your order and are reviewing it.", 'samurai' ); ?></p>
					</div>
				</li>

				<!-- Step 1: Processing -->
				<li class="sf-ty__step <?php echo esc_attr( $sf_step_class( 1 ) ); ?>">
					<span class="sf-ty__step-dot" aria-hidden="true">
						<?php if ( $sf_step_class( 1 ) === 'sf-ty__step--done' ) echo $sf_check_icon; // phpcs:ignore ?>
					</span>
					<div class="sf-ty__step-body">
						<strong><?php esc_html_e( 'Processing', 'samurai' ); ?></strong>
						<p>
							<?php if ( $sf_is_pickup ) :
								esc_html_e( 'Your order is being prepared for pickup.', 'samurai' );
							else :
								esc_html_e( 'Your fireworks are being carefully packed.', 'samurai' );
							endif; ?>
						</p>
					</div>
				</li>

				<?php if ( $sf_is_pickup ) : ?>

				<!-- Step 2 (pickup): Ready for Pickup -->
				<li class="sf-ty__step <?php echo esc_attr( $sf_step_class( 2 ) ); ?>">
					<span class="sf-ty__step-dot" aria-hidden="true">
						<?php if ( $sf_step_class( 2 ) === 'sf-ty__step--done' ) echo $sf_check_icon; // phpcs:ignore ?>
					</span>
					<div class="sf-ty__step-body">
						<strong><?php esc_html_e( 'Ready for Pickup', 'samurai' ); ?></strong>
						<p><?php esc_html_e( "You'll receive a message when your order is ready to be picked up.", 'samurai' ); ?></p>
					</div>
				</li>

				<!-- Step 3 (pickup): Picked Up -->
				<li class="sf-ty__step <?php echo esc_attr( $sf_step_class( 3 ) ); ?>">
					<span class="sf-ty__step-dot" aria-hidden="true">
						<?php if ( $sf_step_class( 3 ) === 'sf-ty__step--done' ) echo $sf_check_icon; // phpcs:ignore ?>
					</span>
					<div class="sf-ty__step-body">
						<strong><?php esc_html_e( 'Picked Up', 'samurai' ); ?></strong>
						<p><?php esc_html_e( 'Enjoy the show!', 'samurai' ); ?></p>
					</div>
				</li>

				<?php else : ?>

				<!-- Step 2 (shipping): Shipped -->
				<li class="sf-ty__step <?php echo esc_attr( $sf_step_class( 2 ) ); ?>">
					<span class="sf-ty__step-dot" aria-hidden="true">
						<?php if ( $sf_step_class( 2 ) === 'sf-ty__step--done' ) echo $sf_check_icon; // phpcs:ignore ?>
					</span>
					<div class="sf-ty__step-body">
						<strong><?php esc_html_e( 'Shipped', 'samurai' ); ?></strong>
						<p><?php esc_html_e( "On the way — you'll receive a tracking notification.", 'samurai' ); ?></p>
					</div>
				</li>

				<!-- Step 3 (shipping): Delivered -->
				<li class="sf-ty__step <?php echo esc_attr( $sf_step_class( 3 ) ); ?>">
					<span class="sf-ty__step-dot" aria-hidden="true">
						<?php if ( $sf_step_class( 3 ) === 'sf-ty__step--done' ) echo $sf_check_icon; // phpcs:ignore ?>
					</span>
					<div class="sf-ty__step-body">
						<strong><?php esc_html_e( 'Delivered', 'samurai' ); ?></strong>
						<p><?php esc_html_e( 'Light up the sky and enjoy the show!', 'samurai' ); ?></p>
					</div>
				</li>

				<?php endif; ?>

			</ol>
		</div>

		<!-- Quick-action cards -->
		<div class="sf-ty__actions">

			<?php if ( is_user_logged_in() ) : ?>
			<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="sf-ty__action">
				<span class="sf-ty__action-icon">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
						<polyline points="14 2 14 8 20 8"/>
						<line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
						<polyline points="10 9 9 9 8 9"/>
					</svg>
				</span>
				<span class="sf-ty__action-title"><?php esc_html_e( 'View Order', 'samurai' ); ?></span>
				<span class="sf-ty__action-desc">
					<?php
					printf(
						/* translators: %s: order number */
						esc_html__( 'Order #%s', 'samurai' ),
						esc_html( $order->get_order_number() )
					);
					?>
				</span>
			</a>

			<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="sf-ty__action">
				<span class="sf-ty__action-icon">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
						<line x1="8" y1="18" x2="21" y2="18"/>
						<line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
						<line x1="3" y1="18" x2="3.01" y2="18"/>
					</svg>
				</span>
				<span class="sf-ty__action-title"><?php esc_html_e( 'Order History', 'samurai' ); ?></span>
				<span class="sf-ty__action-desc"><?php esc_html_e( 'All your past orders', 'samurai' ); ?></span>
			</a>
			<?php endif; ?>

			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sf-ty__action sf-ty__action--primary">
				<span class="sf-ty__action-icon">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
						<line x1="3" y1="6" x2="21" y2="6"/>
						<path d="M16 10a4 4 0 01-8 0"/>
					</svg>
				</span>
				<span class="sf-ty__action-title"><?php esc_html_e( 'Shop More', 'samurai' ); ?></span>
				<span class="sf-ty__action-desc"><?php esc_html_e( 'Browse our fireworks', 'samurai' ); ?></span>
			</a>

			<?php
			$sf_contact_page = get_page_by_path( 'contact' );
			$sf_contact_url  = $sf_contact_page ? get_permalink( $sf_contact_page ) : '#';
			?>
			<a href="<?php echo esc_url( $sf_contact_url ); ?>" class="sf-ty__action">
				<span class="sf-ty__action-icon">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
					</svg>
				</span>
				<span class="sf-ty__action-title"><?php esc_html_e( 'Need Help?', 'samurai' ); ?></span>
				<span class="sf-ty__action-desc"><?php esc_html_e( "We're here for you", 'samurai' ); ?></span>
			</a>

		</div><!-- /.sf-ty__actions -->

	</div><!-- /.sf-ty__body -->

	<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
	<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

<?php endif; // failed/success ?>

<?php else : ?>

	<!-- No order found -->
	<div class="sf-ty__no-order sf-container--sm">
		<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
			<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
		</svg>
		<h2><?php esc_html_e( 'Order not found', 'samurai' ); ?></h2>
		<p><?php esc_html_e( 'We could not find your order. Please check your email for confirmation details.', 'samurai' ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sf-btn sf-btn--primary">
			<?php esc_html_e( 'Browse Our Shop', 'samurai' ); ?>
		</a>
	</div>

<?php endif; ?>

</div><!-- /.sf-ty -->
