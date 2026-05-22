<?php
/**
 * Homepage template.
 *
 * Section order:
 *   1. Hero slider         — full-viewport, ACF slides
 *   2. Shop by Brand       — infinite-scroll brand marquee
 *   3. Shop by Category    — product_cat top-level terms with WC thumbnail images
 *   4. Category Carousel   — horizontal product strip for a configurable product_cat
 *   5. Fire Deals          — fire-deal taxonomy products, dark/orange treatment
 *   6. Trending Now        — featured/trending products carousel with fallback
 *   7. Local Pickup        — Miami store info CTA
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

samurai_mark_has_hero();

get_header();
?>

<main id="sf-main" class="sf-main sf-main--home" role="main">

	<?php /* 1. Hero Slider ───────────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/hero-slider' ); ?>

	<?php /* 2. Shop by Brand ───────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-brands' ); ?>

	<?php /* 3. Shop by Category ──────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-categories' ); ?>

	<?php /* 4. Category Carousel ────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-carousel' ); ?>

	<?php /* 5. Fire Deals ──────────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-fire-deals' ); ?>

	<?php /* 6. Trending Now ─────────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-featured' ); ?>

	<?php /* 7. Local Pickup ─────────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-pickup' ); ?>

</main>

<?php get_footer(); ?>
