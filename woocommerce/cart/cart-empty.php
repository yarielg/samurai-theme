<?php
/**
 * Empty cart state template.
 *
 * Content is editable via Theme Settings → Cart & Checkout.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );

$sf_heading     = samurai_option_text( 'cart_empty_message',   __( 'Your cart is empty', 'samurai' ),       false );
$sf_sub         = samurai_option_text( 'cart_empty_subtext',   __( "Looks like you haven't added anything yet. Browse our fireworks and light up your next celebration!", 'samurai' ), false );
$sf_cta_label   = samurai_option_text( 'cart_empty_cta_label', __( 'Shop Fireworks', 'samurai' ),           false );
$sf_cta_url     = function_exists( 'get_field' ) ? ( get_field( 'cart_empty_cta_url', 'option' ) ?: '' ) : '';
if ( ! $sf_cta_url ) {
	$sf_cta_url = wc_get_page_permalink( 'shop' );
}

$sf_cats = get_terms( [
	'taxonomy'   => 'product_cat',
	'hide_empty' => true,
	'parent'     => 0,
	'number'     => 4,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'exclude'    => [ absint( get_option( 'default_product_cat' ) ) ],
] );
?>
<div class="sf-cart-page sf-container">

	<div class="sf-cart-empty">

		<!-- ── Hero message ─────────────────────────────────────────────── -->
		<div class="sf-cart-empty__hero">

			<div class="sf-cart-empty__icon" aria-hidden="true">
				<svg width="72" height="72" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
					<circle cx="9" cy="21" r="1"/>
					<circle cx="20" cy="21" r="1"/>
					<path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 001.95-1.57l1.65-7.43H6"/>
				</svg>
			</div>

			<h1 class="sf-cart-empty__title"><?php echo esc_html( $sf_heading ); ?></h1>

			<p class="sf-cart-empty__sub"><?php echo esc_html( $sf_sub ); ?></p>

			<a href="<?php echo esc_url( $sf_cta_url ); ?>" class="sf-cart-empty__cta">
				<?php echo esc_html( $sf_cta_label ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<line x1="5" y1="12" x2="19" y2="12"/>
					<polyline points="12 5 19 12 12 19"/>
				</svg>
			</a>

		</div>

		<?php if ( ! empty( $sf_cats ) && ! is_wp_error( $sf_cats ) ) : ?>
		<!-- ── Category grid ────────────────────────────────────────────── -->
		<div class="sf-cart-empty__discover">
			<h2 class="sf-cart-empty__discover-title"><?php esc_html_e( 'Start exploring — top categories', 'samurai' ); ?></h2>
			<div class="sf-cart-empty__cat-grid">
				<?php foreach ( $sf_cats as $sf_cat ) :
					$sf_thumb_id = absint( get_term_meta( $sf_cat->term_id, 'thumbnail_id', true ) );
					$sf_img      = $sf_thumb_id
						? wp_get_attachment_image( $sf_thumb_id, 'samurai-taxonomy', false, [ 'class' => 'sf-cart-empty__cat-img' ] )
						: '';
				?>
				<a href="<?php echo esc_url( get_term_link( $sf_cat ) ); ?>"
				   class="sf-cart-empty__cat-card"
				   aria-label="<?php echo esc_attr( $sf_cat->name ); ?>">

					<?php if ( $sf_img ) : ?>
						<?php echo $sf_img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — wp_get_attachment_image is trusted ?>
					<?php else : ?>
						<div class="sf-cart-empty__cat-placeholder" aria-hidden="true"></div>
					<?php endif; ?>

					<div class="sf-cart-empty__cat-overlay" aria-hidden="true"></div>
					<span class="sf-cart-empty__cat-name"><?php echo esc_html( $sf_cat->name ); ?></span>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>

	</div><!-- /.sf-cart-empty -->

</div><!-- /.sf-cart-page -->
