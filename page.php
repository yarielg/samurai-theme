<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="sf-main" class="sf-main <?php echo ( is_account_page() ) ? '' : 'sf-container '; ?>sf-page-content" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

		<?php if ( is_account_page() ) : ?>

			<?php the_content(); ?>

		<?php else : ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'sf-page-article' ); ?>>

				<?php if ( ! is_front_page() && ! is_cart() && ! is_checkout() ) : ?>
					<header class="sf-page-article__header">
						<h1 class="sf-page-article__title"><?php the_title(); ?></h1>
					</header>
				<?php endif; ?>

				<div class="sf-page-article__content entry-content">
					<?php the_content(); ?>
				</div>

			</article>

		<?php endif; ?>

	<?php endwhile; ?>

</main>
<?php
get_footer();
