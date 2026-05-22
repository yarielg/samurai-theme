<?php
/**
 * Single Product template.
 *
 * @package samurai
 * @version 2.0.0
 */
defined( 'ABSPATH' ) || exit;

/** Extract an 11-char YouTube video ID from any YouTube URL format. */
function samurai_youtube_id( string $url ): string {
	if ( ! $url ) return '';
	preg_match(
		'/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/',
		$url, $m
	);
	return $m[1] ?? '';
}

get_header();

while ( have_posts() ) :
	the_post();

	global $product;
	if ( ! $product instanceof WC_Product ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! $product ) {
		continue;
	}

	$sf_product_id = $product->get_id();

	/* ── Taxonomy terms ───────────────────────────────────────────────────── */
	$sf_brands  = wp_get_post_terms( $sf_product_id, 'brand' );
	$sf_colors  = wp_get_post_terms( $sf_product_id, 'color' );
	$sf_effects = wp_get_post_terms( $sf_product_id, 'effect' );
	$sf_sounds  = wp_get_post_terms( $sf_product_id, 'sound-level' );
	$sf_sizes   = wp_get_post_terms( $sf_product_id, 'size' );

	$sf_brand      = ( ! is_wp_error( $sf_brands ) && ! empty( $sf_brands ) ) ? $sf_brands[0] : null;
	$sf_sound_name = ( ! is_wp_error( $sf_sounds ) && ! empty( $sf_sounds ) ) ? $sf_sounds[0]->name : '';
	$sf_size_name  = ( ! is_wp_error( $sf_sizes ) && ! empty( $sf_sizes ) ) ? $sf_sizes[0]->name : '';

	/* ── ACF fields ───────────────────────────────────────────────────────── */
	$sf_shot_count = '';
	$sf_duration   = '';
	$sf_video_url  = '';
	if ( function_exists( 'get_field' ) ) {
		$sf_shot_count = get_field( 'shot_count', $sf_product_id );
		$sf_duration   = get_field( 'duration', $sf_product_id );
		$sf_video_url  = get_field( 'banner_video_modal', $sf_product_id );
	}
	$sf_video_id = samurai_youtube_id( (string) $sf_video_url );

	/* ── Brand logo ───────────────────────────────────────────────────────── */
	$sf_brand_img_id   = 0;
	$sf_brand_img_url2 = '';
	$sf_brand_url      = '';
	$sf_brand_name     = '';
	if ( $sf_brand ) {
		$sf_brand_name = $sf_brand->name;
		$lnk = get_term_link( $sf_brand );
		$sf_brand_url  = is_wp_error( $lnk ) ? '' : $lnk;
		if ( function_exists( 'get_field' ) ) {
			$raw = get_field( 'image', 'term_' . $sf_brand->term_id );
			if ( is_array( $raw ) ) {
				$sf_brand_img_id   = absint( $raw['ID'] ?? 0 );
				$sf_brand_img_url2 = $raw['url'] ?? '';
			} elseif ( is_numeric( $raw ) && $raw > 0 ) {
				$sf_brand_img_id = absint( $raw );
			} elseif ( is_string( $raw ) && $raw ) {
				$sf_brand_img_url2 = $raw;
			}
		}
	}

	/* ── Stock + Inventory label ──────────────────────────────────────────── */
	$sf_in_stock      = $product->is_in_stock();
	$sf_manage_stock  = $product->get_manage_stock();
	$sf_stock_qty     = $product->get_stock_quantity();

	if ( $sf_manage_stock && is_numeric( $sf_stock_qty ) ) {
		if ( (int) $sf_stock_qty <= 0 ) {
			$sf_inv_text  = __( 'Sold Out', 'samurai' );
			$sf_inv_class = 'critical';
		} elseif ( (int) $sf_stock_qty <= 5 ) {
			$sf_inv_text  = sprintf( __( 'Only %d left!', 'samurai' ), (int) $sf_stock_qty );
			$sf_inv_class = 'critical';
		} elseif ( (int) $sf_stock_qty <= 20 ) {
			$sf_inv_text  = __( 'Low Stock', 'samurai' );
			$sf_inv_class = 'low';
		} else {
			$sf_inv_text  = __( 'In Stock', 'samurai' );
			$sf_inv_class = 'good';
		}
	} elseif ( $sf_in_stock ) {
		$sf_inv_text  = __( 'Available', 'samurai' );
		$sf_inv_class = 'good';
	} else {
		$sf_inv_text  = __( 'Sold Out', 'samurai' );
		$sf_inv_class = 'critical';
	}

	/* ── Product categories (non-uncategorized, up to 3) ─────────────────── */
	$sf_cats = wp_get_post_terms( $sf_product_id, 'product_cat', [ 'number' => 5 ] );
	if ( is_wp_error( $sf_cats ) ) $sf_cats = [];
	$sf_cats = array_filter( $sf_cats, fn( $c ) => $c->slug !== 'uncategorized' && $c->parent !== 0 );
	if ( empty( $sf_cats ) ) {
		$sf_cats = wp_get_post_terms( $sf_product_id, 'product_cat', [ 'number' => 3 ] );
		if ( is_wp_error( $sf_cats ) ) $sf_cats = [];
		$sf_cats = array_filter( $sf_cats, fn( $c ) => $c->slug !== 'uncategorized' );
	}
	$sf_cats = array_values( array_slice( $sf_cats, 0, 2 ) );

	/* ── Pre-fetch effect ACF images ─────────────────────────────────────── */
	$sf_effect_data = [];
	if ( ! is_wp_error( $sf_effects ) && ! empty( $sf_effects ) ) {
		foreach ( $sf_effects as $sf_eff ) {
			$sf_eff_img_url = '';
			if ( function_exists( 'get_field' ) ) {
				$raw = get_field( 'image', 'term_' . $sf_eff->term_id );
				if ( is_array( $raw ) )           $sf_eff_img_url = $raw['url'] ?? '';
				elseif ( is_string( $raw ) )       $sf_eff_img_url = $raw;
			}
			$sf_effect_data[] = [ 'term' => $sf_eff, 'img' => $sf_eff_img_url ];
		}
	}

	/* ── Pre-fetch color ACF images ──────────────────────────────────────── */
	$sf_color_data = [];
	if ( ! is_wp_error( $sf_colors ) && ! empty( $sf_colors ) ) {
		foreach ( $sf_colors as $sf_col ) {
			$sf_col_img_url = '';
			if ( function_exists( 'get_field' ) ) {
				$raw = get_field( 'image', 'term_' . $sf_col->term_id );
				if ( is_array( $raw ) )           $sf_col_img_url = $raw['url'] ?? '';
				elseif ( is_string( $raw ) )       $sf_col_img_url = $raw;
			}
			$sf_color_data[] = [ 'term' => $sf_col, 'img' => $sf_col_img_url ];
		}
	}

	do_action( 'woocommerce_before_single_product' );
	?>

