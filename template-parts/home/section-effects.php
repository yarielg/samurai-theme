<?php
/**
 * Homepage — Shop by Effect section.
 *
 * Queries `effect` taxonomy terms and displays them as a bento grid of
 * discovery cards. Uses ACF `image_2` as the primary display image with
 * `image` as fallback.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'effect' ) ) {
	return;
}

$sf_heading  = samurai_option_text( 'home_effects_heading', __( 'Shop by Effect', 'samurai' ), false );
$sf_effects  = samurai_get_nav_terms( 'effect', 8, 'count' );

if ( empty( $sf_effects ) ) {
	return;
}
?>
<section class="sf-home-section sf-effects-section" aria-label="<?php echo esc_attr( $sf_heading ); ?>">
	<div class="sf-container">

		<div class="sf-section-header">
			<h2 class="sf-section-heading"><?php echo esc_html( $sf_heading ); ?></h2>
			<a href="<?php echo esc_url( samurai_tax_archive_url( 'effect' ) ); ?>" class="sf-section-link">
				<?php esc_html_e( 'All Effects', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>

		<div class="sf-effect-grid">
			<?php foreach ( $sf_effects as $sf_index => $sf_effect ) :
				$sf_effect_url  = get_term_link( $sf_effect );
				$sf_effect_name = $sf_effect->name;
				$sf_effect_desc = $sf_effect->description;
				$sf_desc_words  = ( $sf_index === 0 ) ? 14 : 8;

				// ACF image fields — image_2 is primary, image is fallback.
				$sf_acf_img  = function_exists( 'get_field' ) ? get_field( 'image', $sf_effect ) : false;
				$sf_acf_img2 = function_exists( 'get_field' ) ? get_field( 'image_2', $sf_effect ) : false;
				$sf_img_id   = 0;
				$sf_img2_id  = 0;
				if ( is_array( $sf_acf_img ) ) {
					$sf_img_id = absint( $sf_acf_img['ID'] ?? 0 );
				} elseif ( is_numeric( $sf_acf_img ) ) {
					$sf_img_id = absint( $sf_acf_img );
				}
				if ( is_array( $sf_acf_img2 ) ) {
					$sf_img2_id = absint( $sf_acf_img2['ID'] ?? 0 );
				} elseif ( is_numeric( $sf_acf_img2 ) ) {
					$sf_img2_id = absint( $sf_acf_img2 );
				}
				$sf_display_img_id = $sf_img2_id ?: $sf_img_id;
				?>
			<a href="<?php echo esc_url( $sf_effect_url ); ?>"
			   class="sf-effect-card"
			   aria-label="<?php echo esc_attr( sprintf( /* translators: %s: effect name */ __( 'Shop %s fireworks', 'samurai' ), $sf_effect_name ) ); ?>">

				<div class="sf-effect-card__media">
					<?php if ( $sf_display_img_id ) : ?>
						<?php echo wp_get_attachment_image( $sf_display_img_id, 'samurai-taxonomy', false, [
							'class'   => 'sf-effect-card__img',
							'loading' => 'lazy',
							'alt'     => esc_attr( $sf_effect_name ),
						] ); ?>
					<?php else : ?>
						<div class="sf-effect-card__gradient" aria-hidden="true"></div>
					<?php endif; ?>
					<div class="sf-effect-card__overlay" aria-hidden="true"></div>
				</div>

				<div class="sf-effect-card__info">
					<span class="sf-effect-card__name"><?php echo esc_html( $sf_effect_name ); ?></span>
					<?php if ( $sf_effect_desc ) : ?>
					<span class="sf-effect-card__desc"><?php echo esc_html( wp_trim_words( $sf_effect_desc, $sf_desc_words, '' ) ); ?></span>
					<?php endif; ?>
				</div>

			</a>
			<?php endforeach; ?>
		</div>

	</div>
</section>
