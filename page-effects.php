<?php
/**
 * Effects listing page — loaded for /effect/ via template_redirect.
 *
 * Displays all "effect" taxonomy terms as image cards with product counts,
 * descriptions, and links to each term's product archive.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

// Pull every effect term, most-populated first.
$sf_effects = get_terms( [
	'taxonomy'   => 'effect',
	'hide_empty' => false,
	'orderby'    => 'count',
	'order'      => 'DESC',
	'number'     => 0,
] );

if ( is_wp_error( $sf_effects ) ) {
	$sf_effects = [];
}

$sf_total = count( $sf_effects );

// Title / SEO
add_filter( 'pre_get_document_title', function() {
	return __( 'Shop by Effect', 'samurai' ) . ' — ' . get_bloginfo( 'name' );
} );

get_header();
?>

<main id="sf-main" class="sf-main sf-main--effects" role="main">

	<!-- ── Hero ──────────────────────────────────────────────────────────── -->
	<section class="sf-effects-hero" aria-label="<?php esc_attr_e( 'Shop by Effect', 'samurai' ); ?>">
		<div class="sf-container">

			<nav class="sf-effects-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'samurai' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'samurai' ); ?></a>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
				<span><?php esc_html_e( 'Shop by Effect', 'samurai' ); ?></span>
			</nav>

			<h1 class="sf-effects-hero__heading"><?php esc_html_e( 'Shop by Effect', 'samurai' ); ?></h1>
			<p class="sf-effects-hero__subline"><?php esc_html_e( 'Find fireworks by the visual experience you want to create — from gold cascades to crackling comets.', 'samurai' ); ?></p>

			<?php if ( $sf_total > 0 ) : ?>
			<p class="sf-effects-hero__count">
				<?php echo esc_html( sprintf(
					/* translators: %d: number of effects */
					_n( '%d visual effect category', '%d visual effect categories', $sf_total, 'samurai' ),
					$sf_total
				) ); ?>
			</p>
			<?php endif; ?>

		</div>
	</section>

	<!-- ── Grid ──────────────────────────────────────────────────────────── -->
	<div class="sf-effects-index sf-container">

		<?php if ( empty( $sf_effects ) ) : ?>
			<p class="sf-effects-index__empty"><?php esc_html_e( 'No effects found yet. Check back soon!', 'samurai' ); ?></p>
		<?php else : ?>

		<ul class="sf-effects-index__grid" role="list">
			<?php foreach ( $sf_effects as $sf_i => $sf_term ) :

				$sf_url   = get_term_link( $sf_term );
				$sf_url   = is_wp_error( $sf_url ) ? home_url( '/' ) : $sf_url;
				$sf_name  = $sf_term->name;
				$sf_desc  = $sf_term->description;
				$sf_count = absint( $sf_term->count );

				// ACF images: image_2 primary, image fallback.
				$sf_acf_img  = function_exists( 'get_field' ) ? get_field( 'image',   $sf_term ) : false;
				$sf_acf_img2 = function_exists( 'get_field' ) ? get_field( 'image_2', $sf_term ) : false;

				$sf_img_id = 0;
				if ( is_array( $sf_acf_img ) )        { $sf_img_id = absint( $sf_acf_img['ID'] ?? 0 ); }
				elseif ( is_numeric( $sf_acf_img ) )   { $sf_img_id = absint( $sf_acf_img ); }

				$sf_img2_id = 0;
				if ( is_array( $sf_acf_img2 ) )       { $sf_img2_id = absint( $sf_acf_img2['ID'] ?? 0 ); }
				elseif ( is_numeric( $sf_acf_img2 ) )  { $sf_img2_id = absint( $sf_acf_img2 ); }

				$sf_display_img = $sf_img2_id ?: $sf_img_id;
				?>
			<li class="sf-effects-card-wrap">
				<a href="<?php echo esc_url( $sf_url ); ?>"
				   class="sf-effects-card sf-effects-card--<?php echo ( $sf_i % 8 ) + 1; ?>"
				   aria-label="<?php echo esc_attr( sprintf( /* translators: %s: effect name */ __( 'Shop %s fireworks', 'samurai' ), $sf_name ) ); ?>">

					<!-- Image -->
					<div class="sf-effects-card__media" aria-hidden="true">
						<?php if ( $sf_display_img ) : ?>
							<?php echo wp_get_attachment_image( $sf_display_img, 'samurai-taxonomy', false, [
								'class'   => 'sf-effects-card__img',
								'loading' => $sf_i < 8 ? 'eager' : 'lazy',
								'alt'     => '',
							] ); ?>
						<?php else : ?>
							<div class="sf-effects-card__gradient"></div>
						<?php endif; ?>
						<div class="sf-effects-card__overlay"></div>
					</div>

					<!-- Product count badge -->
					<?php if ( $sf_count > 0 ) : ?>
					<span class="sf-effects-card__badge" aria-hidden="true">
						<?php echo esc_html( sprintf(
							/* translators: %d: product count */
							_n( '%d product', '%d products', $sf_count, 'samurai' ),
							$sf_count
						) ); ?>
					</span>
					<?php endif; ?>

					<!-- Text body -->
					<div class="sf-effects-card__body">
						<h2 class="sf-effects-card__name"><?php echo esc_html( $sf_name ); ?></h2>

						<?php if ( $sf_desc ) : ?>
						<p class="sf-effects-card__desc">
							<?php echo esc_html( wp_trim_words( $sf_desc, 14, '' ) ); ?>
						</p>
						<?php endif; ?>

						<span class="sf-effects-card__cta" aria-hidden="true">
							<?php echo esc_html( sprintf( /* translators: %s: effect name */ __( 'Shop %s', 'samurai' ), $sf_name ) ); ?>
							<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
						</span>
					</div>

				</a>
			</li>
			<?php endforeach; ?>
		</ul>

		<?php endif; ?>

	</div><!-- /.sf-effects-index -->

</main>

<?php get_footer(); ?>
