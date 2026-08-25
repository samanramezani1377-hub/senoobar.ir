<?php
/**
 * Senoobar — Shipping display helpers for custom WooCommerce cart/checkout.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

/**
 * Ensure WooCommerce has a destination to calculate rates against on the
 * initial cart/checkout render. The store base country/state is only a
 * fallback; WooCommerce replaces it with the customer's entered address.
 */
add_filter( 'woocommerce_cart_ready_to_calc_shipping', function ( $ready ) {
    if ( is_admin() || ! function_exists( 'is_cart' ) ) {
        return $ready;
    }

    if ( is_cart() || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        $customer = WC()->customer;

        if ( $customer ) {
            if ( ! $customer->get_shipping_country() ) {
                $customer->set_shipping_country( WC()->countries->get_base_country() );
            }

            if ( ! $customer->get_shipping_state() ) {
                $customer->set_shipping_state( WC()->countries->get_base_state() );
            }
        }

        return true;
    }

    return $ready;
}, 999 );
