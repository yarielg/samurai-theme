<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="sf-content-none">
	<h2 class="sf-content-none__title">
		<?php esc_html_e( 'Nothing found', 'samurai' ); ?>
	</h2>
	<p class="sf-content-none__desc">
		<?php esc_html_e( 'Try browsing our products or use the search below.', 'samurai' ); ?>
	</p>
	<?php get_search_form(); ?>
</div>
