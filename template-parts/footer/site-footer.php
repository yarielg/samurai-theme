<?php
defined( 'ABSPATH' ) || exit;

$phone        = function_exists( 'get_field' ) ? get_field( 'contact_phone', 'option' )        : '';
$email        = function_exists( 'get_field' ) ? get_field( 'contact_email', 'option' )        : '';
$address      = function_exists( 'get_field' ) ? get_field( 'contact_address', 'option' )      : '';
$hours        = function_exists( 'get_field' ) ? get_field( 'contact_hours', 'option' )        : '';
$tagline      = function_exists( 'get_field' ) ? get_field( 'brand_tagline', 'option' )        : 'Ignite Every Moment. Miami Style.';
$footer_desc  = function_exists( 'get_field' ) ? get_field( 'footer_description', 'option' )  : '';
$footer_legal = function_exists( 'get_field' ) ? get_field( 'footer_legal_copy', 'option' )   : '';
$fb_url       = function_exists( 'get_field' ) ? get_field( 'social_facebook', 'option' )     : '';
$ig_url       = function_exists( 'get_field' ) ? get_field( 'social_instagram', 'option' )    : '';
$yt_url       = function_exists( 'get_field' ) ? get_field( 'social_youtube', 'option' )      : '';

$icon_chevron = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
?>
<footer id="sf-footer" class="sf-footer" role="contentinfo">

	<div class="sf-footer__main">
		<div class="sf-container sf-footer__grid">

			<!-- Brand column -->
			<div class="sf-footer__col sf-footer__col--brand">
				<a class="sf-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php if ( has_custom_logo() ) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<span class="sf-footer__site-name"><?php bloginfo( 'name' ); ?></span>
					<?php endif; ?>
				</a>

				<?php if ( $tagline ) : ?>
					<span class="sf-footer__tagline"><?php echo esc_html( $tagline ); ?></span>
				<?php endif; ?>

				<?php if ( $footer_desc ) : ?>
					<p class="sf-footer__desc"><?php echo wp_kses_post( $footer_desc ); ?></p>
				<?php endif; ?>

				<?php if ( $fb_url || $ig_url || $yt_url ) : ?>
					<div class="sf-footer__social">
						<?php if ( $fb_url ) : ?>
							<a href="<?php echo esc_url( $fb_url ); ?>" class="sf-footer__social-link" aria-label="Facebook" target="_blank" rel="noopener noreferrer">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
							</a>
						<?php endif; ?>
						<?php if ( $ig_url ) : ?>
							<a href="<?php echo esc_url( $ig_url ); ?>" class="sf-footer__social-link" aria-label="Instagram" target="_blank" rel="noopener noreferrer">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
							</a>
						<?php endif; ?>
						<?php if ( $yt_url ) : ?>
							<a href="<?php echo esc_url( $yt_url ); ?>" class="sf-footer__social-link" aria-label="YouTube" target="_blank" rel="noopener noreferrer">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.54C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.96A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $phone || $email || $address || $hours ) : ?>
					<address class="sf-footer__contact">
						<?php if ( $phone ) : ?>
							<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>" class="sf-footer__contact-row">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.29a16 16 0 0 0 5.8 5.8l.91-.91a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
								<?php echo esc_html( $phone ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $email ) : ?>
							<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>" class="sf-footer__contact-row">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
								<?php echo esc_html( $email ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $address ) : ?>
							<span class="sf-footer__contact-row">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
								<?php echo esc_html( $address ); ?>
							</span>
						<?php endif; ?>
						<?php if ( $hours ) : ?>
							<span class="sf-footer__contact-row">
								<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
								<?php echo esc_html( $hours ); ?>
							</span>
						<?php endif; ?>
					</address>
				<?php endif; ?>
			</div>

			<!-- Footer nav columns (accordion on mobile) -->
			<?php
			$footer_cols = [
				[ 'location' => 'footer_1', 'label' => __( 'Customer Service', 'samurai' ) ],
				[ 'location' => 'footer_2', 'label' => __( 'Information', 'samurai' ) ],
				[ 'location' => 'footer_3', 'label' => __( 'Legal', 'samurai' ) ],
			];
			foreach ( $footer_cols as $i => $col ) :
				if ( ! has_nav_menu( $col['location'] ) ) continue;
				$col_id = 'sf-footer-col-' . $i;
			?>
				<div class="sf-footer__col">
					<button
						type="button"
						class="sf-footer__col-trigger js-footer-accordion"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $col_id ); ?>"
					>
						<?php echo esc_html( $col['label'] ); ?>
						<?php echo $icon_chevron; // phpcs:ignore ?>
					</button>
					<div id="<?php echo esc_attr( $col_id ); ?>" class="sf-footer__col-menu" aria-hidden="true">
						<?php
						wp_nav_menu( [
							'theme_location' => $col['location'],
							'menu_class'     => 'sf-footer__menu',
							'container'      => false,
							'depth'          => 1,
							'fallback_cb'    => false,
						] );
						?>
					</div>
				</div>
			<?php endforeach; ?>

		</div><!-- .sf-footer__grid -->
	</div><!-- .sf-footer__main -->

	<?php
	/*
	 * Trust strip — editable via ACF Options → Footer.
	 * Fields: trust_item_1 … trust_item_4 (text, plain string).
	 * Falls back to default English text when not set.
	 */
	$trust_icons = [
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>',
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
		'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
	];
	$trust_defaults = [
		__( 'Secure Checkout', 'samurai' ),
		__( 'Fast Local Pickup', 'samurai' ),
		__( 'Licensed & Insured', 'samurai' ),
		/* translators: store tagline in trust strip */
		__( "Miami's #1 Store", 'samurai' ),
	];
	?>
	<div class="sf-footer__trust sf-container">
		<?php foreach ( $trust_defaults as $i => $default_label ) :
			$label = samurai_option_text( 'trust_item_' . ( $i + 1 ), $default_label, false );
			if ( ! $label ) continue;
		?>
			<span class="sf-footer__trust-item">
				<?php echo $trust_icons[ $i ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( $label ); ?>
			</span>
		<?php endforeach; ?>
	</div>

	<!-- Bottom bar -->
	<div class="sf-footer__bottom">
		<div class="sf-container sf-footer__bottom-inner">
			<p class="sf-footer__legal">
				<?php if ( $footer_legal ) : ?>
					<?php echo wp_kses_post( $footer_legal ); ?>
				<?php else : ?>
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
