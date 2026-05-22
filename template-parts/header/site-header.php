<?php
defined( 'ABSPATH' ) || exit;

$home_url    = home_url( '/' );
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : $home_url . 'shop/';
$cart_url    = function_exists( 'wc_get_cart_url' )       ? wc_get_cart_url()               : $home_url . 'cart/';
$account_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'dashboard' ) : $home_url . 'my-account/';
$cart_count  = samurai_cart_count();

$icon_search  = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
$icon_account = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>';
$icon_cart    = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
$icon_chevron = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
$icon_close   = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
$icon_menu    = '<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';
$icon_arrow_l = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
$icon_arrow_r = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>';
?>

<?php
/*
 * Search placeholder text — editable via ACF Options → Header & Promo.
 * Field: search_placeholder (text). Falls back to translated default.
 */
$search_placeholder = samurai_option_text( 'search_placeholder', __( 'Search fireworks, brands, effects…', 'samurai' ), false );
?>

<header id="sf-header" class="sf-header" role="banner">
	<div class="sf-container sf-header__inner">

		<!-- Hamburger — mobile ONLY (hidden on desktop via CSS) -->
		<button
			type="button"
			class="sf-header__icon-btn sf-header__hamburger js-mobile-toggle"
			aria-label="<?php esc_attr_e( 'Open menu', 'samurai' ); ?>"
			aria-expanded="false"
			aria-controls="sf-mobile-menu"
		><?php echo $icon_menu; // phpcs:ignore ?></button>

		<!-- Logo
		     Using <div> wrapper: the_custom_logo() outputs its own <a class="custom-logo-link">
		     so wrapping in another <a> would create invalid nested anchors. -->
		<div class="sf-header__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( $home_url ); ?>" rel="home" aria-label="<?php bloginfo( 'name' ); ?>">
					<span class="sf-header__site-name"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>
		</div>

		<!-- Primary navigation — desktop only (hidden on mobile via CSS).
		     Assign a menu to the "Primary Navigation" location in WP Admin → Appearance → Menus.
		     Add CSS class "fire-deals-item" to any menu item for the orange fire highlight. -->
		<nav class="sf-header__nav" aria-label="<?php esc_attr_e( 'Main navigation', 'samurai' ); ?>">
			<?php
			wp_nav_menu( [
				'theme_location' => 'primary',
				'menu_class'     => 'sf-primary-nav',
				'container'      => false,
				'depth'          => 3,
				'fallback_cb'    => false,
				'walker'         => new SF_Primary_Nav_Walker(),
			] );
			?>
		</nav>

		<!-- Action icons (right side) -->
		<div class="sf-header__actions">

			<!-- Search — visible on all screen sizes; opens search popup -->
			<button
				type="button"
				class="sf-header__icon-btn js-search-toggle"
				aria-label="<?php esc_attr_e( 'Search', 'samurai' ); ?>"
				aria-expanded="false"
				aria-controls="sf-search-modal"
			><?php echo $icon_search; // phpcs:ignore ?></button>

			<!-- Account — desktop only (hidden on mobile via CSS) -->
			<a
				href="<?php echo esc_url( $account_url ); ?>"
				class="sf-header__icon-btn sf-header__account"
				aria-label="<?php esc_attr_e( 'My account', 'samurai' ); ?>"
			><?php echo $icon_account; // phpcs:ignore ?></a>

			<!-- Cart — always visible; opens mini cart drawer -->
			<button
				type="button"
				class="sf-header__icon-btn sf-header__cart-btn js-minicart-open"
				aria-label="<?php printf( esc_attr__( 'Cart, %d items', 'samurai' ), $cart_count ); ?>"
				aria-haspopup="dialog"
				aria-controls="sf-minicart"
			>
				<?php echo $icon_cart; // phpcs:ignore ?>
				<span
					class="sf-cart-badge js-cart-count<?php echo $cart_count === 0 ? ' sf-cart-badge--empty' : ''; ?>"
					aria-hidden="<?php echo $cart_count === 0 ? 'true' : 'false'; ?>"
					<?php echo $cart_count === 0 ? 'style="display:none"' : ''; ?>
				><?php echo absint( $cart_count ); ?></span>
			</button>

		</div>
	</div>
</header>

<!-- =========================================================================
     MOBILE DRAWER (slide-in from left)
     ========================================================================= -->
<div
	id="sf-mobile-menu"
	class="sf-mobile-menu"
	aria-hidden="true"
	role="dialog"
	aria-label="<?php esc_attr_e( 'Navigation menu', 'samurai' ); ?>"
