( function () {
	'use strict';

	var cfg       = window.sfSearch || {};
	var ENDPOINT  = cfg.endpoint || '';
	var SHOP_URL  = cfg.shopUrl  || '/shop/';
	var NONCE     = cfg.nonce    || '';
	var LS_KEY    = 'sf_recent_searches';
	var MAX_RECENT = 8;

	var overlay       = document.getElementById( 'sf-search-overlay' );
	if ( ! overlay ) return;

	var input         = document.getElementById( 'sf-search-input' );
	var clearBtn      = overlay.querySelector( '.js-search-clear' );
	var backdrop      = overlay.querySelector( '.sf-search__backdrop' );
	var defaultPane   = document.getElementById( 'sf-search-default' );
	var loadingPane   = document.getElementById( 'sf-search-loading' );
	var resultsWrap   = document.getElementById( 'sf-search-results-wrap' );
	var resultsList   = document.getElementById( 'sf-search-results' );
	var emptyPane     = document.getElementById( 'sf-search-empty' );
	var allLink       = overlay.querySelector( '.js-search-all' );
	var querySpan     = overlay.querySelector( '.js-search-query' );
	var announce      = document.getElementById( 'sf-search-announce' );
	var recentSection = overlay.querySelector( '.js-search-recent-section' );
	var recentChips   = overlay.querySelector( '.js-search-recent-chips' );
	var clearRecent   = overlay.querySelector( '.js-search-clear-recent' );

	var toggleBtns    = document.querySelectorAll( '.js-search-toggle' );

	var debounceTimer  = null;
	var abortCtrl      = null;
	var focusedIdx     = -1;
	var currentQuery   = '';
	var currentResults = [];

	// ── Open / Close ──────────────────────────────────────────────────────────

	function openSearch() {
		overlay.classList.add( 'is-open' );
		overlay.setAttribute( 'aria-hidden', 'false' );
		toggleBtns.forEach( function ( b ) { b.setAttribute( 'aria-expanded', 'true' ); } );
		document.body.classList.add( 'sf-search-open' );
		showPane( defaultPane );
		renderRecentChips();
		if ( input ) setTimeout( function () { input.focus(); }, 60 );
	}

	function closeSearch() {
		overlay.classList.remove( 'is-open' );
		overlay.setAttribute( 'aria-hidden', 'true' );
		toggleBtns.forEach( function ( b ) { b.setAttribute( 'aria-expanded', 'false' ); } );
		document.body.classList.remove( 'sf-search-open' );
		if ( abortCtrl ) { abortCtrl.abort(); abortCtrl = null; }
		clearTimeout( debounceTimer );
		if ( toggleBtns[ 0 ] ) toggleBtns[ 0 ].focus();
		setTimeout( resetToDefault, 280 );
	}

	function resetToDefault() {
		if ( input ) input.value = '';
		if ( clearBtn ) clearBtn.hidden = true;
		currentQuery   = '';
		currentResults = [];
		focusedIdx     = -1;
		if ( resultsList ) resultsList.innerHTML = '';
		showPane( defaultPane );
	}

	// ── Pane switcher — uses CSS class (not hidden attr) for reliable toggling ──

	function showPane( pane ) {
		[ defaultPane, loadingPane, resultsWrap, emptyPane ].forEach( function ( p ) {
			if ( p ) p.classList.toggle( 'is-active', p === pane );
		} );
	}

	// ── Recent searches ───────────────────────────────────────────────────────

	function getRecent() {
		try { return JSON.parse( localStorage.getItem( LS_KEY ) ) || []; } catch ( e ) { return []; }
	}

	function saveRecent( term ) {
		var items = getRecent().filter( function ( t ) {
			return t.toLowerCase() !== term.toLowerCase();
		} );
		items.unshift( term );
		if ( items.length > MAX_RECENT ) items = items.slice( 0, MAX_RECENT );
		try { localStorage.setItem( LS_KEY, JSON.stringify( items ) ); } catch ( e ) {}
	}

	function renderRecentChips() {
		if ( ! recentSection || ! recentChips ) return;
		var items = getRecent();
		if ( ! items.length ) { recentSection.hidden = true; return; }
		recentSection.hidden = false;
		recentChips.innerHTML = items.map( function ( t ) {
			return '<button type="button" class="sf-search__chip js-search-chip" data-term="' + esc( t ) + '">' +
				'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>' +
				escHtml( t ) + '</button>';
		} ).join( '' );
		recentChips.querySelectorAll( '.js-search-chip' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var term = btn.dataset.term;
				if ( input ) input.value = term;
				if ( clearBtn ) clearBtn.hidden = false;
				runSearch( term );
			} );
		} );
	}

	// ── Search ────────────────────────────────────────────────────────────────

	function runSearch( query ) {
		currentQuery = query;
		if ( abortCtrl ) abortCtrl.abort();
		abortCtrl = new AbortController();
		showPane( loadingPane );
		focusedIdx = -1;

		fetch( ENDPOINT + '?q=' + encodeURIComponent( query ) + '&per_page=8', {
			signal:  abortCtrl.signal,
			headers: NONCE ? { 'X-WP-Nonce': NONCE } : {},
		} )
		.then( function ( r ) { return r.json(); } )
		.then( function ( data ) {
			abortCtrl      = null;
			currentResults = data.results || [];
			if ( ! currentResults.length ) {
				showPane( emptyPane );
				if ( announce ) announce.textContent = 'No results found for ' + query + '.';
				return;
			}
			renderResults( query, data.total || currentResults.length );
		} )
		.catch( function ( err ) {
			if ( err.name === 'AbortError' ) return;
			abortCtrl = null;
			showPane( emptyPane );
		} );
	}

	function renderResults( query, total ) {
		if ( ! resultsList ) return;

		resultsList.innerHTML = currentResults.map( function ( p, i ) {
			var atcBtn = '';
			if ( p.purchasable && p.add_to_cart_url ) {
				atcBtn = '<button type="button" class="sf-search__atc js-search-atc" ' +
					'data-id="' + parseInt( p.id, 10 ) + '" ' +
					'aria-label="Add ' + escHtml( p.title ) + ' to cart">' +
					'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>' +
					'<svg class="sf-search__atc-check" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>' +
					'</button>';
			}

			// Inline styles on the <li> guarantee flex layout regardless of WC/Porto-child overrides
			return '<li class="sf-search__result" role="option" aria-selected="false" id="sf-result-' + i + '" tabindex="-1" data-url="' + esc( p.url ) + '" ' +
				'style="display:flex!important;flex-wrap:nowrap!important;align-items:center;gap:16px;padding:12px 20px;cursor:pointer;list-style:none;box-sizing:border-box;">' +
				'<div class="sf-search__result-thumb" style="width:56px!important;height:56px!important;flex:0 0 56px!important;border-radius:8px;overflow:hidden;background:#f3f4f6;display:flex;align-items:center;justify-content:center;">' +
				( p.thumb ? '<img src="' + esc( p.thumb ) + '" alt="" width="56" height="56" loading="lazy" style="width:100%;height:100%;object-fit:cover;display:block;">' : '' ) +
				'</div>' +
				'<div class="sf-search__result-body" style="flex:1 1 0!important;min-width:0;display:flex;flex-direction:column;gap:3px;">' +
				'<span class="sf-search__result-title">' + escHtml( p.title ) + '</span>' +
				( p.category ? '<span class="sf-search__result-cat">' + escHtml( p.category ) + '</span>' : '' ) +
				'</div>' +
				'<div class="sf-search__result-right" style="display:flex!important;flex-direction:column;align-items:flex-end;gap:6px;flex:0 0 auto!important;">' +
				( p.price_html ? '<div class="sf-search__result-price">' + p.price_html + '</div>' : '' ) +
				atcBtn +
				'</div>' +
				'</li>';
		} ).join( '' );

		var shopQ = SHOP_URL + '?s=' + encodeURIComponent( query ) + '&post_type=product';
		if ( allLink )   allLink.href         = shopQ;
		if ( querySpan ) querySpan.textContent = query;

		showPane( resultsWrap );

		var n    = currentResults.length;
		var desc = ( total > n ? 'Showing ' + n + ' of ' + total : n ) +
		           ' result' + ( n !== 1 ? 's' : '' ) + ' for "' + query + '"';
		if ( announce ) announce.textContent = desc;

		// Navigate to product page on row click (not on the ATC button)
		resultsList.querySelectorAll( '.sf-search__result' ).forEach( function ( li ) {
			li.addEventListener( 'click', function ( e ) {
				if ( e.target.closest( '.js-search-atc' ) ) return;
				var url = li.dataset.url;
				if ( url ) { saveRecent( query ); window.location.href = url; }
			} );
		} );

		// Add-to-cart buttons
		resultsList.querySelectorAll( '.js-search-atc' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function ( e ) {
				e.stopPropagation();
				addToCart( btn.dataset.id, btn );
			} );
		} );
	}

	// ── Add to cart ───────────────────────────────────────────────────────────

	function addToCart( productId, btn ) {
		btn.disabled = true;
		btn.classList.add( 'is-loading' );

		var body = new URLSearchParams();
		body.append( 'product_id', productId );
		body.append( 'quantity', '1' );

		fetch( '/?wc-ajax=add_to_cart', {
			method:  'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:    body.toString(),
		} )
		.then( function ( r ) { return r.json(); } )
		.then( function ( data ) {
			btn.classList.remove( 'is-loading' );
			if ( ! data.error ) {
				btn.classList.add( 'is-added' );
				// Bump the header cart badge immediately
				document.querySelectorAll( '.js-cart-count' ).forEach( function ( el ) {
					var n = ( parseInt( el.textContent, 10 ) || 0 ) + 1;
					el.textContent = n;
					el.style.display = '';
					el.setAttribute( 'aria-hidden', 'false' );
					el.classList.remove( 'sf-cart-badge--empty' );
				} );
				setTimeout( function () {
					btn.classList.remove( 'is-added' );
					btn.disabled = false;
				}, 2200 );
			} else {
				btn.disabled = false;
			}
		} )
		.catch( function () {
			btn.classList.remove( 'is-loading' );
			btn.disabled = false;
		} );
	}

	// ── Keyboard navigation ───────────────────────────────────────────────────

	function getItems() {
		return resultsList
			? Array.prototype.slice.call( resultsList.querySelectorAll( '.sf-search__result' ) )
			: [];
	}

	function setFocused( idx ) {
		var items = getItems();
		items.forEach( function ( li, i ) {
			var on = ( i === idx );
			li.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			li.classList.toggle( 'is-focused', on );
		} );
		focusedIdx = idx;
		if ( input ) {
			if ( idx >= 0 ) {
				input.setAttribute( 'aria-activedescendant', 'sf-result-' + idx );
			} else {
				input.removeAttribute( 'aria-activedescendant' );
			}
		}
	}

	// ── Input handling ────────────────────────────────────────────────────────

	if ( input ) {
		input.addEventListener( 'input', function () {
			var val = input.value.trim();
			if ( clearBtn ) clearBtn.hidden = ! val;
			clearTimeout( debounceTimer );
			if ( ! val || val.length < 2 ) {
				if ( abortCtrl ) { abortCtrl.abort(); abortCtrl = null; }
				showPane( defaultPane );
				return;
			}
			debounceTimer = setTimeout( function () { runSearch( val ); }, 300 );
		} );

		input.addEventListener( 'keydown', function ( e ) {
			var items = getItems();

			if ( e.key === 'Escape' ) {
				e.preventDefault();
				closeSearch();
				return;
			}

			if ( ! items.length ) return;

			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				setFocused( Math.min( focusedIdx + 1, items.length - 1 ) );
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				var next = Math.max( focusedIdx - 1, -1 );
				setFocused( next );
			} else if ( e.key === 'Enter' ) {
				if ( focusedIdx >= 0 ) {
					e.preventDefault();
					var url = items[ focusedIdx ].dataset.url;
					if ( url ) { saveRecent( currentQuery ); window.location.href = url; }
				} else if ( currentQuery ) {
					e.preventDefault();
					saveRecent( currentQuery );
					window.location.href = SHOP_URL + '?s=' + encodeURIComponent( currentQuery ) + '&post_type=product';
				}
			}
		} );
	}

	// ── Clear button ──────────────────────────────────────────────────────────

	if ( clearBtn ) {
		clearBtn.addEventListener( 'click', function () {
			if ( input ) { input.value = ''; input.focus(); }
			clearBtn.hidden = true;
			if ( abortCtrl ) { abortCtrl.abort(); abortCtrl = null; }
			clearTimeout( debounceTimer );
			showPane( defaultPane );
		} );
	}

	// ── Close buttons + backdrop ──────────────────────────────────────────────

	overlay.querySelectorAll( '.js-search-close' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', closeSearch );
	} );

	if ( backdrop ) backdrop.addEventListener( 'click', closeSearch );

	// ── Clear recent ──────────────────────────────────────────────────────────

	if ( clearRecent ) {
		clearRecent.addEventListener( 'click', function () {
			try { localStorage.removeItem( LS_KEY ); } catch ( e ) {}
			if ( recentSection ) recentSection.hidden = true;
		} );
	}

	// ── Toggle buttons (header search icon) ───────────────────────────────────

	toggleBtns.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			overlay.classList.contains( 'is-open' ) ? closeSearch() : openSearch();
		} );
	} );

	// ── Global Escape ─────────────────────────────────────────────────────────

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && overlay.classList.contains( 'is-open' ) ) closeSearch();
	} );

	// ── Helpers ───────────────────────────────────────────────────────────────

	function escHtml( s ) {
		return String( s )
			.replace( /&/g,  '&amp;'  )
			.replace( /</g,  '&lt;'   )
			.replace( />/g,  '&gt;'   )
			.replace( /"/g,  '&quot;' );
	}

	function esc( s ) {
		return String( s ).replace( /"/g, '&quot;' ).replace( /'/g, '&#39;' );
	}

}() );
