/**
 * Mega Menu — hover open/close, keyboard nav, backdrop.
 * Desktop only — CSS hides the panel on mobile.
 *
 * Position strategy: the panel uses position:absolute (relative to
 * .sf-header__inner) so it stays in the same DOM ancestry as the <li>
 * and mouseleave / mouseenter fire correctly across the nav → panel path.
 */
( function () {
	'use strict';

	const OPEN_DELAY  = 80;    // ms before opening on hover-enter
	const CLOSE_DELAY = 200;   // ms before closing on hover-leave

	let megaItem, panel, backdrop, trigger;
	let openTimer = null, closeTimer = null;

	// -------------------------------------------------------------------------
	// State
	// -------------------------------------------------------------------------
	function isOpen() {
		return panel && panel.classList.contains( 'is-open' );
	}

	function openPanel() {
		clearTimeout( closeTimer );
		if ( isOpen() ) return;
		panel.classList.add( 'is-open' );
		if ( backdrop ) backdrop.classList.add( 'is-open' );
		megaItem.classList.add( 'is-open' );
		if ( trigger ) trigger.setAttribute( 'aria-expanded', 'true' );
	}

	function closePanel() {
		clearTimeout( openTimer );
		if ( ! isOpen() ) return;
		panel.classList.remove( 'is-open' );
		if ( backdrop ) backdrop.classList.remove( 'is-open' );
		megaItem.classList.remove( 'is-open' );
		if ( trigger ) trigger.setAttribute( 'aria-expanded', 'false' );
	}

	function scheduleOpen() {
		clearTimeout( closeTimer );
		openTimer = setTimeout( openPanel, OPEN_DELAY );
	}

	function scheduleClose() {
		clearTimeout( openTimer );
		closeTimer = setTimeout( closePanel, CLOSE_DELAY );
	}

	// -------------------------------------------------------------------------
	// Hover — use coordinate tracking to reliably bridge nav item → panel.
	// mouseleave / mouseenter alone are unreliable when the panel overflows the
	// <li> layout box (even with position:absolute the browser measures the <li>
	// bounds excluding absolutely-positioned overflow).
	// -------------------------------------------------------------------------
	function hitRect( rect, x, y ) {
		return x >= rect.left && x <= rect.right && y >= rect.top && y <= rect.bottom;
	}

	let rafId = null;

	function onMouseMove( e ) {
		if ( rafId ) return;
		rafId = requestAnimationFrame( function () {
			rafId = null;
			if ( ! megaItem ) return;

			const x = e.clientX;
			const y = e.clientY;

			const overItem  = hitRect( megaItem.getBoundingClientRect(), x, y );
			const overPanel = isOpen() && panel && hitRect( panel.getBoundingClientRect(), x, y );

			if ( overItem || overPanel ) {
				clearTimeout( closeTimer );
				if ( overItem && ! isOpen() ) scheduleOpen();
			} else {
				clearTimeout( openTimer );
				if ( isOpen() ) scheduleClose();
			}
		} );
	}

	// -------------------------------------------------------------------------
	// Boot
	// -------------------------------------------------------------------------
	document.addEventListener( 'DOMContentLoaded', function () {
		panel    = document.getElementById( 'sf-mega-panel' );
		backdrop = document.querySelector( '.sf-mega-backdrop' );

		if ( ! panel ) return;

		megaItem = panel.closest( 'li' ) || document.querySelector( '.menu-item--mega' );
		if ( ! megaItem ) return;

		trigger = megaItem.querySelector( ':scope > a' );
		if ( trigger ) {
			trigger.setAttribute( 'aria-haspopup', 'true' );
			trigger.setAttribute( 'aria-expanded', 'false' );
			trigger.setAttribute( 'aria-controls', 'sf-mega-panel' );
		}

		// Coordinate-based hover tracking
		document.addEventListener( 'mousemove', onMouseMove );

		// Keyboard
		megaItem.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' || e.key === ' ' ) {
				e.preventDefault();
				isOpen() ? closePanel() : openPanel();
			}
			if ( e.key === 'Escape' ) {
				closePanel();
				if ( trigger ) trigger.focus();
			}
		} );

		panel.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				closePanel();
				if ( trigger ) trigger.focus();
			}
		} );

		// Backdrop and outside click
		if ( backdrop ) backdrop.addEventListener( 'click', closePanel );

		document.addEventListener( 'click', function ( e ) {
			if ( isOpen() && ! megaItem.contains( e.target ) && ! panel.contains( e.target ) ) {
				closePanel();
			}
		} );

		document.addEventListener( 'focusin', function ( e ) {
			if ( isOpen() && ! megaItem.contains( e.target ) && ! panel.contains( e.target ) ) {
				closePanel();
			}
		} );
	} );

} )();
