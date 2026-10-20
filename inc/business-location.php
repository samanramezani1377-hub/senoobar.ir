<?php
/**
 * Business location & opening hours (Customizer + schema data).
 *
 * Everything lives in one Customizer section ("📍 لوکیشن و ساعت کاری فروشگاه")
 * so it can be edited in one place: map link, coordinates, city/province and
 * structured opening hours. The values feed the Organization / FurnitureStore
 * JSON-LD built in inc/seo.php (geo, hasMap, address locality/region,
 * openingHoursSpecification). The free-text address, phones and the footer's
 * display hours are still edited in the "فوتر" section.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default values (current store location).
 *
 * @return array
 */
function senoobar_business_defaults() {
	return array(
		'map_url'  => 'https://maps.app.goo.gl/NZ5DEVAsmGjKrufW6',
		'lat'      => '32.7413397',
		'lng'      => '51.709576',
		'locality' => 'اصفهان',
		'region'   => 'اصفهان',
		'day_from' => 'Saturday',
		'day_to'   => 'Thursday',
		'opens'    => '10:00',
		'closes'   => '21:00',
	);
}

/**
 * Week order used in Iran (Saturday first) → schema.org day names / labels.
 *
 * @return array
 */
function senoobar_business_days() {
	return array(
		'Saturday'  => 'شنبه',
		'Sunday'    => 'یکشنبه',
		'Monday'    => 'دوشنبه',
		'Tuesday'   => 'سه‌شنبه',
		'Wednesday' => 'چهارشنبه',
		'Thursday'  => 'پنجشنبه',
		'Friday'    => 'جمعه',
	);
}

/**
 * Convert Persian/Arabic digits to ASCII.
 */
function senoobar_business_ascii_digits( $value ) {
	return strtr(
		(string) $value,
		array(
			'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
			'۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
			'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
			'٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
			'٫' => '.', '،' => '.', ',' => '.',
		)
	);
}

/**
 * Sanitize a coordinate (empty string allowed = "don't output").
 */
function senoobar_business_sanitize_coord( $value, $max ) {
	$value = trim( senoobar_business_ascii_digits( $value ) );
	if ( '' === $value ) {
		return '';
	}
	if ( ! is_numeric( $value ) || abs( (float) $value ) > $max ) {
		return null;
	}
	return rtrim( rtrim( number_format( (float) $value, 7, '.', '' ), '0' ), '.' );
}

function senoobar_business_sanitize_lat( $value ) {
	$clean = senoobar_business_sanitize_coord( $value, 90 );
	if ( null === $clean ) {
		$defaults = senoobar_business_defaults();
		return $defaults['lat'];
	}
	return $clean;
}

function senoobar_business_sanitize_lng( $value ) {
	$clean = senoobar_business_sanitize_coord( $value, 180 );
	if ( null === $clean ) {
		$defaults = senoobar_business_defaults();
		return $defaults['lng'];
	}
	return $clean;
}

/**
 * Sanitize an HH:MM time (Persian digits accepted).
 */
function senoobar_business_sanitize_time( $value ) {
	$value = trim( senoobar_business_ascii_digits( $value ) );
	if ( preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', $value, $m ) ) {
		return sprintf( '%02d:%s', (int) $m[1], $m[2] );
	}
	return '';
}

function senoobar_business_sanitize_day( $value ) {
	$days = senoobar_business_days();
	return isset( $days[ $value ] ) ? $value : 'Saturday';
}

/**
 * Customizer section.
 *
 * @param WP_Customize_Manager $c Customizer manager.
 */
