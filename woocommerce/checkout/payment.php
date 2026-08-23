<?php
/**
 * Senoobar - WooCommerce Checkout Payment Section
 *
 * Keeps the standard WooCommerce payment gateway hooks intact so gateways
 * such as ZarinPal can render their payment method and process the order.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
$order_button_text = apply_filters(
    'woocommerce_order_button_text',
    __( 'Place order', 'woocommerce' )
);

do_action( 'woocommerce_review_order_before_payment' );
?>

<div id="payment" class="woocommerce-checkout-payment">

    <?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
        <ul class="wc_payment_methods payment_methods methods" aria-label="<?php esc_attr_e( 'Payment methods', 'woocommerce' ); ?>">
            <?php
            if ( ! empty( $available_gateways ) ) {
                foreach ( $available_gateways as $gateway ) {
                    wc_get_template(
                        'checkout/payment-method.php',
                        array( 'gateway' => $gateway )
                    );
                }
            } else {
                echo '<li>';
                wc_print_notice(
                    apply_filters(
                        'woocommerce_no_available_payment_methods_message',
                        WC()->customer->get_billing_country()
                            ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' )
                            : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' )
                    ),
                    'notice'
                );
                echo '</li>';
            }
            ?>
        </ul>
    <?php endif; ?>

    <div class="form-row place-order">
        <noscript>
            <?php
            printf(
                esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order.', 'woocommerce' ),
                '<em>',
                '</em>'
            );
            ?>
            <br />
            <button type="submit" class="button alt" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>">
                <?php esc_html_e( 'Update totals', 'woocommerce' ); ?>
            </button>
        </noscript>

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
