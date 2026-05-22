(function () {
	'use strict';

	var form     = document.getElementById('sf-checkout-form');
	var stepsNav = document.querySelector('.sf-steps');

	if (!form || !stepsNav) return;

	var OFFSCREEN = 'sf-co-panel--offscreen';

	// ── Panel switching ───────────────────────────────────────────────────────

	function showPanel(n) {
		form.querySelectorAll('.js-co-panel').forEach(function (p) {
			var num = parseInt(p.dataset.panel, 10);
			if (num === n) {
				p.classList.remove(OFFSCREEN);
				p.removeAttribute('aria-hidden');
			} else {
				p.classList.add(OFFSCREEN);
				p.setAttribute('aria-hidden', 'true');
			}
		});

		// Panel 1 = checkout step 2 (Shipping), panel 2 = step 3 (Payment)
		updateStepIndicator(n + 1);
		window.scrollTo({ top: 0, behavior: 'smooth' });
	}

	// ── Step indicator ────────────────────────────────────────────────────────

	function updateStepIndicator(step) {
		stepsNav.dataset.current = step;

		stepsNav.querySelectorAll('.sf-steps__item').forEach(function (item) {
			var s = parseInt(item.dataset.stepItem, 10);
			item.classList.remove('is-active', 'is-complete');
			item.setAttribute('aria-current', 'false');

			if (s < step) {
				item.classList.add('is-complete');
			} else if (s === step) {
				item.classList.add('is-active');
				item.setAttribute('aria-current', 'step');
			}
		});

		stepsNav.querySelectorAll('.sf-steps__line').forEach(function (line, idx) {
			if (idx + 2 <= step) {
				line.classList.add('is-passed');
			} else {
				line.classList.remove('is-passed');
			}
		});
	}

	// ── Field validation helpers ──────────────────────────────────────────────

	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

	function setFieldError(f, row, msg) {
		row.classList.add('woocommerce-invalid', 'woocommerce-invalid-required-field');
		f.style.setProperty('border-color', 'var(--sf-color-error, #e2292b)');

		var err = row.querySelector('.sf-field-error');
		if (!err) {
			err = document.createElement('span');
			err.className = 'sf-field-error';
			var wrapper = f.closest('.woocommerce-input-wrapper') || f.parentNode;
			wrapper.insertAdjacentElement('afterend', err);
		}
		err.textContent = msg;
	}

	function clearFieldError(f, row) {
		row.classList.remove('woocommerce-invalid', 'woocommerce-invalid-required-field');
		f.style.removeProperty('border-color');
		var err = row.querySelector('.sf-field-error');
		if (err) err.remove();
	}

	// ── Validation (contact fields only) ─────────────────────────────────────

	function validatePanel1() {
		var fields = [
			{ name: 'billing_first_name', msg: 'First name is required.' },
			{ name: 'billing_last_name',  msg: 'Last name is required.' },
			{ name: 'billing_email',      msg: 'Email address is required.' },
			{ name: 'billing_phone',      msg: 'Phone number is required.' },
		];

		var valid    = true;
		var firstBad = null;

		fields.forEach(function (cfg) {
			var f = form.querySelector('[name="' + cfg.name + '"]');
			if (!f) return;

			var row = f.closest('.form-row') || f.parentNode;
			var val = f.value.trim();
			var empty = !val;
			var badEmail = (cfg.name === 'billing_email') && !empty && !EMAIL_RE.test(val);

			if (empty || badEmail) {
				var msg = badEmail ? 'Please enter a valid email address.' : cfg.msg;
				setFieldError(f, row, msg);
				if (!firstBad) firstBad = f;
				valid = false;
			} else {
				clearFieldError(f, row);
			}

			// Live clear on input (attach once)
			if (!f.dataset.sfValidating) {
				f.dataset.sfValidating = '1';
				f.addEventListener('input', function () {
					var v   = f.value.trim();
					var bad = !v || ((cfg.name === 'billing_email') && !EMAIL_RE.test(v));
					if (!bad) clearFieldError(f, row);
				});
			}
		});

		if (firstBad) firstBad.focus();
		return valid;
	}

	// ── Order summary toggle ──────────────────────────────────────────────────

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.js-co-summary-toggle');
		if (!btn) return;

		var expanded = btn.getAttribute('aria-expanded') === 'true';
		var body     = document.getElementById(btn.getAttribute('aria-controls'));

		btn.setAttribute('aria-expanded', String(!expanded));
		if (body) body.setAttribute('aria-hidden', String(expanded));

		btn.querySelectorAll('.sf-co-summary__left').forEach(function (el) {
			el.childNodes.forEach(function (node) {
				if (node.nodeType === 3 && node.textContent.trim()) {
					node.textContent = expanded ? ' Show order summary ' : ' Hide order summary ';
				}
			});
		});
	});

	// ── Order review refresh ──────────────────────────────────────────────────
	// Sends the current shipping method to WC's update_order_review endpoint and
	// applies the returned fragments (toggle total, applied coupons, order table).

	function sfRefreshCheckout() {
		var nonce = window.sfCheckout && sfCheckout.orderReviewNonce;
		if (!nonce) return;

		var params = new URLSearchParams();
		params.append('security', nonce);

		// Collect all shipping method inputs (hidden = single method, radio = multi)
		form.querySelectorAll('input[name^="shipping_method"]').forEach(function (input) {
			if (input.type === 'hidden' || (input.type === 'radio' && input.checked)) {
				params.append(input.name, input.value);
			}
		});

		fetch('/?wc-ajax=update_order_review', {
			method:      'POST',
			credentials: 'same-origin',
			headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:        params.toString(),
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				if (!data || !data.fragments) return;
				Object.keys(data.fragments).forEach(function (sel) {
					var el = document.querySelector(sel);
					if (el) el.outerHTML = data.fragments[sel];
				});
			})
			.catch(function () {});
	}

	// Refresh totals whenever the user picks a different delivery method
	document.addEventListener('change', function (e) {
		var input = e.target.closest('input[name^="shipping_method"]');
		if (input) sfRefreshCheckout();
	});

	// ── Coupon drawer ─────────────────────────────────────────────────────────

	function setFeedback(el, success, msg) {
		if (!el) return;
		el.textContent   = msg;
		el.style.display = 'block';
		el.className     = 'sf-coupon-feedback js-coupon-feedback ' + (success ? 'is-success' : 'is-error');
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.js-coupon-toggle');
		if (!btn) return;
		var expanded = btn.getAttribute('aria-expanded') === 'true';
		var body     = document.getElementById(btn.getAttribute('aria-controls'));
		btn.setAttribute('aria-expanded', String(!expanded));
		if (body) body.setAttribute('aria-hidden', String(expanded));
	});

	function submitCoupon(couponDiv) {
		var input    = couponDiv.querySelector('.js-coupon-input');
		var nonceEl  = couponDiv.querySelector('.js-coupon-nonce');
		var section  = couponDiv.closest('.sf-co-coupon-section, .sf-coupon-section');
		var feedback = section ? section.querySelector('.js-coupon-feedback') : null;
		var code     = input ? input.value.trim() : '';

		if (!code) {
			setFeedback(feedback, false, (window.sfCheckout && sfCheckout.i18n.enterCode) || 'Please enter a promo code.');
			return;
		}

		var nonce = (nonceEl && nonceEl.value) || (window.sfCheckout && sfCheckout.couponNonce) || '';
		var url   = (window.sfCheckout && sfCheckout.ajaxUrl) || '/wp-admin/admin-ajax.php';

		var params = new URLSearchParams();
		params.append('action',      'sf_apply_coupon');
		params.append('coupon_code', code);
		params.append('security',    nonce);

		fetch(url, {
			method:      'POST',
			credentials: 'same-origin',
			headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:        params.toString(),
		})
			.then(function (r) { return r.json(); })
			.then(function (data) {
				setFeedback(feedback, data.success, data.data && data.data.message ? data.data.message : (data.success ? 'Coupon applied.' : 'Invalid coupon.'));
				if (data.success) {
					if (input) input.value = '';
					sfRefreshCheckout();
				}
			})
			.catch(function () {
				setFeedback(feedback, false, (window.sfCheckout && sfCheckout.i18n.error) || 'Something went wrong.');
			});
	}

	// Click "Apply" button
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.js-coupon-submit');
		if (!btn) return;
		var couponDiv = btn.closest('.js-coupon-form');
		if (couponDiv) submitCoupon(couponDiv);
	});

	// Enter key in coupon input
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Enter') return;
		var input = e.target.closest('.js-coupon-input');
		if (!input) return;
		e.preventDefault();
		var couponDiv = input.closest('.js-coupon-form');
		if (couponDiv) submitCoupon(couponDiv);
	});

	// ── Coupon remove ─────────────────────────────────────────────────────────

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.js-coupon-remove');
		if (!btn) return;

		var code  = btn.dataset.coupon || '';
		var nonce = (window.sfCheckout && sfCheckout.couponNonce) || '';
		var url   = (window.sfCheckout && sfCheckout.ajaxUrl) || '/wp-admin/admin-ajax.php';

		var params = new URLSearchParams();
		params.append('action',      'sf_remove_coupon');
		params.append('coupon_code', code);
		params.append('security',    nonce);

		btn.disabled = true;

		fetch(url, {
			method:      'POST',
			credentials: 'same-origin',
			headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
			body:        params.toString(),
		})
			.then(function (r) { return r.json(); })
			.then(function () { sfRefreshCheckout(); })
			.catch(function () { btn.disabled = false; });
	});

	// ── Panel navigation ──────────────────────────────────────────────────────

	document.addEventListener('click', function (e) {
		var nextBtn = e.target.closest('.js-co-next');
		if (nextBtn) {
			if (!validatePanel1()) return;
			showPanel(parseInt(nextBtn.dataset.next, 10));
			return;
		}

		var backBtn = e.target.closest('.js-co-back');
		if (backBtn) {
			showPanel(parseInt(backBtn.dataset.back, 10));
		}
	});

	// ── Init ─────────────────────────────────────────────────────────────────

	showPanel(1);  // panel 1 visible, panel 2 off-screen; step indicator → Shipping

})();
