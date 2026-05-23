<?php
/**
 * Product Archive — Shop, product categories, and all custom taxonomy archives.
 *
 * @package samurai
 * @version 2.0.0
 */
defined( 'ABSPATH' ) || exit;

/* ── Filter taxonomy definitions (order = display order in sidebar) ───────── */
$sf_filter_groups = [
	'color'       => [ 'label' => __( 'Color',        'samurai' ), 'param' => 'sf_color' ],
	'effect'      => [ 'label' => __( 'Effect',       'samurai' ), 'param' => 'sf_effect' ],
	'brand'       => [ 'label' => __( 'Brand',        'samurai' ), 'param' => 'sf_brand' ],
	'size'        => [ 'label' => __( 'Display Size', 'samurai' ), 'param' => 'sf_size' ],
	'sound-level' => [ 'label' => __( 'Sound Level',  'samurai' ), 'param' => 'sf_sound_level' ],
	'timing'      => [ 'label' => __( 'Timing',       'samurai' ), 'param' => 'sf_timing' ],
];

/* ── Detect queried term (used to pre-check the correct filter) ───────────── */
$sf_queried          = get_queried_object();
$sf_queried_taxonomy = ( $sf_queried instanceof WP_Term ) ? $sf_queried->taxonomy : '';
$sf_queried_slug     = ( $sf_queried instanceof WP_Term ) ? $sf_queried->slug     : '';

/* ── Read active filters from URL ────────────────────────────────────────── */
$sf_active = [];
foreach ( $sf_filter_groups as $sf_tax => $sf_grp ) {
	$sf_raw = isset( $_GET[ $sf_grp['param'] ] )
		? sanitize_text_field( wp_unslash( $_GET[ $sf_grp['param'] ] ) )
		: '';
	$sf_active[ $sf_tax ] = $sf_raw
		? array_values( array_filter( array_map( 'sanitize_title', explode( ',', $sf_raw ) ) ) )
		: [];

	// Pre-check the currently browsed taxonomy term
	if ( $sf_queried_taxonomy === $sf_tax && $sf_queried_slug
		&& ! in_array( $sf_queried_slug, $sf_active[ $sf_tax ], true ) ) {
		$sf_active[ $sf_tax ][] = $sf_queried_slug;
	}
}

/* ── Sort ────────────────────────────────────────────────────────────────── */
$sf_orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
$sf_sort_options = [
	''           => __( 'Default',           'samurai' ),
	'date'       => __( 'Newest',            'samurai' ),
	'price'      => __( 'Price: Low → High', 'samurai' ),
	'price-desc' => __( 'Price: High → Low', 'samurai' ),
	'title'      => __( 'Name A–Z',          'samurai' ),
	'popularity' => __( 'Popularity',        'samurai' ),
];

/* ── Pre-load filter sidebar terms + ACF images ──────────────────────────── */
$sf_filter_terms = [];
foreach ( $sf_filter_groups as $sf_tax => $sf_grp ) {
	$sf_terms = get_terms( [ 'taxonomy' => $sf_tax, 'hide_empty' => true, 'orderby' => 'name' ] );
	if ( is_wp_error( $sf_terms ) || empty( $sf_terms ) ) {
		$sf_filter_terms[ $sf_tax ] = [];
		continue;
	}
	$sf_filter_terms[ $sf_tax ] = array_map( function ( $sf_t ) {
		$sf_img = '';
		if ( function_exists( 'get_field' ) ) {
			$sf_raw = get_field( 'image', 'term_' . $sf_t->term_id );
			if ( is_array( $sf_raw ) )                          $sf_img = $sf_raw['url'] ?? '';
			elseif ( is_string( $sf_raw ) && $sf_raw )          $sf_img = $sf_raw;
			elseif ( is_numeric( $sf_raw ) && (int) $sf_raw ) {
				$sf_src = wp_get_attachment_image_src( (int) $sf_raw, [ 32, 32 ] );
				if ( $sf_src ) $sf_img = $sf_src[0];
			}
		}
		return [ 'term' => $sf_t, 'img' => $sf_img ];
	}, $sf_terms );
}

