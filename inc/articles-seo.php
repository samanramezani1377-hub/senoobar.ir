<?php
/**
 * Senoobar — سئو و اسکیمای بخش مقالات.
 *
 * - روبات‌ها: آدرس‌های فیلتر/جستجو/مرتب‌سازی (?aq= ?asort= ?acat=) → noindex,follow
 * - بردکرامب: افزودن «مقالات» به مسیر دسته/برچسب (هماهنگ با بردکرامب نمایشی)
 * - اسکیما: CollectionPage + ItemList برای صفحه‌ی مقالات و آرشیو دسته/برچسب،
 *   BlogPosting برای مقالات (با Yoast از طریق فیلتر، بدون Yoast به‌صورت JSON-LD)
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * آیا صفحه‌ی فعلی یک لیست مقالات است؟ (صفحه‌ی مقالات، دسته، برچسب، صفحه‌ی نوشته‌ها)
 */
function senoobar_articles_is_listing(): bool {
	return is_page_template( 'template-articles.php' ) || is_category() || is_tag() || ( is_home() && ! is_front_page() );
}

/**
 * آیا آدرس فعلی پارامتر فیلتر/جستجو/مرتب‌سازی دارد؟
 */
function senoobar_articles_is_filtered_request(): bool {
	foreach ( [ 'aq', 'asort', 'acat' ] as $key ) {
		if ( isset( $_GET[ $key ] ) && '' !== (string) $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification
			return true;
		}
	}
	return false;
}

/**
 * noindex,follow برای آدرس‌های فیلتر/جستجو تا محتوای تکراری ایندکس نشود.
 * (هم برای wp_robots وردپرس، هم برای Yoast)
 */
function senoobar_articles_wp_robots( array $robots ): array {
	if ( senoobar_articles_is_listing() && senoobar_articles_is_filtered_request() ) {
		unset( $robots['index'] );
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'senoobar_articles_wp_robots', 20 );

function senoobar_articles_yoast_robots( $robots ) {
	if ( senoobar_articles_is_listing() && senoobar_articles_is_filtered_request() ) {
		return 'noindex, follow';
	}
	return $robots;
}
add_filter( 'wpseo_robots', 'senoobar_articles_yoast_robots', 20 );
