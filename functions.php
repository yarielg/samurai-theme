<?php
defined( 'ABSPATH' ) || exit;

define( 'SAMURAI_VERSION', '2.1.9' );
define( 'SAMURAI_DIR', get_template_directory() );
define( 'SAMURAI_URL', get_template_directory_uri() );

require_once SAMURAI_DIR . '/inc/acf-fields.php';
require_once SAMURAI_DIR . '/inc/mini-cart.php';
require_once SAMURAI_DIR . '/inc/mega-menu.php';

// ---------------------------------------------------------------------------
// Theme setup
// ---------------------------------------------------------------------------
function samurai_setup(): void {
	load_theme_textdomain( 'samurai', SAMURAI_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', [
		'height'      => 80,
		'width'       => 280,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'html5', [
		'search-form', 'comment-form', 'comment-list',
		'gallery', 'caption', 'style', 'script',
	] );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );

	// Custom image sizes
	add_image_size( 'samurai-product-card', 600, 600, true );
	add_image_size( 'samurai-gallery',      900, 900, false );
	add_image_size( 'samurai-taxonomy',     480, 360, true );
	add_image_size( 'samurai-brand',        300, 200, false );
	add_image_size( 'samurai-hero',        1920, 900, true );

	// WooCommerce
	add_theme_support( 'woocommerce', [
		'thumbnail_image_width' => 600,
		'single_image_width'    => 900,
		'product_grid'          => [
			'default_rows'    => 4,
			'min_rows'        => 2,
			'max_rows'        => 8,
			'default_columns' => 4,
			'min_columns'     => 2,
			'max_columns'     => 5,
		],
	] );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Navigation menus
	register_nav_menus( [
		'primary'      => __( 'Primary Navigation', 'samurai' ),
		'mobile'       => __( 'Mobile Navigation', 'samurai' ),
		'mobile-quick' => __( 'Mobile Quick Links', 'samurai' ),
		'footer_1'     => __( 'Footer: Shop', 'samurai' ),
		'footer_2'     => __( 'Footer: Info', 'samurai' ),
		'footer_3'     => __( 'Footer: Legal', 'samurai' ),
	] );
}
add_action( 'after_setup_theme', 'samurai_setup' );

// ---------------------------------------------------------------------------
// Content width
// ---------------------------------------------------------------------------
function samurai_content_width(): void {
	$GLOBALS['content_width'] = 1280;
}
add_action( 'after_setup_theme', 'samurai_content_width', 0 );

// ---------------------------------------------------------------------------
// Add .js class to <html> before any CSS renders so JS-conditional styles
// (e.g. hiding the fallback Update Cart button) have no flash.
// ---------------------------------------------------------------------------
add_action( 'wp_head', function (): void {
	echo '<script>document.documentElement.classList.add("js")</script>' . "\n";
}, 0 );

