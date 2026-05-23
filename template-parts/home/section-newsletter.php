<?php
/**
 * Homepage — Newsletter / Email capture section.
 *
 * Two-column: copy (perks) + form panel.
 * Submits via AJAX to samurai_newsletter_subscribe → Mailchimp API v3.
 * API credentials and content configured via Theme Settings → Newsletter.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_heading   = function_exists( 'get_field' ) ? get_field( 'newsletter_heading', 'option' )   : '';
$sf_subline   = function_exists( 'get_field' ) ? get_field( 'newsletter_subline', 'option' )   : '';
$sf_terms_url = function_exists( 'get_field' ) ? get_field( 'newsletter_terms_url', 'option' ) : '';

$sf_heading   = $sf_heading   ?: __( 'Stay in the Loop', 'samurai' );
$sf_subline   = $sf_subline   ?: __( 'Be first to know about flash sales, new arrivals, and fireworks season promotions.', 'samurai' );
$sf_terms_url = $sf_terms_url ?: home_url( '/terms-and-conditions/' );
?>
<section class="sf-home-section sf-newsletter-section"
         aria-labelledby="sf-nl-heading">
	<div class="sf-container">
		<div class="sf-newsletter">

			<!-- ── Copy ─────────────────────────────────────────────────── -->
			<div class="sf-newsletter__copy">

				<p class="sf-newsletter__eyebrow"><?php esc_html_e( 'Exclusive Deals & Offers', 'samurai' ); ?></p>

				<h2 class="sf-newsletter__heading" id="sf-nl-heading">
					<?php echo esc_html( $sf_heading ); ?>
				</h2>

				<p class="sf-newsletter__subline"><?php echo esc_html( $sf_subline ); ?></p>

				<ul class="sf-newsletter__perks">
					<li><?php esc_html_e( 'Early access to season sales', 'samurai' ); ?></li>
					<li><?php esc_html_e( 'SMS alerts for limited-time deals', 'samurai' ); ?></li>
					<li><?php esc_html_e( 'Subscriber-only discount codes', 'samurai' ); ?></li>
				</ul>

			</div>

			<!-- ── Form panel ───────────────────────────────────────────── -->
			<div class="sf-newsletter__panel">

				<form class="sf-newsletter__form js-newsletter-form"
				      novalidate
				      aria-label="<?php esc_attr_e( 'Newsletter sign-up', 'samurai' ); ?>">

					<div class="sf-newsletter__field">
						<label for="sf-nl-email" class="sf-sr-only">
							<?php esc_html_e( 'Email address', 'samurai' ); ?>
						</label>
						<input type="email"
						       id="sf-nl-email"
						       name="email"
						       class="sf-newsletter__input"
						       placeholder="<?php esc_attr_e( 'Email address *', 'samurai' ); ?>"
						       required
						       autocomplete="email">
					</div>

					<div class="sf-newsletter__field">
						<label for="sf-nl-phone" class="sf-sr-only">
							<?php esc_html_e( 'Phone number', 'samurai' ); ?>
						</label>
						<input type="tel"
						       id="sf-nl-phone"
						       name="phone"
						       class="sf-newsletter__input"
						       placeholder="<?php esc_attr_e( 'Phone number (optional)', 'samurai' ); ?>"
						       autocomplete="tel">
					</div>

					<label class="sf-newsletter__terms-label">
						<input type="checkbox" name="terms" class="sf-newsletter__checkbox" required>
						<span class="sf-newsletter__terms-text">
							<?php esc_html_e( 'I accept the', 'samurai' ); ?>
							<a href="<?php echo esc_url( $sf_terms_url ); ?>"
							   target="_blank"
							   rel="noopener noreferrer">
								<?php esc_html_e( 'Terms & Conditions', 'samurai' ); ?>
							</a>
							<?php esc_html_e( 'and agree to receive marketing communications.', 'samurai' ); ?>
						</span>
					</label>

					<button type="submit" class="sf-btn sf-btn--primary sf-newsletter__btn">
						<span class="sf-newsletter__btn-text">
							<?php esc_html_e( 'Subscribe Now', 'samurai' ); ?>
						</span>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
					</button>

					<p class="sf-newsletter__privacy">
						<?php esc_html_e( 'We respect your privacy. Unsubscribe at any time.', 'samurai' ); ?>
					</p>

				</form>

				<!-- Success state — revealed by JS after submission -->
				<div class="sf-newsletter__success" aria-live="polite" hidden>
					<svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="16 10 11 15 8 12"/></svg>
					<p class="sf-newsletter__success-msg"><?php esc_html_e( 'Subscribed!', 'samurai' ); ?></p>
					<p class="sf-newsletter__success-sub"><?php esc_html_e( "You'll be the first to know about our deals and new arrivals.", 'samurai' ); ?></p>
				</div>

			</div><!-- /.sf-newsletter__panel -->

		</div><!-- /.sf-newsletter -->
	</div><!-- /.sf-container -->
</section>
