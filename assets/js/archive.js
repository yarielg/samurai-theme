/* Product Archive — Samurai Fireworks */
(function () {
  'use strict';

  var qs  = function (s, c) { return (c || document).querySelector(s); };
  var qsa = function (s, c) { return (c || document).querySelectorAll(s); };

  // ── Accordion filter groups ─────────────────────────────────────────────────
  qsa('.js-filter-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var group   = btn.closest('.js-filter-group');
      var panel   = group.querySelector('.sf-filter-group__panel');
      var isOpen  = group.classList.contains('is-open');

      group.classList.toggle('is-open', !isOpen);
      btn.setAttribute('aria-expanded', String(!isOpen));

      if (isOpen) {
        panel.setAttribute('hidden', '');
      } else {
        panel.removeAttribute('hidden');
      }
    });
  });

  // ── Mobile filter sidebar open / close ─────────────────────────────────────
  var sidebar   = document.getElementById('sf-archive-sidebar');
  var backdrop  = qs('.js-filter-backdrop');
  var openBtn   = qs('.js-filter-open');
  var closeBtns = qsa('.js-filter-close');

  function openFilters() {
    if (!sidebar) return;
    sidebar.classList.add('is-open');
    if (backdrop) { backdrop.removeAttribute('hidden'); }
    if (openBtn)  { openBtn.setAttribute('aria-expanded', 'true'); }
    document.body.style.overflow = 'hidden';
  }

  function closeFilters() {
    if (!sidebar) return;
    sidebar.classList.remove('is-open');
    if (backdrop) { backdrop.setAttribute('hidden', ''); }
    if (openBtn)  { openBtn.setAttribute('aria-expanded', 'false'); }
    document.body.style.overflow = '';
  }

  if (openBtn) openBtn.addEventListener('click', openFilters);

  closeBtns.forEach(function (btn) {
    btn.addEventListener('click', closeFilters);
  });

  if (backdrop) backdrop.addEventListener('click', closeFilters);

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
      closeFilters();
    }
  });

  // On desktop the sidebar is always visible — make sure body scroll is unlocked
  // if the window is resized past the breakpoint while sidebar is open.
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 1024 && sidebar && sidebar.classList.contains('is-open')) {
      closeFilters();
    }
  });

  // ── Sort select → navigate with orderby param ───────────────────────────────
  var sortSelect = qs('.js-sort-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', function () {
      var url = new URL(window.location.href);
      url.searchParams.set('orderby', this.value);
      url.searchParams.delete('paged');
      url.searchParams.delete('page');
      window.location.href = url.toString();
    });
  }

})();
