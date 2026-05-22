<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="sf-main" class="sf-main sf-container" role="main">

	<header class="sf-archive-header">
		<?php the_archive_title( '<h1 class="sf-archive-header__title">', '</h1>' ); ?>
		<?php the_archive_description( '<div class="sf-archive-header__description">', '</div>' ); ?>
	</header>

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
					<a href="<?php the_permalink(); ?>" class="sf-btn sf-btn--outline sf-entry__more">
						<?php esc_html_e( 'Read more', 'samurai' ); ?>
					</a>
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