/* ── Faceted counts ──────────────────────────────────────────────────────── */
// Only compute when there is a browse context (taxonomy archive or active
// filter selections). On the plain shop page with nothing selected, WP's
// $term->count is already accurate and no extra queries are needed.
$sf_has_context     = ( $sf_queried instanceof WP_Term ) || ! empty( array_filter( $sf_active ) );
$sf_faceted_counts  = $sf_has_context
	? samurai_get_faceted_counts( $sf_filter_groups, $sf_active, $sf_queried_taxonomy, $sf_queried_slug )
	: [];

/* ── Page title + description ────────────────────────────────────────────── */
if ( $sf_queried instanceof WP_Term ) {
	$sf_page_title = $sf_queried->name;
	$sf_page_desc  = term_description( $sf_queried->term_id, $sf_queried_taxonomy );
} elseif ( is_shop() ) {
	$sf_page_title = get_option( 'woocommerce_shop_page_title' ) ?: __( 'All Products', 'samurai' );
	$sf_page_desc  = '';
} else {
	$sf_page_title = get_the_archive_title();
	$sf_page_desc  = get_the_archive_description();
}

/* ── Category banner data ────────────────────────────────────────────────── */
$sf_show_cat_banner  = $sf_queried instanceof WP_Term;
$sf_show_shop_banner = is_shop();
$sf_hero_img        = '';
$sf_tax_label       = '';
if ( $sf_show_cat_banner ) {
	if ( 'product_cat' === $sf_queried_taxonomy ) {
		$sf_tax_label  = __( 'Category', 'samurai' );
		$sf_wc_img_id  = (int) get_term_meta( $sf_queried->term_id, 'thumbnail_id', true );
		if ( $sf_wc_img_id ) {
			$sf_hero_img = wp_get_attachment_image_url( $sf_wc_img_id, 'samurai-taxonomy' ) ?: '';
		}
	} elseif ( isset( $sf_filter_groups[ $sf_queried_taxonomy ] ) ) {
		$sf_tax_label = $sf_filter_groups[ $sf_queried_taxonomy ]['label'];
	}
	if ( ! $sf_hero_img && function_exists( 'get_field' ) ) {
		$sf_raw_img = get_field( 'image', 'term_' . $sf_queried->term_id );
		if ( is_array( $sf_raw_img ) )                             $sf_hero_img = $sf_raw_img['url'] ?? '';
		elseif ( is_string( $sf_raw_img ) && $sf_raw_img )        $sf_hero_img = $sf_raw_img;
		elseif ( is_numeric( $sf_raw_img ) && (int) $sf_raw_img ) $sf_hero_img = wp_get_attachment_image_url( (int) $sf_raw_img, 'samurai-taxonomy' ) ?: '';
	}
}

/* ── URL builder (closure) ───────────────────────────────────────────────── */
$sf_filter_url = function ( array $overrides ): string {
	$params = [];
	foreach ( $_GET as $k => $v ) {
		if ( strpos( $k, 'sf_' ) === 0 || 'orderby' === $k ) {
			$params[ sanitize_key( $k ) ] = sanitize_text_field( wp_unslash( $v ) );
		}
	}
	foreach ( $overrides as $k => $v ) {
		if ( $v === '' || $v === [] ) {
			unset( $params[ $k ] );
		} else {
			$params[ $k ] = is_array( $v ) ? implode( ',', $v ) : (string) $v;
		}
	}
	unset( $params['paged'], $params['page'] );
	$base = strtok( $_SERVER['REQUEST_URI'], '?' );
	return $params ? esc_url( $base . '?' . http_build_query( $params ) ) : esc_url( $base );
};

/* ── Active filter chip count (exclude auto-checked queried term) ─────────── */
$sf_active_count = 0;
foreach ( $sf_active as $sf_tax => $sf_slugs ) {
	foreach ( $sf_slugs as $sf_slug ) {
		if ( ! ( $sf_queried_taxonomy === $sf_tax && $sf_slug === $sf_queried_slug ) ) {
			$sf_active_count++;
		}
	}
}

/* ── Main query stats (set by pre_get_posts hook before template runs) ───── */
global $wp_query;
$sf_found   = (int) $wp_query->found_posts;
$sf_max_pgs = (int) $wp_query->max_num_pages;
$sf_paged   = max( 1, get_query_var( 'paged' ) ?: 1 );

get_header();
?>

