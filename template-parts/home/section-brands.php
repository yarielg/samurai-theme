<?php
/**
 * Homepage — Shop by Brand — infinite scrolling marquee.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'brand' ) ) {
	return;
}

$sf_heading = samurai_option_text( 'home_brands_heading', __( 'Shop by Brand', 'samurai' ), false );
$sf_brands  = samurai_get_nav_terms( 'brand', 18, 'count' );

if ( empty( $sf_brands ) ) {
	return;
}

// Pre-fetch images so we can duplicate the list for the seamless loop.
$sf_brand_data = [];
foreach ( $sf_brands as $sf_brand ) {
	$sf_acf_img = function_exists( 'get_field' ) ? get_field( 'image', 'term_' . $sf_brand->term_id ) : false;
	$sf_img_id  = 0;
	if ( is_array( $sf_acf_img ) ) {
		$sf_img_id = absint( $sf_acf_img['ID'] ?? 0 );
	} elseif ( is_numeric( $sf_acf_img ) && $sf_acf_img > 0 ) {
		$sf_img_id = absint( $sf_acf_img );
	} elseif ( is_string( $sf_acf_img ) && $sf_acf_img ) {
		$sf_img_id = absint( attachment_url_to_postid( $sf_acf_img ) );
	}
	$sf_brand_data[] = [
		'term'   => $sf_brand,
		'img_id' => $sf_img_id,
	];
}
?>
<section class="sf-home-section sf-brands-section" aria-label="<?php echo esc_attr( $sf_heading ); ?>">

	<div class="sf-container">
		<div class="sf-section-header sf-brands__header">
			<div class="sf-brands__title-group">
				<p class="sf-brands__eyebrow">
					<span class="sf-brands__eyebrow-dot" aria-hidden="true"></span>
					<?php esc_html_e( 'Our Partners', 'samurai' ); ?>
				</p>
				<h2 class="sf-section-heading"><?php echo esc_html( $sf_heading ); ?></h2>
			</div>
			<a href="<?php echo esc_url( samurai_tax_archive_url( 'brand' ) ); ?>" class="sf-section-link">
				<?php esc_html_e( 'All Brands', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>
	</div>

	<!-- Marquee — duplicated set for seamless CSS loop -->
	<div class="sf-brand-marquee-wrap" role="region" aria-label="<?php esc_attr_e( 'Scrolling brand logos', 'samurai' ); ?>">
		<div class="sf-brand-marquee">
			<?php foreach ( [ false, true ] as $sf_is_dupe ) :
				foreach ( $sf_brand_data as $sf_bdata ) :
					$sf_brand     = $sf_bdata['term'];
					$sf_img_id    = $sf_bdata['img_id'];
					$sf_brand_url = get_term_link( $sf_brand );
					$sf_name      = $sf_brand->name;
					?>
			<a href="<?php echo esc_url( $sf_brand_url ); ?>"
			   class="sf-brand-logo-card"
			   title="<?php echo esc_attr( sprintf( /* translators: %s: brand name */ __( 'Shop %s', 'samurai' ), $sf_name ) ); ?>"
			   <?php if ( $sf_is_dupe ) : ?>aria-hidden="true" tabindex="-1"<?php endif; ?>>

				<div class="sf-brand-logo-card__media">
					<?php if ( $sf_img_id ) : ?>
						<?php echo wp_get_attachment_image( $sf_img_id, 'samurai-brand', false, [
							'class'   => 'sf-brand-logo-card__img',
							'loading' => 'lazy',
							'alt'     => esc_attr( $sf_name ),
						] ); ?>
					<?php else : ?>
						<span class="sf-brand-logo-card__initial" aria-hidden="true">
							<?php echo esc_html( mb_strtoupper( mb_substr( $sf_name, 0, 2 ) ) ); ?>
						</span>
					<?php endif; ?>
				</div>

				<span class="sf-brand-logo-card__name"><?php echo esc_html( $sf_name ); ?></span>

			</a>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	</div>

</section>
