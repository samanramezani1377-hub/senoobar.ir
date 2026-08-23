<?php
/**
 * Senoobar - WooCommerce Checkout Payment Section
 *
 * Renders the existing Senoobar payment-method UI exactly once while keeping
 * WooCommerce's payment hooks and selected gateway available to checkout.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
$order_button_text  = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );

do_action( 'woocommerce_review_order_before_payment' );
?>

<div id="payment" class="woocommerce-checkout-payment">
    <?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
        <?php
        // payment-method.php is the site's existing styled payment UI.
        // It renders the available gateways once; do not create a second list.
        wc_get_template(
            'checkout/payment-method.php',
            array( 'available_gateways' => $available_gateways )
        );
        ?>
    <?php endif; ?>

    <div class="form-row place-order">
        <?php do_action( 'woocommerce_review_order_before_submit' ); ?>
        <?php
        echo apply_filters(
            'woocommerce_order_button_html',
            '<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>'
        );
        ?>
        <?php do_action( 'woocommerce_review_order_after_submit' ); ?>
        <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
    </div>
</div>

<?php do_action( 'woocommerce_review_order_after_payment' ); ?>
