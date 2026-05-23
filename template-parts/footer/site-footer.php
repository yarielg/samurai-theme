<?php
/**
 * Site footer template.
 *
 * ACF option fields used (all under "Footer" sub-page):
 *   brand_tagline, footer_description, footer_legal_copy
 *   contact_phone, contact_whatsapp, contact_email, contact_address, contact_hours
 *   social_facebook, social_instagram, social_youtube, social_x
 *   trust_item_1 … trust_item_4
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

// Brand
$sf_tagline        = samurai_option_text( 'brand_tagline',        'Ignite Every Moment. Miami Style.', false );
$sf_footer_desc    = function_exists( 'get_field' ) ? get_field( 'footer_description',  'option' ) : '';
$sf_footer_legal   = function_exists( 'get_field' ) ? get_field( 'footer_legal_copy',   'option' ) : '';
$sf_footer_img     = function_exists( 'get_field' ) ? get_field( 'footer_logo_image',   'option' ) : null;

// Contact
$sf_phone     = function_exists( 'get_field' ) ? get_field( 'contact_phone',    'option' ) : '';
$sf_whatsapp  = function_exists( 'get_field' ) ? get_field( 'contact_whatsapp', 'option' ) : '';
$sf_email     = function_exists( 'get_field' ) ? get_field( 'contact_email',    'option' ) : '';
$sf_address   = function_exists( 'get_field' ) ? get_field( 'contact_address',  'option' ) : '';
$sf_hours     = function_exists( 'get_field' ) ? get_field( 'contact_hours',    'option' ) : '';

// Social
$sf_fb  = function_exists( 'get_field' ) ? get_field( 'social_facebook',  'option' ) : '';
$sf_ig  = function_exists( 'get_field' ) ? get_field( 'social_instagram', 'option' ) : '';
$sf_yt  = function_exists( 'get_field' ) ? get_field( 'social_youtube',   'option' ) : '';
$sf_x   = function_exists( 'get_field' ) ? get_field( 'social_x',         'option' ) : '';

// Helpers
$sf_phone_raw    = preg_replace( '/[^0-9+]/', '', (string) $sf_phone );
$sf_wa_raw       = preg_replace( '/[^0-9]/', '', (string) $sf_whatsapp );
$sf_chevron      = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';

$sf_has_connect  = $sf_phone || $sf_whatsapp || $sf_fb || $sf_ig || $sf_yt || $sf_x;
?>
<footer id="sf-footer" class="sf-footer" role="contentinfo">

	<!-- ══ MAIN GRID ════════════════════════════════════════════════════════════ -->
	<div class="sf-footer__main">
		<div class="sf-container sf-footer__grid">

			<!-- Column 1 — Brand -->
			<div class="sf-footer__col sf-footer__col--brand">

				<a class="sf-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php bloginfo( 'name' ); ?>">
					<?php if ( ! empty( $sf_footer_img['url'] ) ) : ?>
						<img src="<?php echo esc_url( $sf_footer_img['url'] ); ?>"
							alt="<?php echo esc_attr( $sf_footer_img['alt'] ?: get_bloginfo( 'name' ) ); ?>"
							width="<?php echo esc_attr( $sf_footer_img['width'] ?? '' ); ?>"
							height="<?php echo esc_attr( $sf_footer_img['height'] ?? '' ); ?>"
							loading="lazy"
							class="sf-footer__logo-img">
					<?php elseif ( has_custom_logo() ) : the_custom_logo();
					else : ?>
						<span class="sf-footer__site-name"><?php bloginfo( 'name' ); ?></span>
					<?php endif; ?>
				</a>

				<?php if ( $sf_tagline ) : ?>
					<span class="sf-footer__tagline"><?php echo esc_html( $sf_tagline ); ?></span>
				<?php endif; ?>

				<?php if ( $sf_footer_desc ) : ?>
					<p class="sf-footer__desc"><?php echo wp_kses_post( $sf_footer_desc ); ?></p>
				<?php endif; ?>

				<?php if ( $sf_address || $sf_hours || $sf_email ) : ?>
				<address class="sf-footer__contact">
					<?php if ( $sf_address ) : ?>
					<span class="sf-footer__contact-row">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
						<?php echo esc_html( $sf_address ); ?>
					</span>
					<?php endif; ?>
					<?php if ( $sf_hours ) : ?>
					<span class="sf-footer__contact-row">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
						<?php echo esc_html( $sf_hours ); ?>
					</span>
					<?php endif; ?>
					<?php if ( $sf_email ) : ?>
					<a href="mailto:<?php echo esc_attr( antispambot( $sf_email ) ); ?>" class="sf-footer__contact-row">
						<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
						<?php echo esc_html( $sf_email ); ?>
					</a>
					<?php endif; ?>
				</address>
				<?php endif; ?>

			</div><!-- /.sf-footer__col--brand -->

			<!-- Columns 2-4 — Nav menus (accordion on mobile) -->
			<?php
			$sf_nav_cols = [
				[ 'location' => 'footer_1', 'label' => __( 'Shop', 'samurai' ) ],
				[ 'location' => 'footer_2', 'label' => __( 'Information', 'samurai' ) ],
				[ 'location' => 'footer_3', 'label' => __( 'Legal', 'samurai' ) ],
			];
			foreach ( $sf_nav_cols as $sf_i => $sf_col ) :
				if ( ! has_nav_menu( $sf_col['location'] ) ) continue;
				$sf_col_id = 'sf-footer-nav-' . $sf_i;
			?>
			<div class="sf-footer__col">
				<button
					type="button"
					class="sf-footer__col-trigger js-footer-accordion"
					aria-expanded="false"
					aria-controls="<?php echo esc_attr( $sf_col_id ); ?>"
				>
					<?php echo esc_html( $sf_col['label'] ); ?>
					<?php echo $sf_chevron; // phpcs:ignore ?>
				</button>
				<div id="<?php echo esc_attr( $sf_col_id ); ?>" class="sf-footer__col-menu" aria-hidden="true">
					<?php
					wp_nav_menu( [
						'theme_location' => $sf_col['location'],
						'menu_class'     => 'sf-footer__menu',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => false,
					] );
					?>
				</div>
			</div>
			<?php endforeach; ?>

		</div><!-- /.sf-footer__grid -->
	</div><!-- /.sf-footer__main -->

	<!-- ══ STAY CONNECTED ════════════════════════════════════════════════════════ -->
	<?php if ( $sf_has_connect ) : ?>
	<div class="sf-footer__connect">
		<div class="sf-container sf-footer__connect-inner">

			<div class="sf-footer__connect-left">
				<p class="sf-footer__connect-label"><?php esc_html_e( 'Stay Connected', 'samurai' ); ?></p>

				<div class="sf-footer__connect-actions">
					<?php if ( $sf_phone ) : ?>
					<a href="tel:<?php echo esc_attr( $sf_phone_raw ); ?>" class="sf-footer__connect-btn sf-footer__connect-btn--phone">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.29a16 16 0 0 0 5.8 5.8l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
						<span>
							<span class="sf-footer__connect-btn-label"><?php esc_html_e( 'Call Us', 'samurai' ); ?></span>
							<span class="sf-footer__connect-btn-val"><?php echo esc_html( $sf_phone ); ?></span>
						</span>
					</a>
					<?php endif; ?>

					<?php if ( $sf_whatsapp && $sf_wa_raw ) : ?>
					<a href="https://wa.me/<?php echo esc_attr( $sf_wa_raw ); ?>" class="sf-footer__connect-btn sf-footer__connect-btn--wa" target="_blank" rel="noopener noreferrer">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
						<span>
							<span class="sf-footer__connect-btn-label"><?php esc_html_e( 'Text / WhatsApp', 'samurai' ); ?></span>
							<span class="sf-footer__connect-btn-val"><?php echo esc_html( $sf_whatsapp ); ?></span>
						</span>
					</a>
					<?php endif; ?>
				</div>
			</div><!-- /.sf-footer__connect-left -->

			<?php if ( $sf_fb || $sf_ig || $sf_yt || $sf_x ) : ?>
			<div class="sf-footer__connect-right">
				<p class="sf-footer__connect-label"><?php esc_html_e( 'Follow Us', 'samurai' ); ?></p>
				<div class="sf-footer__socials">

					<?php if ( $sf_yt ) : ?>
					<a href="<?php echo esc_url( $sf_yt ); ?>" class="sf-footer__social-link sf-footer__social-link--yt" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.54C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
					</a>
					<?php endif; ?>

					<?php if ( $sf_ig ) : ?>
					<a href="<?php echo esc_url( $sf_ig ); ?>" class="sf-footer__social-link sf-footer__social-link--ig" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
					</a>
					<?php endif; ?>

					<?php if ( $sf_fb ) : ?>
					<a href="<?php echo esc_url( $sf_fb ); ?>" class="sf-footer__social-link sf-footer__social-link--fb" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
					</a>
					<?php endif; ?>

					<?php if ( $sf_x ) : ?>
					<a href="<?php echo esc_url( $sf_x ); ?>" class="sf-footer__social-link sf-footer__social-link--x" aria-label="X (Twitter)" target="_blank" rel="noopener noreferrer">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.746l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-7.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
					</a>
					<?php endif; ?>

				</div>
			</div><!-- /.sf-footer__connect-right -->
			<?php endif; ?>

		</div><!-- /.sf-footer__connect-inner -->
	</div><!-- /.sf-footer__connect -->
	<?php endif; ?>

	<!-- ══ TRUST STRIP ═══════════════════════════════════════════════════════════ -->
	<?php
	$sf_trust_icons = [
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
	];
	$sf_trust_defaults = [
		__( 'Secure Checkout',   'samurai' ),
		__( 'Fast Local Pickup', 'samurai' ),
		__( 'Licensed & Insured','samurai' ),
		__( "Miami's #1 Store",  'samurai' ),
	];
	?>
	<div class="sf-footer__trust sf-container">
		<?php foreach ( $sf_trust_defaults as $sf_i => $sf_default ) :
			$sf_label = samurai_option_text( 'trust_item_' . ( $sf_i + 1 ), $sf_default, false );
			if ( ! $sf_label ) continue;
		?>
		<span class="sf-footer__trust-item">
			<?php echo $sf_trust_icons[ $sf_i ]; // phpcs:ignore ?>
			<?php echo esc_html( $sf_label ); ?>
		</span>
		<?php endforeach; ?>
	</div>

	<!-- ══ BOTTOM BAR ════════════════════════════════════════════════════════════ -->
	<div class="sf-footer__bottom">
		<div class="sf-container sf-footer__bottom-inner">
			<p class="sf-footer__legal">
				<?php if ( $sf_footer_legal ) :
					echo wp_kses_post( $sf_footer_legal );
				else : ?>
					&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.
					<?php esc_html_e( 'All rights reserved.', 'samurai' ); ?>
				<?php endif; ?>
			</p>
			<div class="sf-footer__payment" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'samurai' ); ?>">
				<span class="sf-footer__payment-label"><?php esc_html_e( 'We accept:', 'samurai' ); ?></span>
				<img src="<?php echo esc_url( SAMURAI_URL . '/assets/images/payment-visa.svg' ); ?>"       alt="Visa"       class="sf-payment-icon" width="38" height="22" loading="lazy" onerror="this.style.display='none'">
				<img src="<?php echo esc_url( SAMURAI_URL . '/assets/images/payment-mastercard.svg' ); ?>" alt="Mastercard" class="sf-payment-icon" width="38" height="22" loading="lazy" onerror="this.style.display='none'">
				<img src="<?php echo esc_url( SAMURAI_URL . '/assets/images/payment-amex.svg' ); ?>"       alt="Amex"       class="sf-payment-icon" width="38" height="22" loading="lazy" onerror="this.style.display='none'">
				<img src="<?php echo esc_url( SAMURAI_URL . '/assets/images/payment-paypal.svg' ); ?>"     alt="PayPal"     class="sf-payment-icon" width="38" height="22" loading="lazy" onerror="this.style.display='none'">
			</div>
		</div>
	</div>

</footer>
