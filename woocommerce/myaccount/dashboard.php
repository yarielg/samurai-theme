<?php
/**
 * My Account — Dashboard — Samurai theme override.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_orders_url   = wc_get_account_endpoint_url( 'orders' );
$sf_address_url  = wc_get_account_endpoint_url( 'edit-address' );
$sf_account_url  = wc_get_account_endpoint_url( 'edit-account' );
$sf_payment_url  = wc_get_account_endpoint_url( 'payment-methods' );

// Recent orders.
$sf_recent = wc_get_orders( [
	'customer' => get_current_user_id(),
	'limit'    => 5,
	'orderby'  => 'date',
	'order'    => 'DESC',
] );

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
?>

<div class="sf-dashboard">

	<!-- ── Section heading ─────────────────────────────────────────────── -->
	<div class="sf-dashboard__heading-row">
		<h2 class="sf-dashboard__section-title"><?php esc_html_e( 'My Account', 'samurai' ); ?></h2>
	</div>

	<!-- ── Quick-action cards ──────────────────────────────────────────── -->
	<div class="sf-dashboard__cards">

		<a href="<?php echo esc_url( $sf_orders_url ); ?>" class="sf-dashboard__card">
			<span class="sf-dashboard__card-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
			</span>
			<div class="sf-dashboard__card-body">
				<span class="sf-dashboard__card-title"><?php esc_html_e( 'My Orders', 'samurai' ); ?></span>
				<span class="sf-dashboard__card-desc"><?php esc_html_e( 'Track and manage orders', 'samurai' ); ?></span>
			</div>
			<svg class="sf-dashboard__card-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>

		<a href="<?php echo esc_url( $sf_address_url ); ?>" class="sf-dashboard__card">
			<span class="sf-dashboard__card-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
			</span>
			<div class="sf-dashboard__card-body">
				<span class="sf-dashboard__card-title"><?php esc_html_e( 'Addresses', 'samurai' ); ?></span>
				<span class="sf-dashboard__card-desc"><?php esc_html_e( 'Billing & shipping info', 'samurai' ); ?></span>
			</div>
			<svg class="sf-dashboard__card-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>

		<a href="<?php echo esc_url( $sf_account_url ); ?>" class="sf-dashboard__card">
			<span class="sf-dashboard__card-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
			</span>
			<div class="sf-dashboard__card-body">
				<span class="sf-dashboard__card-title"><?php esc_html_e( 'Account Details', 'samurai' ); ?></span>
				<span class="sf-dashboard__card-desc"><?php esc_html_e( 'Name, email, password', 'samurai' ); ?></span>
			</div>
			<svg class="sf-dashboard__card-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>

		<a href="<?php echo esc_url( $sf_payment_url ); ?>" class="sf-dashboard__card">
			<span class="sf-dashboard__card-icon">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
			</span>
			<div class="sf-dashboard__card-body">
				<span class="sf-dashboard__card-title"><?php esc_html_e( 'Payment Methods', 'samurai' ); ?></span>
				<span class="sf-dashboard__card-desc"><?php esc_html_e( 'Saved cards & methods', 'samurai' ); ?></span>
			</div>
			<svg class="sf-dashboard__card-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>

	</div><!-- /.sf-dashboard__cards -->

	<!-- ── Recent orders ───────────────────────────────────────────────── -->
	<?php if ( ! empty( $sf_recent ) ) : ?>
	<div class="sf-dash-orders">
		<div class="sf-dash-orders__header">
			<h3 class="sf-dash-orders__title"><?php esc_html_e( 'Recent Orders', 'samurai' ); ?></h3>
			<a href="<?php echo esc_url( $sf_orders_url ); ?>" class="sf-dash-orders__view-all">
				<?php esc_html_e( 'View all', 'samurai' ); ?>
				<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
			</a>
		</div>
		<div class="sf-dash-orders__list">
			<div class="sf-dash-orders__list-head">
				<span><?php esc_html_e( 'Order', 'samurai' ); ?></span>
				<span><?php esc_html_e( 'Date', 'samurai' ); ?></span>
				<span><?php esc_html_e( 'Status', 'samurai' ); ?></span>
				<span><?php esc_html_e( 'Total', 'samurai' ); ?></span>
				<span></span>
			</div>
			<?php foreach ( $sf_recent as $sf_order ) :
				$sf_st    = $sf_order->get_status();
				$sf_color = $sf_status_colors[ $sf_st ] ?? 'gray';
			?>
			<div class="sf-dash-orders__row">
				<span class="sf-dash-orders__num">
					<a href="<?php echo esc_url( $sf_order->get_view_order_url() ); ?>">
						#<?php echo esc_html( $sf_order->get_order_number() ); ?>
					</a>
				</span>
				<span class="sf-dash-orders__date">
					<?php echo esc_html( wc_format_datetime( $sf_order->get_date_created() ) ); ?>
				</span>
				<span class="sf-dash-orders__status">
					<mark class="sf-status-badge sf-status-badge--<?php echo esc_attr( $sf_color ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $sf_st ) ); ?>
					</mark>
				</span>
				<span class="sf-dash-orders__total">
					<?php echo wp_kses_post( $sf_order->get_formatted_order_total() ); ?>
				</span>
				<span class="sf-dash-orders__action">
					<a href="<?php echo esc_url( $sf_order->get_view_order_url() ); ?>" class="sf-dash-orders__view-btn">
						<?php esc_html_e( 'View', 'samurai' ); ?>
					</a>
				</span>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

</div><!-- /.sf-dashboard -->

<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
