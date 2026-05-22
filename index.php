<?php
/**
 * Fallback template — WordPress requires this file.
 * Standard pages use page.php, single.php, archive.php, etc.
 */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="sf-main" class="sf-main sf-container" role="main">

	<?php if ( have_posts() ) : ?>

		<div class="sf-loop">
			<?php while ( have_posts() ) : the_post(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'sf-entry' ); ?>>
					<header class="sf-entry__header">
						<?php the_title( '<h2 class="sf-entry__title"><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
					</header>
					<div class="sf-entry__content">
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination( [ 'class' => 'sf-pagination' ] ); ?>

	<?php else : ?>

		<?php get_template_part( 'template-parts/content/content-none' ); ?>

	<?php endif; ?>

</main>
<?php
get_footer();
