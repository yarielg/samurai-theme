<?php
/**
 * Homepage — Shop by Effect section.
 *
 * Queries `effect` taxonomy terms and displays them as discovery cards
 * so customers can shop by the kind of firework experience they want.
 * Uses ACF `image` term field when available.
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
			<?php foreach ( $sf_effects as $sf_effect ) :
				$sf_effect_url  = get_term_link( $sf_effect );
				$sf_effect_name = $sf_effect->name;
				$sf_effect_desc = $sf_effect->description;

				// ACF image field on the term.
				$sf_acf_img = function_exists( 'get_field' ) ? get_field( 'image', $sf_effect ) : false;
				$sf_img_id  = 0;
				if ( is_array( $sf_acf_img ) ) {
					$sf_img_id = absint( $sf_acf_img['ID'] ?? 0 );
				} elseif ( is_numeric( $sf_acf_img ) ) {
					$sf_img_id = absint( $sf_acf_img );
				}
				?>
			<a href="<?php echo esc_url( $sf_effect_url ); ?>"
			   class="sf-effect-card"
			   aria-label="<?php echo esc_attr( sprintf( /* translators: %s: effect name */ __( 'Shop %s fireworks', 'samurai' ), $sf_effect_name ) ); ?>">

				<div class="sf-effect-card__media">
					<?php if ( $sf_img_id ) : ?>
						<?php echo wp_get_attachment_image( $sf_img_id, 'samurai-taxonomy', false, [
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
					<span class="sf-effect-card__desc"><?php echo esc_html( wp_trim_words( $sf_effect_desc, 8, '' ) ); ?></span>
					<?php endif; ?>
				</div>

			</a>
			<?php endforeach; ?>
		</div>

	</div>
</section>
