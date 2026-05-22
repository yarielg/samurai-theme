<?php
/**
 * Hero Slider
 *
 * Slides managed via ACF Options → Header & Promo → hero_slides repeater.
 *
 * Each slide supports:
 *   slide_image         — desktop/landscape background image
 *   slide_image_mobile  — portrait image for phones (optional, falls back to desktop)
 *   slide_headline      — main heading
 *   slide_subline       — small text above heading
 *   slide_cta_label     — button label
 *   slide_cta_url       — button URL
 *   slide_cta_target    — open in new tab (true/false)
 *
 * Responsive images use <picture> + <source media> — the browser picks the
 * correct asset; no JS or CSS swap needed.
 *
 * Dots use a progress-bar style that fills over the autoplay duration.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$slides = [];

if ( function_exists( 'get_field' ) ) {
	$raw = get_field( 'hero_slides', 'option' );
	if ( $raw && is_array( $raw ) ) {
		$slides = $raw;
	}
}

// Fallback: one static slide so the page always renders something.
if ( empty( $slides ) ) {
	$slides = [
		[
			'slide_image'        => null,
			'slide_image_mobile' => null,
			'slide_headline'     => get_bloginfo( 'name' ),
			'slide_subline'      => samurai_option_text( 'brand_tagline', __( 'Ignite Every Moment. Miami Style.', 'samurai' ), false ),
			'slide_cta_label'    => __( 'Shop Now', 'samurai' ),
			'slide_cta_url'      => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ),
			'slide_cta_target'   => false,
		],
	];
}

$slide_count = count( $slides );
$autoplay_ms = 5000;

$icon_prev = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>';
$icon_next = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>';
?>

<section
	class="sf-hero<?php echo $slide_count > 1 ? ' sf-hero--multiple' : ''; ?>"
	aria-label="<?php esc_attr_e( 'Featured promotions', 'samurai' ); ?>"
>
	<div class="sf-hero__slider js-hero-slider"
	     data-autoplay="<?php echo absint( $autoplay_ms ); ?>"
	     style="--sf-slide-duration:<?php echo absint( $autoplay_ms ); ?>ms">

		<div class="sf-hero__track" aria-live="polite">
			<?php foreach ( $slides as $index => $slide ) :
				$is_first = ( $index === 0 );

				// Desktop image
				$desktop     = ! empty( $slide['slide_image'] ) ? $slide['slide_image'] : null;
				$desktop_id  = $desktop ? absint( $desktop['ID'] ) : 0;
				$desktop_url = $desktop ? esc_url( $desktop['url'] ) : '';
				$desktop_alt = $desktop ? esc_attr( $desktop['alt'] ) : '';

				// Mobile image — falls back to desktop if not set
				$mobile    = ! empty( $slide['slide_image_mobile'] ) ? $slide['slide_image_mobile'] : null;
				$mobile_id = $mobile ? absint( $mobile['ID'] ) : 0;

				// Content
				$headline   = ! empty( $slide['slide_headline'] ) ? $slide['slide_headline'] : '';
				$subline    = ! empty( $slide['slide_subline'] ) ? $slide['slide_subline'] : '';
				$cta_label  = ! empty( $slide['slide_cta_label'] ) ? $slide['slide_cta_label'] : '';
				$cta_url    = ! empty( $slide['slide_cta_url'] ) ? esc_url( $slide['slide_cta_url'] ) : '';
				$cta_target = ! empty( $slide['slide_cta_target'] ) ? '_blank' : '_self';
				$cta_rel    = $cta_target === '_blank' ? ' rel="noopener noreferrer"' : '';
				?>
			<div
				class="sf-hero__slide<?php echo $is_first ? ' is-active' : ''; ?>"
				aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
				data-index="<?php echo absint( $index ); ?>"
			>
				<?php if ( $desktop_id || $mobile_id ) : ?>
				<picture class="sf-hero__picture">
					<?php
					// Mobile source — shown at ≤767 px.
					// If a dedicated mobile image exists use it; otherwise fall back to desktop.
					$src_mobile_id = $mobile_id ?: $desktop_id;
					if ( $src_mobile_id ) :
						$mobile_srcset = wp_get_attachment_image_srcset( $src_mobile_id, 'samurai-hero' );
						$mobile_src    = wp_get_attachment_url( $src_mobile_id );
						?>
					<source
						media="(max-width: 767px)"
						srcset="<?php echo esc_attr( $mobile_srcset ?: $mobile_src ); ?>"
						sizes="100vw"
					>
					<?php endif; ?>

					<?php if ( $desktop_id ) :
						$desktop_srcset = wp_get_attachment_image_srcset( $desktop_id, 'samurai-hero' );
						?>
					<img
						src="<?php echo $desktop_url; ?>"
						<?php if ( $desktop_srcset ) : ?>
						srcset="<?php echo esc_attr( $desktop_srcset ); ?>"
						sizes="100vw"
						<?php endif; ?>
						class="sf-hero__picture-img"
						alt="<?php echo $desktop_alt; ?>"
						loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
						<?php if ( $is_first ) : ?>fetchpriority="high"<?php endif; ?>
					>
					<?php endif; ?>
				</picture>
				<?php endif; ?>

				<div class="sf-hero__overlay" aria-hidden="true"></div>

				<div class="sf-container sf-hero__content">
					<?php if ( $subline ) : ?>
					<p class="sf-hero__subline"><?php echo esc_html( $subline ); ?></p>
					<?php endif; ?>

					<?php if ( $headline ) : ?>
						<?php if ( $is_first ) : ?>
						<h1 class="sf-hero__headline"><?php echo esc_html( $headline ); ?></h1>
						<?php else : ?>
						<p class="sf-hero__headline" role="heading" aria-level="2"><?php echo esc_html( $headline ); ?></p>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( $cta_label && $cta_url ) : ?>
					<div class="sf-hero__ctas">
						<a href="<?php echo $cta_url; ?>"
						   class="sf-btn sf-btn--primary sf-btn--lg sf-hero__cta"
						   target="<?php echo esc_attr( $cta_target ); ?>"
						   <?php echo $cta_rel; // phpcs:ignore ?>>
							<?php echo esc_html( $cta_label ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div>

			</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $slide_count > 1 ) : ?>

		<!-- Prev / Next arrows -->
		<button type="button" class="sf-hero__arrow sf-hero__arrow--prev js-hero-prev"
		        aria-label="<?php esc_attr_e( 'Previous slide', 'samurai' ); ?>">
			<?php echo $icon_prev; // phpcs:ignore ?>
		</button>
		<button type="button" class="sf-hero__arrow sf-hero__arrow--next js-hero-next"
		        aria-label="<?php esc_attr_e( 'Next slide', 'samurai' ); ?>">
			<?php echo $icon_next; // phpcs:ignore ?>
		</button>

		<!-- Dot / progress-bar navigation -->
		<nav class="sf-hero__dots" aria-label="<?php esc_attr_e( 'Slide navigation', 'samurai' ); ?>">
			<?php foreach ( $slides as $index => $slide ) : ?>
			<button
				type="button"
				class="sf-hero__dot<?php echo $index === 0 ? ' is-active is-animating' : ''; ?>"
				aria-label="<?php printf( esc_attr__( 'Go to slide %d', 'samurai' ), $index + 1 ); ?>"
				aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"
				data-slide="<?php echo absint( $index ); ?>"
			></button>
			<?php endforeach; ?>
		</nav>

		<?php endif; ?>

	</div>
</section>