// ---------------------------------------------------------------------------
// Preconnect hints — must be early in <head> for Google Fonts to benefit
// ---------------------------------------------------------------------------
function samurai_preconnect(): void {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
add_action( 'wp_head', 'samurai_preconnect', 1 );

// ---------------------------------------------------------------------------
// Enqueue scripts and styles
// ---------------------------------------------------------------------------
function samurai_scripts(): void {
	$ver = SAMURAI_VERSION;

	// Google Fonts: Montserrat (headings/buttons), Oswald (sub-headings), Inter (body)
	// No dependency set — font CSS is non-blocking (display=swap), preconnect handles latency.
	wp_enqueue_style(
		'samurai-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Montserrat:wght@700;800;900&family=Oswald:wght@500;600&display=swap',
		[],
		null
	);

	// base.css has no hard dep on samurai-fonts — fonts load in parallel, text shows immediately.
	wp_enqueue_style( 'samurai-base',       SAMURAI_URL . '/assets/css/base.css',       [], $ver );
	wp_enqueue_style( 'samurai-components', SAMURAI_URL . '/assets/css/components.css', [ 'samurai-base' ], $ver );
	wp_enqueue_style( 'samurai-toast',      SAMURAI_URL . '/assets/css/toast.css',      [ 'samurai-components' ], $ver );
	wp_enqueue_script(
		'samurai-toast',
		SAMURAI_URL . '/assets/js/toast.js',
		[],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);

	if ( samurai_is_woo_page() ) {
		wp_enqueue_style( 'samurai-woocommerce', SAMURAI_URL . '/assets/css/woocommerce.css', [ 'samurai-components' ], $ver );
	}

	// Hero slider CSS — only loaded on front page (or any page that marks itself as having a hero)
	if ( is_front_page() || apply_filters( 'samurai_page_has_hero', false ) ) {
		wp_enqueue_style( 'samurai-hero', SAMURAI_URL . '/assets/css/hero.css', [ 'samurai-components' ], $ver );
	}

	// Homepage-specific styles (sections, product cards)
	if ( is_front_page() ) {
		wp_enqueue_style( 'samurai-product-card', SAMURAI_URL . '/assets/css/product-card.css', [ 'samurai-components' ], $ver );
		wp_enqueue_style( 'samurai-home',         SAMURAI_URL . '/assets/css/home.css',         [ 'samurai-product-card' ], $ver );
		wp_enqueue_style( 'samurai-newsletter',   SAMURAI_URL . '/assets/css/newsletter.css',   [ 'samurai-components' ], $ver );
		wp_enqueue_script(
			'samurai-newsletter',
			SAMURAI_URL . '/assets/js/newsletter.js',
			[],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
		wp_localize_script( 'samurai-newsletter', 'sfNewsletter', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'sf-newsletter' ),
			'i18n'    => [
				'emailInvalid'  => __( 'Please enter a valid email address.', 'samurai' ),
				'termsRequired' => __( 'Please accept the Terms & Conditions.', 'samurai' ),
				'sending'       => __( 'Sending…', 'samurai' ),
				'submit'        => __( 'Subscribe Now', 'samurai' ),
				'error'         => __( 'Something went wrong. Please try again.', 'samurai' ),
			],
		] );
	}

	// Effects listing page — /effect/
	if ( get_query_var( 'sf_effects_listing' ) ) {
		wp_enqueue_style( 'samurai-effects', SAMURAI_URL . '/assets/css/effects.css', [ 'samurai-components' ], $ver );
	}

	// Product archive styles + JS (shop, categories, custom taxonomy archives)
	if ( is_shop() || is_product_category() || is_product_taxonomy() ) {
		wp_enqueue_style( 'samurai-product-card', SAMURAI_URL . '/assets/css/product-card.css', [ 'samurai-components' ], $ver );
		wp_enqueue_style( 'samurai-archive',      SAMURAI_URL . '/assets/css/archive.css',      [ 'samurai-product-card' ], $ver );
		wp_enqueue_script(
			'samurai-archive',
			SAMURAI_URL . '/assets/js/archive.js',
			[],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
	}

	// Cart page styles + JS (checkout.css also loaded here for the step indicator)
	if ( is_cart() ) {
		wp_enqueue_style( 'samurai-product-card', SAMURAI_URL . '/assets/css/product-card.css', [ 'samurai-components' ], $ver );
		wp_enqueue_style( 'samurai-cart',         SAMURAI_URL . '/assets/css/cart.css',         [ 'samurai-product-card' ], $ver );
		wp_enqueue_style( 'samurai-checkout',     SAMURAI_URL . '/assets/css/checkout.css',     [ 'samurai-cart' ], $ver );
		wp_enqueue_script(
			'samurai-cart',
			SAMURAI_URL . '/assets/js/cart.js',
			[],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
		wp_localize_script( 'samurai-cart', 'sfCart', [
			'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			'wcAjaxUrl' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
			'nonce'     => wp_create_nonce( 'sf-cart-nonce' ),
			'i18n'      => [
				'item'      => __( 'item',      'samurai' ),
				'items'     => __( 'items',     'samurai' ),
				'enterCode' => __( 'Please enter a coupon code.', 'samurai' ),
				'error'     => __( 'Something went wrong. Please try again.', 'samurai' ),
			],
		] );
	}

	// Checkout page styles + JS
	if ( is_checkout() ) {
		wp_enqueue_style( 'samurai-product-card', SAMURAI_URL . '/assets/css/product-card.css', [ 'samurai-components' ], $ver );
		wp_enqueue_style( 'samurai-cart',         SAMURAI_URL . '/assets/css/cart.css',         [ 'samurai-product-card' ], $ver );
		wp_enqueue_style( 'samurai-checkout',     SAMURAI_URL . '/assets/css/checkout.css',     [ 'samurai-cart' ], $ver );
		if ( is_wc_endpoint_url( 'order-received' ) ) {
			wp_enqueue_style( 'samurai-thankyou', SAMURAI_URL . '/assets/css/thankyou.css', [ 'samurai-components' ], $ver );
		}
		wp_enqueue_script(
			'samurai-checkout',
			SAMURAI_URL . '/assets/js/checkout.js',
			[],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
		wp_localize_script( 'samurai-checkout', 'sfCheckout', [
			'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
			'couponNonce'      => wp_create_nonce( 'sf-coupon' ),
			'orderReviewNonce' => wp_create_nonce( 'update-order-review' ),
			'i18n'             => [
				'enterCode' => __( 'Please enter a coupon code.', 'samurai' ),
				'error'     => __( 'Something went wrong. Please try again.', 'samurai' ),
			],
		] );
	}

	// My Account + Login styles
	if ( is_account_page() ) {
		wp_enqueue_style( 'samurai-account', SAMURAI_URL . '/assets/css/account.css', [ 'samurai-components' ], $ver );
		if ( is_wc_endpoint_url( 'view-order' ) ) {
			wp_enqueue_style( 'samurai-thankyou', SAMURAI_URL . '/assets/css/thankyou.css', [ 'samurai-components' ], $ver );
		}
		if ( ! is_user_logged_in() ) {
			wp_enqueue_style( 'samurai-login', SAMURAI_URL . '/assets/css/login.css', [ 'samurai-components' ], $ver );
		}
	}

	// Single product styles + JS
	if ( is_product() ) {
		wp_enqueue_style( 'samurai-product-card',   SAMURAI_URL . '/assets/css/product-card.css',   [ 'samurai-components' ], $ver );
		wp_enqueue_style( 'samurai-single-product', SAMURAI_URL . '/assets/css/single-product.css', [ 'samurai-product-card' ], $ver );
		wp_enqueue_script(
			'samurai-single-product',
			SAMURAI_URL . '/assets/js/single-product.js',
			[],
			$ver,
			[ 'strategy' => 'defer', 'in_footer' => true ]
		);
	}

	// Search overlay — global (header component, needed on all pages)
	wp_enqueue_style( 'samurai-search', SAMURAI_URL . '/assets/css/search.css', [ 'samurai-components' ], $ver );
	wp_enqueue_script(
		'samurai-search',
		SAMURAI_URL . '/assets/js/search.js',
		[],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
	wp_localize_script( 'samurai-search', 'sfSearch', [
		'endpoint' => rest_url( 'samurai/v1/search' ),
		'shopUrl'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
	] );

	// Mini cart + mega menu JS are global (header components, needed on all pages)
	wp_enqueue_script(
		'samurai-mini-cart',
		SAMURAI_URL . '/assets/js/mini-cart.js',
		[],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);
	wp_localize_script( 'samurai-mini-cart', 'sfMiniCart', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
	] );

	wp_enqueue_script(
		'samurai-mega-menu',
		SAMURAI_URL . '/assets/js/mega-menu.js',
		[],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);

	wp_enqueue_script(
		'samurai-theme',
		SAMURAI_URL . '/assets/js/theme.js',
		[],
		$ver,
		[ 'strategy' => 'defer', 'in_footer' => true ]
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'samurai_scripts' );

// ---------------------------------------------------------------------------
// Search — quick links for the search overlay chips (configured via ACF Options).
// ---------------------------------------------------------------------------
function samurai_get_search_quick_links(): array {
	if ( ! function_exists( 'get_field' ) ) return [];
	$rows = get_field( 'search_quick_links', 'option' );
	if ( empty( $rows ) ) return [];
	$links = [];
	foreach ( $rows as $row ) {
		$label = sanitize_text_field( $row['link_label'] ?? '' );
		$url   = esc_url_raw( $row['link_url'] ?? '' );
		if ( $label && $url ) {
			$links[] = [ 'name' => $label, 'url' => $url ];
		}
	}
	return $links;
}

// ---------------------------------------------------------------------------
// Archive — register sidebar filter query vars so WP canonical redirect
// does not strip them from the URL.
// NOTE: params intentionally use the "sf_" prefix (not "filter_") to avoid
// WooCommerce's layered-nav interception of filter_* params which targets
// WC product attributes (pa_*) rather than our custom taxonomies.
// ---------------------------------------------------------------------------
add_filter( 'query_vars', function ( array $vars ): array {
	$vars[] = 'sf_color';
	$vars[] = 'sf_effect';
	$vars[] = 'sf_brand';
	$vars[] = 'sf_size';
	$vars[] = 'sf_sound_level';
	$vars[] = 'sf_timing';
	$vars[] = 'sf_effects_listing';
	return $vars;
} );

// ---------------------------------------------------------------------------
// Archive — apply sidebar filters to the main WC product query.
// Runs at priority 100 so it fires after WC's own pre_get_posts (priority 10).
// ---------------------------------------------------------------------------
add_action( 'pre_get_posts', function ( WP_Query $query ): void {
	if ( is_admin() || ! $query->is_main_query() ) return;

	// Use WP_Query native methods (not WC template-tag functions).
	$sf_is_archive = $query->is_post_type_archive( 'product' )
		|| $query->is_tax( 'product_cat' )
		|| $query->is_tax( [ 'color', 'effect', 'brand', 'size', 'sound-level', 'timing' ] );

	if ( ! $sf_is_archive ) return;

	// ── Remove WC's catalog-visibility and out-of-stock restrictions ──────────
	// WC's pre_get_posts (priority 10) adds:
	//   • A NOT IN tax_query clause on product_visibility to exclude
	//     "exclude-from-catalog" products.
	//   • An optional _stock_status meta_query clause to hide out-of-stock items.
	// Removing both ensures every published product appears and filter counts
	// (which use WordPress's $term->count on published posts) stay accurate.

	$sf_tax_q = (array) $query->get( 'tax_query' );
	if ( ! empty( $sf_tax_q ) ) {
		$sf_tax_q_clean = [];
		foreach ( $sf_tax_q as $sf_k => $sf_c ) {
			if ( 'relation' === $sf_k ) {
				$sf_tax_q_clean['relation'] = $sf_c;
			} elseif ( ! is_array( $sf_c ) || ( $sf_c['taxonomy'] ?? '' ) !== 'product_visibility' ) {
				$sf_tax_q_clean[] = $sf_c;
			}
		}
		// If nothing real remains after stripping, pass an empty array.
		$sf_has_clauses = ! empty( array_filter( $sf_tax_q_clean, 'is_array' ) );
		$query->set( 'tax_query', $sf_has_clauses ? $sf_tax_q_clean : [] );
	}

	$sf_meta_q = (array) $query->get( 'meta_query' );
	if ( ! empty( $sf_meta_q ) ) {
		$sf_meta_q_clean = [];
		foreach ( $sf_meta_q as $sf_k => $sf_c ) {
			if ( 'relation' === $sf_k ) {
				$sf_meta_q_clean['relation'] = $sf_c;
			} elseif ( ! is_array( $sf_c ) || ( $sf_c['key'] ?? '' ) !== '_stock_status' ) {
				$sf_meta_q_clean[] = $sf_c;
			}
		}
		$sf_has_meta = ! empty( array_filter( $sf_meta_q_clean, 'is_array' ) );
		$query->set( 'meta_query', $sf_has_meta ? $sf_meta_q_clean : [] );
	}

	// Exclude products without a price.
	$sf_meta_now   = (array) $query->get( 'meta_query' );
	$sf_meta_now[] = [ 'key' => '_price', 'value' => '', 'compare' => '!=' ];
	if ( ! isset( $sf_meta_now['relation'] ) && count( array_filter( $sf_meta_now, 'is_array' ) ) > 1 ) {
		$sf_meta_now['relation'] = 'AND';
	}
	$query->set( 'meta_query', $sf_meta_now );

	// ── Apply sidebar filter selections ───────────────────────────────────────
	$sf_filter_map = [
		'color'       => 'sf_color',
		'effect'      => 'sf_effect',
		'brand'       => 'sf_brand',
		'size'        => 'sf_size',
		'sound-level' => 'sf_sound_level',
		'timing'      => 'sf_timing',
	];

	$sf_extra_tax = [];
	foreach ( $sf_filter_map as $sf_tax => $sf_param ) {
		$sf_raw = isset( $_GET[ $sf_param ] )
			? sanitize_text_field( wp_unslash( $_GET[ $sf_param ] ) )
			: '';
		if ( ! $sf_raw ) continue;
		$sf_slugs = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $sf_raw ) ) ) );
		if ( empty( $sf_slugs ) ) continue;
		$sf_extra_tax[] = [
			'taxonomy' => $sf_tax,
			'field'    => 'slug',
			'terms'    => $sf_slugs,
			'operator' => 'IN',
		];
	}

	if ( ! empty( $sf_extra_tax ) ) {
		$sf_existing = (array) $query->get( 'tax_query' );
		if ( empty( $sf_existing ) ) {
			$sf_existing = [ 'relation' => 'AND' ];
		} elseif ( ! isset( $sf_existing['relation'] ) ) {
			$sf_existing['relation'] = 'AND';
		}
		$query->set( 'tax_query', array_merge( $sf_existing, $sf_extra_tax ) );
	}

	// 'title' sort not natively handled by WC — apply it here.
	$sf_ob = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
	if ( 'title' === $sf_ob ) {
		$query->set( 'orderby', 'title' );
		$query->set( 'order',   'ASC' );
	}

	$query->set( 'post_status',    'publish' );
	$query->set( 'posts_per_page', 24 );
}, 100 );

