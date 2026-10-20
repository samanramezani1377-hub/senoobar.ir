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
 * First numeric price found in an offer markup (price, lowPrice, spec price).
 *
 * @param array $offer Offer markup.
 * @return float
 */
function senoobar_schema_first_price( $offer ) {
	foreach ( array( 'price', 'lowPrice' ) as $key ) {
		if ( isset( $offer[ $key ] ) && is_numeric( $offer[ $key ] ) && (float) $offer[ $key ] > 0 ) {
			return (float) $offer[ $key ];
		}
	}
	if ( ! empty( $offer['priceSpecification'] ) && is_array( $offer['priceSpecification'] ) ) {
		foreach ( $offer['priceSpecification'] as $spec ) {
			if ( is_array( $spec ) && isset( $spec['price'] ) && is_numeric( $spec['price'] ) && (float) $spec['price'] > 0 ) {
				return (float) $spec['price'];
			}
		}
	}
	return 0.0;
}

/**
 * The product's own price in store currency (used to detect markup that was
 * already converted to Rial by another plugin, e.g. Persian WooCommerce).
 *
 * @param mixed $product WC_Product or null.
 * @return float
 */
function senoobar_schema_reference_price( $product ) {
	if ( ! is_object( $product ) || ! method_exists( $product, 'get_price' ) ) {
		return 0.0;
	}
	$price = $product->get_price();
	return is_numeric( $price ) ? (float) $price : 0.0;
}

/**
 * Convert an Offer / AggregateOffer markup array to IRR.
 *
 * Idempotent: if the markup already holds Rial values (another plugin ran
 * first), prices are left alone and only the currency code is normalised, so
 * values are never multiplied twice.
 *
 * @param array $offer   Offer markup from WooCommerce.
 * @param mixed $product WC_Product the markup was built for.
 * @return array
 */
function senoobar_schema_convert_offer( $offer, $product = null ) {
	if ( ! is_array( $offer ) ) {
		return $offer;
	}
	$multiplier = senoobar_schema_price_multiplier();
	if ( 1 === $multiplier ) {
		return $offer;
	}

	$node_price = senoobar_schema_first_price( $offer );
	$reference  = senoobar_schema_reference_price( $product );

	if ( $reference > 0 && $node_price > 0 ) {
		// Converted values are ~$multiplier x the product price; unconverted
		// ones (even with tax) stay close to 1x, so 0.5x is a safe midpoint.
		$already_converted = $node_price >= $reference * $multiplier * 0.5;
	} else {
		$node_currency     = isset( $offer['priceCurrency'] ) ? $offer['priceCurrency'] : '';
		$already_converted = ( 'IRR' === $node_currency && 'IRR' !== get_woocommerce_currency() );
	}
	$factor = $already_converted ? 1 : $multiplier;

	$walk = function ( $node ) use ( &$walk, $factor ) {
		foreach ( $node as $key => $value ) {
			if ( is_array( $value ) ) {
				$node[ $key ] = $walk( $value );
			} elseif ( $factor > 1 && in_array( $key, array( 'price', 'lowPrice', 'highPrice' ), true ) && is_numeric( $value ) ) {
				$node[ $key ] = number_format( round( (float) $value * $factor ), 0, '.', '' );
			} elseif ( 'priceCurrency' === $key ) {
				$node[ $key ] = 'IRR';
			}
		}
		return $node;
	};

	return $walk( $offer );
}
add_filter( 'woocommerce_structured_data_product_offer', 'senoobar_schema_convert_offer', 20, 2 );
