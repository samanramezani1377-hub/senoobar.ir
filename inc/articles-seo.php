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

/**
 * بردکرامب Yoast: برای دسته/برچسب نوشته‌ها «مقالات» بین «خانه» و نام دسته قرار بگیرد
 * تا با بردکرامب نمایشی و اسکیمای BreadcrumbList یکی باشد.
 */
function senoobar_articles_breadcrumb_links( $links ) {
	if ( ! is_array( $links ) || ! ( is_category() || is_tag() ) ) {
		return $links;
	}
	$url = senoobar_articles_url();
	foreach ( $links as $link ) {
		if ( isset( $link['url'] ) && untrailingslashit( $link['url'] ) === untrailingslashit( $url ) ) {
			return $links; // قبلاً هست
		}
	}
	array_splice( $links, 1, 0, [ [ 'url' => $url, 'text' => 'مقالات' ] ] );
	return $links;
}
add_filter( 'wpseo_breadcrumb_links', 'senoobar_articles_breadcrumb_links', 20 );

/* ════════════════════════════════════════════════════════════════
   اسکیمای لیست: CollectionPage + ItemList
   ════════════════════════════════════════════════════════════════ */

/**
 * آدرس canonical لیست فعلی (بدون پارامترهای فیلتر).
 */
function senoobar_articles_listing_url(): string {
	if ( is_category() || is_tag() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? senoobar_articles_url() : $link;
	}
	if ( is_home() && ! is_front_page() ) {
		$id = (int) get_option( 'page_for_posts' );
		return $id ? get_permalink( $id ) : senoobar_articles_url();
	}
	return get_permalink( get_queried_object_id() );
}

function senoobar_articles_listing_name(): string {
	if ( is_category() || is_tag() ) {
		return single_term_title( '', false );
	}
	$title = get_the_title( get_queried_object_id() );
	return $title ? $title : 'مقالات';
}

/**
 * ItemList مقالات همین صفحه. برای صفحه‌ی مقالات فهرست بدون فیلتر (مطابق canonical).
 */
function senoobar_articles_item_list(): array {
	$ids    = [];
	$offset = 0;

	if ( is_page_template( 'template-articles.php' ) ) {
		$per    = 12;
		$paged  = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
		$q      = new WP_Query( [
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $per,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
			'fields'              => 'ids',
			'no_found_rows'       => true,
		] );
		$ids    = $q->posts;
		$offset = ( $paged - 1 ) * $per;
	} else {
		global $wp_query;
		$ids    = wp_list_pluck( $wp_query->posts, 'ID' );
		$paged  = max( 1, (int) get_query_var( 'paged' ) );
		$offset = ( $paged - 1 ) * (int) $wp_query->get( 'posts_per_page' );
	}

	if ( empty( $ids ) ) {
		return [];
	}

	$items = [];
	foreach ( array_values( $ids ) as $i => $id ) {
		$items[] = [
			'@type'    => 'ListItem',
			'position' => $offset + $i + 1,
			'url'      => get_permalink( $id ),
			'name'     => wp_strip_all_tags( get_the_title( $id ) ),
		];
	}

	return [
		'@type'           => 'ItemList',
		'numberOfItems'   => count( $items ),
		'itemListElement' => $items,
	];
}

/**
 * Yoast: صفحه‌ی لیست → CollectionPage با mainEntity از نوع ItemList.
 */
function senoobar_articles_yoast_webpage( $data ) {
	if ( ! is_array( $data ) || ! senoobar_articles_is_listing() || senoobar_articles_is_filtered_request() ) {
		return $data;
	}
	$data['@type'] = 'CollectionPage';
	$list          = senoobar_articles_item_list();
	if ( $list ) {
		$data['mainEntity'] = $list;
	}
	return $data;
}
add_filter( 'wpseo_schema_webpage', 'senoobar_articles_yoast_webpage', 20 );

/**
 * چاپ JSON-LD (امن برای تگ script).
 */
function senoobar_articles_print_jsonld( array $graph ): void {
	echo '<script type="application/ld+json">' . wp_json_encode(
		[ '@context' => 'https://schema.org', '@graph' => $graph ],
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
	) . "</script>\n";
}

/**
 * بدون Yoast: CollectionPage + BreadcrumbList برای لیست‌ها.
 */
function senoobar_articles_fallback_collection_jsonld(): void {
	if ( senoobar_seo_plugin_active() || ! senoobar_articles_is_listing() || senoobar_articles_is_filtered_request() ) {
		return;
	}
	$url   = senoobar_articles_listing_url();
	$name  = senoobar_articles_listing_name();
	$crumb = [
		[ '@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => home_url( '/' ) ],
		[ '@type' => 'ListItem', 'position' => 2, 'name' => 'مقالات', 'item' => senoobar_articles_url() ],
	];
	if ( is_category() || is_tag() ) {
		$crumb[] = [ '@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $url ];
	}
	$page = [
		'@type'      => 'CollectionPage',
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => $name,
		'inLanguage' => get_bloginfo( 'language' ),
		'isPartOf'   => [ '@id' => home_url( '/#website' ) ],
		'breadcrumb' => [ '@id' => $url . '#breadcrumb' ],
	];
	$list = senoobar_articles_item_list();
	if ( $list ) {
		$page['mainEntity'] = $list;
	}
	senoobar_articles_print_jsonld( [
		$page,
		[ '@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb', 'itemListElement' => $crumb ],
	] );
}
add_action( 'wp_head', 'senoobar_articles_fallback_collection_jsonld', 6 );
