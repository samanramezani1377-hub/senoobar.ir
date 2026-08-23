(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(initBuyBox);

  function initBuyBox() {
    var box = document.querySelector('.pd-buy-box');
    if (!box) return;

    var form = box.querySelector('form.cart, form.variations_form');
    var btn = box.querySelector('.single_add_to_cart_button');
    if (!form || !btn) return;

    var productId = 0;
    var pidInput = form.querySelector('input[name="add-to-cart"]');
    if (pidInput && pidInput.value) productId = pidInput.value;
    if (!productId) {
      var pidBtn = form.querySelector('button[name="add-to-cart"]');
      if (pidBtn && pidBtn.value) productId = pidBtn.value;
    }
    if (!productId && form.dataset.product_id) productId = form.dataset.product_id;

    var qtyHidden = box.querySelector('#pdHiddenQty') || form.querySelector('input[name="quantity"]');
    var qty = 1;

    var stepper = document.createElement('div');
    stepper.className = 'pd-buy-stepper';
    stepper.style.display = 'none';

    var minusBtn = document.createElement('button');
    minusBtn.type = 'button'; minusBtn.className = 'pd-step-btn'; minusBtn.textContent = '−';
    var qtyShow = document.createElement('span');
    qtyShow.className = 'pd-step-qty'; qtyShow.textContent = '1';
    var plusBtn = document.createElement('button');
    plusBtn.type = 'button'; plusBtn.className = 'pd-step-btn'; plusBtn.textContent = '+';
    var removeBtn = document.createElement('button');
    removeBtn.type = 'button'; removeBtn.className = 'pd-step-remove'; removeBtn.title = 'حذف';
    removeBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>';

    stepper.appendChild(minusBtn);
    stepper.appendChild(qtyShow);
    stepper.appendChild(plusBtn);
    stepper.appendChild(removeBtn);
    btn.parentNode.insertBefore(stepper, btn);

    function variantId() {
      var variationId = form.querySelector('input.variation_id');
      return (variationId && variationId.value && variationId.value !== '0') ? variationId.value : 0;
    }

    function setQty(n) {
      qty = Math.max(1, n);
      qtyShow.textContent = String(qty);
      if (qtyHidden) qtyHidden.value = String(qty);
    }

    function showButton() { stepper.style.display = 'none'; btn.style.display = ''; }
    function showStepper() { btn.style.display = 'none'; stepper.style.display = 'inline-flex'; }

    function postAjax(action, data) {
      var body = new URLSearchParams();
      body.set('action', action);
      body.set('nonce', senoobarData.nonce);
      for (var k in data) body.set(k, data[k]);
      return fetch(senoobarData.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      }).then(function (r) {
        return r.json().then(function (json) {
          if (!r.ok || !json || json.success !== true) {
            var msg = json && json.data && json.data.message ? json.data.message : 'افزودن به سبد خرید انجام نشد.';
            throw new Error(msg);
          }
          return json;
        });
      });
    }

    function getFragments(resp) {
      if (!resp) return null;
      if (resp.fragments) return resp.fragments;
      if (resp.data && resp.data.fragments) return resp.data.fragments;
      return null;
    }

    function getCount(resp) {
      if (!resp) return null;
      if (typeof resp.cart_count === 'number') return resp.cart_count;
      if (resp.data && typeof resp.data.cart_count === 'number') return resp.data.cart_count;
      return null;
    }

    function applyFragments(fragments) {
      if (!fragments) return;
      Object.keys(fragments).forEach(function (selector) {
        var el = document.querySelector(selector);
        if (el) el.outerHTML = fragments[selector];
      });
      if (window.jQuery) jQuery(document.body).trigger('wc_fragments_refreshed');
    }

    function forceUpdateBadges(count) {
      document.querySelectorAll('.cart-badge[data-cart-count], .mbn-badge[data-cart-count]').forEach(function (el) {
        el.textContent = String(count);
        if (count > 0) el.classList.remove('is-hidden');
        else el.classList.add('is-hidden');
      });
    }

    function setCartQuantity(quantity) {
      return postAjax('senoobar_cart_set_quantity', {
        product_id: productId,
        variation_id: variantId(),
        quantity: quantity,
        attributes: collectAttributes()
      });
    }

    function collectAttributes() {
      var vars = {};
      form.querySelectorAll('select[name^="attribute_"]').forEach(function (sel) {
        if (sel.value) vars[sel.name] = sel.value;
      });
      return JSON.stringify(vars);
    }

    function removeFromCart() {
      return postAjax('senoobar_cart_remove_by_product', {
        product_id: productId,
        variation_id: variantId()
      });
    }

    function getCartTarget() {
      var bottom = document.querySelector('[data-cart-fly="bottom"]');
      var header = document.querySelector('[data-cart-fly="header"]');
      var isMobile = window.innerWidth < 1024;
      if (isMobile && bottom) {
        var br = bottom.getBoundingClientRect();
        if (br.width > 0 && br.height > 0) return bottom;
      }
      if (header) {
        var hr = header.getBoundingClientRect();
        if (hr.width > 0 && hr.height > 0) return header;
      }
      return bottom || header || null;
    }

    function flyToCart() {
      var target = getCartTarget();
      if (!target) return;
      var startEl = stepper.style.display === 'none' ? btn : stepper;
      var startRect = startEl.getBoundingClientRect();
      var iconEl = target.querySelector('svg') || target;
      var endRect = iconEl.getBoundingClientRect();
      var flyer = document.createElement('div');
      flyer.className = 'pd-fly-item';
      flyer.textContent = '+';
      var sx = startRect.left + startRect.width / 2;
      var sy = startRect.top + startRect.height / 2;
      var ex = endRect.left + endRect.width / 2;
      var ey = endRect.top + endRect.height / 2;
      flyer.style.left = '0px'; flyer.style.top = '0px';
      flyer.style.transform = 'translate(' + sx + 'px, ' + sy + 'px) scale(1)';
      document.body.appendChild(flyer);
      void flyer.offsetWidth;
      flyer.style.opacity = '0.35';
      flyer.style.transform = 'translate(' + ex + 'px, ' + ey + 'px) scale(0.15)';
      var badge = target.querySelector('.cart-badge, .mbn-badge');
      var done = false;
      var land = function () {
        if (done) return;
        done = true;
        if (badge) {
          badge.classList.remove('pd-badge-pop');
          void badge.offsetWidth;
          badge.classList.add('pd-badge-pop');
        }
        if (flyer.parentNode) flyer.parentNode.removeChild(flyer);
      };
      flyer.addEventListener('transitionend', function (e) { if (e.propertyName === 'transform') land(); });
      setTimeout(land, 950);
    }

    function cartBump() {
      var headerBadge = document.querySelector('.cart-badge[data-cart-count]');
      var navBadge = document.querySelector('.mbn-badge[data-cart-count]');
      [headerBadge, navBadge].forEach(function (badge) {
        if (!badge) return;
        var wrap = badge.closest('.mbn-icon') || badge.parentElement;
        if (!wrap) return;
        wrap.classList.remove('cart-bump');
        void wrap.offsetWidth;
        wrap.classList.add('cart-bump');
      });
    }

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      if (btn.dataset.pending === '1') return;

      // Optimistic UI: change the button immediately, before any network trip.
      // The actual WooCommerce/session work continues asynchronously below.
      btn.dataset.pending = '1';
      btn.disabled = true;
      setQty(1);
      showStepper();
      flyToCart();

      setCartQuantity(1).then(function (data) {
        btn.dataset.pending = '0';
        btn.disabled = false;
        applyFragments(getFragments(data));
        var c = getCount(data);
        if (typeof c === 'number') forceUpdateBadges(c);
        cartBump();
      }).catch(function () {
        // Only revert the optimistic state when the server actually rejects it.
        btn.dataset.pending = '0';
        btn.disabled = false;
        showButton();
      });
    });

    minusBtn.addEventListener('click', function () {
      if (qty <= 1) return;
      setQty(qty - 1);
      setCartQuantity(qty).then(function (data) {
        applyFragments(getFragments(data));
        var c = getCount(data);
        if (typeof c === 'number') forceUpdateBadges(c);
      }).catch(function () { setQty(qty + 1); });
    });

    plusBtn.addEventListener('click', function () {
      setQty(qty + 1);
      setCartQuantity(qty).then(function (data) {
        applyFragments(getFragments(data));
        var c = getCount(data);
        if (typeof c === 'number') forceUpdateBadges(c);
        cartBump();
        flyToCart();
      }).catch(function () { setQty(qty - 1); });
    });

    removeBtn.addEventListener('click', function () {
      if (removeBtn.disabled) return;
      removeBtn.disabled = true;
      removeFromCart().then(function (data) {
        removeBtn.disabled = false;
        setQty(1);
        showButton();
        applyFragments(getFragments(data));
        var c = getCount(data);
        if (typeof c === 'number') forceUpdateBadges(c);
      }).catch(function () { removeBtn.disabled = false; });
    });
  }
})();
