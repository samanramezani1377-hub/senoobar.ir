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
    <?php
    // The place-order button is rendered once, in review-order.php.
    // Do not add a second #place_order button here.
    ?>
</div>

<?php do_action( 'woocommerce_review_order_after_payment' ); ?>
