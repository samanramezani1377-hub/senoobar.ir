<?php
define('SENOOBAR_VERSION', '2.16.1');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

/**
 * تبدیل ارقام فارسی به انگلیسی و پاک‌سازی برای href="tel:".
 * یک‘جا تعریف می‌شود تا در فوتر و صفحات از تداخل (redeclare) جلوگیری شود.
 */
if ( ! function_exists( 'senoobar_tel_href' ) ) {
	function senoobar_tel_href( $num ) {
		$en = str_replace(
			['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],
			['0','1','2','3','4','5','6','7','8','9'],
			(string) $num
		);
		return preg_replace( '/[^0-9+]/', '', $en );
	}
}

require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';
require_once SENOOBAR_DIR . '/inc/critical-css.php';
require_once SENOOBAR_DIR . '/inc/cart-handlers.php';
require_once SENOOBAR_DIR . '/inc/woocommerce-setup.php';
require_once SENOOBAR_DIR . '/inc/cart-page-setup.php';
require_once SENOOBAR_DIR . '/inc/newsletter-handlers.php';
require_once SENOOBAR_DIR . '/inc/push-handlers.php';
require_once SENOOBAR_DIR . '/inc/wishlist-page-setup.php';
require_once SENOOBAR_DIR . '/inc/legal-pages-setup.php';
require_once SENOOBAR_DIR . '/inc/account.php';
require_once SENOOBAR_DIR . '/inc/password-change.php';
require_once SENOOBAR_DIR . '/inc/shipping-display.php';
require_once SENOOBAR_DIR . '/inc/wishlist.php';
require_once SENOOBAR_DIR . '/inc/otp-login.php';
require_once SENOOBAR_DIR . '/inc/otp-register.php';
require_once SENOOBAR_DIR . '/inc/seo.php';
require_once SENOOBAR_DIR . '/inc/showroom-pages-setup.php';
require_once SENOOBAR_DIR . '/inc/ideas-setup.php';
require_once SENOOBAR_DIR . '/inc/idea-admin.php';
require_once SENOOBAR_DIR . '/inc/bulk-post-importer.php';

function senoobar_init() {
    Senoobar_Theme::get_instance()->init();
}
add_action('after_setup_theme', 'senoobar_init');

function senoobar_img($src, $attr = []) {
    $alt    = isset($attr['alt'])    ? $attr['alt']    : '';
    $width  = isset($attr['width'])  ? ' width="'  . (int)$attr['width']  . '"' : '';
    $height = isset($attr['height']) ? ' height="' . (int)$attr['height'] . '"' : '';
    $extra  = '';
    foreach ($attr as $k => $v) {
        if (in_array($k, ['alt', 'width', 'height'], true)) continue;
        $extra .= ' ' . $k . '="' . esc_attr($v) . '"';
    }
    return '<img src="' . esc_url($src) . '" alt="' . esc_attr($alt) . '"' . $width . $height . $extra . '>';
}

function senoobar_optimize_jquery() {
    if (is_admin()) return;
    if (function_exists('is_checkout') && is_checkout()) return;
    wp_deregister_script('jquery-migrate');
    wp_dequeue_script('jquery-migrate');
    add_filter('script_loader_tag', function ($tag, $handle) {
        if ($handle === 'jquery-core' || $handle === 'jquery') {
            return str_replace(' src', ' defer src', $tag);
        }
        return $tag;
    }, 10, 2);
}
add_action('wp_enqueue_scripts', 'senoobar_optimize_jquery', 20);

/**
 * Prioritize the Persian Vazirmatn subset.
 */
function senoobar_preload_vazirmatn_arabic() {
    if (is_admin()) return;
    echo '<link rel="preload" href="' . esc_url(SENOOBAR_URI . '/assets/fonts/vazirmatn-arabic.woff2') . '" as="font" type="font/woff2" crossorigin>';
}
add_action('wp_head', 'senoobar_preload_vazirmatn_arabic', 0);

/**
 * Keep the newsletter script out of the browser's critical request chain.
 */
function senoobar_defer_newsletter_script($tag, $handle) {
    if (is_admin()) return $tag;
    if (strpos($tag, '/assets/js/newsletter.js') === false) return $tag;
    if (strpos($tag, ' defer') !== false) return $tag;
    return str_replace(' src', ' defer src', $tag);
}
add_filter('script_loader_tag', 'senoobar_defer_newsletter_script', 20, 2);

/**
 * Keep cart and wishlist JavaScript out of the render-blocking critical path.
 */
function senoobar_defer_cart_wishlist_scripts($tag, $handle) {
    if (is_admin()) return $tag;

    $is_target = (
        strpos($tag, '/assets/js/cart.js') !== false ||
        strpos($tag, '/assets/js/wishlist.js') !== false
    );

    if (!$is_target || strpos($tag, ' defer') !== false) return $tag;

    return str_replace(' src', ' defer src', $tag);
}
add_filter('script_loader_tag', 'senoobar_defer_cart_wishlist_scripts', 20, 2);

/**
 * Reduce main-thread Style & Layout work by allowing the browser to skip
 * rendering work for sections that are below the viewport until they are
 * near the viewport.
 */
function senoobar_reduce_below_fold_layout_work() {
    if (is_admin()) return;
    ?>
    <style id="senoobar-below-fold-performance">
      .cats-section,
      .section {
        content-visibility: auto;
        contain-intrinsic-size: auto 500px;
      }
    </style>
    <?php
}
add_action('wp_head', 'senoobar_reduce_below_fold_layout_work', 99);
