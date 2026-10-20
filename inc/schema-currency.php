<?php
/**
 * Normalise WooCommerce Product schema prices to ISO 4217 Rial (IRR).
 *
 * WooCommerce stores prices in the configured store currency. A display symbol
 * such as "تومان" does not change the unit of the stored value, so we must not
 * infer a conversion from the symbol. IRT means Toman (1 IRT = 10 IRR);
 * IRHT means thousand Toman (1 IRHT = 10,000 IRR); IRR is already Rial.
 *
 * If another integration has already converted an Offer and marks its
 * priceCurrency as IRR, this filter leaves the numeric prices alone to avoid
 * converting the same amount twice. The multiplier can be customised with
 * the senoobar_schema_price_multiplier filter.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Number of Rial represented by one unit of the configured store currency.
 *
 * Do not infer the stored unit from the currency symbol: symbols are display
 * labels and may be changed independently of the actual WooCommerce currency.
 *
 * @return int
 */
function senoobar_schema_price_multiplier() {
	$currency   = get_woocommerce_currency();
	$multiplier = 1;

	if ( 'IRT' === $currency ) {
		$multiplier = 10; // Toman.
	} elseif ( 'IRHT' === $currency ) {
		$multiplier = 10000; // Thousand Toman.
	}

	return max( 1, (int) apply_filters( 'senoobar_schema_price_multiplier', $multiplier, $currency ) );
}

/**
 * Convert an Offer / AggregateOffer markup array to IRR.
 *
 * Conversion is based on explicit currency metadata, never on comparing the
 * schema price with WC_Product::get_price(). The latter can differ because of
 * sale prices, tax display, variations, or currency/display plugins.
 *
 * @param array $offer   Offer markup from WooCommerce.
 * @param mixed $product WC_Product the markup was built for (unused; retained
 *                       for compatibility with WooCommerce's filter signature).
 * @return array
 */
function senoobar_schema_convert_offer( $offer, $product = null ) {
	if ( ! is_array( $offer ) ) {
		return $offer;
	}

	$multiplier = senoobar_schema_price_multiplier();

	// An IRR offer is already expressed in the target unit, even if the store
	// currency is IRT. This prevents multiplying prices that another plugin
	// has already converted and labelled as Rial.
	$offer_currency = isset( $offer['priceCurrency'] ) && is_string( $offer['priceCurrency'] )
		? strtoupper( $offer['priceCurrency'] )
		: '';

	if ( 1 === $multiplier || 'IRR' === $offer_currency ) {
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

	$converted = $walk( $offer );

	// Also normalise the top-level currency if the offer has no numeric price
	// fields, so the schema does not advertise a non-ISO Toman currency code.
	if ( isset( $converted['priceCurrency'] ) ) {
		$converted['priceCurrency'] = 'IRR';
	}

	return $converted;
}
add_filter( 'woocommerce_structured_data_product_offer', 'senoobar_schema_convert_offer', 20, 2 );