// ---------------------------------------------------------------------------
// Archive — faceted filter counts.
//
// For each filter group, counts how many published products match the current
// browse context (archive URL term + every OTHER selected filter) and have
// each term in that group. This lets the sidebar show accurate numbers before
// the user clicks — if Effect: Aerial is active, Color counts reflect only
// aerial products, while Effect's own counts stay unaffected by its own
// selection so the user can see what switching would give them.
//
// Implementation: one raw SQL JOIN query per filter group (6 total), bypassing
// WC's pre_get_posts entirely. WordPress indexes on term_relationships and
// posts make these sub-10 ms for ~1 000 products. Results are transient-cached
// keyed by context and auto-busted whenever any product is saved.
// ---------------------------------------------------------------------------
function samurai_get_faceted_counts(
	array  $filter_groups,
	array  $active_filters,
	string $queried_taxonomy,
	string $queried_slug
): array {
	global $wpdb;

	$cache_ver = (int) get_option( 'samurai_facet_version', 0 );
	$cache_key = 'sf_facet_' . md5( serialize( [
		'v' => $cache_ver,
		'q' => $queried_taxonomy . ':' . $queried_slug,
		'a' => $active_filters,
	] ) );

	$cached = get_transient( $cache_key );
	if ( false !== $cached ) return $cached;

	$counts  = [];
	$alias_n = 0; // unique alias counter shared across all group iterations

	foreach ( $filter_groups as $group_tax => $group_def ) {
		$joins  = [];
		$wheres = [ 'p.post_status = %s', 'p.post_type = %s' ];
		$params = [ 'publish', 'product' ];

		// ── Archive taxonomy constraint (the URL the user is currently on) ────
		if ( $queried_taxonomy && $queried_slug ) {
			$alias_n++;
			$a        = "q{$alias_n}";
			$joins[]  = "INNER JOIN {$wpdb->term_relationships} tr_{$a} ON p.ID = tr_{$a}.object_id
			             INNER JOIN {$wpdb->term_taxonomy} tt_{$a} ON tr_{$a}.term_taxonomy_id = tt_{$a}.term_taxonomy_id
			             INNER JOIN {$wpdb->terms} t_{$a} ON tt_{$a}.term_id = t_{$a}.term_id";
			$wheres[] = "tt_{$a}.taxonomy = %s AND t_{$a}.slug = %s";
			$params[] = $queried_taxonomy;
			$params[] = $queried_slug;
		}

		// ── Other active filter groups (exclude the one being counted so its
		//    own selection does not self-restrict its facet counts) ─────────────
		foreach ( $active_filters as $other_tax => $other_slugs ) {
			if ( $other_tax === $group_tax )        continue; // counting this group — skip
			if ( $other_tax === $queried_taxonomy ) continue; // already in archive constraint
			if ( empty( $other_slugs ) )            continue;

			$alias_n++;
			$a       = "f{$alias_n}";
			$slug_ph = implode( ',', array_fill( 0, count( $other_slugs ), '%s' ) );
			$joins[]  = "INNER JOIN {$wpdb->term_relationships} tr_{$a} ON p.ID = tr_{$a}.object_id
			             INNER JOIN {$wpdb->term_taxonomy} tt_{$a} ON tr_{$a}.term_taxonomy_id = tt_{$a}.term_taxonomy_id
			             INNER JOIN {$wpdb->terms} t_{$a} ON tt_{$a}.term_id = t_{$a}.term_id";
			$wheres[] = "tt_{$a}.taxonomy = %s AND t_{$a}.slug IN ({$slug_ph})";
			$params[] = $other_tax;
			$params   = array_merge( $params, $other_slugs );
		}

		// ── Main join: count terms for this group among the matching products ──
		$alias_n++;
		$m        = "m{$alias_n}";
		$joins[]  = "INNER JOIN {$wpdb->term_relationships} tr_{$m} ON p.ID = tr_{$m}.object_id
		             INNER JOIN {$wpdb->term_taxonomy} tt_{$m} ON tr_{$m}.term_taxonomy_id = tt_{$m}.term_taxonomy_id
		             INNER JOIN {$wpdb->terms} t_{$m} ON tt_{$m}.term_id = t_{$m}.term_id";
		$wheres[] = "tt_{$m}.taxonomy = %s";
		$params[] = $group_tax;

		$join_sql  = implode( "\n\t\t\t\t", $joins );
		$where_sql = implode( ' AND ', $wheres );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql  = $wpdb->prepare(
			"SELECT t_{$m}.term_id, COUNT(DISTINCT p.ID) AS cnt
			 FROM {$wpdb->posts} p
			 {$join_sql}
			 WHERE {$where_sql}
			 GROUP BY t_{$m}.term_id",
			$params
		);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows               = $wpdb->get_results( $sql );
		$counts[$group_tax] = $rows ? array_column( $rows, 'cnt', 'term_id' ) : [];
	}

	set_transient( $cache_key, $counts, 10 * MINUTE_IN_SECONDS );
	return $counts;
}

