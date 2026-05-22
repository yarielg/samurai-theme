/**
 * Cart page — AJAX interactions.
 *
 * Handles quantity ±, direct input changes, item removal, coupon
 * drawer, apply coupon, remove coupon. All price/stock calculations
 * are performed server-side; JS never derives totals independently.
 *
 * Requires: sfCart global (ajaxUrl, wcAjaxUrl, nonce, i18n).
 *
 * @package samurai
 */
( function () {
	'use strict';

	if ( typeof sfCart === 'undefined' ) return;

	var qs  = function ( sel, ctx ) { return ( ctx || document ).querySelector( sel ); };
	var qsa = function ( sel, ctx ) { return [].slice.call( ( ctx || document ).querySelectorAll( sel ) ); };

	var DEBOUNCE_MS  = 700;
	var itemsWrap    = qs( '.js-cart-items' );
	var summaryWrap  = qs( '.js-cart-summary' );

	if ( ! itemsWrap ) return; // not on cart page

	var busy     = false;
	var qtyTimer = null;

	// ─── Core helpers ────────────────────────────────────────────────────────

	function post( params ) {
		return fetch( sfCart.ajaxUrl, {
			method:  'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:    new URLSearchParams( params ).toString(),
		} ).then( function ( r ) { return r.json(); } );
	}

	function applyRefresh( data ) {
		if ( ! data || ! data.success ) return;
		var d = data.data;

		if ( itemsWrap  && d.items   !== undefined ) itemsWrap.innerHTML   = d.items;
		if ( summaryWrap && d.summary !== undefined ) summaryWrap.innerHTML = d.summary;

		if ( d.count !== undefined ) {
			var n = parseInt( d.count, 10 ) || 0;
			qsa( '.js-mini-cart-count, .sf-cart-count' ).forEach( function ( el ) {
				el.textContent = n;
				el.hidden = ( n === 0 );
			} );
			var headerCount = qs( '.js-cart-header-count' );
			if ( headerCount ) {
				headerCount.textContent = n + ' ' +
					( n === 1 ? ( sfCart.i18n.item || 'item' ) : ( sfCart.i18n.items || 'items' ) );
			}
		}

		document.body.dispatchEvent( new CustomEvent( 'wc_fragment_refresh' ) );
	}

	function updateCart( key, qty ) {
		if ( busy ) return;
		busy = true;

		var item = qs( '[data-cart-key="' + key + '"]', itemsWrap );
		if ( item ) item.classList.add( 'is-updating' );

		post( {
			action:   'sf_cart_update',
			nonce:    sfCart.nonce,
			cart_key: key,
			qty:      qty,
		} )
		.then( function ( data ) {
			if ( data && data.success && data.data && data.data.empty ) {
				window.location.reload();
				return;
			}
			applyRefresh( data );
		} )
		.catch( function () { /* network failure — leave DOM as-is */ } )
		.finally( function () { busy = false; } );
	}

	function removeWithAnimation( key ) {
		var item = qs( '[data-cart-key="' + key + '"]', itemsWrap );
		if ( item ) {
			item.style.maxHeight = item.offsetHeight + 'px';
			requestAnimationFrame( function () {
				item.classList.add( 'is-removing' );
				setTimeout( function () { updateCart( key, 0 ); }, 320 );
			} );
		} else {
			updateCart( key, 0 );
		}
	}

	function scheduleUpdate( key, qty ) {
		clearTimeout( qtyTimer );
		qtyTimer = setTimeout( function () { updateCart( key, qty ); }, DEBOUNCE_MS );
	}

	// ─── Quantity controls ───────────────────────────────────────────────────

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.js-cart-minus' );
		if ( ! btn || busy ) return;
		var key   = btn.dataset.key;
		var input = qs( '.js-cart-qty[data-key="' + key + '"]', itemsWrap );
		if ( ! input ) return;
		var val = parseInt( input.value, 10 ) - 1;
		if ( val < 1 ) { removeWithAnimation( key ); return; }
		input.value = val;
		scheduleUpdate( key, val );
	} );

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.js-cart-plus' );
		if ( ! btn || busy ) return;
		var key   = btn.dataset.key;
		var input = qs( '.js-cart-qty[data-key="' + key + '"]', itemsWrap );
		if ( ! input ) return;
		var max = parseInt( input.max, 10 ) || 0;
		var val = parseInt( input.value, 10 ) + 1;
		if ( max > 0 && val > max ) val = max;
		input.value = val;
		scheduleUpdate( key, val );
	} );

	document.addEventListener( 'change', function ( e ) {
		if ( ! e.target.classList.contains( 'js-cart-qty' ) || busy ) return;
		var key = e.target.dataset.key;
		var val = parseInt( e.target.value, 10 );
		if ( isNaN( val ) || val < 1 ) {
			e.target.value = 1;
			removeWithAnimation( key );
			return;
		}
		var max = parseInt( e.target.max, 10 ) || 0;
		if ( max > 0 && val > max ) { val = max; e.target.value = val; }
		scheduleUpdate( key, val );
	} );

	// ─── Remove ──────────────────────────────────────────────────────────────

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.js-cart-remove' );
		if ( ! btn || busy ) return;
		removeWithAnimation( btn.dataset.key );
	} );

	// ─── Coupon drawer ───────────────────────────────────────────────────────

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.js-coupon-toggle' );
		if ( ! btn ) return;
		var drawer = document.getElementById( 'sf-coupon-drawer' );
		if ( ! drawer ) return;
		var open = btn.getAttribute( 'aria-expanded' ) === 'true';
		btn.setAttribute( 'aria-expanded', String( ! open ) );
		drawer.setAttribute( 'aria-hidden', String( open ) );
		drawer.classList.toggle( 'is-open', ! open );
		if ( ! open ) {
			setTimeout( function () {
				var inp = qs( '.js-coupon-input', drawer );
				if ( inp ) inp.focus();
			}, 50 );
		}
	} );

	// ─── Apply coupon ────────────────────────────────────────────────────────

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target.closest( '.js-coupon-form' );
		if ( ! form ) return;
		e.preventDefault();

		var codeInput  = qs( '.js-coupon-input', form );
		var feedback   = qs( '.js-coupon-feedback' );
		var submitBtn  = qs( '.js-coupon-submit', form );
		var nonceInput = qs( '[name="apply-coupon-nonce"]', form );
		var code       = codeInput ? codeInput.value.trim() : '';

		if ( ! code ) {
			setFeedback( feedback, false, sfCart.i18n.enterCode || 'Please enter a coupon code.' );
			if ( codeInput ) codeInput.focus();
			return;
		}

		if ( submitBtn ) submitBtn.disabled = true;
		clearFeedback( feedback );

		fetch( sfCart.wcAjaxUrl.replace( '%%endpoint%%', 'apply_coupon' ), {
			method:  'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:    new URLSearchParams( {
				coupon_code: code,
				security:    nonceInput ? nonceInput.value : '',
			} ).toString(),
		} )
		.then( function ( r ) { return r.text(); } )
		.then( function ( html ) {
			// WC notice templates now render .sf-notice-seed divs; parse them.
			var tmp  = document.createElement( 'div' );
			tmp.innerHTML = html;
			var seed  = tmp.querySelector( '.sf-notice-seed' );
			var isErr = seed
				? seed.getAttribute( 'data-type' ) === 'error'
				: /woocommerce-error/.test( html );
			var msg = seed ? seed.innerHTML.trim() : html;

			if ( window.sfToast ) {
				sfToast.show( msg, isErr ? 'error' : 'success' );
			} else {
				setFeedback( feedback, ! isErr, msg );
			}

			if ( ! isErr ) {
				if ( codeInput ) codeInput.value = '';
				return post( { action: 'sf_cart_update', nonce: sfCart.nonce } )
					.then( applyRefresh );
			}
		} )
		.catch( function () {
			var errMsg = sfCart.i18n.error || 'Something went wrong. Please try again.';
			if ( window.sfToast ) {
				sfToast.show( errMsg, 'error' );
			} else {
				setFeedback( feedback, false, errMsg );
			}
		} )
		.finally( function () {
			if ( submitBtn ) submitBtn.disabled = false;
		} );
	} );

	// ─── Remove coupon ───────────────────────────────────────────────────────

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.js-remove-coupon' );
		if ( ! btn || busy ) return;
		btn.disabled = true;

		fetch( sfCart.wcAjaxUrl.replace( '%%endpoint%%', 'remove_coupon' ), {
			method:  'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:    new URLSearchParams( {
				coupon:   btn.dataset.coupon,
				security: btn.dataset.nonce,
			} ).toString(),
		} )
		.then( function () {
			return post( { action: 'sf_cart_update', nonce: sfCart.nonce } );
		} )
		.then( applyRefresh )
		.catch( function () { /* leave as-is on network error */ } )
		.finally( function () { btn.disabled = false; } );
	} );

	// ─── Feedback helpers ─────────────────────────────────────────────────────

	function setFeedback( el, success, html ) {
		if ( ! el ) return;
		el.className = 'sf-coupon-feedback js-coupon-feedback ' + ( success ? 'is-success' : 'is-error' );
		el.innerHTML = html;
	}

	function clearFeedback( el ) {
		if ( ! el ) return;
		el.className = 'sf-coupon-feedback js-coupon-feedback';
		el.innerHTML = '';
	}

} )();
