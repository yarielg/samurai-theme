<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="sf-main" class="sf-main sf-container" role="main">

	<?php while ( have_posts() ) : the_post(); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'sf-single-post' ); ?>>

			<header class="sf-single-post__header">
				<h1 class="sf-single-post__title"><?php the_title(); ?></h1>
				<div class="sf-single-post__meta sf-text-muted">
					<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date() ); ?>
					</time>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="sf-single-post__thumbnail">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>

			<div class="sf-single-post__content entry-content">
				<?php the_content(); ?>
			</div>

			<footer class="sf-single-post__footer">
				<?php
				$tags = get_the_tags();
				if ( $tags ) {
					echo '<div class="sf-single-post__tags">';
					foreach ( $tags as $tag ) {
						echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="sf-badge sf-badge--outline">' . esc_html( $tag->name ) . '</a> ';
					}
					echo '</div>';
				}
				?>
			</footer>

		</article>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div class="sf-comments sf-container">
				<?php comments_template(); ?>
			</div>
		<?php endif; ?>

	<?php endwhile; ?>

</main>
<?php
get_footer();