function senoobar_business_customize_register( $c ) {
	$d = senoobar_business_defaults();

	$c->add_section(
		'senoobar_business',
		array(
			'title'       => '📍 لوکیشن و ساعت کاری فروشگاه',
			'description' => 'این اطلاعات فقط در داده‌ساختاریافته‌ی سایت (برای گوگل و هوش مصنوعی) استفاده می‌شوند. آدرس متنی و شماره‌ها را از بخش «فوتر» تغییر دهید. هر فیلدی را خالی بگذارید، همان مورد در اسکیما ارسال نمی‌شود.',
			'priority'    => 91,
		)
	);

	$text_fields = array(
		'map_url'  => array( 'لینک فروشگاه در Google Maps', 'url', 'esc_url_raw', 'مثال: https://maps.app.goo.gl/… (اگر خالی باشد و مختصات پر باشد، لینک نقشه از روی مختصات ساخته می‌شود)' ),
		'lat'      => array( 'عرض جغرافیایی (Latitude)', 'text', 'senoobar_business_sanitize_lat', 'مثال: 32.7413397 — در Google Maps روی نقطه راست‌کلیک کنید و عدد اول را کپی کنید.' ),
		'lng'      => array( 'طول جغرافیایی (Longitude)', 'text', 'senoobar_business_sanitize_lng', 'مثال: 51.709576 — عدد دوم همان مختصات.' ),
		'locality' => array( 'شهر', 'text', 'sanitize_text_field', '' ),
		'region'   => array( 'استان', 'text', 'sanitize_text_field', '' ),
		'opens'    => array( 'ساعت شروع کار (HH:MM)', 'text', 'senoobar_business_sanitize_time', 'مثال: 10:00 — خالی = ساعت کاری ارسال نشود.' ),
		'closes'   => array( 'ساعت پایان کار (HH:MM)', 'text', 'senoobar_business_sanitize_time', 'مثال: 21:00' ),
	);

	foreach ( $text_fields as $key => $cfg ) {
		$c->add_setting(
			"senoobar_biz_{$key}",
			array(
				'default'           => $d[ $key ],
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => $cfg[2],
			)
		);
		$c->add_control(
			"senoobar_biz_{$key}",
			array(
				'label'       => $cfg[0],
				'description' => $cfg[3],
				'section'     => 'senoobar_business',
				'type'        => $cfg[1],
			)
		);
	}

	foreach ( array(
		'day_from' => 'اولین روز کاری هفته',
		'day_to'   => 'آخرین روز کاری هفته',
	) as $key => $label ) {
		$c->add_setting(
			"senoobar_biz_{$key}",
			array(
				'default'           => $d[ $key ],
				'type'              => 'theme_mod',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'senoobar_business_sanitize_day',
			)
		);
		$c->add_control(
			"senoobar_biz_{$key}",
			array(
				'label'   => $label,
				'section' => 'senoobar_business',
				'type'    => 'select',
				'choices' => senoobar_business_days(),
			)
		);
	}
}
add_action( 'customize_register', 'senoobar_business_customize_register' );

/**
 * Normalised location/hours data for the schema (only valid, non-empty values).
 *
 * @return array
 */
function senoobar_business_location_data() {
	$d   = senoobar_business_defaults();
	$get = function ( $key ) use ( $d ) {
		return trim( (string) get_theme_mod( "senoobar_biz_{$key}", $d[ $key ] ) );
	};

	$data = array(
		'map_url'  => esc_url_raw( $get( 'map_url' ) ),
		'locality' => $get( 'locality' ),
		'region'   => $get( 'region' ),
		'lat'      => '',
		'lng'      => '',
		'hours'    => array(),
	);

	$lat = $get( 'lat' );
	$lng = $get( 'lng' );
	if ( '' !== $lat && '' !== $lng && is_numeric( $lat ) && is_numeric( $lng ) ) {
		$data['lat'] = (float) $lat;
		$data['lng'] = (float) $lng;
	}

	if ( '' === $data['map_url'] && '' !== $data['lat'] ) {
		$data['map_url'] = 'https://www.google.com/maps?q=' . $data['lat'] . ',' . $data['lng'];
	}

	$opens  = senoobar_business_sanitize_time( $get( 'opens' ) );
	$closes = senoobar_business_sanitize_time( $get( 'closes' ) );
	if ( '' !== $opens && '' !== $closes ) {
		$order = array_keys( senoobar_business_days() );
		$from  = array_search( senoobar_business_sanitize_day( $get( 'day_from' ) ), $order, true );
		$to    = array_search( senoobar_business_sanitize_day( $get( 'day_to' ) ), $order, true );
		$days  = array();
		for ( $i = $from, $n = 0; $n < 7; $i = ( $i + 1 ) % 7, $n++ ) {
			$days[] = $order[ $i ];
			if ( $i === $to ) {
				break;
			}
		}
		$data['hours'] = array(
			'@type'     => 'OpeningHoursSpecification',
			'dayOfWeek' => $days,
			'opens'     => $opens,
			'closes'    => $closes,
		);
	}

	return $data;
}
