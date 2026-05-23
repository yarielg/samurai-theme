<?php
/**
 * Homepage — Product Spotlight slider.
 *
 * Two-column layout: 3/4 full-bleed image + 1/4 dark text panel.
 * Slides are managed via ACF Options → Homepage → home_spotlight_slides.
 *
 * Each slide supports: spotlight_image, spotlight_eyebrow, spotlight_heading,
 * spotlight_description, spotlight_cta_label, spotlight_cta_url.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_slides = function_exists( 'get_field' ) ? get_field( 'home_spotlight_slides', 'option' ) : [];

if ( empty( $sf_slides ) || ! is_array( $sf_slides ) ) {
	return;
}

$sf_count = count( $sf_slides );
?>
<section class="sf-home-section sf-spotlight-section"
         aria-label="<?php esc_attr_e( 'Product Spotlight', 'samurai' ); ?>">

	<div class="sf-container">
	<div class="sf-spotlight js-spotlight"
	     id="sf-spotlight-slider"
	     data-count="<?php echo absint( $sf_count ); ?>">

		<div class="sf-spotlight__track" aria-live="polite">
			<?php foreach ( $sf_slides as $sf_i => $sf_slide ) :
				$sf_first       = ( $sf_i === 0 );
				$sf_img         = ! empty( $sf_slide['spotlight_image'] ) ? $sf_slide['spotlight_image'] : null;
				$sf_img_id      = $sf_img ? absint( $sf_img['ID'] ) : 0;
				$sf_img_alt     = $sf_img ? esc_attr( $sf_img['alt'] ) : '';
				$sf_eyebrow     = ! empty( $sf_slide['spotlight_eyebrow'] ) ? $sf_slide['spotlight_eyebrow'] : '';
				$sf_heading     = ! empty( $sf_slide['spotlight_heading'] ) ? $sf_slide['spotlight_heading'] : '';
				$sf_desc        = ! empty( $sf_slide['spotlight_description'] ) ? $sf_slide['spotlight_description'] : '';
				$sf_cta_label   = ! empty( $sf_slide['spotlight_cta_label'] ) ? $sf_slide['spotlight_cta_label'] : __( 'Shop Now', 'samurai' );
				$sf_cta_url     = ! empty( $sf_slide['spotlight_cta_url'] ) ? esc_url( $sf_slide['spotlight_cta_url'] ) : '';
				?>
			<div class="sf-spotlight__slide<?php echo $sf_first ? ' is-active' : ''; ?>"
			     aria-hidden="<?php echo $sf_first ? 'false' : 'true'; ?>"
			     data-slide="<?php echo absint( $sf_i ); ?>"
			     role="group"
			     aria-label="<?php printf( esc_attr__( 'Slide %1$d of %2$d', 'samurai' ), $sf_i + 1, $sf_count ); ?>">

				<div class="sf-spotlight__media">
					<?php if ( $sf_img_id ) : ?>
						<?php echo wp_get_attachment_image( $sf_img_id, 'samurai-hero', false, [
							'class'           => 'sf-spotlight__img',
							'loading'         => $sf_first ? 'eager' : 'lazy',
							'fetchpriority'   => $sf_first ? 'high' : 'auto',
							'alt'             => $sf_img_alt,
						] ); ?>
					<?php else : ?>
						<div class="sf-spotlight__img-placeholder" aria-hidden="true"></div>
					<?php endif; ?>
				</div>

				<div class="sf-spotlight__panel">

					<?php if ( $sf_eyebrow ) : ?>
					<p class="sf-spotlight__eyebrow"><?php echo esc_html( $sf_eyebrow ); ?></p>
					<?php endif; ?>

					<?php if ( $sf_heading ) : ?>
					<h2 class="sf-spotlight__heading"><?php echo esc_html( $sf_heading ); ?></h2>
					<?php endif; ?>

					<?php if ( $sf_desc ) : ?>
					<p class="sf-spotlight__desc"><?php echo esc_html( $sf_desc ); ?></p>
					<?php endif; ?>

					<?php if ( $sf_cta_url ) : ?>
					<a href="<?php echo $sf_cta_url; ?>" class="sf-btn sf-btn--primary sf-spotlight__cta">
						<?php echo esc_html( $sf_cta_label ); ?>
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
					</a>
					<?php endif; ?>

				</div>

			</div>
			<?php endforeach; ?>
		</div>

		<button type="button"
		        class="sf-spotlight__arrow sf-spotlight__arrow--prev js-spotlight-prev"
		        aria-label="<?php esc_attr_e( 'Previous slide', 'samurai' ); ?>"
		        <?php if ( $sf_count < 2 ) : ?>hidden<?php endif; ?>>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
		</button>
		<button type="button"
		        class="sf-spotlight__arrow sf-spotlight__arrow--next js-spotlight-next"
		        aria-label="<?php esc_attr_e( 'Next slide', 'samurai' ); ?>"
		        <?php if ( $sf_count < 2 ) : ?>hidden<?php endif; ?>>
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
		</button>

		<?php if ( $sf_count > 1 ) : ?>
		<nav class="sf-spotlight__dots"
		     aria-label="<?php esc_attr_e( 'Slide navigation', 'samurai' ); ?>">
			<?php foreach ( $sf_slides as $sf_i => $sf_slide ) : ?>
			<button type="button"
			        class="sf-spotlight__dot<?php echo $sf_i === 0 ? ' is-active' : ''; ?>"
			        aria-label="<?php printf( esc_attr__( 'Go to slide %d', 'samurai' ), $sf_i + 1 ); ?>"
			        aria-current="<?php echo $sf_i === 0 ? 'true' : 'false'; ?>"
			        data-slide="<?php echo absint( $sf_i ); ?>"></button>
			<?php endforeach; ?>
		</nav>
		<?php endif; ?>

	</div>
	</div><!-- /.sf-container -->

</section>
