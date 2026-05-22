<?php
/**
 * My Account — View Order — Samurai theme override.
 *
 * @package samurai
 * @var WC_Order $order
 * @var int      $order_id
 */
defined( 'ABSPATH' ) || exit;

// Remove WC's default order-details table + address output.
remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 );

$sf_notes = $order->get_customer_order_notes();

// Status badge colour map.
$sf_status_colors = [
	'completed'        => 'green',
	'processing'       => 'orange',
	'on-hold'          => 'blue',
	'pending'          => 'blue',
	'cancelled'        => 'red',
	'failed'           => 'red',
	'refunded'         => 'gray',
	'ready-for-pickup' => 'orange',
];
$sf_status      = $order->get_status();
$sf_badge_color = $sf_status_colors[ $sf_status ] ?? 'gray';

// Detect local pickup.
$sf_is_pickup = false;
foreach ( $order->get_shipping_methods() as $sf_sm ) {
	if ( strpos( $sf_sm->get_method_id(), 'local_pickup' ) !== false ) {
		$sf_is_pickup = true;
		break;
	}
}

// Map order status → active step index.
if ( $sf_is_pickup ) {
	$sf_step_pos = match ( $sf_status ) {
		'ready-for-pickup' => 2,
		'completed'        => 3,
		default            => 1,
	};
} else {
	$sf_step_pos = match ( $sf_status ) {
		'shipped'   => 2,
		'completed' => 3,
		default     => 1,
	};
}

$sf_step_class = function ( int $idx ) use ( $sf_step_pos ): string {
	if ( $idx < $sf_step_pos )  return 'sf-ty__step--done';
	if ( $idx === $sf_step_pos ) return 'sf-ty__step--active';
	return '';
};

$sf_check_icon = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>';
?>

<div class="sf-account-section sf-view-order">

	<!-- ── Section header ──────────────────────────────────────────────────── -->
	<div class="sf-account-section__header">
		<h2 class="sf-account-section__title">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
				<polyline points="14 2 14 8 20 8"/>
				<line x1="16" y1="13" x2="8" y2="13"/>
				<line x1="16" y1="17" x2="8" y2="17"/>
				<polyline points="10 9 9 9 8 9"/>
			</svg>
			<?php printf( esc_html__( 'Order #%s', 'samurai' ), esc_html( $order->get_order_number() ) ); ?>
		</h2>
		<mark class="sf-status-badge sf-status-badge--<?php echo esc_attr( $sf_badge_color ); ?>">
			<?php echo esc_html( wc_get_order_status_name( $sf_status ) ); ?>
		</mark>
	</div>

	<!-- ── Order meta strip ────────────────────────────────────────────────── -->
	<div class="sf-view-order__meta">
		<div class="sf-view-order__meta-item">
			<span class="sf-view-order__meta-label"><?php esc_html_e( 'Date', 'samurai' ); ?></span>
			<span class="sf-view-order__meta-val"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
		</div>
		<div class="sf-view-order__meta-item">
			<span class="sf-view-order__meta-label"><?php esc_html_e( 'Total', 'samurai' ); ?></span>
			<span class="sf-view-order__meta-val sf-view-order__meta-val--total"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
		</div>
		<?php if ( $order->get_payment_method_title() ) : ?>
		<div class="sf-view-order__meta-item">
			<span class="sf-view-order__meta-label"><?php esc_html_e( 'Payment', 'samurai' ); ?></span>
			<span class="sf-view-order__meta-val"><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
		</div>
		<?php endif; ?>
	</div>

	<!-- ── Status timeline ─────────────────────────────────────────────────── -->
	<div class="sf-ty__card sf-ty__card--steps">
		<h3 class="sf-ty__card-title">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
			</svg>
			<?php esc_html_e( 'Order Status', 'samurai' ); ?>
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
	</div><!-- /.sf-ty__card--steps -->

	<!-- ── Order items + totals ────────────────────────────────────────────── -->
	<div class="sf-ty__card">
		<h3 class="sf-ty__card-title"><?php esc_html_e( 'Order Summary', 'samurai' ); ?></h3>

		<div class="sf-ty__items">
			<?php foreach ( $order->get_items() as $item_id => $item ) :
				$sf_product  = $item->get_product();
				$sf_thumb    = $sf_product && $sf_product->get_image_id()
					? wp_get_attachment_image_url( $sf_product->get_image_id(), 'thumbnail' )
					: wc_placeholder_img_src( 'thumbnail' );
			?>
			<div class="sf-ty__item">
				<div class="sf-ty__item-img">
					<img src="<?php echo esc_url( $sf_thumb ); ?>"
					     alt="<?php echo esc_attr( $item->get_name() ); ?>"
					     width="56" height="56" loading="lazy">
					<span class="sf-ty__item-qty"><?php echo absint( $item->get_quantity() ); ?></span>
				</div>
				<div class="sf-ty__item-info">
					<span class="sf-ty__item-name"><?php echo esc_html( $item->get_name() ); ?></span>
					<?php
					$sf_meta = wc_display_item_meta( $item, [ 'echo' => false ] );
					if ( $sf_meta ) echo '<span class="sf-ty__item-meta">' . wp_kses_post( $sf_meta ) . '</span>';
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

	<!-- ── Order updates / notes ───────────────────────────────────────────── -->
	<?php if ( $sf_notes ) : ?>
	<div class="sf-ty__card">
		<h3 class="sf-ty__card-title">
			<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
			</svg>
			<?php esc_html_e( 'Order Updates', 'samurai' ); ?>
		</h3>
		<ol class="sf-view-order__note-list">
			<?php foreach ( $sf_notes as $sf_note ) : ?>
			<li class="sf-view-order__note">
				<time class="sf-view-order__note-date">
					<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $sf_note->comment_date ) ) ); ?>
				</time>
				<div class="sf-view-order__note-text">
					<?php echo wp_kses_post( wpautop( wptexturize( $sf_note->comment_content ) ) ); ?>
				</div>
			</li>
			<?php endforeach; ?>
		</ol>
	</div>
	<?php endif; ?>

	<!-- ── Back link ───────────────────────────────────────────────────────── -->
	<div class="sf-view-order__back">
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="sf-view-order__back-link">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<line x1="19" y1="12" x2="5" y2="12"/>
				<polyline points="12 19 5 12 12 5"/>
			</svg>
			<?php esc_html_e( 'Back to Orders', 'samurai' ); ?>
		</a>
	</div>

	<?php do_action( 'woocommerce_view_order', $order_id ); ?>

</div><!-- /.sf-view-order -->
