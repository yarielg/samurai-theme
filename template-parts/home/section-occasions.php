<?php
/**
 * Homepage — Shop by Occasion section.
 *
 * Queries `special-occasion` taxonomy terms and presents them as
 * seasonal marketing cards (4th of July, New Year's, Weddings, etc.).
 * Uses ACF `image` field when available.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

if ( ! taxonomy_exists( 'special-occasion' ) ) {
	return;
}

$sf_heading   = samurai_option_text( 'home_occasions_heading', __( 'Shop by Occasion', 'samurai' ), false );
$sf_occasions = samurai_get_nav_terms( 'special-occasion', 6, 'count' );

if ( empty( $sf_occasions ) ) {
	return;
}
?>
<section class="sf-home-section sf-occasions-section" aria-label="<?php echo esc_attr( $sf_heading ); ?>">
	<div class="sf-container">

		<div class="sf-section-header">
			<h2 class="sf-section-heading"><?php echo esc_html( $sf_heading ); ?></h2>
			<a href="<?php echo esc_url( samurai_tax_archive_url( 'special-occasion' ) ); ?>" class="sf-section-link">
				<?php esc_html_e( 'All Occasions', 'samurai' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</a>
		</div>

		<div class="sf-occasion-grid">
			<?php foreach ( $sf_occasions as $sf_occ ) :
				$sf_occ_url  = get_term_link( $sf_occ );
				$sf_occ_name = $sf_occ->name;
				$sf_occ_desc = $sf_occ->description;

				// ACF image field on the term.
				$sf_acf_img = function_exists( 'get_field' ) ? get_field( 'image', $sf_occ ) : false;
				$sf_img_id  = 0;
				if ( is_array( $sf_acf_img ) ) {
					$sf_img_id = absint( $sf_acf_img['ID'] ?? 0 );
				} elseif ( is_numeric( $sf_acf_img ) ) {
					$sf_img_id = absint( $sf_acf_img );
				}
				?>
			<a href="<?php echo esc_url( $sf_occ_url ); ?>"
			   class="sf-occasion-card"
			   aria-label="<?php echo esc_attr( sprintf( /* translators: %s: occasion name */ __( 'Shop %s fireworks', 'samurai' ), $sf_occ_name ) ); ?>">

				<div class="sf-occasion-card__media">
					<?php if ( $sf_img_id ) : ?>
						<?php echo wp_get_attachment_image( $sf_img_id, 'samurai-taxonomy', false, [
							'class'   => 'sf-occasion-card__img',
							'loading' => 'lazy',
							'alt'     => esc_attr( $sf_occ_name ),
						] ); ?>
					<?php else : ?>
						<div class="sf-occasion-card__gradient" aria-hidden="true"></div>
					<?php endif; ?>
					<div class="sf-occasion-card__overlay" aria-hidden="true"></div>
				</div>

				<div class="sf-occasion-card__info">
					<span class="sf-occasion-card__name"><?php echo esc_html( $sf_occ_name ); ?></span>
					<?php if ( $sf_occ_desc ) : ?>
					<span class="sf-occasion-card__desc"><?php echo esc_html( wp_trim_words( $sf_occ_desc, 8, '' ) ); ?></span>
					<?php endif; ?>
				</div>

			</a>
			<?php endforeach; ?>
		</div>

	</div>
</section>