add_action( 'save_post_product', function () {
	// Bump version → all cached faceted counts are invalidated on next load.
	update_option( 'samurai_facet_version', time(), false );
} );

// ---------------------------------------------------------------------------
// Single product — hooks cleanup:
//   • Remove default upsells/related (template handles them).
//   • Keep wc_print_notices on woocommerce_before_single_product so the
//     notice is rendered in DOM; JS reads and converts it to a toast.
// ---------------------------------------------------------------------------
add_action( 'wp', function () {
	if ( ! is_product() ) {
		return;
	}
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display',          15 );
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
} );

// ---------------------------------------------------------------------------
// Disable wc-cart-fragments on non-WooCommerce pages.
// This AJAX call fires on every page load and is the #1 WooCommerce perf killer.
// The header cart count is rendered server-side on initial load — still correct.
// Fragments only needed where cart interaction happens (shop, product, cart, checkout).
// ---------------------------------------------------------------------------
function samurai_disable_cart_fragments(): void {
	if ( samurai_is_woo_page() ) {
		return;
	}
	wp_dequeue_script( 'wc-cart-fragments' );
}
add_action( 'wp_enqueue_scripts', 'samurai_disable_cart_fragments', 99 );

// ---------------------------------------------------------------------------
// Dynamic CSS variables from ACF Options (override defaults when set)
// ---------------------------------------------------------------------------
function samurai_output_dynamic_css(): void {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$overrides = [];

	$map = [
		'brand_color_primary'   => '--sf-color-primary',
		'brand_color_secondary' => '--sf-color-secondary',
		'brand_color_accent'    => '--sf-color-accent',
		'brand_color_dark'      => '--sf-color-dark',
		'header_nav_bg_color'   => '--sf-header-nav-bg',
	];

	foreach ( $map as $acf_key => $css_var ) {
		$val = get_field( $acf_key, 'option' );
		if ( $val ) {
			$overrides[] = esc_attr( $css_var ) . ':' . esc_attr( $val );
		}
	}

	if ( empty( $overrides ) ) {
		return;
	}

	echo '<style id="samurai-acf-vars">:root{' . implode( ';', $overrides ) . '}</style>' . "\n";
}
add_action( 'wp_head', 'samurai_output_dynamic_css', 5 );

