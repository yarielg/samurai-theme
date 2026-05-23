/**
 * Samurai Fireworks — Theme JS
 * Transparent header scroll, hero slider, mobile drawer, search popup,
 * accordion, mega menu, cart badge.
 * Uses only vanilla JS — no jQuery dependency.
 */
( function () {
	'use strict';

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------
	function qs( sel, ctx )  { return ( ctx || document ).querySelector( sel ); }
	function qsa( sel, ctx ) { return [].slice.call( ( ctx || document ).querySelectorAll( sel ) ); }

	function lockScroll()   { document.body.style.overflow = 'hidden'; }
	function unlockScroll() { document.body.style.overflow = ''; }

	// -------------------------------------------------------------------------
	// Transparent header: adds .is-scrolled when user scrolls past threshold.
	// CSS handles the actual background transition.
	// -------------------------------------------------------------------------
	var sfHeader    = qs( '#sf-header' );
	var SCROLL_THRESH = 60;

	function onHeaderScroll() {
		if ( ! sfHeader ) return;
		sfHeader.classList.toggle( 'is-scrolled', window.scrollY > SCROLL_THRESH );
	}

	if ( sfHeader ) {
		window.addEventListener( 'scroll', onHeaderScroll, { passive: true } );
		onHeaderScroll(); // run on load in case page is already scrolled
	}

	// -------------------------------------------------------------------------
	// Hero Slider
	// -------------------------------------------------------------------------
	qsa( '.js-hero-slider' ).forEach( function ( sliderEl ) {
		var slides        = qsa( '.sf-hero__slide', sliderEl );
		var dots          = qsa( '.sf-hero__dot', sliderEl );
		var prevBtn       = qs( '.js-hero-prev', sliderEl );
		var nextBtn       = qs( '.js-hero-next', sliderEl );
		var current       = 0;
		var timer         = null;
		var autoDelay     = parseInt( sliderEl.getAttribute( 'data-autoplay' ), 10 ) || 5000;
		var reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		// Pass autoplay duration as a CSS variable so the dot progress animation
		// always matches the JS interval — even if the value changes.
		sliderEl.style.setProperty( '--sf-slide-duration', autoDelay + 'ms' );

		if ( slides.length < 2 ) {
			// Single slide: run first dot progress fill and stop.
			if ( dots[ 0 ] && ! reducedMotion ) {
				dots[ 0 ].classList.add( 'is-active', 'is-animating' );
			}
			return;
		}

		/**
		 * Restart the CSS fill animation on a dot.
		 * Forces a reflow between class removal and re-add so the browser
		 * resets the @keyframes rather than continuing mid-way.
		 */
		function restartDotAnimation( dot ) {
			if ( ! dot || reducedMotion ) return;
			dot.classList.remove( 'is-animating' );
			void dot.offsetWidth; // trigger reflow
			dot.classList.add( 'is-animating' );
		}

		function goTo( index ) {
			// Deactivate current
			slides[ current ].classList.remove( 'is-active' );
			slides[ current ].setAttribute( 'aria-hidden', 'true' );
			if ( dots[ current ] ) {
				dots[ current ].classList.remove( 'is-active', 'is-animating' );
				dots[ current ].setAttribute( 'aria-current', 'false' );
			}

			current = ( index + slides.length ) % slides.length;

			// Activate next
			slides[ current ].classList.add( 'is-active' );
			slides[ current ].setAttribute( 'aria-hidden', 'false' );
			if ( dots[ current ] ) {
				dots[ current ].classList.add( 'is-active' );
				dots[ current ].setAttribute( 'aria-current', 'true' );
				restartDotAnimation( dots[ current ] );
			}
		}

		function autoPlay() {
			if ( reducedMotion ) return;
			clearInterval( timer );
			timer = setInterval( function () { goTo( current + 1 ); }, autoDelay );
		}

		function pause() { clearInterval( timer ); }

		// Dot clicks
		dots.forEach( function ( dot ) {
			dot.addEventListener( 'click', function () {
				var idx = parseInt( dot.getAttribute( 'data-slide' ), 10 );
				goTo( idx );
				pause();
				autoPlay();
			} );
		} );

		if ( prevBtn ) prevBtn.addEventListener( 'click', function () { goTo( current - 1 ); pause(); autoPlay(); } );
		if ( nextBtn ) nextBtn.addEventListener( 'click', function () { goTo( current + 1 ); pause(); autoPlay(); } );

		// Pause on hover/focus
		sliderEl.addEventListener( 'mouseenter', pause );
		sliderEl.addEventListener( 'mouseleave', autoPlay );
		sliderEl.addEventListener( 'focusin',    pause );
		sliderEl.addEventListener( 'focusout',   autoPlay );

		// Touch/swipe support
		var touchStartX = 0;
		sliderEl.addEventListener( 'touchstart', function ( e ) {
			touchStartX = e.changedTouches[ 0 ].screenX;
			pause();
		}, { passive: true } );
		sliderEl.addEventListener( 'touchend', function ( e ) {
			var diff = touchStartX - e.changedTouches[ 0 ].screenX;
			if ( Math.abs( diff ) > 40 ) {
				goTo( diff > 0 ? current + 1 : current - 1 );
			}
			autoPlay();
		}, { passive: true } );

		autoPlay();
	} );

	// -------------------------------------------------------------------------
	// Mobile drawer
	// -------------------------------------------------------------------------
	var mobileMenu     = qs( '#sf-mobile-menu' );
	var mobileToggle   = qs( '.js-mobile-toggle' );
	var mobileClose    = qs( '.js-mobile-close' );
	var mobileBackdrop = qs( '.sf-mobile-menu__backdrop' );

	function openMobileMenu() {
		if ( ! mobileMenu ) return;
		mobileMenu.classList.add( 'is-open' );
		mobileMenu.setAttribute( 'aria-hidden', 'false' );
		if ( mobileBackdrop ) mobileBackdrop.classList.add( 'is-visible' );
		if ( mobileToggle )   mobileToggle.setAttribute( 'aria-expanded', 'true' );
		lockScroll();
		if ( mobileClose ) setTimeout( function () { mobileClose.focus(); }, 50 );
	}

	function closeMobileMenu() {
		if ( ! mobileMenu ) return;
		mobileMenu.classList.remove( 'is-open' );
		mobileMenu.setAttribute( 'aria-hidden', 'true' );
		if ( mobileBackdrop ) mobileBackdrop.classList.remove( 'is-visible' );
		if ( mobileToggle ) {
			mobileToggle.setAttribute( 'aria-expanded', 'false' );
			mobileToggle.focus();
		}
		unlockScroll();
	}

	if ( mobileToggle )   mobileToggle.addEventListener( 'click', openMobileMenu );
	if ( mobileClose )    mobileClose.addEventListener( 'click', closeMobileMenu );
	if ( mobileBackdrop ) mobileBackdrop.addEventListener( 'click', closeMobileMenu );

	// -------------------------------------------------------------------------
	// Mobile accordion (drawer) and footer accordion
	// -------------------------------------------------------------------------
	qsa( '.js-accordion-trigger' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			var expanded = trigger.getAttribute( 'aria-expanded' ) === 'true';
			var panel    = qs( '.sf-mobile-accordion__panel', trigger.parentElement );
			if ( ! panel ) return;
			trigger.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			panel.setAttribute( 'aria-hidden', expanded ? 'true' : 'false' );
			panel.classList.toggle( 'is-open', ! expanded );
		} );
	} );

	qsa( '.js-footer-accordion' ).forEach( function ( trigger ) {
		trigger.addEventListener( 'click', function () {
			if ( window.innerWidth >= 768 ) return;
			var expanded = trigger.getAttribute( 'aria-expanded' ) === 'true';
			var panelId  = trigger.getAttribute( 'aria-controls' );
			var panel    = panelId ? document.getElementById( panelId ) : null;
			if ( ! panel ) return;
			trigger.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
			panel.setAttribute( 'aria-hidden', expanded ? 'true' : 'false' );
			panel.classList.toggle( 'is-open', ! expanded );
		} );
	} );

	// -------------------------------------------------------------------------
	// Desktop mega menu (hover + click for accessibility)
	// -------------------------------------------------------------------------
	var megaTrigger  = qs( '.js-mega-trigger' );
	var megaPanel    = qs( '.js-mega-panel' );
	var megaBackdrop = qs( '.js-mega-backdrop' );
	var megaParent   = qs( '.js-mega-parent' );
	var megaOpen     = false;
	var megaTimer    = null;

	function openMega() {
		if ( ! megaPanel ) return;
		clearTimeout( megaTimer );
		megaOpen = true;
		megaPanel.classList.add( 'is-open' );
		megaPanel.setAttribute( 'aria-hidden', 'false' );
		if ( megaBackdrop ) megaBackdrop.classList.add( 'is-visible' );
		if ( megaTrigger )  megaTrigger.setAttribute( 'aria-expanded', 'true' );
	}

	function closeMega() {
		megaTimer = setTimeout( function () {
			if ( ! megaPanel ) return;
			megaOpen = false;
			megaPanel.classList.remove( 'is-open' );
			megaPanel.setAttribute( 'aria-hidden', 'true' );
			if ( megaBackdrop ) megaBackdrop.classList.remove( 'is-visible' );
			if ( megaTrigger )  megaTrigger.setAttribute( 'aria-expanded', 'false' );
		}, 150 );
	}

	if ( megaParent ) {
		megaParent.addEventListener( 'mouseenter', openMega );
		megaParent.addEventListener( 'mouseleave', closeMega );
	}

	if ( megaPanel ) {
		megaPanel.addEventListener( 'mouseenter', function () { clearTimeout( megaTimer ); } );
		megaPanel.addEventListener( 'mouseleave', closeMega );
	}

	if ( megaTrigger ) {
		megaTrigger.addEventListener( 'click', function () {
			if ( megaOpen ) { clearTimeout( megaTimer ); closeMega(); } else { openMega(); }
		} );
	}

	if ( megaBackdrop ) {
		megaBackdrop.addEventListener( 'click', function () {
			clearTimeout( megaTimer );
			megaOpen = false;
			if ( megaPanel ) { megaPanel.classList.remove( 'is-open' ); megaPanel.setAttribute( 'aria-hidden', 'true' ); }
			megaBackdrop.classList.remove( 'is-visible' );
			if ( megaTrigger ) megaTrigger.setAttribute( 'aria-expanded', 'false' );
		} );
	}

	// -------------------------------------------------------------------------
	// Desktop simple dropdowns (hover)
	// -------------------------------------------------------------------------
	qsa( '.js-dropdown-parent' ).forEach( function ( parent ) {
		var panel   = qs( '.js-dropdown-panel', parent );
		var trigger = qs( '.js-dropdown-trigger', parent );
		var timer;
		if ( ! panel ) return;

		function openDrop() {
			clearTimeout( timer );
			panel.classList.add( 'is-open' );
			panel.setAttribute( 'aria-hidden', 'false' );
			if ( trigger ) trigger.setAttribute( 'aria-expanded', 'true' );
		}

		function closeDrop() {
			timer = setTimeout( function () {
				panel.classList.remove( 'is-open' );
				panel.setAttribute( 'aria-hidden', 'true' );
				if ( trigger ) trigger.setAttribute( 'aria-expanded', 'false' );
			}, 120 );
		}

		parent.addEventListener( 'mouseenter', openDrop );
		parent.addEventListener( 'mouseleave', closeDrop );
		panel.addEventListener( 'mouseenter', function () { clearTimeout( timer ); } );
		panel.addEventListener( 'mouseleave', closeDrop );
		if ( trigger ) {
			trigger.addEventListener( 'click', function () {
				if ( panel.classList.contains( 'is-open' ) ) { clearTimeout( timer ); closeDrop(); } else { openDrop(); }
			} );
		}
	} );

	// -------------------------------------------------------------------------
	// Escape key closes all open panels
	// -------------------------------------------------------------------------
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Escape' ) return;
		if ( mobileMenu && mobileMenu.classList.contains( 'is-open' ) ) closeMobileMenu();
		if ( megaPanel  && megaPanel.classList.contains( 'is-open' ) )  { clearTimeout( megaTimer ); megaOpen = false; megaPanel.classList.remove( 'is-open' ); if ( megaBackdrop ) megaBackdrop.classList.remove( 'is-visible' ); if ( megaTrigger ) megaTrigger.setAttribute( 'aria-expanded', 'false' ); }
	} );

	// -------------------------------------------------------------------------
	// Cart count badge — updated from WooCommerce fragment events (via jQuery)
	// -------------------------------------------------------------------------
	function updateCartCount( n ) {
		qsa( '.js-cart-count' ).forEach( function ( el ) {
			var count = parseInt( n, 10 ) || 0;
			el.textContent = count;
			el.style.display = count > 0 ? '' : 'none';
			el.setAttribute( 'aria-hidden', count > 0 ? 'false' : 'true' );
		} );
	}

	// WooCommerce fires these events via jQuery after cart fragments refresh
	if ( window.jQuery ) {
		jQuery( document.body ).on( 'wc_fragments_loaded wc_fragments_refreshed', function () {
			if ( window.wc_cart_fragments_params && window.sessionStorage ) {
				try {
					var raw = sessionStorage.getItem( wc_cart_fragments_params.fragment_name );
					if ( raw ) {
						var frags  = JSON.parse( raw );
						var parser = new DOMParser();
						Object.values( frags ).forEach( function ( html ) {
							var doc   = parser.parseFromString( html, 'text/html' );
							var badge = doc.querySelector( '.sf-cart-badge' );
							if ( badge ) updateCartCount( badge.textContent.trim() );
						} );
					}
				} catch ( err ) { /* non-critical */ }
			}
		} );

		// Store original href before AJAX add-to-cart changes it
		jQuery( document.body ).on( 'adding_to_cart', function ( e, $btn ) {
			if ( ! $btn || ! $btn.hasClass( 'sf-product-card__atc' ) ) return;
			if ( ! $btn.data( 'sf-original-href' ) ) {
				$btn.data( 'sf-original-href', $btn.attr( 'href' ) );
			}
		} );

		// After AJAX add-to-cart: reset button to normal after 2.5 s
		jQuery( document.body ).on( 'added_to_cart', function ( e, fragments, hash, $btn ) {
			if ( ! $btn || ! $btn.hasClass( 'sf-product-card__atc' ) ) return;
			var origHref = $btn.data( 'sf-original-href' );
			setTimeout( function () {
				$btn.removeClass( 'added' );
				if ( origHref ) $btn.attr( 'href', origHref );
			}, 2500 );
		} );
	}

	// -------------------------------------------------------------------------
	// Click-outside closes mega menu
	// -------------------------------------------------------------------------
	document.addEventListener( 'click', function ( e ) {
		if ( megaPanel && megaPanel.classList.contains( 'is-open' ) ) {
			if ( ! megaPanel.contains( e.target ) && ! ( megaParent && megaParent.contains( e.target ) ) ) {
				clearTimeout( megaTimer );
				megaOpen = false;
				megaPanel.classList.remove( 'is-open' );
				if ( megaBackdrop ) megaBackdrop.classList.remove( 'is-visible' );
				if ( megaTrigger )  megaTrigger.setAttribute( 'aria-expanded', 'false' );
			}
		}
	} );

	// -------------------------------------------------------------------------
	// Product carousel — scroll, progress bar, glassmorphism arrows
	// -------------------------------------------------------------------------
	qsa( '.js-carousel' ).forEach( function ( wrap ) {
		var track   = qs( '.sf-carousel-track', wrap );
		var prevBtn = qs( '.js-carousel-prev', wrap );
		var nextBtn = qs( '.js-carousel-next', wrap );
		var bar     = qs( '.js-carousel-bar' );
		if ( ! track ) return;

		function scrollAmount() {
			var item = track.querySelector( '.sf-carousel-item' );
			var gap  = parseInt( getComputedStyle( track ).gap, 10 ) || 16;
			return item ? item.offsetWidth + gap : 280;
		}

		function updateState() {
			var max    = track.scrollWidth - track.clientWidth;
			var pos    = track.scrollLeft;
			var atEnd  = pos >= max - 4;
			var atStart = pos <= 4;

			if ( prevBtn ) prevBtn.hidden = atStart;
			if ( nextBtn ) nextBtn.hidden = atEnd;
			wrap.classList.toggle( 'sf-carousel--at-end', atEnd );

			if ( bar && max > 0 ) {
				bar.style.width = ( ( pos / max ) * 100 ).toFixed( 1 ) + '%';
			}
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				track.scrollBy( { left: -scrollAmount(), behavior: 'smooth' } );
			} );
		}
		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				track.scrollBy( { left: scrollAmount(), behavior: 'smooth' } );
			} );
		}

		track.addEventListener( 'scroll', updateState, { passive: true } );
		window.addEventListener( 'resize', updateState );
		updateState();
	} );

	// -------------------------------------------------------------------------
	// Product card — gallery swatch hover swaps main image
	// -------------------------------------------------------------------------
	qsa( '.sf-product-card' ).forEach( function ( card ) {
		var mainImg = qs( '.sf-product-card__img', card );
		var swatches = qsa( '.js-card-swatch', card );
		if ( ! mainImg || ! swatches.length ) return;

		var origSrc    = mainImg.src;
		var origSrcset = mainImg.srcset || '';

		swatches.forEach( function ( sw ) {
			sw.addEventListener( 'mouseenter', function () {
				var s = sw.getAttribute( 'data-src' );
				var ss = sw.getAttribute( 'data-srcset' );
				if ( s ) { mainImg.src = s; mainImg.srcset = ss || ''; }
			} );
			sw.addEventListener( 'mouseleave', function () {
				mainImg.src = origSrc;
				mainImg.srcset = origSrcset;
			} );
		} );
	} );

	// -------------------------------------------------------------------------
	// Dynamic scroll-padding-top (updates if header height changes on resize)
	// -------------------------------------------------------------------------
	function setScrollPadding() {
		if ( sfHeader ) {
			document.documentElement.style.scrollPaddingTop = sfHeader.offsetHeight + 8 + 'px';
		}
	}
	setScrollPadding();
	window.addEventListener( 'resize', setScrollPadding );

	// -------------------------------------------------------------------------
	// Spotlight slider — two-column product feature slider
	// -------------------------------------------------------------------------
	qsa( '.js-spotlight' ).forEach( function ( sliderEl ) {
		var slides  = qsa( '.sf-spotlight__slide', sliderEl );
		var dots    = qsa( '.sf-spotlight__dot', sliderEl );
		var prevBtn = qs( '.js-spotlight-prev', sliderEl );
		var nextBtn = qs( '.js-spotlight-next', sliderEl );
		var current = 0;
		var total   = slides.length;
		var reducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		if ( total < 1 ) return;

		function goTo( n ) {
			slides[ current ].classList.remove( 'is-active', 'is-entering' );
			slides[ current ].setAttribute( 'aria-hidden', 'true' );
			if ( dots[ current ] ) {
				dots[ current ].classList.remove( 'is-active' );
				dots[ current ].setAttribute( 'aria-current', 'false' );
			}

			current = ( n + total ) % total;

			slides[ current ].classList.add( 'is-active' );
			slides[ current ].setAttribute( 'aria-hidden', 'false' );
			if ( ! reducedMotion ) {
				slides[ current ].classList.add( 'is-entering' );
				setTimeout( function () { slides[ current ].classList.remove( 'is-entering' ); }, 450 );
			}
			if ( dots[ current ] ) {
				dots[ current ].classList.add( 'is-active' );
				dots[ current ].setAttribute( 'aria-current', 'true' );
			}
		}

		if ( prevBtn ) prevBtn.addEventListener( 'click', function () { goTo( current - 1 ); } );
		if ( nextBtn ) nextBtn.addEventListener( 'click', function () { goTo( current + 1 ); } );

		dots.forEach( function ( dot ) {
			dot.addEventListener( 'click', function () {
				var idx = parseInt( dot.getAttribute( 'data-slide' ), 10 );
				goTo( idx );
			} );
		} );

		// Touch/swipe
		var touchStartX = 0;
		sliderEl.addEventListener( 'touchstart', function ( e ) {
			touchStartX = e.changedTouches[ 0 ].screenX;
		}, { passive: true } );
		sliderEl.addEventListener( 'touchend', function ( e ) {
			var diff = touchStartX - e.changedTouches[ 0 ].screenX;
			if ( Math.abs( diff ) > 40 ) goTo( diff > 0 ? current + 1 : current - 1 );
		}, { passive: true } );
	} );

} )();
