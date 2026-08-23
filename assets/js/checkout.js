/**
 * Senoobar — Checkout JS
 * Native WooCommerce checkout validation and payment handling.
 *
 * Important: wc-checkout.js is intentionally disabled by the theme, so this
 * script must never prevent the native checkout form POST.
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initCheckout();
    });

    function initCheckout() {
        const checkoutForm = document.querySelector('form.checkout.woocommerce-checkout');
        if (!checkoutForm) return;

        initPaymentMethods(checkoutForm);
        initFormValidation(checkoutForm);
        initPlaceOrder(checkoutForm);
    }

    function initPaymentMethods(checkoutForm) {
        checkoutForm.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                checkoutForm.querySelectorAll('.senoobar-payment-method').forEach(function (card) {
                    card.classList.toggle('selected', card.querySelector('input[name="payment_method"]') === radio);
                });
            });
        });

        const selected = checkoutForm.querySelector('input[name="payment_method"]:checked');
        if (!selected) {
            const first = checkoutForm.querySelector('input[name="payment_method"]');
            if (first) {
                first.checked = true;
                first.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    }

    function getFieldLabel(field) {
        const label = document.querySelector('label[for="' + CSS.escape(field.id) + '"]');
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
        if (!field.required) {
            clearFieldError(field);
            return true;
        }

        const value = String(field.value || '').trim();
        if (!value) {
            showFieldError(field, 'لطفاً «' + getFieldLabel(field) + '» را وارد کنید.');
            return false;
        }

        // Basic phone validation for the billing mobile/phone field.
        if ((field.name === 'billing_phone' || field.id === 'billing_phone') && !/^[0-9۰-۹+\-\s()]{10,15}$/.test(value)) {
            showFieldError(field, 'لطفاً شماره موبایل را به‌صورت صحیح وارد کنید.');
            return false;
        }

        clearFieldError(field);
        return true;
    }

    function initFormValidation(checkoutForm) {
        checkoutForm.querySelectorAll('[required]').forEach(function (field) {
            field.addEventListener('blur', function () { validateField(field); });
            field.addEventListener('input', function () {
                if (field.value.trim()) clearFieldError(field);
            });
            field.addEventListener('change', function () { validateField(field); });
        });
    }

    function showCheckoutError(message) {
        let box = document.getElementById('senoobar-checkout-errors');
        if (!box) {
            box = document.createElement('div');
            box.id = 'senoobar-checkout-errors';
            box.className = 'woocommerce-error senoobar-checkout-errors';
            box.setAttribute('role', 'alert');
            const form = document.querySelector('form.checkout.woocommerce-checkout');
            if (form) form.parentNode.insertBefore(box, form);
        }
        box.innerHTML = '<strong>خطا در ثبت سفارش</strong><br>' + message;
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function initPlaceOrder(checkoutForm) {
        const button = document.getElementById('place_order');
        if (!button) return;

        checkoutForm.addEventListener('submit', function (event) {
            // Do not use preventDefault: native POST is required because the
            // WooCommerce checkout AJAX script is disabled in this theme.
            let valid = true;
            let firstInvalid = null;

            checkoutForm.querySelectorAll('[required]').forEach(function (field) {
                if (!validateField(field)) {
                    valid = false;
                    if (!firstInvalid) firstInvalid = field;
                }
            });

            const payment = checkoutForm.querySelector('input[name="payment_method"]:checked');
            if (!payment) {
                valid = false;
                showCheckoutError('لطفاً یک روش پرداخت را انتخاب کنید.');
            }

            if (!valid) {
                event.preventDefault();
                if (firstInvalid) {
                    firstInvalid.focus({ preventScroll: true });
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                button.disabled = false;
                return;
            }

            // Allow the browser to submit normally. WooCommerce will create
            // the order and the selected gateway's process_payment() will run.
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'در حال انتقال به درگاه...';
        });
    }
})();
