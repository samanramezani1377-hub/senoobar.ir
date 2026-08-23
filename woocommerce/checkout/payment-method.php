<?php
/**
 * Senoobar - Payment Methods Template
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $available_gateways ) || ! is_array( $available_gateways ) ) {
    return;
}

$available_gateways = apply_filters( 'woocommerce_available_payment_gateways', $available_gateways );
$chosen_gateway     = WC()->session ? WC()->session->chosen_payment_method : '';
?>

<div class="senoobar-payment-section" id="payment">
    <div class="senoobar-payment-methods" role="radiogroup" aria-label="روش پرداخت">
        <?php foreach ( $available_gateways as $gateway ) : ?>
            <?php $selected = $chosen_gateway === $gateway->id; ?>
            <div class="senoobar-payment-method <?php echo $selected ? 'selected' : ''; ?>" data-gateway="<?php echo esc_attr( $gateway->id ); ?>">
                <input
                    type="radio"
                    id="payment_method_<?php echo esc_attr( $gateway->id ); ?>"
                    name="payment_method"
                    value="<?php echo esc_attr( $gateway->id ); ?>"
                    <?php checked( $selected ); ?>
                    data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>"
                    aria-label="<?php echo esc_attr( $gateway->get_title() ); ?>"
                >
                <div class="senoobar-payment-icon">
                    <?php
                    $icon_map = [
                        'zarinpal' => '🏦', 'idpay' => '💳', 'nextpay' => '💳',
                        'mellat' => '🏦', 'parsian' => '🏦', 'saman' => '🏦',
                        'pasargad' => '🏦', 'paypal' => '🅿️', 'stripe' => '💳',
                        'cod' => '💵', 'bacs' => '🏦', 'cheque' => '📝',
                    ];
                    $icon = '💳';
                    foreach ( $icon_map as $key => $emoji ) {
                        if ( strpos( $gateway->id, $key ) !== false ) { $icon = $emoji; break; }
                    }
                    echo $icon;
                    ?>
                </div>
                <div class="senoobar-payment-info">
                    <div class="senoobar-payment-name"><?php echo wp_kses_post( $gateway->get_title() ); ?></div>
                    <?php if ( $gateway->get_description() ) : ?>
                        <div class="senoobar-payment-desc"><?php echo wp_kses_post( $gateway->get_description() ); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php do_action( 'woocommerce_checkout_after_payment_methods', $available_gateways ); ?>
</div>

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
                });
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initPaymentCards);
    else initPaymentCards();
})();
</script>