<div id="sf-product-<?php the_ID(); ?>" class="sf-single-product" itemscope itemtype="https://schema.org/Product">

	<!-- ── Main: Gallery + Summary ─────────────────────────────────────────── -->
	<div class="sf-sp-main">
		<div class="sf-container sf-sp-main-grid">

			<!-- Gallery column -->
			<div class="sf-sp-gallery-col">
				<div class="sf-sp-gallery-wrap" id="sf-sp-gallery">
					<?php do_action( 'woocommerce_before_single_product_summary' ); ?>

					<?php if ( $sf_video_id ) : ?>
					<button type="button"
					        class="sf-sp-video-trigger js-video-trigger"
					        data-video="<?php echo esc_attr( $sf_video_id ); ?>"
					        aria-label="<?php esc_attr_e( 'Watch product video', 'samurai' ); ?>">
						<span class="sf-sp-video-trigger__ring" aria-hidden="true"></span>
						<span class="sf-sp-video-trigger__icon" aria-hidden="true">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="5 3 19 12 5 21 5 3"/></svg>
						</span>
						<span class="sf-sp-video-trigger__label"><?php esc_html_e( 'Watch it Fly!', 'samurai' ); ?></span>
					</button>
					<?php endif; ?>
				</div>
			</div>

			<!-- Summary column -->
			<div class="sf-sp-summary">

				<!-- Title + rating -->
				<?php woocommerce_template_single_title(); ?>
				<?php woocommerce_template_single_rating(); ?>

				<!-- Brand + Category badges -->
				<?php if ( $sf_brand || ! empty( $sf_cats ) ) : ?>
				<div class="sf-sp-badges">
					<?php if ( $sf_brand ) : ?>
					<a href="<?php echo esc_url( $sf_brand_url ); ?>" class="sf-sp-brand-badge" title="<?php echo esc_attr( $sf_brand_name ); ?>">
						<?php if ( $sf_brand_img_id ) : ?>
							<?php echo wp_get_attachment_image( $sf_brand_img_id, [ 80, 32 ], false, [
								'class' => 'sf-sp-brand-badge__img',
								'alt'   => esc_attr( $sf_brand_name ),
							] ); ?>
						<?php elseif ( $sf_brand_img_url2 ) : ?>
							<img src="<?php echo esc_url( $sf_brand_img_url2 ); ?>"
							     alt="<?php echo esc_attr( $sf_brand_name ); ?>"
							     class="sf-sp-brand-badge__img" width="80" height="32" loading="lazy">
						<?php else : ?>
							<span class="sf-sp-brand-badge__name"><?php echo esc_html( $sf_brand_name ); ?></span>
						<?php endif; ?>
					</a>
					<?php endif; ?>
					<?php foreach ( $sf_cats as $sf_cat ) : ?>
					<a href="<?php echo esc_url( get_term_link( $sf_cat ) ); ?>" class="sf-sp-cat-badge">
						<?php echo esc_html( $sf_cat->name ); ?>
					</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<!-- Price -->
				<div class="sf-sp-price-row">
					<?php woocommerce_template_single_price(); ?>
				</div>

				<!-- Spec metrics grid -->
				<?php if ( $sf_shot_count || $sf_duration || $sf_size_name ) : ?>
				<div class="sf-sp-specs">
					<?php if ( $sf_shot_count ) : ?>
					<div class="sf-sp-spec-tile">
						<span class="sf-sp-spec-tile__val"><?php echo esc_html( $sf_shot_count ); ?></span>
						<span class="sf-sp-spec-tile__label">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
							<?php esc_html_e( 'Pieces', 'samurai' ); ?>
						</span>
					</div>
					<?php endif; ?>
					<?php if ( $sf_duration ) : ?>
					<div class="sf-sp-spec-tile">
						<span class="sf-sp-spec-tile__val"><?php echo esc_html( $sf_duration ); ?><small>s</small></span>
						<span class="sf-sp-spec-tile__label">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
							<?php esc_html_e( 'Duration', 'samurai' ); ?>
						</span>
					</div>
					<?php endif; ?>
					<div class="sf-sp-spec-tile sf-sp-spec-tile--inv-<?php echo esc_attr( $sf_inv_class ); ?>">
						<span class="sf-sp-spec-tile__val sf-sp-spec-tile__val--inv"><?php echo esc_html( $sf_inv_text ); ?></span>
						<span class="sf-sp-spec-tile__label">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
							<?php esc_html_e( 'Inventory', 'samurai' ); ?>
						</span>
					</div>
					<?php if ( $sf_size_name ) : ?>
					<div class="sf-sp-spec-tile">
						<span class="sf-sp-spec-tile__val"><?php echo esc_html( $sf_size_name ); ?></span>
						<span class="sf-sp-spec-tile__label">
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
							<?php esc_html_e( 'Display', 'samurai' ); ?>
						</span>
					</div>
					<?php endif; ?>
				</div>
				<?php endif; ?>

				<!-- Add-to-cart form -->
				<div class="sf-sp-cart-wrap">
					<?php woocommerce_template_single_add_to_cart(); ?>
				</div>

				<!-- Trust badges -->
				<div class="sf-sp-trust">
					<div class="sf-sp-trust-item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
						<span><?php esc_html_e( 'Secure Checkout', 'samurai' ); ?></span>
					</div>
					<div class="sf-sp-trust-item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
						<span><?php esc_html_e( 'In-Store Pickup', 'samurai' ); ?></span>
					</div>
					<div class="sf-sp-trust-item">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.44 2 2 0 0 1 3.59 1.25h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 5.45 5.45l.91-.91a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 15.92z"/></svg>
						<span><?php esc_html_e( 'Expert Support', 'samurai' ); ?></span>
					</div>
				</div>

				<!-- Effects -->
				<?php if ( ! empty( $sf_effect_data ) ) : ?>
				<div class="sf-sp-tax-block">
					<h3 class="sf-sp-tax-block__heading">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/><path d="M12 6v6l4 2"/></svg>
						<?php esc_html_e( 'Effects', 'samurai' ); ?>
					</h3>
					<div class="sf-sp-tax-chips">
						<?php foreach ( $sf_effect_data as $sf_ed ) : ?>
						<a href="<?php echo esc_url( get_term_link( $sf_ed['term'] ) ); ?>"
						   class="sf-sp-tax-chip sf-sp-tax-chip--effect"
						   title="<?php echo esc_attr( sprintf( __( 'Browse %s products', 'samurai' ), $sf_ed['term']->name ) ); ?>">
							<?php if ( $sf_ed['img'] ) : ?>
							<span class="sf-sp-tax-chip__thumb">
								<img src="<?php echo esc_url( $sf_ed['img'] ); ?>"
								     alt="<?php echo esc_attr( $sf_ed['term']->name ); ?>"
								     width="36" height="36" loading="lazy">
							</span>
							<?php else : ?>
							<span class="sf-sp-tax-chip__thumb sf-sp-tax-chip__thumb--placeholder" aria-hidden="true">
								<?php echo esc_html( mb_strtoupper( mb_substr( $sf_ed['term']->name, 0, 1 ) ) ); ?>
							</span>
							<?php endif; ?>
							<span class="sf-sp-tax-chip__name"><?php echo esc_html( $sf_ed['term']->name ); ?></span>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

				<!-- Colors -->
				<?php if ( ! empty( $sf_color_data ) ) : ?>
				<div class="sf-sp-tax-block">
					<h3 class="sf-sp-tax-block__heading">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
						<?php esc_html_e( 'Colors', 'samurai' ); ?>
					</h3>
					<div class="sf-sp-tax-chips">
						<?php foreach ( $sf_color_data as $sf_cd ) : ?>
						<a href="<?php echo esc_url( get_term_link( $sf_cd['term'] ) ); ?>"
						   class="sf-sp-tax-chip sf-sp-tax-chip--color"
						   title="<?php echo esc_attr( sprintf( __( 'Browse %s products', 'samurai' ), $sf_cd['term']->name ) ); ?>">
							<?php if ( $sf_cd['img'] ) : ?>
							<span class="sf-sp-tax-chip__thumb">
								<img src="<?php echo esc_url( $sf_cd['img'] ); ?>"
								     alt="<?php echo esc_attr( $sf_cd['term']->name ); ?>"
								     width="36" height="36" loading="lazy">
							</span>
							<?php else : ?>
							<span class="sf-sp-tax-chip__thumb sf-sp-tax-chip__thumb--placeholder" aria-hidden="true">
								<?php echo esc_html( mb_strtoupper( mb_substr( $sf_cd['term']->name, 0, 1 ) ) ); ?>
							</span>
							<?php endif; ?>
							<span class="sf-sp-tax-chip__name"><?php echo esc_html( $sf_cd['term']->name ); ?></span>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>

			</div><!-- .sf-sp-summary -->
		</div><!-- .sf-sp-main-grid -->
	</div><!-- .sf-sp-main -->

	<!-- ── Tabs: Description, Info, Reviews ─────────────────────────────────── -->
	<div class="sf-sp-tabs-section">
		<div class="sf-container">
			<?php do_action( 'woocommerce_after_single_product_summary' ); ?>
		</div>
	</div>

	<!-- ── Related products ──────────────────────────────────────────────────── -->
	<?php
	$sf_rel_cat_ids = wp_get_post_terms( $sf_product_id, 'product_cat', [ 'fields' => 'ids' ] );
	$sf_rel_args    = [
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 8,
		'post__not_in'   => [ $sf_product_id ],
		'orderby'        => 'rand',
	];
	if ( ! is_wp_error( $sf_rel_cat_ids ) && ! empty( $sf_rel_cat_ids ) ) {
		$sf_rel_args['tax_query'] = [ [
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $sf_rel_cat_ids,
		] ];
	}
	$sf_rel_query = new WP_Query( $sf_rel_args );
	if ( $sf_rel_query->have_posts() ) :
	?>
	<div class="sf-sp-related-section">
		<div class="sf-container">
			<h2 class="sf-sp-related-heading"><?php esc_html_e( 'You Might Also Like', 'samurai' ); ?></h2>
		</div>
		<div class="sf-container sf-carousel-container">
			<div class="sf-carousel-wrap js-carousel">
				<div class="sf-carousel-track">
					<?php foreach ( $sf_rel_query->posts as $sf_rel_post ) :
						$sf_rel = wc_get_product( $sf_rel_post->ID );
						if ( ! $sf_rel ) continue;
					?>
					<div class="sf-carousel-item">
						<?php get_template_part( 'template-parts/product/product-card', null, [ 'product' => $sf_rel ] ); ?>
					</div>
					<?php endforeach; ?>
				</div>
				<button class="sf-carousel-arrow sf-carousel-arrow--prev js-carousel-prev" aria-label="<?php esc_attr_e( 'Previous', 'samurai' ); ?>" hidden>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
				</button>
				<button class="sf-carousel-arrow sf-carousel-arrow--next js-carousel-next" aria-label="<?php esc_attr_e( 'Next', 'samurai' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
				</button>
			</div>
			<div class="sf-carousel-progress-wrap">
				<div class="sf-carousel-progress-bar js-carousel-bar"></div>
			</div>
		</div>
	</div>
	<?php
	wp_reset_postdata();
	endif;
	?>

