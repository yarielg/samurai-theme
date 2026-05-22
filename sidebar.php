<?php
defined( 'ABSPATH' ) || exit;

if ( ! is_active_sidebar( 'shop-sidebar' ) ) {
	return;
}
?>
<aside id="sf-sidebar" class="sf-sidebar" role="complementary" aria-label="<?php esc_attr_e( 'Shop filters', 'samurai' ); ?>">
	<?php dynamic_sidebar( 'shop-sidebar' ); ?>
</aside>
