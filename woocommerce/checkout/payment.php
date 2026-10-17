<?php
/**
 * Senoobar - WooCommerce payment template.
 *
 * The regular Senoobar checkout renders payment methods in review-order.php.
 * This template remains functional for WooCommerce's order-pay / retry-payment
 * flow and any other context that invokes checkout/payment.php.
 *
 * @package Senoobar
 */
defined('ABSPATH') || exit;

$available_gateways = isset($available_gateways) && is_array($available_gateways)
    ? apply_filters('woocommerce_available_payment_gateways', $available_gateways)
    : [];

$is_order_pay = function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('order-pay');
$order_button_text = isset($order_button_text) && $order_button_text !== ''
    ? $order_button_text
    : ($is_order_pay
        ? apply_filters('woocommerce_pay_order_button_text', __('Pay for order', 'woocommerce'))
        : apply_filters('woocommerce_order_button_text', __('Place order', 'woocommerce')));

do_action('woocommerce_review_order_before_payment');
?>
<div id="payment" class="woocommerce-checkout-payment">
    <?php if (!empty($available_gateways)): ?>
        <?php wc_get_template('checkout/payment-method.php', ['available_gateways' => $available_gateways]); ?>
    <?php else: ?>
        <p class="woocommerce-notice woocommerce-info"><?php esc_html_e('Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce'); ?></p>
    <?php endif; ?>

    <div class="form-row place-order">
        <?php do_action('woocommerce_review_order_before_submit'); ?>
        <button
            type="submit"
            class="button alt"
            name="<?php echo $is_order_pay ? 'woocommerce_pay' : 'woocommerce_checkout_place_order'; ?>"
            id="place_order"
            value="<?php echo esc_attr($order_button_text); ?>"
            data-value="<?php echo esc_attr($order_button_text); ?>"
        ><?php echo esc_html($order_button_text); ?></button>
        <?php do_action('woocommerce_review_order_after_submit'); ?>
        <?php
        if ($is_order_pay) {
            wp_nonce_field('woocommerce-pay', 'woocommerce-pay-nonce');
        } else {
            wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce');
        }
        ?>
    </div>
</div>
<?php do_action('woocommerce_review_order_after_payment'); ?>
