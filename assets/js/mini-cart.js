/**
 * Mini Cart — drawer open/close, qty updates, remove, WC fragment sync.
 */
( function () {
	'use strict';

	const AJAX_URL   = typeof sfMiniCart !== 'undefined' ? sfMiniCart.ajaxUrl : '/wp-admin/admin-ajax.php';
	const NOOP       = () => {};

	// -------------------------------------------------------------------------
	// DOM refs (set after DOMContentLoaded)
	// -------------------------------------------------------------------------
	let drawer, backdrop, cartBtn;

	// -------------------------------------------------------------------------
	// Open / Close
	// -------------------------------------------------------------------------
	function openCart() {
		if ( ! drawer ) return;
		drawer.classList.add( 'is-open' );
		backdrop.classList.add( 'is-open' );
		document.body.classList.add( 'sf-minicart-is-open' );
		drawer.removeAttribute( 'aria-hidden' );
		// Focus close button for a11y
		const closeBtn = drawer.querySelector( '.js-minicart-close' );
		if ( closeBtn ) closeBtn.focus();
	}

	function closeCart() {
		if ( ! drawer ) return;
		drawer.classList.remove( 'is-open' );
		backdrop.classList.remove( 'is-open' );
		document.body.classList.remove( 'sf-minicart-is-open' );
		drawer.setAttribute( 'aria-hidden', 'true' );
		if ( cartBtn ) cartBtn.focus();
	}

	// -------------------------------------------------------------------------
	// Fragment application — replace drawer + badge from WC or custom AJAX
	// -------------------------------------------------------------------------
	function applyFragments( fragments ) {
		if ( ! fragments ) return;
		Object.keys( fragments ).forEach( function ( selector ) {
			const el = document.querySelector( selector );
			if ( ! el ) return;
			const tmp = document.createElement( 'div' );
			tmp.innerHTML = fragments[ selector ];
			const newNode = tmp.firstElementChild;
			if ( newNode ) {
				el.replaceWith( newNode );
				// Re-cache drawer ref if it was replaced
				if ( selector === 'div.sf-minicart' ) {
					drawer = document.getElementById( 'sf-minicart' );
				}
			}
		} );
	}

	// -------------------------------------------------------------------------
	// Pulse animation on cart button badge
	// -------------------------------------------------------------------------
	function pulseCartBtn() {
		if ( ! cartBtn ) return;
		cartBtn.classList.remove( 'is-pulsing' );
		// Force reflow to restart animation
		void cartBtn.offsetWidth;
		cartBtn.classList.add( 'is-pulsing' );
		cartBtn.addEventListener( 'animationend', function handler() {
			cartBtn.classList.remove( 'is-pulsing' );
			cartBtn.removeEventListener( 'animationend', handler );
		} );
	}

	// -------------------------------------------------------------------------
	// AJAX — update qty or remove item
	// -------------------------------------------------------------------------
	function updateItem( cartKey, qty, nonce, itemEl ) {
		if ( itemEl ) itemEl.classList.add( 'is-loading' );

		const body = new URLSearchParams();
		body.set( 'action',        'sf_mc_update' );
		body.set( 'nonce',         nonce );
		body.set( 'cart_item_key', cartKey );
		body.set( 'quantity',      qty );

		fetch( AJAX_URL, {
			method:  'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:    body.toString(),
		} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( data ) {
				if ( data && data.success && data.data && data.data.fragments ) {
					applyFragments( data.data.fragments );
					// drawer ref may have changed after fragment replace
					drawer   = document.getElementById( 'sf-minicart' );
					backdrop = document.querySelector( '.sf-minicart-backdrop' );
					// Keep drawer open
					openCart();
					pulseCartBtn();
				} else if ( itemEl ) {
					itemEl.classList.remove( 'is-loading' );
				}
			} )
			.catch( NOOP );
	}

	// -------------------------------------------------------------------------
	// Delegated events inside the drawer
	// -------------------------------------------------------------------------
	function handleDrawerClick( e ) {
		const target = e.target;

		// Close button
		if ( target.closest( '.js-minicart-close' ) ) {
			closeCart();
			return;
		}

		const qtyWidget = target.closest( '.js-mc-qty' );

		// Increase qty
		if ( target.closest( '.js-mc-plus' ) && qtyWidget ) {
			const key   = qtyWidget.dataset.cartKey;
			const nonce = qtyWidget.dataset.nonce;
			const valEl = qtyWidget.querySelector( '.sf-minicart__qty-val' );
			const current = valEl ? parseInt( valEl.textContent, 10 ) : 1;
			updateItem( key, current + 1, nonce, target.closest( '.sf-minicart__item' ) );
			return;
		}

		// Decrease qty
		if ( target.closest( '.js-mc-minus' ) && qtyWidget ) {
			const key   = qtyWidget.dataset.cartKey;
			const nonce = qtyWidget.dataset.nonce;
			const valEl = qtyWidget.querySelector( '.sf-minicart__qty-val' );
			const current = valEl ? parseInt( valEl.textContent, 10 ) : 1;
			const newQty  = current - 1;
			updateItem( key, newQty, nonce, target.closest( '.sf-minicart__item' ) );
			return;
		}

		// Remove item
		const removeBtn = target.closest( '.js-mc-remove' );
		if ( removeBtn ) {
			const key   = removeBtn.dataset.cartKey;
			const nonce = removeBtn.dataset.nonce;
			updateItem( key, 0, nonce, target.closest( '.sf-minicart__item' ) );
			return;
		}
	}

	// -------------------------------------------------------------------------
	// WooCommerce native fragment update (add-to-cart etc.)
	// -------------------------------------------------------------------------
	function onWcFragmentsRefreshed() {
		// WC has already applied the fragments. Re-cache our refs in case the
		// drawer element was replaced.
		drawer   = document.getElementById( 'sf-minicart' );
		backdrop = document.querySelector( '.sf-minicart-backdrop' );
	}

	function onAddedToCart() {
		// WC's own fragment handler runs as part of the same jQuery event chain
		// and replaces div.sf-minicart synchronously before or after our handler.
		// Defer the open to the next tick so the DOM is fully settled regardless
		// of handler registration order.
		setTimeout( function () {
			drawer   = document.getElementById( 'sf-minicart' );
			backdrop = document.querySelector( '.sf-minicart-backdrop' );
			openCart();
			pulseCartBtn();
		}, 0 );
	}

	// -------------------------------------------------------------------------
	// Boot
	// -------------------------------------------------------------------------
	document.addEventListener( 'DOMContentLoaded', function () {
		drawer   = document.getElementById( 'sf-minicart' );
		backdrop = document.querySelector( '.sf-minicart-backdrop' );
		cartBtn  = document.querySelector( '.js-minicart-open' );

		if ( ! drawer ) return;

		// Open on cart button click
		if ( cartBtn ) {
			cartBtn.addEventListener( 'click', openCart );
		}

		// Close on backdrop click
		if ( backdrop ) {
			backdrop.addEventListener( 'click', closeCart );
		}

		// Delegated events inside drawer
		document.addEventListener( 'click', function ( e ) {
			// Only process clicks on/inside the drawer
			if ( drawer && ( drawer === e.target || drawer.contains( e.target ) ) ) {
				handleDrawerClick( e );
			}
		} );

		// Escape key
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && drawer && drawer.classList.contains( 'is-open' ) ) {
				closeCart();
			}
		} );

		// WooCommerce jQuery events (wc-cart-fragments)
		if ( typeof jQuery !== 'undefined' ) {
			jQuery( document.body ).on( 'wc_fragments_refreshed', onWcFragmentsRefreshed );
			jQuery( document.body ).on( 'added_to_cart',          onAddedToCart );
		}
	} );

} )();