// ---------------------------------------------------------------------------
// ACF Options pages
// ---------------------------------------------------------------------------
function samurai_register_acf_options(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( [
		'page_title'  => 'Samurai Theme Settings',
		'menu_title'  => 'Theme Settings',
		'menu_slug'   => 'samurai-theme-settings',
		'capability'  => 'manage_options',
		'redirect'    => false,
		'icon_url'    => 'dashicons-store',
		'position'    => 61,
	] );

	$sub_pages = [
		'Brand & Colors'      => 'samurai-brand-colors',
		'Contact & Hours'     => 'samurai-contact',
		'Header & Promo'      => 'samurai-header-promo',
		'Homepage'            => 'samurai-homepage',
		'Newsletter'          => 'samurai-newsletter',
		'Cart & Checkout'     => 'samurai-cart-checkout',
		'Footer'              => 'samurai-footer',
	];

	foreach ( $sub_pages as $title => $slug ) {
		acf_add_options_sub_page( [
			'page_title'  => $title,
			'menu_title'  => $title,
			'menu_slug'   => $slug,
			'parent_slug' => 'samurai-theme-settings',
			'capability'  => 'manage_options',
		] );
	}
}
add_action( 'acf/init', 'samurai_register_acf_options' );

// ---------------------------------------------------------------------------
// Widget areas
// ---------------------------------------------------------------------------
function samurai_widgets_init(): void {
	register_sidebar( [
		'name'          => __( 'Shop Sidebar', 'samurai' ),
		'id'            => 'shop-sidebar',
		'description'   => __( 'Filters and widgets for WooCommerce shop and archive pages.', 'samurai' ),
		'before_widget' => '<div id="%1$s" class="sf-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="sf-widget__title">',
		'after_title'   => '</h3>',
	] );

	register_sidebar( [
		'name'          => __( 'Product Sidebar', 'samurai' ),
		'id'            => 'product-sidebar',
		'description'   => __( 'Sidebar displayed on single product pages.', 'samurai' ),
		'before_widget' => '<div id="%1$s" class="sf-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="sf-widget__title">',
		'after_title'   => '</h3>',
	] );
}
add_action( 'widgets_init', 'samurai_widgets_init' );

// ---------------------------------------------------------------------------
// Body classes
// ---------------------------------------------------------------------------
function samurai_body_classes( array $classes ): array {
	if ( samurai_is_woo_page() ) {
		$classes[] = 'sf-woo-page';
	}
	if ( is_front_page() ) {
		$classes[] = 'sf-home';
	}
	// sf-has-hero: tells CSS the transparent header should overlay a hero.
	// Templates can call samurai_mark_has_hero() before get_header() to set this.
	if ( is_front_page() || apply_filters( 'samurai_page_has_hero', false ) ) {
		$classes[] = 'sf-has-hero';
	}

	return $classes;
}
add_filter( 'body_class', 'samurai_body_classes' );

/**
 * Templates or plugins can call this before get_header() to enable the
 * transparent hero header on non-front-page templates.
 */
function samurai_mark_has_hero(): void {
	add_filter( 'samurai_page_has_hero', '__return_true' );
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function samurai_is_woo_page(): bool {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}
	// Include front page: it shows products and needs WC scripts/styles.
	return is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_front_page();
}

/**
 * Output ACF theme option with proper escaping.
 * Falls back to $default if not set.
 */
function samurai_option( string $key, string $default = '', bool $echo = true ): string {
	$val = function_exists( 'get_field' ) ? ( get_field( $key, 'option' ) ?: $default ) : $default;
	$val = wp_kses_post( $val );
	if ( $echo ) {
		echo $val; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return $val;
}

/**
 * Output a raw ACF option value escaped as plain text.
 */
function samurai_option_text( string $key, string $default = '', bool $echo = true ): string {
	$val = function_exists( 'get_field' ) ? ( get_field( $key, 'option' ) ?: $default ) : $default;
	$val = esc_html( $val );
	if ( $echo ) {
		echo $val;
	}
	return $val;
}

/**
 * Get cart item count for display.
 */
function samurai_cart_count(): int {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}
	return (int) WC()->cart->get_cart_contents_count();
}

// ---------------------------------------------------------------------------
// Enqueue header and footer stylesheets
// ---------------------------------------------------------------------------
function samurai_enqueue_layout_styles(): void {
	$ver = SAMURAI_VERSION;
	wp_enqueue_style( 'samurai-header',    SAMURAI_URL . '/assets/css/header.css',    [ 'samurai-components' ], $ver );
	wp_enqueue_style( 'samurai-footer',    SAMURAI_URL . '/assets/css/footer.css',    [ 'samurai-components' ], $ver );
	wp_enqueue_style( 'samurai-mini-cart', SAMURAI_URL . '/assets/css/mini-cart.css', [ 'samurai-components' ], $ver );
	wp_enqueue_style( 'samurai-mega-menu', SAMURAI_URL . '/assets/css/mega-menu.css', [ 'samurai-header' ], $ver );
}
add_action( 'wp_enqueue_scripts', 'samurai_enqueue_layout_styles', 11 );

// ---------------------------------------------------------------------------
// Nav term helper — cached taxonomy queries for header nav
// ---------------------------------------------------------------------------
function samurai_get_nav_terms( string $taxonomy, int $number = 12, string $orderby = 'count' ): array {
	$cache_key = 'sf_nav_' . $taxonomy . '_' . $number;
	$terms     = get_transient( $cache_key );
	if ( false === $terms ) {
		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'number'     => $number,
			'hide_empty' => true,
			'orderby'    => $orderby,
			'order'      => ( $orderby === 'name' ) ? 'ASC' : 'DESC',
		] );
		if ( is_wp_error( $terms ) ) {
			$terms = [];
		}
		set_transient( $cache_key, $terms, HOUR_IN_SECONDS );
	}
	return $terms ?: [];
}

