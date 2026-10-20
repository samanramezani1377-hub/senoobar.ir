<?php
/**
 * Senoobar — صفحه‌ی «مقالات» (آرشیو مقالات).
 *
 * - یک صفحه‌ی «مقالات» با قالب template-articles.php یک‌بار به‌صورت خودکار ساخته می‌شود
 *   (مثل صفحه‌ی علاقه‌مندی‌ها) و شناسه‌اش ذخیره می‌شود.
 * - senoobar_articles_url() همیشه یک آدرس معتبر برمی‌گرداند؛ لینک «مشاهده همه مقالات»
 *   صفحه‌ی اصلی هم از همین استفاده می‌کند.
 * - senoobar_reading_time() زمان مطالعه‌ی تقریبی هر مقاله را (به دقیقه) برمی‌گرداند.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * آدرس صفحه‌ی مقالات.
 * ترتیب: صفحه‌ی ساخته‌شده با قالب مقالات ← «صفحه‌ی نوشته‌ها»ی وردپرس ← آرشیو ساده‌ی نوشته‌ها.
 */
function senoobar_articles_url(): string {
	$id = (int) get_option( 'senoobar_articles_page_id' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}

	$pages = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'template-articles.php',
		'number'     => 1,
	] );
	if ( ! empty( $pages ) ) {
		update_option( 'senoobar_articles_page_id', $pages[0]->ID );
		return get_permalink( $pages[0]->ID );
	}

	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( $posts_page_id ) {
		return get_permalink( $posts_page_id );
	}

	return home_url( '/?post_type=post' );
}

function senoobar_ensure_articles_page(): void {
	if ( get_option( 'senoobar_articles_page_created' ) ) {
		return;
	}

	$existing = get_pages( [
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'template-articles.php',
		'number'     => 1,
	] );
	if ( ! empty( $existing ) ) {
		update_option( 'senoobar_articles_page_id', $existing[0]->ID );
		update_option( 'senoobar_articles_page_created', 1 );
		return;
	}

	$page_id = wp_insert_post( [
		'post_title'   => 'مقالات',
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => 'articles',
		'post_content' => '',
	], true );

	if ( ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'template-articles.php' );
		update_option( 'senoobar_articles_page_id', $page_id );
	}

	update_option( 'senoobar_articles_page_created', 1 );
}
add_action( 'init', 'senoobar_ensure_articles_page', 20 );

/**
 * زمان مطالعه (دقیقه) — حدود ۲۰۰ کلمه در دقیقه برای متن فارسی.
 */
function senoobar_reading_time( $post = null ): int {
	$post = get_post( $post );
	if ( ! $post ) {
		return 1;
	}
	$text  = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$words = preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY );
	return max( 1, (int) ceil( count( $words ) / 200 ) );
}