>
	<div class="sf-mobile-menu__head">
		<div class="sf-mobile-menu__logo">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</div>
		<button
			type="button"
			class="sf-mobile-menu__close js-mobile-close"
			aria-label="<?php esc_attr_e( 'Close menu', 'samurai' ); ?>"
		><?php echo $icon_close; // phpcs:ignore ?></button>
	</div>

	<div class="sf-mobile-menu__search">
		<form role="search" method="get" action="<?php echo esc_url( $shop_url ); ?>">
			<label for="sf-search-mobile" class="screen-reader-text"><?php esc_html_e( 'Search products', 'samurai' ); ?></label>
			<div class="sf-mobile-search-wrap">
				<input
					id="sf-search-mobile"
					type="search"
					name="s"
					placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
					autocomplete="off"
				>
				<input type="hidden" name="post_type" value="product">
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'samurai' ); ?>">
					<?php echo $icon_search; // phpcs:ignore ?>
				</button>
			</div>
		</form>
	</div>

	<?php
	/*
	 * Mobile Quick Links — assign items in WP Admin → Appearance → Menus → "Mobile Quick Links".
	 * Add CSS class "fire-deals-item" to any item for the orange highlight.
	 * Falls back to two default links when the menu location is not yet assigned.
	 */
	?>
	<div class="sf-mobile-menu__quick">
		<?php if ( has_nav_menu( 'mobile-quick' ) ) : ?>
			<?php wp_nav_menu( [
				'theme_location' => 'mobile-quick',
				'menu_class'     => 'sf-mobile-quick-nav',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => false,
			] ); ?>
		<?php else : ?>
			<?php
			// samurai_tax_archive_url('fire-deal') returns the taxonomy base (/fire-deal/)
			// which 404s; real archives live at /fire-deal/[term-slug]/.
			$sf_fd_terms = get_terms( [ 'taxonomy' => 'fire-deal', 'hide_empty' => true, 'number' => 1 ] );
			$sf_fd_url   = ( ! is_wp_error( $sf_fd_terms ) && ! empty( $sf_fd_terms ) )
				? get_term_link( $sf_fd_terms[0] )
				: $shop_url;
			if ( is_wp_error( $sf_fd_url ) ) { $sf_fd_url = $shop_url; }
			?>
			<a class="sf-mobile-quick-link" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop All', 'samurai' ); ?></a>
			<a class="sf-mobile-quick-link sf-mobile-quick-link--deals" href="<?php echo esc_url( $sf_fd_url ); ?>">
				<span aria-hidden="true">&#x1F525;</span> <?php esc_html_e( 'Fire Deals', 'samurai' ); ?>
			</a>
		<?php endif; ?>
	</div>

	<nav class="sf-mobile-menu__nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'samurai' ); ?>">
		<?php
		/*
		 * Mobile nav driven by the Primary Navigation menu.
		 * Structure mirrors the mega menu: depth 0 = accordion, depth 1 = links
		 * or section headers (if they have depth-2 children).
		 * Falls back to nothing if no menu is assigned to the primary location.
		 */
		if ( function_exists( 'samurai_mobile_nav_html' ) ) {
			echo samurai_mobile_nav_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
	</nav>

	<div class="sf-mobile-menu__footer">
		<a href="<?php echo esc_url( $account_url ); ?>" class="sf-btn sf-btn--outline sf-btn--block">
			<?php echo $icon_account; // phpcs:ignore ?>
			<?php esc_html_e( 'My Account', 'samurai' ); ?>
		</a>
		<a href="<?php echo esc_url( $cart_url ); ?>" class="sf-btn sf-btn--primary sf-btn--block">
			<?php echo $icon_cart; // phpcs:ignore ?>
			<?php esc_html_e( 'View Cart', 'samurai' ); ?>
			<?php if ( $cart_count > 0 ) : ?>
				<span class="sf-btn-cart-count"><?php echo absint( $cart_count ); ?></span>
			<?php endif; ?>
		</a>
	</div>
</div>

<div class="sf-mobile-menu__backdrop js-mobile-backdrop" aria-hidden="true"></div>

<!-- =========================================================================
     MINI CART DRAWER (slide-in from right)
     ========================================================================= -->
<?php if ( function_exists( 'samurai_mini_cart_html' ) ) : ?>
<?php echo samurai_mini_cart_html(); // phpcs:ignore ?>
<?php endif; ?>
<div class="sf-minicart-backdrop" aria-hidden="true"></div>

<!-- =========================================================================
     MEGA MENU BACKDROP — panel itself is injected inline by SF_Primary_Nav_Walker
     ========================================================================= -->
<div class="sf-mega-backdrop" aria-hidden="true"></div>

<!-- =========================================================================
     SEARCH POPUP — opens on search icon click (all screen sizes).
     Phase 5 will add AJAX live results to this form.
     ========================================================================= -->
<div
	id="sf-search-modal"
	class="sf-search-modal js-search-modal"
	aria-hidden="true"
	role="dialog"
	aria-label="<?php esc_attr_e( 'Search', 'samurai' ); ?>"
>
	<div class="sf-search-modal__inner sf-container">
		<form role="search" method="get" action="<?php echo esc_url( $shop_url ); ?>" class="sf-search-modal__form">
			<label for="sf-search-modal-input" class="screen-reader-text"><?php esc_html_e( 'Search products', 'samurai' ); ?></label>
			<input
				id="sf-search-modal-input"
				type="search"
				name="s"
				placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
				autocomplete="off"
				autofocus
			>
			<input type="hidden" name="post_type" value="product">
			<button type="submit" class="sf-btn sf-btn--primary" aria-label="<?php esc_attr_e( 'Search', 'samurai' ); ?>">
				<?php echo $icon_search; // phpcs:ignore ?>
			</button>
		</form>
		<button type="button" class="sf-search-modal__close js-search-close" aria-label="<?php esc_attr_e( 'Close search', 'samurai' ); ?>">
			<?php echo $icon_close; // phpcs:ignore ?>
		</button>
	</div>
</div>
