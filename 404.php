<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="sf-main" class="sf-main sf-container sf-404" role="main">

	<div class="sf-404__inner">
		<h1 class="sf-404__title">404</h1>
		<p class="sf-404__message">
			<?php esc_html_e( 'That page doesn\'t exist. Let\'s find something that lights up your night.', 'samurai' ); ?>
		</p>
		<div class="sf-404__actions">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sf-btn sf-btn--primary">
				<?php esc_html_e( 'Back to Home', 'samurai' ); ?>
			</a>
			<?php if ( function_exists( 'is_woocommerce' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="sf-btn sf-btn--outline">
					<?php esc_html_e( 'Browse Products', 'samurai' ); ?>
				</a>
			<?php endif; ?>
		</div>
		<div class="sf-404__search">
			<?php get_search_form(); ?>
		</div>
	</div>

</main>
<?php
get_footer();