// Clear nav term cache when any term is saved/deleted
add_action( 'created_term', 'samurai_flush_nav_cache' );
add_action( 'edited_term',  'samurai_flush_nav_cache' );
add_action( 'delete_term',  'samurai_flush_nav_cache' );
function samurai_flush_nav_cache(): void {
	foreach ( [ 'product_cat', 'brand', 'effect', 'special-occasion', 'fire-deal', 'color', 'sound-level', 'timing', 'size' ] as $tax ) {
		delete_transient( 'sf_nav_' . $tax . '_12' );
		delete_transient( 'sf_nav_' . $tax . '_24' );
		delete_transient( 'sf_nav_' . $tax . '_8' );
	}
}

// ---------------------------------------------------------------------------
// Get taxonomy archive base URL from the registered rewrite slug.
// More reliable than hardcoding the slug string in templates.
// ---------------------------------------------------------------------------
function samurai_tax_archive_url( string $taxonomy ): string {
	$tax = get_taxonomy( $taxonomy );
	if ( ! $tax ) {
		return home_url( '/' );
	}
	$slug = ( is_array( $tax->rewrite ) && ! empty( $tax->rewrite['slug'] ) )
		? $tax->rewrite['slug']
		: $taxonomy;
	return home_url( '/' . $slug . '/' );
}

// ---------------------------------------------------------------------------
// Effects listing — /effect/ base URL resolves to all-effects index page.
//
// WordPress does not automatically route a taxonomy's base slug to any
// template (individual term archives work via is_product_taxonomy(), but the
// bare /effect/ URL falls through to 404). We add a rewrite rule that
// matches ^effect/? and sets a custom query var, then load page-effects.php
// via template_redirect.
//
// After activating or switching themes, visit Settings → Permalinks and click
// "Save Changes" once to flush the rewrite rules and activate this rule.
// ---------------------------------------------------------------------------
function samurai_effects_rewrite(): void {
	add_rewrite_rule( '^effect/?$', 'index.php?sf_effects_listing=1', 'top' );
	add_rewrite_tag( '%sf_effects_listing%', '([^&]+)' );
}
add_action( 'init', 'samurai_effects_rewrite' );

add_action( 'template_redirect', function (): void {
	if ( ! get_query_var( 'sf_effects_listing' ) ) {
		return;
	}
	include get_template_directory() . '/page-effects.php';
	exit;
} );

add_action( 'after_switch_theme', 'flush_rewrite_rules' );

// ---------------------------------------------------------------------------
// WooCommerce: keep default styles but allow targeted dequeue later
// ---------------------------------------------------------------------------
function samurai_woo_style_tweaks( array $enqueue_styles ): array {
	// Phase 9: selectively remove WC styles we fully replace.
	// For now keep them all active for compatibility.
	return $enqueue_styles;
}
add_filter( 'woocommerce_enqueue_styles', 'samurai_woo_style_tweaks' );

// Declare WooCommerce HPOS compatibility
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
} );

// ---------------------------------------------------------------------------
// Cart AJAX handler — sf_cart_update
//
// Accepts (all optional):
//   cart_key  — cart item key to update/remove
//   qty       — new quantity (0 = remove)
//
// Returns JSON:
//   { items: string, summary: string, count: int, empty: bool }
// ---------------------------------------------------------------------------
function samurai_cart_update(): void {
	check_ajax_referer( 'sf-cart-nonce', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( [ 'message' => 'Cart not available.' ] );
	}

	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
	$qty      = isset( $_POST['qty'] )      ? (int) wp_unslash( $_POST['qty'] )                       : -1;

	if ( $cart_key !== '' && $qty >= 0 ) {
		if ( 0 === $qty ) {
			WC()->cart->remove_cart_item( $cart_key );
		} else {
			WC()->cart->set_quantity( $cart_key, $qty, false );
		}
	}

	WC()->cart->calculate_totals();

	ob_start();
	get_template_part( 'template-parts/cart/items' );
	$items_html = ob_get_clean();

	ob_start();
	get_template_part( 'template-parts/cart/totals' );
	$summary_html = ob_get_clean();

	wp_send_json_success( [
		'items'   => $items_html,
		'summary' => $summary_html,
		'count'   => WC()->cart->get_cart_contents_count(),
		'empty'   => WC()->cart->is_empty(),
	] );
}
add_action( 'wp_ajax_sf_cart_update',        'samurai_cart_update' );
add_action( 'wp_ajax_nopriv_sf_cart_update', 'samurai_cart_update' );

// ---------------------------------------------------------------------------
// Force classic WooCommerce checkout template.
//
// WooCommerce 8.3+ defaults to the Gutenberg Checkout Block, which bypasses
// all theme template overrides in woocommerce/checkout/form-checkout.php.
// This filter intercepts the block's render output and replaces it with the
// classic [woocommerce_checkout] shortcode, which DOES respect theme overrides.
//
// Permanent fix: WP Admin → Pages → Checkout → replace the Checkout Block
// with a Shortcode block containing [woocommerce_checkout].
// ---------------------------------------------------------------------------
add_filter( 'render_block', function ( string $content, array $block ): string {
	if ( ( $block['blockName'] ?? '' ) !== 'woocommerce/checkout' ) {
		return $content;
	}
	ob_start();
	echo do_shortcode( '[woocommerce_checkout]' );
	return ob_get_clean();
}, 5, 2 );

// ---------------------------------------------------------------------------
// Remove Content-Security-Policy on checkout so payment gateways (PayPal,
// Square) can render their inline scripts without being blocked.
// wp_headers covers headers set through WP; send_headers covers header() calls.
// ---------------------------------------------------------------------------
add_filter( 'wp_headers', function ( array $headers ): array {
	if ( is_checkout() ) {
		unset( $headers['Content-Security-Policy'] );
		unset( $headers['Content-Security-Policy-Report-Only'] );
	}
	return $headers;
}, 20 );

add_action( 'send_headers', function (): void {
	if ( is_checkout() ) {
		header_remove( 'Content-Security-Policy' );
		header_remove( 'Content-Security-Policy-Report-Only' );
	}
}, 20 );

// ---------------------------------------------------------------------------
// Hide the WordPress page title on My Account pages — our account hero
// banner in my-account.php replaces it with a proper user greeting.
// ---------------------------------------------------------------------------
add_filter( 'woocommerce_show_page_title', function ( bool $show ): bool {
	return is_account_page() ? false : $show;
} );

