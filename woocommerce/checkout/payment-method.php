<?php
/**
 * Senoobar - WooCommerce payment methods.
 *
 * @package Senoobar
 */
defined('ABSPATH') || exit;

if (empty($available_gateways) || !is_array($available_gateways)) {
    return;
}

$available_gateways = apply_filters('woocommerce_available_payment_gateways', $available_gateways);
$chosen_gateway = (WC()->session) ? WC()->session->get('chosen_payment_method') : '';

if (!$chosen_gateway || !isset($available_gateways[$chosen_gateway])) {
    $chosen_gateway = (string) array_key_first($available_gateways);
}
?>
<div class="senoobar-payment-methods wc_payment_methods payment_methods methods" role="radiogroup" aria-label="روش پرداخت">
    <?php foreach ($available_gateways as $gateway): ?>
        <?php
        $gateway_id = $gateway->id;
        $selected = ($chosen_gateway === $gateway_id);
        $input_id = 'payment_method_' . $gateway_id;
        ?>
        <div class="senoobar-payment-method wc_payment_method payment_method_<?php echo esc_attr($gateway_id); ?> <?php echo $selected ? 'selected' : ''; ?>" data-gateway="<?php echo esc_attr($gateway_id); ?>">
            <label for="<?php echo esc_attr($input_id); ?>" class="senoobar-payment-label">
                <input
                    id="<?php echo esc_attr($input_id); ?>"
                    type="radio"
                    name="payment_method"
                    value="<?php echo esc_attr($gateway_id); ?>"
                    <?php checked($selected); ?>
                    data-order_button_text="<?php echo esc_attr($gateway->order_button_text); ?>"
                >
                <span class="senoobar-payment-icon" aria-hidden="true">
                    <?php
                    $icon_map = [
                        'zarinpal' => '🏦', 'idpay' => '💳', 'nextpay' => '💳',
                        'mellat' => '🏦', 'parsian' => '🏦', 'saman' => '🏦',
                        'pasargad' => '🏦', 'paypal' => '🅿️', 'stripe' => '💳',
                        'cod' => '💵', 'bacs' => '🏦', 'cheque' => '📝',
                    ];
                    $icon = '💳';
                    foreach ($icon_map as $key => $emoji) {
                        if (strpos($gateway_id, $key) !== false) {
                            $icon = $emoji;
                            break;
                        }
                    }
                    echo esc_html($icon);
                    ?>
                </span>
                <span class="senoobar-payment-info">
                    <span class="senoobar-payment-name"><?php echo wp_kses_post($gateway->get_title()); ?></span>
                </span>
            </label>
            <?php if ($gateway->has_fields() || $gateway->get_description()): ?>
                <div class="payment_box payment_method_<?php echo esc_attr($gateway_id); ?>" <?php echo $selected ? '' : 'style="display:none"'; ?>>
                    <?php $gateway->payment_fields(); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php do_action('woocommerce_checkout_after_payment_methods', $available_gateways); ?>
<script>
(function () {
    'use strict';
    function initPaymentCards() {
        document.querySelectorAll('.senoobar-payment-method').forEach(function (card) {
            var radio = card.querySelector('input[name="payment_method"]');
            if (!radio || card.dataset.paymentReady === '1') return;
            card.dataset.paymentReady = '1';
            card.addEventListener('click', function (event) {
                if (event.target !== radio) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                }
                document.querySelectorAll('.senoobar-payment-method').forEach(function (item) {
                    item.classList.toggle('selected', item === card);
                    var box = item.querySelector('.payment_box');
                    if (box) box.style.display = item === card ? '' : 'none';
                });
            });
            radio.addEventListener('change', function () {
                document.querySelectorAll('.senoobar-payment-method').forEach(function (item) {
                    item.classList.toggle('selected', item === card);
                    var box = item.querySelector('.payment_box');
                    if (box) box.style.display = item === card ? '' : 'none';
                });
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPaymentCards);
    else initPaymentCards();
})();
</script>
