<?php
/**
 * Checkout — Login modal.
 *
 * Replaces WooCommerce's default inline "click here to login" toggle with a
 * centred modal dialog. Opens when the user clicks the trigger button; closes
 * on backdrop click, × button, "Continue as guest", or Escape key.
 *
 * On a failed login attempt the page reloads via POST. We capture any error
 * notices early, display them inside the modal, and auto-open it so the user
 * doesn't have to hunt for the form again.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$registration_at_checkout   = WC_Checkout::instance()->is_registration_enabled();
$login_reminder_at_checkout = 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder' );

if ( is_user_logged_in() ) {
	return;
}

// phpcs:ignore WordPress.Security.NonceVerification.Missing
$sf_show_modal = isset( $_POST['login'] );

// Capture login error notices so we can show them inside the modal and prevent
// them from appearing behind the backdrop (woocommerce_output_all_notices has
// not fired yet at this point in the hook chain).
$sf_error_html = '';
if ( $sf_show_modal ) {
	$sf_raw = wc_get_notices( 'error' );
	if ( ! empty( $sf_raw ) ) {
		$sf_error_html = '<ul class="sf-co-login-modal__errors woocommerce-error" role="alert">';
		foreach ( $sf_raw as $sf_n ) {
			$sf_msg         = is_array( $sf_n ) ? ( $sf_n['notice'] ?? '' ) : $sf_n;
			$sf_error_html .= '<li>' . wp_kses_post( $sf_msg ) . '</li>';
		}
		$sf_error_html .= '</ul>';
		wc_clear_notices(); // Prevent double-display outside the modal
	}
}

if ( $login_reminder_at_checkout ) : ?>
<div class="sf-co-login-strip">
	<span class="sf-co-login-strip__text">
		<?php echo esc_html( apply_filters( 'woocommerce_checkout_login_message', __( 'Returning customer?', 'woocommerce' ) ) ); ?>
	</span>
	<button type="button" class="sf-co-login-strip__link js-co-login-open">
		<?php esc_html_e( 'Sign in to your account', 'samurai' ); ?>
	</button>
</div>
<?php endif;

if ( $registration_at_checkout || $login_reminder_at_checkout ) :

	ob_start();
	woocommerce_login_form( [
		'message'  => '',
		'redirect' => wc_get_checkout_url(),
		'hidden'   => false,
	] );
	$sf_form_html = ob_get_clean();
?>
<div class="sf-co-login-modal<?php echo $sf_show_modal ? ' is-open' : ''; ?>"
     id="sf-co-login-modal"
     role="dialog"
     aria-modal="true"
     aria-labelledby="sf-co-login-modal-title"
     <?php echo $sf_show_modal ? '' : 'aria-hidden="true"'; ?>>

	<div class="sf-co-login-modal__backdrop js-co-login-close"></div>

	<div class="sf-co-login-modal__dialog">

		<div class="sf-co-login-modal__head">
			<div class="sf-co-login-modal__head-left">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<h2 class="sf-co-login-modal__title" id="sf-co-login-modal-title">
					<?php esc_html_e( 'Sign In', 'samurai' ); ?>
				</h2>
			</div>
			<button type="button"
			        class="sf-co-login-modal__close js-co-login-close"
			        aria-label="<?php esc_attr_e( 'Close sign-in dialog', 'samurai' ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
			</button>
		</div>

		<div class="sf-co-login-modal__body">

			<?php if ( $sf_error_html ) : ?>
			<?php echo $sf_error_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<?php echo $sf_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

			<div class="sf-co-login-modal__footer">
				<?php esc_html_e( 'No account yet?', 'samurai' ); ?>
				<button type="button" class="sf-co-login-modal__guest-btn js-co-login-close">
					<?php esc_html_e( 'Continue as guest', 'samurai' ); ?>
				</button>
			</div>

		</div><!-- /.sf-co-login-modal__body -->

	</div><!-- /.sf-co-login-modal__dialog -->

</div><!-- /.sf-co-login-modal -->
<?php endif; ?>
