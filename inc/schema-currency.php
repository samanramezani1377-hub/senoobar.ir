<?php
/**
 * Product schema currency (Toman -> ISO 4217 Rial).
 *
 * WooCommerce copies the store's currency code straight into the Product
 * JSON-LD (`priceCurrency`). "IRT" (Toman) is not an ISO 4217 code, so Google
 * and other consumers of schema.org data can't reliably read it. The valid
 * code is IRR (Rial), with 1 Toman = 10 Rial. When the store is priced in
 * Toman, this converts the offer markup to IRR so prices stay correct.
 *
 * Override the multiplier with the `senoobar_schema_price_multiplier` filter
 * (return 1 to send WooCommerce's values untouched).
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How many Rial one unit of the store currency is worth (1 = no conversion).
 *
 * @return int
 */
function senoobar_schema_price_multiplier() {
	$currency   = get_woocommerce_currency();
	$multiplier = 1;

	if ( 'IRT' === $currency ) {
		$multiplier = 10;      // Toman.
	} elseif ( 'IRHT' === $currency ) {
		$multiplier = 10000;   // Hezar Toman (1000 Toman).
	} elseif ( 'IRR' === $currency ) {
		// Many Persian stores keep IRR but relabel the symbol as Toman, which
		// means the stored prices are really Toman values.
		$symbol = html_entity_decode( wp_strip_all_tags( (string) get_woocommerce_currency_symbol( $currency ) ), ENT_QUOTES, 'UTF-8' );
		if ( false !== strpos( $symbol, 'تومان' ) || false !== stripos( $symbol, 'toman' ) ) {
			$multiplier = 10;
		}
	}

	return max( 1, (int) apply_filters( 'senoobar_schema_price_multiplier', $multiplier, $currency ) );
}

/**
 * Convert an Offer / AggregateOffer markup array to IRR.
 *
 * @param array $offer Offer markup from WooCommerce.
 * @return array
 */
function senoobar_schema_convert_offer( $offer ) {
	if ( ! is_array( $offer ) ) {
		return $offer;
	}
	$multiplier = senoobar_schema_price_multiplier();
	if ( 1 === $multiplier ) {
		return $offer;
	}

	$walk = function ( $node ) use ( &$walk, $multiplier ) {
		foreach ( $node as $key => $value ) {
			if ( is_array( $value ) ) {
				$node[ $key ] = $walk( $value );
			} elseif ( in_array( $key, array( 'price', 'lowPrice', 'highPrice' ), true ) && is_numeric( $value ) ) {
				$node[ $key ] = number_format( round( (float) $value * $multiplier ), 0, '.', '' );
			} elseif ( 'priceCurrency' === $key ) {
				$node[ $key ] = 'IRR';
			}
		}
		return $node;
	};

	return $walk( $offer );
}
add_filter( 'woocommerce_structured_data_product_offer', 'senoobar_schema_convert_offer', 20 );
