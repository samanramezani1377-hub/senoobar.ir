<?php
/**
 * Public, read-only WebMCP integration for Senoobar.
 * No custom REST routes, cron jobs, background workers, or admin assets.
 */
defined('ABSPATH') || exit;

function senoobar_webmcp_enqueue_assets() {
    if (is_admin()) {
        return;
    }

    $relative_path = '/assets/js/webmcp.js';
    $file_path = SENOOBAR_DIR . $relative_path;
    if (!is_readable($file_path)) {
        return;
    }

    // Bust browser/CDN caches whenever this file changes, even if the theme version stays the same.
    $asset_version = filemtime($file_path);
    if ($asset_version === false) {
        $asset_version = defined('SENOOBAR_VERSION') ? SENOOBAR_VERSION : null;
    }

    wp_enqueue_script(
        'senoobar-webmcp',
        SENOOBAR_URI . $relative_path,
        [],
        $asset_version,
        true
    );

    $shop_url = function_exists('wc_get_page_permalink') && class_exists('WooCommerce')
        ? wc_get_page_permalink('shop')
        : home_url('/shop/');

    $contact_page = get_page_by_path('contact');
    if (!$contact_page || $contact_page->post_status !== 'publish') {
        $contact_page = get_page_by_path('contact-us');
    }

    wp_localize_script('senoobar-webmcp', 'SenoobarWebMCPConfig', [
        'storeApiBase' => esc_url_raw(rest_url('wc/store/v1/')),
        'storeName' => wp_strip_all_tags(get_bloginfo('name')),
        'homeUrl' => esc_url_raw(home_url('/')),
        'shopUrl' => esc_url_raw($shop_url),
        'contactUrl' => ($contact_page && $contact_page->post_status === 'publish')
            ? esc_url_raw(get_permalink($contact_page))
            : '',
    ]);
}
add_action('wp_enqueue_scripts', 'senoobar_webmcp_enqueue_assets', 30);
