<?php
/**
 * Homepage — Miami Local Pickup section.
 *
 * Renders a strong local pickup CTA strip powered by ACF contact fields
 * and homepage settings. Addresses Miami customers who prefer in-store pickup.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

// Homepage heading/copy — Theme Settings → Homepage
$sf_heading  = samurai_option_text( 'home_pickup_heading', __( 'Local Pickup in Miami, FL', 'samurai' ), false );
$sf_subline  = samurai_option_text( 'home_pickup_subline', __( 'Order online and pick up at our warehouse — same day, no shipping fee.', 'samurai' ), false );
$sf_cta_lbl  = samurai_option_text( 'home_pickup_cta_label', __( 'Get Directions', 'samurai' ), false );
$sf_cta_url  = function_exists( 'get_field' ) ? get_field( 'home_pickup_cta_url', 'option' ) : '';

// Contact details — Theme Settings → Contact & Hours
$sf_address  = samurai_option_text( 'contact_address', '', false );
$sf_hours    = samurai_option_text( 'contact_hours', '', false );
$sf_phone    = samurai_option_text( 'contact_phone', '', false );
?>
<section class="sf-home-section sf-pickup-section" aria-label="<?php esc_attr_e( 'Local pickup information', 'samurai' ); ?>">
	<div class="sf-container">
		<div class="sf-pickup-inner">

			<div class="sf-pickup-icon" aria-hidden="true">
				<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
					<path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
					<circle cx="12" cy="9" r="2.5"/>
				</svg>
			</div>

			<div class="sf-pickup-content">
				<h2 class="sf-pickup-heading"><?php echo esc_html( $sf_heading ); ?></h2>
				<p class="sf-pickup-subline"><?php echo esc_html( $sf_subline ); ?></p>

				<?php if ( $sf_address || $sf_hours || $sf_phone ) : ?>
				<ul class="sf-pickup-details">
					<?php if ( $sf_address ) : ?>
					<li class="sf-pickup-detail">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
						<span><?php echo esc_html( $sf_address ); ?></span>
					</li>
					<?php endif; ?>
					<?php if ( $sf_hours ) : ?>
					<li class="sf-pickup-detail">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
						<span><?php echo esc_html( $sf_hours ); ?></span>
					</li>
					<?php endif; ?>
					<?php if ( $sf_phone ) : ?>
					<li class="sf-pickup-detail">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6.18 6.18l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $sf_phone ) ); ?>"><?php echo esc_html( $sf_phone ); ?></a>
					</li>
					<?php endif; ?>
				</ul>
				<?php endif; ?>
			</div>

			<?php if ( $sf_cta_url ) : ?>
			<div class="sf-pickup-cta">
				<a href="<?php echo esc_url( $sf_cta_url ); ?>"
				   class="sf-btn sf-btn--primary sf-btn--lg"
				   target="_blank"
				   rel="noopener noreferrer">
					<?php echo esc_html( $sf_cta_lbl ); ?>
				</a>
			</div>
			<?php endif; ?>

		</div>
	</div>
</section>