<div class="sf-archive-page">
	<div class="sf-container">

		<?php if ( $sf_show_cat_banner ) : ?>
		<div class="sf-cat-banner<?php echo $sf_hero_img ? ' sf-cat-banner--has-img' : ''; ?>">
			<?php if ( $sf_hero_img ) : ?>
			<div class="sf-cat-banner__img-wrap" aria-hidden="true">
				<img src="<?php echo esc_url( $sf_hero_img ); ?>" alt="" class="sf-cat-banner__img" loading="eager">
			</div>
			<?php endif; ?>
			<div class="sf-cat-banner__body">
				<?php if ( $sf_tax_label ) : ?>
				<span class="sf-cat-banner__label"><?php echo esc_html( $sf_tax_label ); ?></span>
				<?php endif; ?>
				<h1 class="sf-cat-banner__title"><?php echo esc_html( $sf_page_title ); ?></h1>
				<?php if ( $sf_page_desc ) : ?>
				<div class="sf-cat-banner__desc"><?php echo wp_kses_post( $sf_page_desc ); ?></div>
				<?php endif; ?>
				<p class="sf-cat-banner__count">
					<?php if ( $sf_found ) :
						printf(
							esc_html( _n( '%s product', '%s products', $sf_found, 'samurai' ) ),
							'<strong>' . number_format_i18n( $sf_found ) . '</strong>'
						);
					else :
						esc_html_e( 'No products', 'samurai' );
					endif; ?>
				</p>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $sf_show_shop_banner ) : ?>
		<div class="sf-cat-banner">
			<div class="sf-cat-banner__body">
				<h1 class="sf-cat-banner__title"><?php echo esc_html( $sf_page_title ); ?></h1>
				<p class="sf-cat-banner__count">
					<?php if ( $sf_found ) :
						printf(
							esc_html( _n( '%s product', '%s products', $sf_found, 'samurai' ) ),
							'<strong>' . number_format_i18n( $sf_found ) . '</strong>'
						);
					else :
						esc_html_e( 'No products', 'samurai' );
					endif; ?>
				</p>
			</div>
		</div>
		<?php endif; ?>

		<div class="sf-archive-layout">

			<!-- ═══════════════════════════════════════════════════════════════ -->
			<!-- SIDEBAR — Filters                                               -->
			<!-- ═══════════════════════════════════════════════════════════════ -->
			<aside class="sf-archive-sidebar" id="sf-archive-sidebar"
			       aria-label="<?php esc_attr_e( 'Product filters', 'samurai' ); ?>">
				<div class="sf-archive-sidebar__inner">

					<div class="sf-filter-header">
						<span class="sf-filter-header__title">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
							<?php esc_html_e( 'Filters', 'samurai' ); ?>
						</span>
						<div class="sf-filter-header__actions">
							<?php if ( $sf_active_count > 0 ) : ?>
							<a href="<?php echo esc_url( strtok( $_SERVER['REQUEST_URI'], '?' ) ); ?>"
							   class="sf-filter-clear-all">
								<?php esc_html_e( 'Clear all', 'samurai' ); ?>
							</a>
							<?php endif; ?>
							<button type="button" class="sf-filter-close js-filter-close"
							        aria-label="<?php esc_attr_e( 'Close filters', 'samurai' ); ?>">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
							</button>
						</div>
					</div>

					<?php
					// When browsing a specific taxonomy term (e.g. /color/red/), show a
					// locked context chip so the user knows they're inside that term.
					if ( $sf_queried instanceof WP_Term && isset( $sf_filter_groups[ $sf_queried_taxonomy ] ) ) :
						$sf_ctx_label = $sf_filter_groups[ $sf_queried_taxonomy ]['label'];
					?>
					<div class="sf-filter-context">
						<span class="sf-filter-context__label"><?php echo esc_html( $sf_ctx_label ); ?></span>
						<span class="sf-filter-context__term"><?php echo esc_html( $sf_queried->name ); ?></span>
					</div>
					<?php endif; ?>

					<?php foreach ( $sf_filter_groups as $sf_tax => $sf_grp ) :
						// Hide the filter group that matches the currently-browsed taxonomy —
						// the archive URL already constrains it and cross-selecting would
						// produce an impossible AND condition (e.g. color=red AND color=blue).
						if ( $sf_tax === $sf_queried_taxonomy ) continue;
						$sf_terms_data   = $sf_filter_terms[ $sf_tax ] ?? [];
						if ( empty( $sf_terms_data ) ) continue;
						$sf_active_slugs = $sf_active[ $sf_tax ];
						$sf_is_open      = ! empty( $sf_active_slugs );
						$sf_panel_id     = 'sf-filter-panel-' . sanitize_html_class( $sf_tax );
					?>
					<div class="sf-filter-group js-filter-group <?php echo $sf_is_open ? 'is-open' : ''; ?>">
						<button type="button"
						        class="sf-filter-group__toggle js-filter-toggle"
						        aria-expanded="<?php echo $sf_is_open ? 'true' : 'false'; ?>"
						        aria-controls="<?php echo esc_attr( $sf_panel_id ); ?>">
							<span class="sf-filter-group__label"><?php echo esc_html( $sf_grp['label'] ); ?></span>
							<?php if ( ! empty( $sf_active_slugs ) ) : ?>
							<span class="sf-filter-group__active-count"><?php echo count( $sf_active_slugs ); ?></span>
							<?php endif; ?>
							<svg class="sf-filter-group__chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
						</button>

						<div class="sf-filter-group__panel"
						     id="<?php echo esc_attr( $sf_panel_id ); ?>"
						     <?php echo $sf_is_open ? '' : 'hidden'; ?>>
							<ul class="sf-filter-options" role="list">
								<?php foreach ( $sf_terms_data as $sf_td ) :
									$sf_t       = $sf_td['term'];
									$sf_img     = $sf_td['img'];
									$sf_checked = in_array( $sf_t->slug, $sf_active_slugs, true );

									// Resolve count before rendering so we can skip dead-end options.
									// When faceted context is active, missing entries mean 0 products —
									// fall back to 0, NOT to the global $term->count (which ignores context).
									$sf_opt_count = $sf_has_context
										? (int) ( $sf_faceted_counts[ $sf_tax ][ $sf_t->term_id ] ?? 0 )
										: (int) $sf_t->count;

									// Hide options with 0 products in context unless already checked
									// (a checked option must stay visible so the user can deselect it).
									if ( $sf_has_context && ! $sf_checked && $sf_opt_count === 0 ) continue;

									$sf_new_slugs = $sf_active_slugs;
									if ( $sf_checked ) {
										$sf_new_slugs = array_values( array_diff( $sf_new_slugs, [ $sf_t->slug ] ) );
									} else {
										$sf_new_slugs[] = $sf_t->slug;
									}
									$sf_opt_url = $sf_filter_url( [ $sf_grp['param'] => $sf_new_slugs ] );
								?>
								<li class="sf-filter-option<?php echo $sf_checked ? ' is-active' : ''; ?>">
									<a href="<?php echo $sf_opt_url; // phpcs:ignore ?>"
									   class="sf-filter-option__link"
									   aria-pressed="<?php echo $sf_checked ? 'true' : 'false'; ?>">
										<span class="sf-filter-option__check" aria-hidden="true">
											<?php if ( $sf_checked ) : ?>
											<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
											<?php endif; ?>
										</span>
										<span class="sf-filter-option__thumb<?php echo $sf_img ? '' : ' sf-filter-option__thumb--placeholder'; ?>" aria-hidden="true">
											<?php if ( $sf_img ) : ?>
											<img src="<?php echo esc_url( $sf_img ); ?>" alt="" width="24" height="24" loading="lazy">
											<?php else : ?>
											<?php echo esc_html( mb_strtoupper( mb_substr( $sf_t->name, 0, 1 ) ) ); ?>
											<?php endif; ?>
										</span>
										<span class="sf-filter-option__name"><?php echo esc_html( $sf_t->name ); ?></span>
										<span class="sf-filter-option__count">(<?php echo absint( $sf_opt_count ); ?>)</span>
									</a>
								</li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
					<?php endforeach; ?>

				</div>
			</aside>

			<!-- ═══════════════════════════════════════════════════════════════ -->
			<!-- MAIN — header + grid + pagination                               -->
			<!-- ═══════════════════════════════════════════════════════════════ -->
			<main class="sf-archive-main" id="sf-archive-main">

				<div class="sf-archive-header">
					<button type="button"
					        class="sf-filter-toggle-btn js-filter-open"
					        aria-expanded="false"
					        aria-controls="sf-archive-sidebar">
						<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
						<?php esc_html_e( 'Filters', 'samurai' ); ?>
						<?php if ( $sf_active_count > 0 ) : ?>
						<span class="sf-filter-toggle-btn__badge"><?php echo absint( $sf_active_count ); ?></span>
						<?php endif; ?>
					</button>
					<div class="sf-archive-header__left">
						<?php if ( ! $sf_show_cat_banner && ! $sf_show_shop_banner ) : ?>
						<h1 class="sf-archive-header__title"><?php echo esc_html( $sf_page_title ); ?></h1>
						<?php endif; ?>
					</div>
					<div class="sf-archive-header__right">
						<div class="sf-archive-sort">
							<label for="sf-sort" class="screen-reader-text"><?php esc_html_e( 'Sort products', 'samurai' ); ?></label>
							<select id="sf-sort" class="sf-archive-sort__select js-sort-select">
								<?php foreach ( $sf_sort_options as $sf_val => $sf_label ) : ?>
								<option value="<?php echo esc_attr( $sf_val ); ?>"<?php selected( $sf_orderby, $sf_val ); ?>>
									<?php echo esc_html( $sf_label ); ?>
								</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<span class="sf-archive-header__count">
						<?php if ( $sf_found ) :
							printf(
								esc_html( _n( '%s product', '%s products', $sf_found, 'samurai' ) ),
								'<strong>' . number_format_i18n( $sf_found ) . '</strong>'
							);
						else :
							esc_html_e( 'No products', 'samurai' );
						endif; ?>
					</span>
				</div>

				<?php if ( $sf_page_desc && ! $sf_show_cat_banner ) : ?>
				<div class="sf-archive-desc"><?php echo wp_kses_post( $sf_page_desc ); ?></div>
				<?php endif; ?>

				<!-- Active filter chips -->
				<?php if ( $sf_active_count > 0 ) : ?>
				<div class="sf-active-filters">
					<?php foreach ( $sf_filter_groups as $sf_tax => $sf_grp ) :
						foreach ( $sf_active[ $sf_tax ] as $sf_slug ) :
							if ( $sf_queried_taxonomy === $sf_tax && $sf_slug === $sf_queried_slug ) continue;
							$sf_term_obj  = get_term_by( 'slug', $sf_slug, $sf_tax );
							$sf_chip_name = $sf_term_obj ? $sf_term_obj->name : $sf_slug;
							$sf_rem_slugs = array_values( array_diff( $sf_active[ $sf_tax ], [ $sf_slug ] ) );
							$sf_chip_url  = $sf_filter_url( [ $sf_grp['param'] => $sf_rem_slugs ] );
					?>
					<a href="<?php echo $sf_chip_url; // phpcs:ignore ?>" class="sf-active-filter-chip">
						<?php echo esc_html( $sf_chip_name ); ?>
						<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
					</a>
					<?php
						endforeach;
					endforeach; ?>
				</div>
				<?php endif; ?>

				<!-- Products -->
				<?php if ( have_posts() ) : ?>
				<div class="sf-product-grid sf-product-grid--archive">
					<?php
					while ( have_posts() ) :
						the_post();
						$sf_card_product = wc_get_product( get_the_ID() );
						if ( ! $sf_card_product ) continue;
						get_template_part( 'template-parts/product/product-card', null, [ 'product' => $sf_card_product ] );
					endwhile;
					?>
				</div>

				<?php if ( $sf_max_pgs > 1 ) : ?>
				<nav class="sf-archive-pagination" aria-label="<?php esc_attr_e( 'Products pagination', 'samurai' ); ?>">
					<?php
					echo paginate_links( [ // phpcs:ignore
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'current'   => $sf_paged,
						'total'     => $sf_max_pgs,
						'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg><span class="screen-reader-text">' . esc_html__( 'Previous', 'samurai' ) . '</span>',
						'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Next', 'samurai' ) . '</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>',
					] );
					?>
				</nav>
				<?php endif; ?>

				<?php else : ?>
				<div class="sf-archive-empty">
					<span class="sf-archive-empty__icon" aria-hidden="true">
						<svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
					</span>
					<p class="sf-archive-empty__title"><?php esc_html_e( 'No products found', 'samurai' ); ?></p>
					<p class="sf-archive-empty__sub"><?php esc_html_e( 'Try removing some filters or browse all products.', 'samurai' ); ?></p>
					<a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
					   class="sf-btn sf-btn--primary">
						<?php esc_html_e( 'Browse All Products', 'samurai' ); ?>
					</a>
				</div>
				<?php endif; ?>

			</main>
		</div>
	</div>
</div>

<div class="sf-filter-backdrop js-filter-backdrop" aria-hidden="true" hidden></div>

<?php get_footer(); ?>
