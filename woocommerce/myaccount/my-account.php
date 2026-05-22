<?php
/**
 * My Account page — Samurai theme override.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_user       = wp_get_current_user();
$sf_initials   = '';
if ( $sf_user->first_name && $sf_user->last_name ) {
	$sf_initials = strtoupper( $sf_user->first_name[0] . $sf_user->last_name[0] );
} elseif ( $sf_user->display_name ) {
	$sf_parts    = explode( ' ', trim( $sf_user->display_name ) );
	$sf_initials = strtoupper( $sf_parts[0][0] . ( isset( $sf_parts[1] ) ? $sf_parts[1][0] : '' ) );
}

// Quick stats for hero.
$sf_order_count = wc_get_customer_order_count( $sf_user->ID );
$sf_user_data   = get_userdata( $sf_user->ID );
$sf_member_year = $sf_user_data ? date_i18n( 'Y', strtotime( $sf_user_data->user_registered ) ) : '';
?>
<div class="sf-account-page">

	<!-- ── Account hero ──────────────────────────────────────────────────── -->
	<div class="sf-account-hero">
		<div class="sf-container">
			<div class="sf-account-hero__inner">

				<div class="sf-account-hero__avatar" aria-hidden="true">
					<?php echo esc_html( $sf_initials ?: '?' ); ?>
				</div>

				<div class="sf-account-hero__info">
					<p class="sf-account-hero__name">
						<?php
						printf(
							/* translators: %s: first name */
							esc_html__( 'Hello, %s', 'samurai' ),
							esc_html( $sf_user->first_name ?: $sf_user->display_name )
						);
						?>
					</p>
					<p class="sf-account-hero__email"><?php echo esc_html( $sf_user->user_email ); ?></p>
				</div>

				<div class="sf-account-hero__stats">
					<div class="sf-account-hero__stat">
						<span class="sf-account-hero__stat-num"><?php echo absint( $sf_order_count ); ?></span>
						<span class="sf-account-hero__stat-label"><?php esc_html_e( 'Orders', 'samurai' ); ?></span>
					</div>
					<?php if ( $sf_member_year ) : ?>
					<div class="sf-account-hero__stat">
						<span class="sf-account-hero__stat-num"><?php echo esc_html( $sf_member_year ); ?></span>
						<span class="sf-account-hero__stat-label"><?php esc_html_e( 'Member Since', 'samurai' ); ?></span>
					</div>
					<?php endif; ?>
				</div>

				<a href="<?php echo esc_url( wc_logout_url() ); ?>" class="sf-account-hero__logout">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
						<polyline points="16 17 21 12 16 7"/>
						<line x1="21" y1="12" x2="9" y2="12"/>
					</svg>
					<?php esc_html_e( 'Sign Out', 'samurai' ); ?>
				</a>

			</div>
		</div>
	</div><!-- /.sf-account-hero -->

	<!-- ── Sidebar + content ──────────────────────────────────────────────── -->
	<div class="sf-container">
		<div class="sf-account-layout">

			<div class="sf-account-nav-wrapper">
				<?php do_action( 'woocommerce_account_navigation' ); ?>
			</div>

			<div class="sf-account-content woocommerce-MyAccount-content">
				<?php do_action( 'woocommerce_account_content' ); ?>
			</div>

		</div>
	</div>

</div><!-- /.sf-account-page -->
