/**
 * Newsletter sign-up — AJAX form submission.
 *
 * Validates email (required) and terms acceptance (required),
 * phone is optional. On success swaps the form for the success panel.
 * Notifications surface through window.sfToast.
 *
 * Config injected by PHP via wp_localize_script as window.sfNewsletter:
 *   { ajaxUrl, nonce, i18n: { emailInvalid, termsRequired, sending, submit, error } }
 *
 * @package samurai
 */
( function () {
	'use strict';

	var form = document.querySelector( '.js-newsletter-form' );
	if ( ! form ) return;

	var emailEl   = form.querySelector( '[name="email"]' );
	var phoneEl   = form.querySelector( '[name="phone"]' );
	var termsEl   = form.querySelector( '[name="terms"]' );
	var btn       = form.querySelector( '[type="submit"]' );
	var btnText   = btn ? btn.querySelector( '.sf-newsletter__btn-text' ) : null;
	var panel     = form.closest( '.sf-newsletter__panel' );
	var successEl = panel ? panel.querySelector( '.sf-newsletter__success' ) : null;

	var cfg  = window.sfNewsletter || {};
	var i18n = cfg.i18n || {};

	function toast( msg, type ) {
		if ( window.sfToast && msg ) window.sfToast.show( msg, type );
	}

	function setLoading( loading ) {
		if ( ! btn ) return;
		btn.disabled = loading;
		if ( btnText ) {
			btnText.textContent = loading
				? ( i18n.sending || 'Sending…' )
				: ( i18n.submit  || 'Subscribe Now' );
		}
	}

	function showSuccess( message ) {
		form.hidden = true;
		if ( ! successEl ) return;
		if ( message ) {
			var msgEl = successEl.querySelector( '.sf-newsletter__success-msg' );
			if ( msgEl ) msgEl.textContent = message;
		}
		successEl.hidden = false;
	}

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();

		var email = emailEl ? emailEl.value.trim() : '';
		var phone = phoneEl ? phoneEl.value.trim() : '';
		var terms = termsEl ? termsEl.checked : false;

		// ── Client-side validation ─────────────────────────────────────────────
		if ( ! email || ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
			toast( i18n.emailInvalid || 'Please enter a valid email address.', 'error' );
			if ( emailEl ) emailEl.focus();
			return;
		}

		if ( ! terms ) {
			toast( i18n.termsRequired || 'Please accept the Terms & Conditions.', 'error' );
			if ( termsEl ) termsEl.focus();
			return;
		}

		// ── Submit ─────────────────────────────────────────────────────────────
		setLoading( true );

		var data = new FormData();
		data.append( 'action', 'sf_newsletter_subscribe' );
		data.append( 'nonce',  cfg.nonce || '' );
		data.append( 'email',  email );
		data.append( 'phone',  phone );
		data.append( 'terms',  '1' );

		fetch( cfg.ajaxUrl || '', {
			method:      'POST',
			credentials: 'same-origin',
			body:        data,
		} )
		.then( function ( r ) { return r.json(); } )
		.then( function ( res ) {
			if ( res.success ) {
				showSuccess( res.data && res.data.message );
				toast( ( res.data && res.data.message ) || 'Subscribed!', 'success' );
			} else {
				var msg = ( res.data && res.data.message ) || i18n.error || 'Something went wrong. Please try again.';
				toast( msg, 'error' );
				setLoading( false );
			}
		} )
		.catch( function () {
			toast( i18n.error || 'Something went wrong. Please try again.', 'error' );
			setLoading( false );
		} );
	} );

} )();
