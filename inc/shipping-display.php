<?php
/**
 * Senoobar — Shipping display helpers for custom WooCommerce cart/checkout.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

/**
 * Allow WooCommerce to calculate shipping on the custom cart/checkout.
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

/**
 * Custom templates must show the shipping section even when WooCommerce's
 * default "show shipping" flag is false (for example with a zero-cost
 * pay-on-delivery/پس‌کرایه method). The actual rate calculation is still
 * performed by WooCommerce.
 */
add_filter( 'woocommerce_cart_show_shipping', function ( $show ) {
    if ( is_admin() ) {
        return $show;
    }

    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        return WC()->cart && WC()->cart->needs_shipping();
    }

    return $show;
}, 999 );

/**
 * Recalculate WooCommerce shipping packages before custom totals render.
 */
add_action( 'woocommerce_before_calculate_totals', function ( $cart ) {
    if ( is_admin() || ! $cart || ! WC()->customer ) {
        return;
    }

    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        WC()->customer->set_calculated_shipping( true );
    }
}, 1 );

/**
 * Get the configured WooCommerce shipping method title as a fallback when
 * the rate list is empty in the custom template.
 */
function senoobar_get_shipping_method_title() {
    if ( ! class_exists( 'WC_Shipping_Zones' ) ) {
        return '';
    }

    $zone = WC_Shipping_Zones::get_zone_matching_package( [
        'destination' => [
            'country'  => WC()->customer ? WC()->customer->get_shipping_country() : WC()->countries->get_base_country(),
            'state'    => WC()->customer ? WC()->customer->get_shipping_state() : WC()->countries->get_base_state(),
            'postcode' => WC()->customer ? WC()->customer->get_shipping_postcode() : '',
            'city'     => WC()->customer ? WC()->customer->get_shipping_city() : '',
        ],
    ] );

    if ( $zone ) {
        foreach ( $zone->get_shipping_methods( true ) as $method ) {
            if ( $method->is_enabled() ) {
                $title = $method->get_title();
                if ( $title ) {
                    return $title;
                }
            }
        }
    }

    // Final fallback: inspect the store's configured zones, including the
    // "Rest of the world" zone, and return the first enabled method title.
    $zones = WC_Shipping_Zones::get_zones();
    foreach ( $zones as $zone_data ) {
        if ( empty( $zone_data['shipping_methods'] ) ) {
            continue;
        }

        foreach ( $zone_data['shipping_methods'] as $method ) {
            if ( $method->is_enabled() ) {
                $title = $method->get_title();
                if ( $title ) {
                    return $title;
                }
            }
        }
    }

    $rest_zone = new WC_Shipping_Zone( 0 );
    foreach ( $rest_zone->get_shipping_methods( true ) as $method ) {
        if ( $method->is_enabled() ) {
            $title = $method->get_title();
            if ( $title ) {
                return $title;
            }
        }
    }

    return '';
}

/**
 * WooCommerce normally appends "رایگان" to a zero-cost rate. The store's
 * configured method name (e.g. "پس‌کرایه") is the correct customer-facing
 * label, so use the actual method title instead.
 */
add_filter( 'woocommerce_cart_shipping_method_full_label', function ( $label, $method ) {
    if ( is_admin() ) {
        return $label;
    }

    if ( ( function_exists( 'is_cart' ) && is_cart() ) || ( function_exists( 'is_checkout' ) && is_checkout() ) ) {
        if ( is_object( $method ) && is_callable( [ $method, 'get_label' ] ) ) {
            $method_title = $method->get_label();

            if ( $method_title !== '' ) {
                return esc_html( $method_title );
            }
        }
    }

    return $label;
}, 20, 2 );
