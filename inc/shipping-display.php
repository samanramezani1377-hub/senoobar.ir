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

add_filter( 'woocommerce_cart_show_shipping', function ( $show ) {
    if ( is_admin() ) {
        return $show;
    }

    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        return WC()->cart && WC()->cart->needs_shipping();
    }

    return $show;
}, 999 );

add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
    if ( is_admin() || ! $cart || ! WC()->customer ) {
        return;
    }

    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        WC()->customer->set_calculated_shipping( true );
    }
}, 1 );

/**
 * Return the actual currently available/selected WooCommerce shipping method
 * title. This reads the calculated package rates, not a hard-coded label.
 */
function senoobar_get_shipping_method_title() {
    if ( ! WC()->cart || ! WC()->cart->needs_shipping() ) {
        return '';
    }

    $chosen = WC()->session ? WC()->session->get( 'chosen_shipping_methods', [] ) : [];
    $chosen = is_array( $chosen ) ? $chosen : [];

    // First: use the currently selected calculated rate.
    $packages = WC()->shipping() ? WC()->shipping()->get_packages() : [];
    foreach ( $packages as $package_index => $package ) {
        $rates = isset( $package['rates'] ) && is_array( $package['rates'] ) ? $package['rates'] : [];
        $chosen_rate_id = isset( $chosen[ $package_index ] ) ? $chosen[ $package_index ] : '';

        if ( $chosen_rate_id && isset( $rates[ $chosen_rate_id ] ) ) {
            $rate = $rates[ $chosen_rate_id ];
            if ( is_object( $rate ) && method_exists( $rate, 'get_label' ) && $rate->get_label() ) {
                return $rate->get_label();
            }
        }

        // If no chosen rate is stored, use the first calculated rate.
        foreach ( $rates as $rate ) {
            if ( is_object( $rate ) && method_exists( $rate, 'get_label' ) && $rate->get_label() ) {
                return $rate->get_label();
            }
        }
    }

    // Fallback: read the active method directly from the matching WooCommerce
    // shipping zone. This is still the configured method name, never a fixed
    // "رایگان" label.
    if ( class_exists( 'WC_Shipping_Zones' ) ) {
        $destination = [
            'country'  => WC()->customer ? WC()->customer->get_shipping_country() : WC()->countries->get_base_country(),
            'state'    => WC()->customer ? WC()->customer->get_shipping_state() : WC()->countries->get_base_state(),
            'postcode' => WC()->customer ? WC()->customer->get_shipping_postcode() : '',
            'city'     => WC()->customer ? WC()->customer->get_shipping_city() : '',
        ];

        $zone = WC_Shipping_Zones::get_zone_matching_package( [ 'destination' => $destination ] );
        if ( $zone ) {
            foreach ( $zone->get_shipping_methods( true ) as $method ) {
                if ( $method->is_enabled() && $method->get_title() ) {
                    return $method->get_title();
                }
            }
        }
    }

    return '';
}

/**
 * Do not append WooCommerce's generic "رایگان" text to zero-cost shipping.
 * The configured method title (e.g. پس‌کرایه) is the customer-facing label.
 */
add_filter( 'woocommerce_cart_shipping_method_full_label', function ( $label, $method ) {
    if ( is_admin() ) {
        return $label;
    }

    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        if ( is_object( $method ) && is_callable( [ $method, 'get_label' ] ) ) {
            $title = $method->get_label();
            if ( $title !== '' ) {
                return esc_html( $title );
            }
        }
    }

    return $label;
}, 20, 2 );
