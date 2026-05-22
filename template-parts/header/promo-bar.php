<?php
defined( 'ABSPATH' ) || exit;

$message = function_exists( 'get_field' ) ? get_field( 'header_promo_message', 'option' ) : '';
$link    = function_exists( 'get_field' ) ? get_field( 'header_promo_link', 'option' ) : '';

if ( ! $message ) {
	return;
}
?>
<div class="sf-promo-bar" role="banner" aria-label="<?php esc_attr_e( 'Promotional message', 'samurai' ); ?>">
	<div class="sf-promo-bar__inner sf-container">
		<?php if ( $link ) : ?>
			<a href="<?php echo esc_url( $link ); ?>" class="sf-promo-bar__link">
				<?php echo wp_kses_post( $message ); ?>
			</a>
		<?php else : ?>
			<span class="sf-promo-bar__text"><?php echo wp_kses_post( $message ); ?></span>
		<?php endif; ?>
	</div>
</div>
