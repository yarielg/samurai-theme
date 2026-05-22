<?php
/**
 * Checkout step progress indicator.
 *
 * $args['step'] — integer:
 *   1 = Cart (active on cart page)
 *   2 = Shipping (active on checkout panel 1)
 *   3 = Payment  (active on checkout panel 2)
 *   4 = Review   (active on order received)
 *
 * JS updates data-current on the wrapper when panels change.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_step = isset( $args['step'] ) ? (int) $args['step'] : 1;
?>
<nav class="sf-steps" data-current="<?php echo absint( $sf_step ); ?>"
     aria-label="<?php esc_attr_e( 'Checkout progress', 'samurai' ); ?>">

	<!-- Step 1: Cart -->
	<div class="sf-steps__item<?php echo $sf_step > 1 ? ' is-complete' : ( $sf_step === 1 ? ' is-active' : '' ); ?>"
	     data-step-item="1"
	     aria-current="<?php echo 1 === $sf_step ? 'step' : 'false'; ?>">
		<span class="sf-steps__dot">
			<?php if ( $sf_step > 1 ) : ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
			<?php else : ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-7.43H6"/></svg>
			<?php endif; ?>
		</span>
		<span class="sf-steps__label"><?php esc_html_e( 'Cart', 'samurai' ); ?></span>
	</div>

	<div class="sf-steps__line<?php echo $sf_step >= 2 ? ' is-passed' : ''; ?>" aria-hidden="true"></div>

	<!-- Step 2: Shipping -->
	<div class="sf-steps__item<?php echo $sf_step > 2 ? ' is-complete' : ( $sf_step === 2 ? ' is-active' : '' ); ?>"
	     data-step-item="2"
	     aria-current="<?php echo 2 === $sf_step ? 'step' : 'false'; ?>">
		<span class="sf-steps__dot">
			<?php if ( $sf_step > 2 ) : ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
			<?php else : ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="3" width="15" height="13" rx="1"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
			<?php endif; ?>
		</span>
		<span class="sf-steps__label"><?php esc_html_e( 'Shipping', 'samurai' ); ?></span>
	</div>

	<div class="sf-steps__line<?php echo $sf_step >= 3 ? ' is-passed' : ''; ?>" aria-hidden="true"></div>

	<!-- Step 3: Payment -->
	<div class="sf-steps__item<?php echo $sf_step > 3 ? ' is-complete' : ( $sf_step === 3 ? ' is-active' : '' ); ?>"
	     data-step-item="3"
	     aria-current="<?php echo 3 === $sf_step ? 'step' : 'false'; ?>">
		<span class="sf-steps__dot">
			<?php if ( $sf_step > 3 ) : ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
			<?php else : ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
			<?php endif; ?>
		</span>
		<span class="sf-steps__label"><?php esc_html_e( 'Payment', 'samurai' ); ?></span>
	</div>

	<div class="sf-steps__line<?php echo $sf_step >= 4 ? ' is-passed' : ''; ?>" aria-hidden="true"></div>

	<!-- Step 4: Review -->
	<div class="sf-steps__item<?php echo $sf_step === 4 ? ' is-active' : ''; ?>"
	     data-step-item="4"
	     aria-current="<?php echo 4 === $sf_step ? 'step' : 'false'; ?>">
		<span class="sf-steps__dot">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
		</span>
		<span class="sf-steps__label"><?php esc_html_e( 'Review', 'samurai' ); ?></span>
	</div>

</nav>
