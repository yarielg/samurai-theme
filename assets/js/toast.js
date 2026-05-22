/**
 * Floating toast notification system.
 *
 * WC notice templates output hidden .sf-notice-seed divs.
 * This script harvests them on page load and after every AJAX DOM update,
 * then displays them as floating toasts.
 *
 * Public API: window.sfToast.show( html, type )
 *   html — string (may contain safe HTML like links)
 *   type — 'success' | 'error' | 'notice'
 *
 * @package samurai
 */
( function () {
	'use strict';

	var DURATION = 5000; // ms before auto-dismiss

	var ICONS = {
		success: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>',
		error:   '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
		notice:  '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
	};

	// ── Container ─────────────────────────────────────────────────────────────

	function getContainer() {
		var el = document.getElementById( 'sf-toast-container' );
		if ( ! el ) {
			el = document.createElement( 'div' );
			el.id = 'sf-toast-container';
			el.setAttribute( 'aria-live', 'polite' );
			el.setAttribute( 'aria-atomic', 'false' );
			document.body.appendChild( el );
		}
		return el;
	}

	// ── Dismiss ───────────────────────────────────────────────────────────────

	function dismiss( toast ) {
		toast.classList.remove( 'is-visible' );
		toast.classList.add( 'is-leaving' );
		toast.addEventListener( 'transitionend', function handler() {
			toast.removeEventListener( 'transitionend', handler );
			if ( toast.parentNode ) toast.parentNode.removeChild( toast );
		} );
	}

	// ── Show ──────────────────────────────────────────────────────────────────

	function show( html, type ) {
		html = ( html || '' ).trim();
		if ( ! html ) return;
		type = ( type === 'success' || type === 'error' ) ? type : 'notice';

		var wrap  = getContainer();
		var toast = document.createElement( 'div' );
		toast.className = 'sf-toast sf-toast--' + type;
		toast.setAttribute( 'role', type === 'error' ? 'alert' : 'status' );

		toast.innerHTML =
			'<span class="sf-toast__icon">' + ( ICONS[ type ] || ICONS.notice ) + '</span>' +
			'<span class="sf-toast__msg">'  + html + '</span>' +
			'<button class="sf-toast__close" type="button" aria-label="Dismiss">×</button>' +
			'<span class="sf-toast__bar" style="animation-duration:' + DURATION + 'ms"></span>';

		wrap.appendChild( toast );

		// Double rAF: let the browser paint the initial (hidden) state first
		requestAnimationFrame( function () {
			requestAnimationFrame( function () {
				toast.classList.add( 'is-visible' );
			} );
		} );

		var timer = setTimeout( function () { dismiss( toast ); }, DURATION );

		toast.querySelector( '.sf-toast__close' ).addEventListener( 'click', function () {
			clearTimeout( timer );
			dismiss( toast );
		} );
	}

	// ── Seed harvesting ───────────────────────────────────────────────────────
	// Seeds are .sf-notice-seed[data-type] elements rendered by WC templates.
	// We mark each with data-sf-done before processing to avoid double-firing
	// when the MutationObserver triggers on our own removal setTimeout.

	function harvest() {
		var seeds = document.querySelectorAll( '.sf-notice-seed:not([data-sf-done])' );
		seeds.forEach( function ( seed ) {
			seed.setAttribute( 'data-sf-done', '1' );
			var type = seed.getAttribute( 'data-type' ) || 'notice';
			show( seed.innerHTML.trim(), type );
			setTimeout( function () {
				if ( seed.parentNode ) seed.parentNode.removeChild( seed );
			}, 0 );
		} );
	}

	// Run on load
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', harvest );
	} else {
		harvest();
	}

	// Watch for seeds injected after AJAX updates (cart refresh, WC fragments, etc.)
	var observer = new MutationObserver( harvest );
	observer.observe( document.body, { childList: true, subtree: true } );

	// ── Public API ────────────────────────────────────────────────────────────

	window.sfToast = { show: show };

} )();
