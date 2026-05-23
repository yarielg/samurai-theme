<?php
/**
 * Homepage template.
 *
 * Section order:
 *   1. Hero slider         — full-viewport, ACF slides
 *   2. Shop by Brand       — infinite-scroll brand marquee
 *   3. Shop by Category    — product_cat terms, 4-col grid
 *   4. Category Carousel   — horizontal product strip (pyro kit / configurable)
 *   5. Fire Deals          — fire-deal taxonomy products, dark/orange treatment
 *   6. Shop by Effect      — effect taxonomy discovery cards (bento)
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

	<?php /* 6. Shop by Effect ──────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-effects' ); ?>

	<?php /* 7. Spotlight Slider ──────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-spotlight' ); ?>

	<?php /* 8. Newsletter ───────────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-newsletter' ); ?>

	<?php /* 9. Local Pickup ─────────────────────────────────────────── */ ?>
	<?php get_template_part( 'template-parts/home/section-pickup' ); ?>

</main>

<?php get_footer(); ?>