add_filter( 'the_title', function ( string $title, $id = 0 ): string {
	if (
		is_account_page()
		&& ! is_null( $id )
		&& (int) $id === (int) get_option( 'woocommerce_myaccount_page_id' )
		&& in_the_loop()
		&& is_main_query()
	) {
		return '';
	}
	return $title;
}, 10, 2 );

// ---------------------------------------------------------------------------
// Billing address fields — keep optional so guests who choose local pickup
// don't hit required-field errors. Contact fields remain required.
// Default country/state to base values if absent so WC tax calc works.
// ---------------------------------------------------------------------------
add_filter( 'woocommerce_checkout_fields', function ( array $fields ): array {
	foreach ( [ 'billing_address_1', 'billing_city', 'billing_postcode', 'billing_state' ] as $k ) {
		if ( isset( $fields['billing'][ $k ] ) ) {
			$fields['billing'][ $k ]['required'] = false;
		}
	}
	return $fields;
} );

add_action( 'woocommerce_checkout_process', function (): void {
	if ( empty( $_POST['billing_country'] ) ) {
		$_POST['billing_country'] = WC()->countries->get_base_country();
	}
	if ( empty( $_POST['billing_state'] ) ) {
		$_POST['billing_state'] = WC()->countries->get_base_state();
	}
} );

// ---------------------------------------------------------------------------
// Force shipping zone matching to always use the store's base location.
//
// WC recalculates shipping packages using the customer's billing address,
// which may not fall within any configured shipping zone — causing
// "There are no shipping options available" in the order review and
// blocking order placement. Overriding the package destination to the
// store's base country/state keeps rates consistent throughout checkout.
// ---------------------------------------------------------------------------
add_filter( 'woocommerce_cart_shipping_packages', function ( array $packages ): array {
	$country = WC()->countries->get_base_country();
	$state   = WC()->countries->get_base_state();
	foreach ( $packages as $key => &$package ) {
		$package['destination']['country']   = $country;
		$package['destination']['state']     = $state;
		$package['destination']['postcode']  = '';
		$package['destination']['city']      = '';
		$package['destination']['address']   = '';
		$package['destination']['address_2'] = '';
		// WC caches rates in session keyed by package hash. Clearing the entry
		// forces a fresh zone-match on every request, which is needed because
		// we override the destination above and stale empty-rate entries would
		// otherwise survive across requests.
		WC()->session->set( 'shipping_for_package_' . $key, null );
	}
	unset( $package );
	return $packages;
} );

// ---------------------------------------------------------------------------
// Coupon AJAX handler — sf_apply_coupon
//
// Returns JSON success/error with a plain-text message string.
// Using a custom handler avoids the raw-HTML response from WC's built-in
// woocommerce_apply_coupon AJAX action which returns WC notice HTML that
// is unreliable to parse for success/failure detection.
// ---------------------------------------------------------------------------
function samurai_handle_apply_coupon(): void {
	check_ajax_referer( 'sf-coupon', 'security' );

	$code = sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) );

	if ( empty( $code ) ) {
		wp_send_json_error( [ 'message' => __( 'Please enter a coupon code.', 'samurai' ) ] );
	}

	WC()->cart->add_discount( wc_format_coupon_code( $code ) );

	$errors  = wc_get_notices( 'error' );
	$success = wc_get_notices( 'success' );
	wc_clear_notices();

	if ( ! empty( $errors ) ) {
		wp_send_json_error( [ 'message' => wp_strip_all_tags( $errors[0]['notice'] ) ] );
	}

	// Flush session to DB now so the immediately-following update_order_review
	// AJAX call sees the coupon rather than racing against PHP's shutdown hook.
	WC()->cart->calculate_totals();
	WC()->session->save_data();

	$msg = ! empty( $success )
		? wp_strip_all_tags( $success[0]['notice'] )
		/* translators: %s: coupon code */
		: sprintf( __( 'Coupon "%s" applied successfully.', 'samurai' ), esc_html( $code ) );

	wp_send_json_success( [ 'message' => $msg ] );
}
add_action( 'wp_ajax_sf_apply_coupon',        'samurai_handle_apply_coupon' );
add_action( 'wp_ajax_nopriv_sf_apply_coupon', 'samurai_handle_apply_coupon' );

// ---------------------------------------------------------------------------
// WC fragment: push the toggle total + applied-coupon chips into every
// update_order_review response so our custom UI refreshes alongside the
// standard order-review table.
// ---------------------------------------------------------------------------
add_filter( 'woocommerce_update_order_review_fragments', function ( array $fragments ): array {
	// Grand total for the toggle button
	$fragments['.sf-co-summary__total'] = '<span class="sf-co-summary__total">'
		. wp_kses_post( wc_price( WC()->cart->total ) ) . '</span>';

	// Applied coupon chips
	$chips = '';
	foreach ( WC()->cart->get_coupons() as $sf_code => $sf_coupon ) {
		$sf_discount = WC()->cart->get_coupon_discount_amount( $sf_code );
		$chips      .= '<div class="sf-co-applied-coupon">'
			. '<span class="sf-co-applied-coupon__code">' . esc_html( strtoupper( $sf_code ) ) . '</span>'
			. ( $sf_discount
				? '<span class="sf-co-applied-coupon__discount">&minus;' . wp_kses_post( wc_price( $sf_discount ) ) . '</span>'
				: '' )
			. '<button type="button"'
			. ' class="sf-co-applied-coupon__remove js-coupon-remove"'
			. ' data-coupon="' . esc_attr( $sf_code ) . '"'
			. ' aria-label="' . esc_attr( sprintf( __( 'Remove coupon %s', 'samurai' ), strtoupper( $sf_code ) ) ) . '"'
			. '>&times;</button>'
			. '</div>';
	}
	$fragments['.js-co-applied-coupons'] = '<div class="sf-co-applied-coupons js-co-applied-coupons">' . $chips . '</div>';

	return $fragments;
} );

