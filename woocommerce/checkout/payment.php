<?php
/**
 * Senoobar - WooCommerce Checkout Payment Section
 *
 * Uses WooCommerce's standard payment-gateway flow so gateways such as
 * ZarinPal can render their fields and handle process_payment().
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

// This template is called directly by our custom checkout template, so make
// the same variables available that WooCommerce normally passes to payment.php.
$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
$order_button_text  = apply_filters(
    'woocommerce_order_button_text',
    __( 'Place order', 'woocommerce' )
);

do_action( 'woocommerce_review_order_before_payment' );
?>

<div id="payment" class="woocommerce-checkout-payment">

    <?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
        <ul class="wc_payment_methods payment_methods methods">
            <?php if ( ! empty( $available_gateways ) ) : ?>
                <?php foreach ( $available_gateways as $gateway ) : ?>
                    <?php wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) ); ?>
                <?php endforeach; ?>
            <?php else : ?>
                <li>
                    <?php
                    wc_print_notice(
                        apply_filters(
                            'woocommerce_no_available_payment_methods_message',
                            WC()->customer && WC()->customer->get_billing_country()
                                ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' )
                                : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' )
                        ),
                        'notice'
                    );
                    ?>
                </li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>

    <div class="form-row place-order">
        <?php wc_get_template( 'checkout/terms.php' ); ?>

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
