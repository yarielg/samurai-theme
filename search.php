<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="sf-main" class="sf-main sf-container" role="main">

	<header class="sf-archive-header">
		<h1 class="sf-archive-header__title">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'Search results for: %s', 'samurai' ),
				'<span class="sf-search-term">' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>
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
				</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination( [ 'class' => 'sf-pagination' ] ); ?>

	<?php else : ?>

		<div class="sf-notice sf-notice--info">
			<p><?php esc_html_e( 'No results found. Try different search terms.', 'samurai' ); ?></p>
		</div>

		<?php get_search_form(); ?>

	<?php endif; ?>

</main>
<?php
get_footer();
