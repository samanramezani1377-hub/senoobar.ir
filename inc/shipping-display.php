<?php
/**
 * Senoobar — Shipping display helpers for custom WooCommerce cart/checkout.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

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

/**
 * Recalculate WooCommerce shipping packages on cart/checkout before the
 * custom templates render their shipping totals.
 */
add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
    if ( is_admin() || ! $cart || ! WC()->customer ) {
        return;
    }

    if ( function_exists( 'is_cart' ) && is_cart() || function_exists( 'is_checkout' ) && is_checkout() ) {
        WC()->customer->set_calculated_shipping( true );
    }
}, 1 );