// ---------------------------------------------------------------------------
// Remove coupon AJAX handler — sf_remove_coupon
// ---------------------------------------------------------------------------
function samurai_handle_remove_coupon(): void {
	check_ajax_referer( 'sf-coupon', 'security' );

	$code = sanitize_text_field( wp_unslash( $_POST['coupon_code'] ?? '' ) );

	if ( empty( $code ) ) {
		wp_send_json_error( [ 'message' => __( 'Coupon code missing.', 'samurai' ) ] );
	}

	WC()->cart->remove_coupon( wc_format_coupon_code( $code ) );
	WC()->cart->calculate_totals();
	WC()->session->save_data();
	wc_clear_notices();

	wp_send_json_success( [
		'message' => sprintf(
			/* translators: %s: coupon code */
			__( 'Coupon "%s" removed.', 'samurai' ),
			esc_html( strtoupper( $code ) )
		),
	] );
}
add_action( 'wp_ajax_sf_remove_coupon',        'samurai_handle_remove_coupon' );
add_action( 'wp_ajax_nopriv_sf_remove_coupon', 'samurai_handle_remove_coupon' );

// ---------------------------------------------------------------------------
// Newsletter subscription AJAX handler — sf_newsletter_subscribe
//
// Collects email + phone, validates, then POSTs to Mailchimp Marketing API v3.
// API key format: {key}-{datacenter}  (e.g. abc123-us1).
// Credentials stored in ACF Options → Theme Settings → Newsletter.
//
// Returns JSON success/error with a plain-text message.
// ---------------------------------------------------------------------------
function samurai_newsletter_subscribe(): void {
	check_ajax_referer( 'sf-newsletter', 'nonce' );

	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
	$terms = ! empty( $_POST['terms'] );

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => __( 'Please enter a valid email address.', 'samurai' ) ] );
	}

	if ( ! $terms ) {
		wp_send_json_error( [ 'message' => __( 'Please accept the Terms & Conditions.', 'samurai' ) ] );
	}

	$api_key      = function_exists( 'get_field' ) ? (string) get_field( 'mailchimp_api_key',      'option' ) : '';
	$list_id      = function_exists( 'get_field' ) ? (string) get_field( 'mailchimp_list_id',      'option' ) : '';
	$double_optin = function_exists( 'get_field' ) ? (bool)   get_field( 'mailchimp_double_optin', 'option' ) : false;
	$tag          = function_exists( 'get_field' ) ? (string) get_field( 'mailchimp_tag',          'option' ) : '';

	if ( ! $api_key || ! $list_id ) {
		wp_send_json_error( [ 'message' => __( 'Newsletter service is not configured yet. Please try again later.', 'samurai' ) ] );
	}

	// Datacenter is the suffix after the last dash (e.g. "abc123-us1" → "us1")
	$server = substr( $api_key, strrpos( $api_key, '-' ) + 1 );

	$body = [
		'email_address' => $email,
		'status'        => $double_optin ? 'pending' : 'subscribed',
	];

	if ( $phone ) {
		$body['merge_fields'] = [ 'PHONE' => $phone ];
	}

	// On local/dev environments (WAMP, XAMPP, MAMP, .local, localhost) PHP's
	// cURL cannot verify external SSL certificates — no CA bundle is configured.
	// Detect local by URL pattern OR by ABSPATH containing a known stack directory
	// (covers virtual-host setups where the site URL looks like a real domain).
	$sf_home      = home_url();
	$sf_abspath   = strtolower( ABSPATH );
	$sf_is_local  = (
		str_contains( $sf_home,    'localhost' )  ||
		str_contains( $sf_home,    '127.0.0.1' )  ||
		str_contains( $sf_home,    '.local' )      ||
		str_contains( $sf_home,    '.test' )       ||
		str_contains( $sf_abspath, 'wamp' )        ||
		str_contains( $sf_abspath, 'xampp' )       ||
		str_contains( $sf_abspath, 'mamp' )        ||
		str_contains( $sf_abspath, 'laragon' )     ||
		( isset( $_SERVER['SERVER_ADDR'] ) && $_SERVER['SERVER_ADDR'] === '127.0.0.1' )
	);

	$response = wp_remote_post(
		"https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}/members",
		[
			'headers'   => [
				'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
				'Content-Type'  => 'application/json',
			],
			'body'      => wp_json_encode( $body ),
			'timeout'   => 15,
			'sslverify' => ! $sf_is_local,
		]
	);

	if ( is_wp_error( $response ) ) {
		error_log( '[Samurai Newsletter] Mailchimp connection failed: ' . $response->get_error_message() );
		wp_send_json_error( [ 'message' => __( 'Could not reach the newsletter service. Please try again.', 'samurai' ) ] );
	}

	$code    = (int) wp_remote_retrieve_response_code( $response );
	$payload = json_decode( wp_remote_retrieve_body( $response ), true );

	// Success: new subscriber created
	if ( $code === 200 || $code === 201 ) {

		// Apply tag if configured — non-fatal if it fails
		if ( $tag ) {
			$subscriber_hash = md5( strtolower( $email ) );
			wp_remote_post(
				"https://{$server}.api.mailchimp.com/3.0/lists/{$list_id}/members/{$subscriber_hash}/tags",
				[
					'headers'   => [
						'Authorization' => 'Basic ' . base64_encode( 'anystring:' . $api_key ),
						'Content-Type'  => 'application/json',
					],
					'body'      => wp_json_encode( [
						'tags' => [ [ 'name' => sanitize_text_field( $tag ), 'status' => 'active' ] ],
					] ),
					'timeout'   => 10,
					'sslverify' => ! $sf_is_local,
				]
			);
		}

		$msg = $double_optin
			? __( 'One more step — check your inbox and click the confirmation link.', 'samurai' )
			: __( 'Subscribed! You\'ll be the first to know about our deals and new arrivals.', 'samurai' );
		wp_send_json_success( [ 'message' => $msg ] );
	}

	// Already subscribed — soft success so UX doesn't expose who's on the list
	if ( $code === 400 && ( $payload['title'] ?? '' ) === 'Member Exists' ) {
		wp_send_json_success( [ 'message' => __( 'You\'re already subscribed — keep an eye on your inbox for upcoming deals!', 'samurai' ) ] );
	}

	// Any other Mailchimp error
	wp_send_json_error( [ 'message' => __( 'Something went wrong. Please try again later.', 'samurai' ) ] );
}
add_action( 'wp_ajax_sf_newsletter_subscribe',        'samurai_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_sf_newsletter_subscribe', 'samurai_newsletter_subscribe' );
