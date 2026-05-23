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

/*
 * Account state — computed once, used in header actions and mobile footer.
 */
$sf_logged_in = is_user_logged_in();
if ( $sf_logged_in ) {
	$sf_current_user  = wp_get_current_user();
	$sf_display_name  = $sf_current_user->display_name ?: $sf_current_user->user_login;
	$sf_user_email    = $sf_current_user->user_email;

	// Initials: first letter of each word, max 2 chars
	$sf_name_parts = array_filter( explode( ' ', $sf_display_name ) );
	$sf_initials   = '';
	foreach ( array_slice( $sf_name_parts, 0, 2 ) as $sf_part ) {
		$sf_initials .= mb_strtoupper( mb_substr( $sf_part, 0, 1 ) );
	}
	if ( ! $sf_initials ) $sf_initials = '?';

	// Consistent avatar color based on user ID
	$sf_avatar_palette = [ '#C0392B', '#8E44AD', '#2471A3', '#117A65', '#D68910' ];
	$sf_avatar_bg      = $sf_avatar_palette[ $sf_current_user->ID % count( $sf_avatar_palette ) ];
}
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
				aria-controls="sf-search-overlay"
			><?php echo $icon_search; // phpcs:ignore ?></button>

			<!-- Account — desktop only (hidden on mobile via CSS) -->
			<?php if ( $sf_logged_in ) : ?>
			<div class="sf-header__account-wrap js-dropdown-parent">
				<button
					type="button"
					class="sf-header__icon-btn sf-header__account js-dropdown-trigger"
					aria-haspopup="true"
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'My account', 'samurai' ); ?>"
				>
					<span class="sf-header__account-avatar" style="background-color:<?php echo esc_attr( $sf_avatar_bg ); ?>" aria-hidden="true"><?php echo esc_html( $sf_initials ); ?></span>
				</button>

				<div class="sf-dropdown sf-dropdown--right sf-dropdown--account js-dropdown-panel"
				     role="menu" aria-hidden="true">

					<div class="sf-account-menu__head">
						<span class="sf-account-menu__name"><?php echo esc_html( $sf_display_name ); ?></span>
						<span class="sf-account-menu__email"><?php echo esc_html( $sf_user_email ); ?></span>
					</div>

					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>"
					   class="sf-dropdown__item sf-account-menu__item" role="menuitem">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
						<?php esc_html_e( 'Dashboard', 'samurai' ); ?>
					</a>
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"
					   class="sf-dropdown__item sf-account-menu__item" role="menuitem">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
						<?php esc_html_e( 'Orders', 'samurai' ); ?>
					</a>
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>"
					   class="sf-dropdown__item sf-account-menu__item" role="menuitem">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
						<?php esc_html_e( 'Addresses', 'samurai' ); ?>
					</a>
					<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>"
					   class="sf-dropdown__item sf-account-menu__item" role="menuitem">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
						<?php esc_html_e( 'Account Details', 'samurai' ); ?>
					</a>

					<div class="sf-account-menu__divider" role="separator"></div>

					<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"
					   class="sf-dropdown__item sf-account-menu__item sf-account-menu__signout" role="menuitem">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
						<?php esc_html_e( 'Sign Out', 'samurai' ); ?>
					</a>
				</div>
			</div>
			<?php else : ?>
			<a
				href="<?php echo esc_url( $account_url ); ?>"
				class="sf-header__icon-btn sf-header__account"
				aria-label="<?php esc_attr_e( 'Sign in', 'samurai' ); ?>"
			><?php echo $icon_account; // phpcs:ignore ?></a>
			<?php endif; ?>

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
			<?php if ( $sf_logged_in ) : ?>
				<span class="sf-header__account-avatar sf-header__account-avatar--sm" style="background-color:<?php echo esc_attr( $sf_avatar_bg ); ?>" aria-hidden="true"><?php echo esc_html( $sf_initials ); ?></span>
				<?php esc_html_e( 'My Account', 'samurai' ); ?>
			<?php else : ?>
				<?php echo $icon_account; // phpcs:ignore ?>
				<?php esc_html_e( 'Sign In', 'samurai' ); ?>
			<?php endif; ?>
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
     SEARCH OVERLAY — rich AJAX product search.
     ========================================================================= -->
<div
	id="sf-search-overlay"
	class="sf-search"
	aria-hidden="true"
	role="dialog"
	aria-modal="true"
	aria-label="<?php esc_attr_e( 'Search products', 'samurai' ); ?>"
