<?php
define('SENOOBAR_VERSION', '2.18.0');
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
require_once SENOOBAR_DIR . '/inc/google-tags.php';
require_once SENOOBAR_DIR . '/inc/critical-css.php';
require_once SENOOBAR_DIR . '/inc/schema-currency.php';
require_once SENOOBAR_DIR . '/inc/business-location.php';
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


/**
 * Responsive WebP candidates for bundled hero images.
 * Files are generated in uploads from wp-admin, never during a public request.
 */
function senoobar_hero_responsive_data($filename) {
    $uploads = wp_upload_dir();
    $dir = trailingslashit($uploads['basedir']) . 'senoobar-hero/';
    $url = trailingslashit($uploads['baseurl']) . 'senoobar-hero/';
    $stem = sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME));
    $candidates = [];
    foreach ([320, 480, 640, 800] as $width) {
        $path = $dir . $stem . '-' . $width . '.webp';
        if (is_readable($path) && filesize($path) > 0) {
            $candidates[] = esc_url($url . $stem . '-' . $width . '.webp') . ' ' . $width . 'w';
        }
    }
    $srcset = implode(', ', $candidates);
    $last_candidate = $candidates ? explode(' ', end($candidates), 2) : [];
    return [
        'srcset' => $srcset,
        'sizes' => '50vw',
        'src' => !empty($last_candidate[0]) ? $last_candidate[0] : '',
    ];
}

function senoobar_generate_hero_responsive_images() {
    if (!current_user_can('manage_options')) return;
    $attempted = (int) get_option('senoobar_hero_responsive_attempted_v2', 0);
    if ($attempted && (time() - $attempted) < WEEK_IN_SECONDS) return;
    $uploads = wp_upload_dir();
    if (!empty($uploads['error'])) return;
    $dir = trailingslashit($uploads['basedir']) . 'senoobar-hero/';
    if (!wp_mkdir_p($dir)) return;

    // Mark the attempt only after the upload directory is confirmed writable.
    update_option('senoobar_hero_responsive_attempted_v2', time(), false);

    foreach (['hero-1.webp', 'hero-2.webp'] as $filename) {
        $source = trailingslashit(SENOOBAR_DIR) . 'assets/images/' . $filename;
        if (!is_readable($source)) continue;
        foreach ([320, 480, 640, 800] as $width) {
            $target = $dir . pathinfo($filename, PATHINFO_FILENAME) . '-' . $width . '.webp';
            $editor = wp_get_image_editor($source);
            if (is_wp_error($editor)) continue;
            $resize_result = $editor->resize($width, (int) round($width * 1.25), false);
            if (is_wp_error($resize_result)) continue;
            $save_result = $editor->save($target, 'image/webp');
            if (is_wp_error($save_result) && file_exists($target)) unlink($target);
        }
    }
}
add_action('admin_init', 'senoobar_generate_hero_responsive_images', 99);

/**
 * Match WooCommerce thumbnail srcset selection to the real homepage product grid.
 * The homepage uses 2 columns on mobile, 3 on tablet and 4 on desktop; the
 * default WooCommerce sizes value (300px) overstates the mobile slot and can
 * cause unnecessarily large source candidates to be selected.
 */
function senoobar_responsive_home_product_thumbnail_sizes($attr, $attachment, $size) {
    if (is_admin() || !is_front_page()) {
        return $attr;
    }

    if (empty($attr['class']) || strpos($attr['class'], 'woocommerce_thumbnail') === false) {
        return $attr;
    }

    $attr['sizes'] = '(max-width: 639px) calc(50vw - 24px), (max-width: 991px) calc(33.333vw - 24px), 25vw';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'senoobar_responsive_home_product_thumbnail_sizes', 20, 3);

/**
 * Avoid downloading 600px product images for homepage cards whose rendered
 * thumbnail is about 300px or smaller. The 300px generated WooCommerce
 * thumbnail remains the largest candidate on the homepage; other pages and
 * full-size product/gallery images retain their normal high-DPI srcset.
 */
function senoobar_limit_home_product_thumbnail_srcset($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    if (is_admin() || !is_front_page() || empty($sources) || !function_exists('is_a')) {
        return $sources;
    }

    global $product;
    if (!isset($product) || !is_object($product) || !is_a($product, 'WC_Product') || (int) $product->get_image_id() !== (int) $attachment_id) {
        return $sources;
    }

    if (empty($size_array[0]) || empty($size_array[1]) || max((int) $size_array[0], (int) $size_array[1]) > 320) {
        return $sources;
    }

    foreach ($sources as $width => $source) {
        if (!empty($source['value']) && (int) $source['value'] > 320) {
            unset($sources[$width]);
        }
    }

    return $sources;
}
add_filter('wp_calculate_image_srcset', 'senoobar_limit_home_product_thumbnail_srcset', 20, 5);

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
