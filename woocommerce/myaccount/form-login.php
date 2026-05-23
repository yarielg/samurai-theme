<?php
/**
 * Login / Register — Samurai theme.
 *
 * Layout: hero panel (left) + single form card (right, toggles between login/register).
 * Supports passwordless phone-code login, standard WC login, and custom registration.
 *
 * @package samurai
 */
defined( 'ABSPATH' ) || exit;

$sf_registration  = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$sf_sms_enabled   = function_exists( 'samurai_sms_auth_enabled' ) && samurai_sms_auth_enabled();
$sf_verify_step   = function_exists( 'samurai_sms_is_verify_step' ) && samurai_sms_is_verify_step();
$sf_recaptcha_key = function_exists( 'samurai_sms_recaptcha_key' ) ? samurai_sms_recaptcha_key() : '';

// Pre-open the register card if user submitted the register form (e.g. validation errors)
$sf_show_register = $sf_registration && ! empty( $_POST['register'] );

do_action( 'woocommerce_before_customer_login_form' );
?>

<div class="sf-login<?php echo $sf_verify_step ? ' sf-login--verify' : ''; ?>" id="customer_login">

	<!-- ── Hero / benefit panel ─────────────────────────────────────── -->
	<div class="sf-login__hero" aria-hidden="true">
		<div class="sf-login__hero-inner">

			<h2 class="sf-login__hero-title">Your Account,<br>Your Fireworks</h2>
			<p class="sf-login__hero-sub">Miami's #1 destination for professional-grade fireworks.</p>

			<ul class="sf-login__benefits">

				<li class="sf-login__benefit">
					<span class="sf-login__benefit-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
					</span>
					<div>
						<strong>Track Every Order</strong>
						<span>Real-time status from purchase to your door.</span>
					</div>
				</li>

				<li class="sf-login__benefit">
					<span class="sf-login__benefit-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
					</span>
					<div>
						<strong>Faster Checkout</strong>
						<span>Saved addresses and order history at your fingertips.</span>
					</div>
				</li>

				<li class="sf-login__benefit">
					<span class="sf-login__benefit-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18.01"/></svg>
					</span>
					<div>
						<strong>Sign In by Phone</strong>
						<span>No password needed — just your number and a code.</span>
					</div>
				</li>

				<li class="sf-login__benefit">
					<span class="sf-login__benefit-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
					</span>
					<div>
						<strong>Exclusive Member Deals</strong>
						<span>Early access to sales and member-only promotions.</span>
					</div>
				</li>

			</ul>

			<div class="sf-login__hero-badge">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
				<span>Secure &amp; encrypted — your data stays private.</span>
			</div>

		</div>
	</div><!-- /.sf-login__hero -->

	<!-- ── Forms area ───────────────────────────────────────────────── -->
	<div class="sf-login__forms">

		<?php if ( $sf_verify_step ) : ?>
		<!-- ── OTP Verify Step ──────────────────────────────────────── -->
		<div class="sf-lcard sf-lcard--verify">

			<div class="sf-lcard__icon">
				<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12" y2="18.01"/></svg>
			</div>

			<h1 class="sf-lcard__title"><?php esc_html_e( 'Enter Your Code', 'samurai' ); ?></h1>
			<p class="sf-lcard__sub"><?php esc_html_e( 'We texted a 6-digit code to your phone. Enter it below to sign in.', 'samurai' ); ?></p>

			<form method="post" class="sf-lcard__form" novalidate>
				<?php wp_nonce_field( 'wtlo_sms_verify_code', 'wtlo_sms_nonce' ); ?>
				<input type="hidden" name="wtlo_sms_action" value="verify_code">

				<div class="sf-lfield sf-lfield--otp">
					<label for="wtlo_sms_code" class="screen-reader-text"><?php esc_html_e( 'Verification code', 'samurai' ); ?></label>
					<input type="text"
					       name="wtlo_sms_code"
					       id="wtlo_sms_code"
					       class="sf-linput sf-linput--otp"
					       inputmode="numeric"
					       pattern="[0-9]*"
					       autocomplete="one-time-code"
					       maxlength="8"
					       placeholder="_ _ _ _ _ _"
					       required
					       autofocus>
				</div>

				<button type="submit" class="sf-lbtn sf-lbtn--primary sf-lbtn--full">
					<?php esc_html_e( 'Verify &amp; Sign In', 'samurai' ); ?>
				</button>
			</form>

			<p class="sf-lcard__switch">
				<a href="<?php echo esc_url( remove_query_arg( 'wtlo_sms_step', wc_get_page_permalink( 'myaccount' ) ) ); ?>">
					<?php esc_html_e( '&larr; Back to Sign In', 'samurai' ); ?>
				</a>
			</p>

		</div><!-- /.sf-lcard--verify -->

		<?php else : ?>

		<!-- ── Login card ───────────────────────────────────────────── -->
		<div class="sf-lcard sf-lcard--login<?php echo $sf_show_register ? ' sf-lcard--hidden' : ''; ?>" id="sf-card-login">

			<h1 class="sf-lcard__title"><?php esc_html_e( 'Welcome Back', 'samurai' ); ?></h1>
			<p class="sf-lcard__sub"><?php esc_html_e( 'Sign in to your Samurai account.', 'samurai' ); ?></p>

			<?php if ( $sf_sms_enabled ) : ?>
			<div class="sf-ltabs" role="tablist" aria-label="<?php esc_attr_e( 'Sign-in method', 'samurai' ); ?>">
				<button type="button" class="sf-ltabs__btn sf-ltabs__btn--active" role="tab"
				        id="sf-tab-phone" aria-selected="true" aria-controls="sf-panel-phone">
					<?php esc_html_e( 'Phone Code', 'samurai' ); ?>
				</button>
				<button type="button" class="sf-ltabs__btn" role="tab"
				        id="sf-tab-email" aria-selected="false" aria-controls="sf-panel-email">
					<?php esc_html_e( 'Email / Password', 'samurai' ); ?>
				</button>
			</div>

			<!-- Phone Code panel -->
			<div class="sf-ltab-panel" id="sf-panel-phone" role="tabpanel" aria-labelledby="sf-tab-phone">
				<form method="post" class="sf-lcard__form" novalidate>
					<?php wp_nonce_field( 'wtlo_sms_send_code', 'wtlo_sms_nonce' ); ?>
					<input type="hidden" name="wtlo_sms_action" value="send_code">
					<div class="sf-lfield">
						<label for="wtlo_sms_phone"><?php esc_html_e( 'Phone number', 'samurai' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
						<input type="tel" name="wtlo_sms_phone" id="wtlo_sms_phone" class="sf-linput"
						       placeholder="305 555 1234" autocomplete="tel"
						       value="<?php echo ! empty( $_POST['wtlo_sms_phone'] ) ? esc_attr( wp_unslash( $_POST['wtlo_sms_phone'] ) ) : ''; ?>"
						       required>
						<span class="sf-lfield__hint"><?php esc_html_e( 'US numbers only — we\'ll text you a login code.', 'samurai' ); ?></span>
					</div>
					<?php if ( $sf_recaptcha_key ) : ?>
					<div class="sf-lfield"><div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $sf_recaptcha_key ); ?>"></div></div>
					<?php endif; ?>
					<button type="submit" class="sf-lbtn sf-lbtn--primary sf-lbtn--full">
						<?php esc_html_e( 'Send Code', 'samurai' ); ?>
					</button>
				</form>
			</div>
			<?php endif; ?>

			<!-- Email / Password panel -->
			<div class="sf-ltab-panel<?php echo $sf_sms_enabled ? '' : ' sf-ltab-panel--active'; ?>"
			     id="sf-panel-email" role="tabpanel" aria-labelledby="sf-tab-email">
				<form class="woocommerce-form woocommerce-form-login login sf-lcard__form" method="post" novalidate>
					<?php do_action( 'woocommerce_login_form_start' ); ?>
					<div class="sf-lfield">
						<label for="username"><?php esc_html_e( 'Email address', 'woocommerce' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
						<input type="text" class="woocommerce-Input woocommerce-Input--text input-text sf-linput"
						       name="username" id="username" autocomplete="username"
						       value="<?php echo ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
						       required>
					</div>
					<div class="sf-lfield">
						<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
						<input class="woocommerce-Input woocommerce-Input--text input-text sf-linput"
						       type="password" name="password" id="password" autocomplete="current-password" required>
					</div>
					<?php do_action( 'woocommerce_login_form' ); ?>
					<div class="sf-lrow">
						<label class="sf-lcheck">
							<input class="woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever">
							<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						</div>
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit"
					        class="sf-lbtn sf-lbtn--primary sf-lbtn--full woocommerce-button woocommerce-form-login__submit"
					        name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
						<?php esc_html_e( 'Sign In', 'samurai' ); ?>
					</button>
					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>
			</div>

			<?php if ( $sf_registration ) : ?>
			<p class="sf-lcard__switch">
				<?php esc_html_e( 'Don\'t have an account?', 'samurai' ); ?>
				<a href="#" id="sf-show-register"><?php esc_html_e( 'Sign up', 'samurai' ); ?></a>
			</p>
			<?php endif; ?>

		</div><!-- /.sf-lcard--login -->

		<!-- ── Register card ────────────────────────────────────────── -->
		<?php if ( $sf_registration ) : ?>
		<div class="sf-lcard sf-lcard--register<?php echo $sf_show_register ? '' : ' sf-lcard--hidden'; ?>" id="sf-card-register">

			<h2 class="sf-lcard__title"><?php esc_html_e( 'Create Account', 'samurai' ); ?></h2>
			<p class="sf-lcard__sub"><?php esc_html_e( 'Join Samurai Fireworks for faster checkout and order tracking.', 'samurai' ); ?></p>

			<form method="post"
			      class="woocommerce-form woocommerce-form-register register sf-lcard__form"
			      <?php do_action( 'woocommerce_register_form_tag' ); ?>>

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<div class="sf-lrow sf-lrow--cols">
					<div class="sf-lfield">
						<label for="reg_first_name"><?php esc_html_e( 'First name', 'samurai' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
						<input type="text" class="sf-linput" name="reg_first_name" id="reg_first_name"
						       autocomplete="given-name"
						       value="<?php echo ! empty( $_POST['reg_first_name'] ) ? esc_attr( wp_unslash( $_POST['reg_first_name'] ) ) : ''; ?>"
						       required>
					</div>
					<div class="sf-lfield">
						<label for="reg_last_name"><?php esc_html_e( 'Last name', 'samurai' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
						<input type="text" class="sf-linput" name="reg_last_name" id="reg_last_name"
						       autocomplete="family-name"
						       value="<?php echo ! empty( $_POST['reg_last_name'] ) ? esc_attr( wp_unslash( $_POST['reg_last_name'] ) ) : ''; ?>"
						       required>
					</div>
				</div>

				<div class="sf-lfield">
					<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
					<input type="email" class="woocommerce-Input woocommerce-Input--text input-text sf-linput"
					       name="email" id="reg_email" autocomplete="email"
					       value="<?php echo ! empty( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
					       required>
				</div>

				<div class="sf-lfield">
					<label for="reg_billing_phone"><?php esc_html_e( 'Phone number', 'samurai' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
					<input type="tel" class="sf-linput" name="billing_phone" id="reg_billing_phone"
					       autocomplete="tel" placeholder="305 555 1234"
					       value="<?php echo ! empty( $_POST['billing_phone'] ) ? esc_attr( wp_unslash( $_POST['billing_phone'] ) ) : ''; ?>"
					       required>
					<span class="sf-lfield__hint"><?php esc_html_e( 'US numbers only — used for order updates and SMS login.', 'samurai' ); ?></span>
				</div>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				<div class="sf-lfield">
					<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="sf-lfield__req" aria-hidden="true">*</span></label>
					<input type="password" class="woocommerce-Input woocommerce-Input--text input-text sf-linput"
					       name="password" id="reg_password" autocomplete="new-password" required>
				</div>
				<?php else : ?>
				<p class="sf-lfield__hint sf-lfield__hint--block">
					<?php esc_html_e( 'A link to set your password will be sent to your email address.', 'woocommerce' ); ?>
				</p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>
				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<?php do_action( 'woocommerce_after_customer_login_form' ); ?>

				<button type="submit"
				        class="sf-lbtn sf-lbtn--primary sf-lbtn--full woocommerce-Button woocommerce-form-register__submit"
				        name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Create Account', 'samurai' ); ?>
				</button>

				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>

			<p class="sf-lcard__switch">
				<?php esc_html_e( 'Already have an account?', 'samurai' ); ?>
				<a href="#" id="sf-show-login"><?php esc_html_e( 'Sign in', 'samurai' ); ?></a>
			</p>

		</div><!-- /.sf-lcard--register -->
		<?php endif; ?>

		<?php endif; // verify step ?>

	</div><!-- /.sf-login__forms -->

</div><!-- /.sf-login -->

<!-- Tab switching + login↔register toggle -->
<script>
(function () {
	// ── Login ↔ Register toggle ──────────────────────────────────────────
	var cardLogin    = document.getElementById('sf-card-login');
	var cardRegister = document.getElementById('sf-card-register');
	var btnToReg     = document.getElementById('sf-show-register');
	var btnToLogin   = document.getElementById('sf-show-login');

	function showCard(show, hide) {
		if (hide) hide.classList.add('sf-lcard--hidden');
		if (show) {
			show.classList.remove('sf-lcard--hidden');
			show.classList.add('sf-lcard--appearing');
			setTimeout(function () { show.classList.remove('sf-lcard--appearing'); }, 350);
		}
	}

	if (btnToReg) {
		btnToReg.addEventListener('click', function (e) {
			e.preventDefault();
			showCard(cardRegister, cardLogin);
		});
	}
	if (btnToLogin) {
		btnToLogin.addEventListener('click', function (e) {
			e.preventDefault();
			showCard(cardLogin, cardRegister);
		});
	}

	// ── SMS / Email tab switching ────────────────────────────────────────
	var panels = {
		phone: document.getElementById('sf-panel-phone'),
		email: document.getElementById('sf-panel-email')
	};
	var tabs = {
		phone: document.getElementById('sf-tab-phone'),
		email: document.getElementById('sf-tab-email')
	};

	if (tabs.phone && tabs.email) {
		function activateTab(which) {
			Object.keys(panels).forEach(function (k) {
				var on = k === which;
				if (panels[k]) panels[k].classList.toggle('sf-ltab-panel--active', on);
				if (tabs[k]) {
					tabs[k].classList.toggle('sf-ltabs__btn--active', on);
					tabs[k].setAttribute('aria-selected', on ? 'true' : 'false');
				}
			});
		}
		activateTab('phone');
		tabs.phone.addEventListener('click', function () { activateTab('phone'); });
		tabs.email.addEventListener('click', function () { activateTab('email'); });

		// Keep email tab if the login form was submitted with errors
		if (document.querySelector('.woocommerce-form-login') && document.querySelector('.woocommerce-error')) {
			activateTab('email');
		}
	}
}());
</script>