</div><!-- #sf-product -->

<?php if ( $sf_video_id ) : ?>
<!-- ── YouTube video modal ───────────────────────────────────────────────── -->
<div class="sf-video-modal" id="sf-video-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Product video', 'samurai' ); ?>" hidden>
	<div class="sf-video-modal__backdrop js-video-close"></div>
	<div class="sf-video-modal__box">
		<button type="button" class="sf-video-modal__close js-video-close" aria-label="<?php esc_attr_e( 'Close video', 'samurai' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<div class="sf-video-modal__ratio">
			<iframe id="sf-video-iframe"
			        width="1280" height="720"
			        src=""
			        data-src="https://www.youtube.com/embed/<?php echo esc_attr( $sf_video_id ); ?>?autoplay=1&rel=0&modestbranding=1"
			        title="<?php esc_attr_e( 'Product video', 'samurai' ); ?>"
			        frameborder="0"
			        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
			        allowfullscreen></iframe>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- ── Add-to-cart toast ─────────────────────────────────────────────────── -->
<div class="sf-sp-toast" id="sf-sp-toast" role="status" aria-live="polite" aria-atomic="true" hidden>
	<div class="sf-sp-toast__inner">
		<span class="sf-sp-toast__icon" aria-hidden="true">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
		</span>
		<div class="sf-sp-toast__body">
			<strong class="sf-sp-toast__title"><?php esc_html_e( 'Added to cart!', 'samurai' ); ?></strong>
			<span class="sf-sp-toast__sub js-toast-product-name"></span>
		</div>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="sf-sp-toast__view-cart">
			<?php esc_html_e( 'View Cart', 'samurai' ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
		</a>
		<button type="button" class="sf-sp-toast__close js-toast-close" aria-label="<?php esc_attr_e( 'Dismiss notification', 'samurai' ); ?>">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
	</div>
	<div class="sf-sp-toast__progress js-toast-progress"></div>
</div>

<?php
	do_action( 'woocommerce_after_single_product' );
endwhile;

get_footer();
