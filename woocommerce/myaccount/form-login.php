<?php
/**
 * Login / Register form — Samurai theme override.
 *
 * Keeps every WC action hook so the Twilio OTP plugin's
 * woocommerce_before_customer_login_form and woocommerce_register_form_start
 * hooks continue to fire unchanged.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_registration = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
?>

<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="sf-account-login<?php echo $sf_registration ? ' sf-account-login--two-col' : ''; ?>" id="customer_login">

	<!-- ── Login panel ───────────────────────────────────────────────────── -->
	<div class="sf-account-login__panel sf-account-card">

		<h2 class="sf-account-card__title"><?php esc_html_e( 'Sign In', 'samurai' ); ?></h2>

		<form class="woocommerce-form woocommerce-form-login login" method="post" novalidate>

			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<div class="sf-account-field">
				<label for="username">
					<?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
				</label>
				<input type="text"
				       class="woocommerce-Input woocommerce-Input--text input-text sf-input"
				       name="username"
				       id="username"
				       autocomplete="username"
				       value="<?php echo ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
				       required
				       aria-required="true">
			</div>

			<div class="sf-account-field">
				<label for="password">
					<?php esc_html_e( 'Password', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
				</label>
				<input class="woocommerce-Input woocommerce-Input--text input-text sf-input"
				       type="password"
				       name="password"
				       id="password"
				       autocomplete="current-password"
				       required
				       aria-required="true">
			</div>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<div class="sf-account-login__row">
				<label class="sf-checkbox-label">
					<input class="woocommerce-form__input-checkbox"
					       name="rememberme"
					       type="checkbox"
					       id="rememberme"
					       value="forever">
					<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
				</label>
				<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="sf-account-lost-pw">
					<?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?>
				</a>
			</div>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

			<button type="submit"
			        class="sf-btn sf-btn--primary sf-btn--full woocommerce-button woocommerce-form-login__submit"
			        name="login"
			        value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
				<?php esc_html_e( 'Sign In', 'samurai' ); ?>
			</button>

			<?php do_action( 'woocommerce_login_form_end' ); ?>

		</form>

	</div><!-- /.sf-account-login__panel -->

	<!-- ── Register panel (only when registration is enabled) ─────────── -->
	<?php if ( $sf_registration ) : ?>
	<div class="sf-account-login__panel sf-account-card sf-account-card--alt">

		<h2 class="sf-account-card__title"><?php esc_html_e( 'Create Account', 'samurai' ); ?></h2>
		<p class="sf-account-card__sub"><?php esc_html_e( 'Track orders, save addresses, and check out faster.', 'samurai' ); ?></p>

		<form method="post"
		      class="woocommerce-form woocommerce-form-register register"
		      <?php do_action( 'woocommerce_register_form_tag' ); ?>>

			<?php do_action( 'woocommerce_register_form_start' ); ?>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
			<div class="sf-account-field">
				<label for="reg_username">
					<?php esc_html_e( 'Username', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
				</label>
				<input type="text"
				       class="woocommerce-Input woocommerce-Input--text input-text sf-input"
				       name="username"
				       id="reg_username"
				       autocomplete="username"
				       value="<?php echo ! empty( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
				       required
				       aria-required="true">
			</div>
			<?php endif; ?>

			<div class="sf-account-field">
				<label for="reg_email">
					<?php esc_html_e( 'Email address', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
				</label>
				<input type="email"
				       class="woocommerce-Input woocommerce-Input--text input-text sf-input"
				       name="email"
				       id="reg_email"
				       autocomplete="email"
				       value="<?php echo ! empty( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
				       required
				       aria-required="true">
			</div>

			<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
			<div class="sf-account-field">
				<label for="reg_password">
					<?php esc_html_e( 'Password', 'woocommerce' ); ?>
					<span class="required" aria-hidden="true">*</span>
					<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
				</label>
				<input type="password"
				       class="woocommerce-Input woocommerce-Input--text input-text sf-input"
				       name="password"
				       id="reg_password"
				       autocomplete="new-password"
				       required
				       aria-required="true">
			</div>
			<?php else : ?>
			<p class="sf-account-card__note">
				<?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?>
			</p>
			<?php endif; ?>

			<?php do_action( 'woocommerce_register_form' ); ?>

			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

			<button type="submit"
			        class="sf-btn sf-btn--outline sf-btn--full woocommerce-Button woocommerce-form-register__submit"
			        name="register"
			        value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
				<?php esc_html_e( 'Create Account', 'samurai' ); ?>
			</button>

			<?php do_action( 'woocommerce_register_form_end' ); ?>

		</form>

	</div><!-- /.sf-account-login__panel -->
	<?php endif; ?>

</div><!-- /#customer_login -->

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
