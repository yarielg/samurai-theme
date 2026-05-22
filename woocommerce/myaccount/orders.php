<?php
/**
 * My Account — Orders list — Samurai theme override.
 *
 * @package samurai
 * @var WP_Post[]     $customer_orders
 * @var bool          $has_orders
 * @var int           $current_page
 */
defined( 'ABSPATH' ) || exit;

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

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<div class="sf-account-section">
	<div class="sf-account-section__header">
		<h2 class="sf-account-section__title">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
			<?php esc_html_e( 'My Orders', 'samurai' ); ?>
		</h2>
	</div>

<?php if ( $has_orders ) : ?>

	<div class="sf-orders-list">

		<!-- Table head (desktop) -->
		<div class="sf-orders-list__head" aria-hidden="true">
			<span><?php esc_html_e( 'Order', 'samurai' ); ?></span>
			<span><?php esc_html_e( 'Date', 'samurai' ); ?></span>
			<span><?php esc_html_e( 'Status', 'samurai' ); ?></span>
			<span><?php esc_html_e( 'Total', 'samurai' ); ?></span>
			<span><?php esc_html_e( 'Actions', 'samurai' ); ?></span>
		</div>

		<?php foreach ( $customer_orders->orders as $sf_order_id ) :
			$sf_order      = wc_get_order( $sf_order_id );
			$sf_item_count = $sf_order->get_item_count() - $sf_order->get_item_count_refunded();
			$sf_st         = $sf_order->get_status();
			$sf_color      = $sf_status_colors[ $sf_st ] ?? 'gray';
			$sf_actions    = wc_get_account_orders_actions( $sf_order );
		?>
		<div class="sf-orders-list__row">

			<span class="sf-orders-list__num" data-label="<?php esc_attr_e( 'Order', 'samurai' ); ?>">
				<a href="<?php echo esc_url( $sf_order->get_view_order_url() ); ?>" class="sf-orders-list__num-link">
					#<?php echo esc_html( $sf_order->get_order_number() ); ?>
				</a>
			</span>

			<span class="sf-orders-list__date" data-label="<?php esc_attr_e( 'Date', 'samurai' ); ?>">
				<time datetime="<?php echo esc_attr( $sf_order->get_date_created()->date( 'c' ) ); ?>">
					<?php echo esc_html( wc_format_datetime( $sf_order->get_date_created() ) ); ?>
				</time>
			</span>

			<span class="sf-orders-list__status" data-label="<?php esc_attr_e( 'Status', 'samurai' ); ?>">
				<mark class="sf-status-badge sf-status-badge--<?php echo esc_attr( $sf_color ); ?>">
					<?php echo esc_html( wc_get_order_status_name( $sf_st ) ); ?>
				</mark>
			</span>

			<span class="sf-orders-list__total" data-label="<?php esc_attr_e( 'Total', 'samurai' ); ?>">
				<?php
				printf(
					/* translators: 1: formatted total 2: item count */
					wp_kses_post( _n( '%1$s &mdash; %2$s item', '%1$s &mdash; %2$s items', $sf_item_count, 'samurai' ) ),
					wp_kses_post( $sf_order->get_formatted_order_total() ),
					absint( $sf_item_count )
				);
				?>
			</span>

			<span class="sf-orders-list__actions" data-label="<?php esc_attr_e( 'Actions', 'samurai' ); ?>">
				<?php if ( ! empty( $sf_actions ) ) :
					foreach ( $sf_actions as $sf_key => $sf_action ) :
						$sf_is_primary = in_array( $sf_key, [ 'view', 'pay' ], true );
				?>
				<a href="<?php echo esc_url( $sf_action['url'] ); ?>"
				   class="sf-orders-list__action sf-orders-list__action--<?php echo esc_attr( $sf_key ); ?><?php echo $sf_is_primary ? ' is-primary' : ''; ?>"
				   aria-label="<?php echo esc_attr( $sf_action['name'] . ' #' . $sf_order->get_order_number() ); ?>">
					<?php echo esc_html( $sf_action['name'] ); ?>
				</a>
				<?php endforeach; endif; ?>
			</span>

		</div>
		<?php endforeach; ?>

	</div><!-- /.sf-orders-list -->

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
	<div class="sf-orders-pagination">
		<?php if ( 1 !== $current_page ) : ?>
		<a class="sf-orders-pagination__btn" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
			<?php esc_html_e( 'Previous', 'samurai' ); ?>
		</a>
		<?php endif; ?>
		<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
		<a class="sf-orders-pagination__btn" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
			<?php esc_html_e( 'Next', 'samurai' ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
		</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>

<?php else : ?>

	<div class="sf-account-empty">
		<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
		<p><?php esc_html_e( "You haven't placed any orders yet.", 'samurai' ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sf-btn sf-btn--primary">
			<?php esc_html_e( 'Browse Our Fireworks', 'samurai' ); ?>
		</a>
	</div>

<?php endif; ?>

</div><!-- /.sf-account-section -->

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
