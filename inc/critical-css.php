<?php
/**
 * Senoobar — Inline Critical CSS.
 *
 * محتوای assets/css/critical.css را مستقیم داخل <head> چاپ می‌کند تا
 * استایل پایه (reset + فونت + هدر + منو + دکمه + فوتر) همراه خود HTML
 * بیاید و در اولین بازدید (وقتی Service Worker هنوز نصب نیست یا سرور
 * کند است) صفحه unstyled (FOUC) نمایش داده نشود.
 *
 * آدرس فونت داخل critical.css نسبی است (../fonts/). چون اینجا inline
 * می‌شود (نه از داخل پوشه css)، آن را به آدرس مطلق پوشه تم تبدیل می‌کنیم.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function senoobar_print_critical_css() {
    $css_file = SENOOBAR_DIR . '/assets/css/critical.css';

    if ( ! file_exists( $css_file ) ) {
        return;
    }

    $css = file_get_contents( $css_file );
    if ( false === $css || '' === trim( $css ) ) {
        return;
    }

    // تبدیل آدرس نسبی فونت به آدرس مطلق تم
    $css = str_replace( "url('../fonts/", "url('" . SENOOBAR_URI . '/assets/fonts/', $css );

    echo "<style id=\"senoobar-critical-css\">\n" . $css . "\n</style>\n";
}
add_action( 'wp_head', 'senoobar_print_critical_css', 1 );

// Add preconnect for fonts and critical resources
add_action( 'wp_head', function() {
    // Preconnect to self-hosted font origin
    echo '<link rel="preconnect" href="' . esc_url(SENOOBAR_URI) . '" crossorigin>';
    // Preload critical font
    echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url(SENOOBAR_URI . '/assets/fonts/vazirmatn-arabic.woff2') . '">';
}, 0 );
