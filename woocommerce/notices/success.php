<?php
/**
 * Success notice — Samurai theme override.
 * Outputs a hidden seed div; toast.js converts it to a floating toast.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;
if ( ! $notices ) return;
foreach ( $notices as $notice ) :
?>
<div class="sf-notice-seed" data-type="success" aria-hidden="true"><?php echo wc_kses_notice( $notice['notice'] ); ?></div>
<?php endforeach; ?>
