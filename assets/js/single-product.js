/* Single Product v2 — Samurai Fireworks */
(function () {
  'use strict';

  var qs  = function (s, c) { return (c || document).querySelector(s); };
  var qsa = function (s, c) { return (c || document).querySelectorAll(s); };

  // ── Cart icon injected into ATC button ─────────────────────────────────────
  function injectAtcIcon() {
    var btn = qs('.sf-sp-cart-wrap .single_add_to_cart_button');
    if (!btn || btn.querySelector('.sf-sp-atc-icon')) return;
    var icon = document.createElement('span');
    icon.className = 'sf-sp-atc-icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>';
    btn.insertBefore(icon, btn.firstChild);
  }

  // ── Quantity stepper ────────────────────────────────────────────────────────
  function initQtyStepper() {
    var input = qs('.sf-sp-cart-wrap input.qty');
    if (!input) return;
    var wrap = input.closest('.quantity');
    if (!wrap) return;

    var min = parseInt(input.getAttribute('min') || '1', 10);
    var max = parseInt(input.getAttribute('max') || '9999', 10);

    function mkBtn(cls, label, html) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'sf-qty-btn ' + cls;
      b.setAttribute('aria-label', label);
      b.innerHTML = html;
      return b;
    }

    var minus = mkBtn('sf-qty-btn--minus', 'Decrease quantity',
      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>');
    var plus  = mkBtn('sf-qty-btn--plus', 'Increase quantity',
      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>');

    wrap.insertBefore(minus, input);
    wrap.appendChild(plus);

    function sync() { minus.disabled = parseInt(input.value, 10) <= min; }
    sync();

    minus.addEventListener('click', function () {
      var v = parseInt(input.value, 10) || min;
      if (v > min) { input.value = v - 1; input.dispatchEvent(new Event('change', { bubbles: true })); sync(); }
    });
    plus.addEventListener('click', function () {
      var v = parseInt(input.value, 10) || min;
      if (v < max) { input.value = v + 1; input.dispatchEvent(new Event('change', { bubbles: true })); }
    });
  }

  // ── Sticky bottom bar ───────────────────────────────────────────────────────
  function initStickyBar() {
    var cartWrap = qs('.sf-sp-cart-wrap');
    if (!cartWrap) return;
    var titleEl = qs('.sf-sp-summary .product_title');
    var priceEl = qs('.sf-sp-summary .price');
    if (!titleEl) return;

    var bar = document.createElement('div');
    bar.className = 'sf-sp-sticky-bar';
    bar.innerHTML =
      '<div class="sf-container">' +
        '<div class="sf-sp-sticky-bar__inner">' +
          '<p class="sf-sp-sticky-bar__title">' + titleEl.textContent.trim() + '</p>' +
          '<span class="sf-sp-sticky-bar__price">' + (priceEl ? priceEl.innerHTML : '') + '</span>' +
          '<button type="button" class="sf-sp-sticky-bar__btn js-sticky-atc">' +
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>' +
            'Add to Cart' +
          '</button>' +
        '</div>' +
      '</div>';

    document.body.appendChild(bar);

    bar.querySelector('.js-sticky-atc').addEventListener('click', function () {
      cartWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
      var btn = qs('.single_add_to_cart_button', cartWrap);
      if (btn) setTimeout(function () { btn.focus(); }, 500);
    });

    function updateStickyBar() {
      var rect = cartWrap.getBoundingClientRect();
      var inView = rect.bottom > 0 && rect.top < window.innerHeight;
      bar.classList.toggle('is-visible', !inView);
    }

    window.addEventListener('scroll', updateStickyBar, { passive: true });
    updateStickyBar();
  }

  // ── Move video trigger inside main gallery image (avoids thumbnail overlap) ─
  function positionVideoTrigger() {
    var trigger = qs('.js-video-trigger');
    if (!trigger) return;
    // WC renders: .woocommerce-product-gallery > .woocommerce-product-gallery__image (first item)
    var mainImgDiv = qs('.woocommerce-product-gallery .woocommerce-product-gallery__image');
    if (mainImgDiv) {
      mainImgDiv.style.position = 'relative';
      mainImgDiv.appendChild(trigger);
    }
  }

  // ── YouTube video modal ─────────────────────────────────────────────────────
  function initVideoModal() {
    var trigger = qs('.js-video-trigger');
    var modal   = qs('#sf-video-modal');
    if (!trigger || !modal) return;

    var iframe = qs('#sf-video-iframe');

    function openModal() {
      if (iframe) iframe.src = iframe.getAttribute('data-src');
      modal.removeAttribute('hidden');
      document.body.style.overflow = 'hidden';
      modal.querySelector('.sf-video-modal__close').focus();
    }

    function closeModal() {
      modal.setAttribute('hidden', '');
      document.body.style.overflow = '';
      if (iframe) iframe.src = '';
      trigger.focus();
    }

    trigger.addEventListener('click', openModal);

    qsa('.js-video-close').forEach(function (el) {
      el.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !modal.hasAttribute('hidden')) closeModal();
    });
  }

  // ── Toast notification ──────────────────────────────────────────────────────
  var toastTimer = null;

  function showToast(productName) {
    var toast    = qs('#sf-sp-toast');
    var nameEl   = qs('.js-toast-product-name');
    var progress = qs('.js-toast-progress');
    var closeBtn = qs('.js-toast-close');
    if (!toast) return;

    if (nameEl && productName) nameEl.textContent = productName;

    clearTimeout(toastTimer);
    toast.removeAttribute('hidden');
    // Force reflow so transition runs
    void toast.offsetHeight;
    toast.classList.add('is-visible');

    // Reset + restart progress bar animation (4 s)
    if (progress) {
      progress.style.animationDuration = '';
      progress.style.animation = 'none';
      void progress.offsetHeight;
      progress.style.animation = 'sf-toast-progress 4s linear forwards';
    }

    toastTimer = setTimeout(function () { hideToast(); }, 4000);

    if (closeBtn) {
      closeBtn.onclick = function () { clearTimeout(toastTimer); hideToast(); };
    }
  }

  function hideToast() {
    var toast = qs('#sf-sp-toast');
    if (!toast) return;
    toast.classList.remove('is-visible');
    setTimeout(function () { toast.setAttribute('hidden', ''); }, 400);
  }

  // On page load: if WC added a notice (after form POST), intercept it.
  function interceptWcNotice() {
    // WC outputs its notice inside .woocommerce-notices-wrapper or directly.
    var notice = qs('.woocommerce-message');
    if (!notice) return;

    // Extract product name from the notice text (everything after the "View cart" link).
    var text = notice.textContent.trim().replace(/View cart/i, '').trim().replace(/^"(.*)".*$/, '$1');
    showToast(text || '');
    notice.style.display = 'none';
  }

  // Also hook jQuery WC added_to_cart event (for future AJAX scenarios).
  function initJqueryHook() {
    if (typeof jQuery === 'undefined') return;
    jQuery(document.body).on('added_to_cart', function (e, fragments, hash, $btn) {
      var name = '';
      if ($btn && $btn.length) {
        var card = $btn[0].closest('.sf-product-card, article.product');
        if (card) {
          var titleEl = card.querySelector('.sf-product-card__title a, .woocommerce-loop-product__title');
          if (titleEl) name = titleEl.textContent.trim();
        }
      }
      showToast(name);
    });
  }

  // ── Description clamp with Read more toggle ─────────────────────────────────
  function initDescriptionClamp() {
    var panel = document.getElementById('tab-description');
    if (!panel) return;

    var CLAMP = 320;
    if (panel.scrollHeight <= CLAMP + 60) return; // short descriptions: do nothing

    panel.classList.add('sf-desc-clamped');

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'sf-desc-toggle';
    btn.innerHTML =
      '<span class="sf-desc-toggle__label">Read more</span>' +
      '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>';
    panel.parentNode.insertBefore(btn, panel.nextSibling);

    btn.addEventListener('click', function () {
      var expanded = panel.classList.toggle('sf-desc-expanded');
      btn.querySelector('.sf-desc-toggle__label').textContent = expanded ? 'Show less' : 'Read more';
      btn.querySelector('svg').style.transform = expanded ? 'rotate(180deg)' : '';
    });
  }

  // ── Init ────────────────────────────────────────────────────────────────────
  function init() {
    positionVideoTrigger(); // must run before initVideoModal (re-queries trigger)
    injectAtcIcon();
    initQtyStepper();
    initStickyBar();
    initVideoModal();
    interceptWcNotice();
    initJqueryHook();
    initDescriptionClamp();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
