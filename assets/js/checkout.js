/**
 * Senoobar — Checkout JS
 * Validation/error feedback only. Never replaces WooCommerce payment UI.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        const checkoutForm = document.querySelector('form.checkout.woocommerce-checkout');
        if (!checkoutForm) return;

        initValidation(checkoutForm);
        initErrorEvents();
    });

    function labelFor(field) {
        if (!field) return 'این قسمت';
        const label = field.id ? document.querySelector('label[for="' + CSS.escape(field.id) + '"]') : null;
        return label ? label.textContent.replace('*', '').trim() : 'این قسمت';
    }

    function showFieldError(field, message) {
        const group = field.closest('.senoobar-form-group, .form-row') || field.parentElement;
        if (!group) return;
        group.classList.add('has-error', 'woocommerce-invalid', 'woocommerce-invalid-required-field');
        let error = group.querySelector('.senoobar-field-error');
        if (!error) {
            error = document.createElement('div');
            error.className = 'senoobar-field-error';
            error.setAttribute('role', 'alert');
            group.appendChild(error);
        }
        error.textContent = message;
    }

    function clearFieldError(field) {
        const group = field.closest('.senoobar-form-group, .form-row') || field.parentElement;
        if (!group) return;
        group.classList.remove('has-error', 'woocommerce-invalid', 'woocommerce-invalid-required-field');
        const error = group.querySelector('.senoobar-field-error');
        if (error) error.remove();
    }

    function validateField(field) {
        if (!field || field.type === 'hidden' || field.disabled) return true;
        if (!field.required) return true;

        const value = String(field.value || '').trim();
        if (!value) {
            showFieldError(field, 'لطفاً «' + labelFor(field) + '» را وارد کنید.');
            return false;
        }

        if ((field.name === 'billing_phone' || field.id === 'billing_phone') && !/^[0-9۰-۹+\-\s()]{10,15}$/.test(value)) {
            showFieldError(field, 'لطفاً شماره موبایل/تلفن را به‌صورت صحیح وارد کنید.');
            return false;
        }

        clearFieldError(field);
        return true;
    }

    function initValidation(form) {
        form.querySelectorAll('[required]').forEach(function (field) {
            field.addEventListener('blur', function () { validateField(field); });
            field.addEventListener('change', function () { validateField(field); });
            field.addEventListener('input', function () {
                if (String(field.value || '').trim()) clearFieldError(field);
            });
        });

        const button = document.getElementById('place_order');
        if (!button) return;

        form.addEventListener('submit', function (event) {
            let valid = true;
            let firstInvalid = null;

            form.querySelectorAll('[required]').forEach(function (field) {
                if (!validateField(field)) {
                    valid = false;
                    if (!firstInvalid) firstInvalid = field;
                }
            });

            if (!form.querySelector('input[name="payment_method"]:checked')) {
                valid = false;
                showCheckoutError('لطفاً روش پرداخت را انتخاب کنید.');
            }

            if (!valid) {
                event.preventDefault();
                button.disabled = false;
                if (firstInvalid) {
                    firstInvalid.focus({ preventScroll: true });
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            // Do NOT preventDefault. The normal WooCommerce checkout request
            // must continue so the selected gateway can execute process_payment().
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'در حال پردازش پرداخت...';
        });
    }

    function showCheckoutError(message) {
        let box = document.getElementById('senoobar-checkout-errors');
        if (!box) {
            box = document.createElement('div');
            box.id = 'senoobar-checkout-errors';
            box.className = 'senoobar-checkout-errors';
            box.setAttribute('role', 'alert');
            document.body.appendChild(box);
        }
        box.innerHTML = '<div class="senoobar-error-title">خطا در ثبت سفارش</div><div class="senoobar-error-message">' + escapeHtml(message) + '</div>';
        box.classList.add('is-visible');
        window.setTimeout(function () { box.classList.remove('is-visible'); }, 9000);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function initErrorEvents() {
        if (!window.jQuery) return;
        jQuery(document.body).on('checkout_error wc_checkout_error', function (event, error) {
            const text = typeof error === 'string' ? error.replace(/<[^>]*>/g, ' ').trim() : 'اطلاعات واردشده کامل یا صحیح نیست. لطفاً موارد مشخص‌شده را بررسی کنید.';
            showCheckoutError(text);
            const button = document.getElementById('place_order');
            if (button) {
                button.disabled = false;
                button.textContent = button.dataset.originalText || 'ثبت سفارش';
            }
        });
    }
})();
