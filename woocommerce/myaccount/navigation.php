<?php
/**
 * My Account navigation — Samurai theme override.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

/* Icon SVG map keyed by WC endpoint slug. */
$sf_nav_icons = [
	'dashboard'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
	'orders'          => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
	'downloads'       => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
	'edit-address'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
	'payment-methods' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
	'edit-account'    => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
	'customer-logout' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
];

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="sf-account-nav woocommerce-MyAccount-navigation"
     aria-label="<?php esc_attr_e( 'Account pages', 'woocommerce' ); ?>">

	<button class="sf-account-nav__toggle js-account-nav-toggle"
	        type="button"
	        aria-expanded="false"
	        aria-controls="sf-account-nav-list">
		<span class="sf-account-nav__toggle-label">
			<?php
			/* Show the active item label on the mobile toggle */
			foreach ( wc_get_account_menu_items() as $sf_ep => $sf_lbl ) {
				if ( wc_is_current_account_menu_item( $sf_ep ) ) {
					echo esc_html( $sf_lbl );
					break;
				}
			}
			?>
		</span>
		<svg class="sf-account-nav__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
	</button>

	<ul class="sf-account-nav__list" id="sf-account-nav-list">
		<?php foreach ( wc_get_account_menu_items() as $sf_endpoint => $sf_label ) : ?>
			<li class="sf-account-nav__item <?php echo esc_attr( wc_get_account_menu_item_classes( $sf_endpoint ) ); ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $sf_endpoint ) ); ?>"
				   <?php echo wc_is_current_account_menu_item( $sf_endpoint ) ? 'aria-current="page"' : ''; ?>>
					<?php if ( isset( $sf_nav_icons[ $sf_endpoint ] ) ) : ?>
						<span class="sf-account-nav__icon"><?php echo $sf_nav_icons[ $sf_endpoint ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<?php endif; ?>
					<span><?php echo esc_html( $sf_label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>

</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>

<script>
(function () {
	var btn  = document.querySelector( '.js-account-nav-toggle' );
	var list = document.getElementById( 'sf-account-nav-list' );
	if ( ! btn || ! list ) return;
	btn.addEventListener( 'click', function () {
		var open = btn.getAttribute( 'aria-expanded' ) === 'true';
		btn.setAttribute( 'aria-expanded', String( ! open ) );
		list.classList.toggle( 'is-open', ! open );
	} );
} )();
</script>