>
	<div class="sf-search__backdrop" aria-hidden="true"></div>

	<div class="sf-search__panel">

		<!-- Search bar -->
		<div class="sf-search__bar">
			<span class="sf-search__bar-icon" aria-hidden="true"><?php echo $icon_search; // phpcs:ignore ?></span>
			<input
				id="sf-search-input"
				type="search"
				class="sf-search__input"
				name="s"
				placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
				autocomplete="off"
				aria-label="<?php esc_attr_e( 'Search products', 'samurai' ); ?>"
				aria-autocomplete="list"
				aria-controls="sf-search-results"
			>
			<button type="button" class="sf-search__clear js-search-clear" aria-label="<?php esc_attr_e( 'Clear', 'samurai' ); ?>" hidden>
				<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
			<div class="sf-search__divider" aria-hidden="true"></div>
			<button type="button" class="sf-search__close js-search-close" aria-label="<?php esc_attr_e( 'Close search', 'samurai' ); ?>">
				<?php esc_html_e( 'Close', 'samurai' ); ?>
			</button>
		</div><!-- /.sf-search__bar -->

		<!-- Body -->
		<div class="sf-search__body">

			<!-- Default state: recent searches + browse categories -->
			<div class="sf-search__default" id="sf-search-default">
				<div class="sf-search__section js-search-recent-section" hidden>
					<div class="sf-search__section-hd">
						<span class="sf-search__section-title"><?php esc_html_e( 'Recent Searches', 'samurai' ); ?></span>
						<button type="button" class="sf-search__clear-recent js-search-clear-recent"><?php esc_html_e( 'Clear', 'samurai' ); ?></button>
					</div>
					<div class="sf-search__chips js-search-recent-chips"></div>
				</div>
				<?php $sf_quick_links = samurai_get_search_quick_links(); ?>
				<?php if ( $sf_quick_links ) : ?>
				<div class="sf-search__section">
					<div class="sf-search__section-hd">
						<span class="sf-search__section-title"><?php esc_html_e( 'Suggested Collections', 'samurai' ); ?></span>
					</div>
					<div class="sf-search__chips">
						<?php foreach ( $sf_quick_links as $sf_ql ) : ?>
						<a href="<?php echo esc_url( $sf_ql['url'] ); ?>" class="sf-search__chip sf-search__chip--cat">
							<?php echo esc_html( $sf_ql['name'] ); ?>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</div><!-- /.sf-search__default -->

			<!-- Loading skeleton -->
			<div class="sf-search__loading" id="sf-search-loading">
				<?php for ( $sf_sk = 0; $sf_sk < 4; $sf_sk++ ) : ?>
				<div class="sf-search__skeleton">
					<div class="sf-search__skeleton-thumb"></div>
					<div class="sf-search__skeleton-body">
						<div class="sf-search__skeleton-line sf-search__skeleton-line--long"></div>
						<div class="sf-search__skeleton-line sf-search__skeleton-line--short"></div>
					</div>
				</div>
				<?php endfor; ?>
			</div>

			<!-- Results listbox -->
			<div id="sf-search-results-wrap">
				<ul class="sf-search__results" id="sf-search-results" role="listbox" aria-label="<?php esc_attr_e( 'Product results', 'samurai' ); ?>"></ul>
				<div class="sf-search__foot">
					<a href="<?php echo esc_url( $shop_url ); ?>" class="sf-search__all js-search-all">
						<?php esc_html_e( 'View all results for', 'samurai' ); ?> &ldquo;<span class="js-search-query"></span>&rdquo;
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
					</a>
				</div>
			</div>

			<!-- No results -->
			<div class="sf-search__empty" id="sf-search-empty">
				<svg class="sf-search__empty-icon" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
				<p class="sf-search__empty-title"><?php esc_html_e( 'No results found', 'samurai' ); ?></p>
				<p class="sf-search__empty-sub">
					<?php printf(
						/* translators: %s: shop URL */
						wp_kses( __( 'Try different keywords or <a href="%s">browse all products</a>.', 'samurai' ), [ 'a' => [ 'href' => [] ] ] ),
						esc_url( $shop_url )
					); ?>
				</p>
			</div>

		</div><!-- /.sf-search__body -->

		<span class="screen-reader-text" aria-live="polite" id="sf-search-announce"></span>

	</div><!-- /.sf-search__panel -->
</div><!-- /.sf-search -->
