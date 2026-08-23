<?php
/**
 * WooCommerce Integration (v2)
 * Deep Green Palette — senoobar2 style
 */

if (!class_exists('WooCommerce')) {
    return;
}

// ─── 1. Theme Support ──────────────────────
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
});

// ─── 1b. Force cart + checkout templates independent of shortcode/content ──
// The shop page works even with empty content because it is an archive.
// Cart / checkout pages normally need the [woocommerce_cart] or
// [woocommerce_checkout] shortcode in their content to be recognized. This
// filter points them at the custom templates directly, so they render
// correctly even when the page content is empty (and lets us delete the page
// content entirely, matching the cart-page approach).
add_filter('template_include', function ($template) {
    $forced = '';
    if (function_exists('is_cart') && function_exists('is_checkout')) {
        $is_cart = is_cart();
        $is_checkout = is_checkout();

        // Fallback: if is_cart/is_checkout haven't resolved yet (they can be
        // unreliable at the template_include stage, especially when the page
        // content is empty), detect by page ID.
        if (!$is_cart && !$is_checkout && function_exists('wc_get_page_id')) {
            $cid = wc_get_page_id('cart');
            $kid = wc_get_page_id('checkout');
            if ($cid && is_page($cid)) {
                $is_cart = true;
            } elseif ($kid && is_page($kid)) {
                $is_checkout = true;
            }
        }

        // Last-resort: detect by the checkout/cart page shortcode existing in
        // the current queried object (in case page IDs are unset in WC settings).
        if (!$is_cart && !$is_checkout) {
            $q = get_queried_object();
            if ($q && !empty($q->post_content)) {
                if (strpos($q->post_content, '[woocommerce_cart]') !== false) {
                    $is_cart = true;
                } elseif (strpos($q->post_content, '[woocommerce_checkout]') !== false) {
                    $is_checkout = true;
                }
            }
        }

        if ($is_cart) {
            $forced = '/woocommerce/cart/cart.php';
        } elseif ($is_checkout) {
            $forced = '/woocommerce/checkout/form-checkout.php';
        }
    }
    if ($forced !== '') {
        $custom = get_stylesheet_directory() . $forced;
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
}, 100);

// ─── 1c. Remove login/coupon forms that render their own <form> tags ──
// The theme renders its own styled checkout form. The default WooCommerce
// login + coupon forms (hooked into woocommerce_checkout_login_form and
// woocommerce_checkout_coupon_form) wrap content in their own <form>, which
// nests inside our checkout form and breaks field submission.
add_action( 'init', function () {
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
    remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
    remove_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_login_form', 10 );
    remove_action( 'woocommerce_checkout_before_customer_details', 'woocommerce_checkout_coupon_form', 10 );
}, 20 );

// ─── 2. Kill default Woo styles ─────────────
add_filter('woocommerce_enqueue_styles', '__return_empty_array');

// ─── 3. Content wrappers ────────────────────
// archive-product.php handles its own wrapper, so only wrap in non-shop contexts
remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', function () {
    if (is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page()) {
        return;
    }
    echo '<main id="primary" class="site-main"><div class="container page-content">';
}, 10);

add_action('woocommerce_after_main_content', function () {
    if (is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() || is_account_page()) {
        return;
    }
    echo '</div></main>';
}, 10);

// ─── 4. Remove sidebar ──────────────────────
remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

// ─── 5. Products per page ───────────────────
add_filter('loop_shop_per_page', function () {
    return 24;
});

// ─── 6. Products per row ────────────────────
// CSS Grid handles columns
add_filter('loop_shop_columns', function () {
    return 3;
});

// Override WooCommerce body class columns
add_filter('body_class', function ($classes) {
    if (is_shop() || is_product_category() || is_product_tag()) {
        $classes = array_diff($classes, ['columns-4', 'columns-3', 'columns-2']);
        $classes[] = 'columns-4';
    }
    return $classes;
});

// Remove WooCommerce columns class from ul.products
add_filter('post_class', function ($classes) {
    if (function_exists('is_shop') && (is_shop() || is_product_category() || is_product_tag())) {
        $classes = array_diff($classes, ['columns-4', 'columns-3', 'columns-2']);
        $classes[] = 'columns-4';
    }
    return $classes;
});

// ─── 7. Sale badge ──────────────────────────
add_filter('woocommerce_sale_flash', function ($html) {
    return '<span class="onsale-badge">حراج</span>';
});

// ─── 8. Add-to-cart button text ──────────────
add_filter('woocommerce_product_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

add_filter('woocommerce_product_single_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

// ─── 9. Related products ─────────────────────
add_filter('woocommerce_output_related_products_args', function ($args) {
    $args['posts_per_page'] = 8;
    $args['columns']        = 4;
    return $args;
});

// ─── 10. AJAX add-to-cart ──────────────────
// Force-enable AJAX add-to-cart on archives so the button always carries the
// ajax_add_to_cart class + data-product_id, independent of the DB option
// (woocommerce_enable_ajax_add_to_cart) which can drift/be disabled and then
// WooCommerce falls back to a full-page reload.
// 'pre_option_' short-circuits get_option so the value is ALWAYS 'yes' without
// ever touching the database — the most reliable way to keep Ajax add-to-cart on.
add_filter('pre_option_woocommerce_enable_ajax_add_to_cart', function () {
    return 'yes';
});
add_filter('woocommerce_loop_add_to_cart_args', function ($args) {
    if (strpos($args['class'], 'ajax_add_to_cart') === false) {
        $args['class'] .= ' ajax_add_to_cart';
    }
    return $args;
});

// ─── 11. AJAX Cart Fragments (cart count) ──
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $count = WC()->cart->get_cart_contents_count();
    ob_start();
    ?>
    <span class="cart-badge"><?php echo $count; ?></span>
    <?php
    $fragments['.cart-badge'] = ob_get_clean();
    return $fragments;
});

// ─── 12. Disable password strength meter ────
add_action('wp_print_scripts', function () {
    if (wp_script_is('wc-password-strength-meter', 'enqueued')) {
        wp_dequeue_script('wc-password-strength-meter');
    }
}, 100);

// ─── 12b. Disable default Woo cart JS on our custom cart page ──
// The theme uses its own cart markup. NOTE: wc-checkout must stay enabled —
// WooCommerce's checkout (redirect to payment gateway, order creation) only
// runs through its AJAX handler (wc-ajax=checkout). Dequeuing wc-checkout
// makes the place-order button submit as a normal form post, which WooCommerce
// does not process: the page just reloads and no order is created.
add_action( 'wp_enqueue_scripts', function () {
    if ( is_cart() ) {
        wp_dequeue_script( 'wc-cart' );
        wp_deregister_script( 'wc-cart' );
    }
}, 100 );

// ─── 13. Persian translations for ALL buttons & labels ───

// Breadcrumbs
add_filter('woocommerce_breadcrumb_defaults', function ($args) {
    $args['home'] = 'خانه';
    return $args;
});

// Out of stock
add_filter('woocommerce_get_availability_text', function ($text, $product) {
    return $product->is_in_stock() ? 'موجود' : 'ناموجود';
}, 10, 2);

// Out of stock label
add_filter('woocommerce_out_of_stock_message', function () {
    return 'ناموجود';
});

// Add to cart
add_filter('woocommerce_product_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});
add_filter('woocommerce_product_single_add_to_cart_text', function () {
    return 'افزودن به سبد خرید';
});

// Select options (variable products)
add_filter('woocommerce_product_add_to_cart_text', function ($text, $product) {
    if ($product->is_type('variable')) {
        return 'انتخاب گزینه‌ها';
    }
    return $text;
}, 10, 2);

// Read more (out of stock products)
add_filter('woocommerce_loop_add_to_cart_link', function ($link, $product) {
    if (!$product->is_in_stock()) {
        return '<a href="' . get_permalink($product->get_id()) . '" class="button out-of-stock">مشاهده محصول</a>';
    }
    return $link;
}, 10, 2);

// Placeholder image text
add_filter('woocommerce_placeholder_img_src', function () {
    return '';
});

// Results count
add_filter('woocommerce_get_result_count', function ($html) {
    return '';
});

// Cart page
add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain !== 'woocommerce') return $translated;
    
    $persian = [
        'Product'             => 'محصول',
        'Products'            => 'محصولات',
        'Price'               => 'قیمت',
        'Quantity'            => 'تعداد',
        'Total'               => 'مجموع',
        'Subtotal'            => 'جمع جزء',
        'Cart'                => 'سبد خرید',
        'Shop'                => 'فروشگاه',
        'Home'                => 'خانه',
        'Search'              => 'جستجو',
        'Search results'      => 'نتایج جستجو',
        'Category'            => 'دسته‌بندی',
        'Categories'          => 'دسته‌بندی‌ها',
        'SKU'                 => 'کد محصول',
        'SKU:'                => 'کد محصول:',
        'Description'         => 'توضیحات',
        'Reviews'             => 'نظرات',
        'Review'              => 'نظر',
        'Additional information' => 'اطلاعات بیشتر',
        'Related products'    => 'محصولات مرتبط',
        'You may also like'   => 'ممکن است بپسندید',
        'View cart'           => 'مشاهده سبد خرید',
        'Checkout'            => 'تسویه حساب',
        'Proceed to checkout' => 'ادامه جهت تسویه حساب',
        'Place order'         => 'ثبت سفارش',
        'Update cart'         => 'بروزرسانی سبد خرید',
        'Apply coupon'        => 'اعمال کد تخفیف',
        'Coupon code'         => 'کد تخفیف',
        'Coupon'              => 'کد تخفیف',
        'Have a coupon?'       => 'کد تخفیف دارید؟',
        'Click here to enter your code' => 'کد خود را وارد کنید',
        'Continue shopping'   => 'ادامه خرید',
        'Shipping'            => 'حمل و نقل',
        'Flat rate'           => 'نرخ ثابت',
        'Free shipping'       => 'ارسال رایگان',
        'Billing details'     => 'اطلاعات صورتحساب',
        'First name'          => 'نام',
        'Last name'           => 'نام خانوادگی',
        'Phone'               => 'تلفن',
        'Address'             => 'آدرس',
        'City'                => 'شهر',
        'Province'            => 'استان',
        'Postcode'            => 'کد پستی',
        'Order notes'         => 'یادداشت سفارش',
        '(optional)'          => '(اختیاری)',
        'Your order'          => 'سفارش شما',
        'Payment'             => 'پرداخت',
        'Cash on delivery'    => 'پرداخت در محل',
        'Thank you.'           => 'متشکریم.',
        'Thank you. Your order has been received.' => 'متشکریم. سفارش شما ثبت شد.',
        'Order received'      => 'سفارش دریافت شد',
        'Thank you'           => 'متشکریم',
        'Your order'          => 'سفارش شما',
        'Order number'        => 'شماره سفارش',
        'Date'                => 'تاریخ',
        'Email'               => 'ایمیل',
        'Total'               => 'مجموع',
        'Payment method'      => 'روش پرداخت',
        'Loading'             => 'در حال بارگذاری',
        'Load more'           => 'بارگذاری بیشتر',
        'Show all'            => 'نمایش همه',
        'New'                 => 'جدید',
        'Sale'                => 'حراج',
        'Save'                => 'ذخیره',
        'Filter'              => 'فیلتر',
        'Previous'            => 'قبلی',
        'Next'                => 'بعدی',
        'No products found'   => 'محصولی یافت نشد',
        'No products were found matching your selection.' => 'هیچ محصولی با این شرایط یافت نشد.',
        'Go to shop'          => 'رفتن به فروشگاه',
        'Add to wishlist'     => 'افزودن به علاقه‌مندی‌ها',
        'Browse wishlist'     => 'مشاهده علاقه‌مندی‌ها',
        'Add to cart'        => 'افزودن به سبد خرید',
        'Select options'      => 'انتخاب گزینه‌ها',
        'Read more'           => 'اطلاعات بیشتر',
        'View products'       => 'مشاهده محصولات',
        'Out of stock'        => 'ناموجود',
        'In stock'            => 'موجود',
        'Availability'        => 'موجودی',
        'Weight'              => 'وزن',
        'Dimensions'          => 'ابعاد',
        'Share'               => 'اشتراک‌گذاری',
        'Return to shop'      => 'بازگشت به فروشگاه',
        'Recently viewed'     => 'بازدیدهای اخیر',
        'Top rated products'  => 'محصولات برتر',
        'On sale'             => 'حراج',
        'Best sellers'        => 'پرفروش‌ترین‌ها',
        'Featured products'   => 'محصولات ویژه',
        'New products'        => 'محصولات جدید',
        'Popular'             => 'محبوب‌ترین',
        'Average rating'      => 'امتیاز متوسط',
        'Rated'               => 'امتیاز',
        'out of 5'            => 'از ۵',
        'Customer reviews'    => 'نظرات مشتریان',
        'Add a review'        => 'ثبت نظر',
        'Submit'              => 'ارسال',
        'Your rating'         => 'امتیاز شما',
        'Your review'         => 'نظر شما',
        'No reviews yet'      => 'هنوز نظری ثبت نشده',
        'Be the first to review' => 'اولین نفر باشید که نظر می‌دهد',
        'Login'               => 'ورود',
        'Register'            => 'ثبت نام',
        'Username'            => 'نام کاربری',
        'Password'            => 'رمز عبور',
        'Remember me'         => 'مرا به خاطر بسپار',
        'Lost your password?'  => 'رمز عبور را فراموش کرده‌اید؟',
        'My Account'          => 'حساب کاربری',
        'Dashboard'           => 'پیشخوان',
        'Orders'              => 'سفارش‌ها',
        'Downloads'           => 'دانلودها',
        'Addresses'           => 'آدرس‌ها',
        'Account details'     => 'جزئیات حساب',
        'Logout'              => 'خروج',
        'View'                => 'مشاهده',
        'Edit'                => 'ویرایش',
        'Delete'              => 'حذف',
        'Cancel'              => 'لغو',
        'Confirm'             => 'تایید',
        'Back'                => 'بازگشت',
        'Close'               => 'بستن',
        'Reset'               => 'بازنشانی',
        'Clear'               => 'پاک کردن',
        'Apply'               => 'اعمال',
        'Choose an option'    => 'انتخاب گزینه',
        'Sort by popularity'  => 'مرتب‌سازی بر اساس محبوبیت',
        'Sort by average rating' => 'مرتب‌سازی بر اساس امتیاز',
        'Sort by latest'       => 'مرتب‌سازی بر اساس جدیدترین',
        'Sort by price: low to high' => 'مرتب‌سازی بر اساس قیمت: کم به زیاد',
        'Sort by price: high to low' => 'مرتب‌سازی بر اساس قیمت: زیاد به کم',
        'Default sorting'     => 'مرتب‌سازی پیش‌فرض',
        'Show'                => 'نمایش',
        'per page'            => 'در هر صفحه',
        'All'                 => 'همه',
        'Pages'               => 'صفحات',
        'Page'                => 'صفحه',
        'of'                  => 'از',
    ];
    
    if (isset($persian[$text])) {
        return $persian[$text];
    }
    return $translated;
}, 20, 3);


/* ─── Product page: hide availability + compact currency ── */

// Hide the "موجود / ناموجود" stock line on the single product page.
add_filter('woocommerce_get_availability_text', function ($availability, $product) {
    if (is_product()) {
        return '';
    }
    return $availability;
}, 20, 2);

// Hide the stock HTML too (in case a plugin/template prints wc_get_stock_html).
add_filter('woocommerce_get_stock_html', function ($html, $product) {
    if (is_product()) {
        return '';
    }
    return $html;
}, 20, 2);

// Currency: always place the "تومان" symbol AFTER the number (right side),
// regardless of the `woocommerce_currency_pos` option, so it reads correctly
// in RTL: "۱۲٬۰۰۰٬۰۰۰ تومان" (number first, symbol to its left).
add_filter('woocommerce_price_format', function ($format, $currency_pos) {
    return '%2$s%1$s'; // price then currency symbol
}, 20, 2);

// Currency: render a lightweight, styled "تومان" label.
add_filter('woocommerce_currency_symbol', function ($symbol, $currency) {
    if ($symbol === 'تومان' || mb_strtolower($symbol) === 'toman' || $symbol === 'ت') {
        return '<span class="pd-currency">تومان</span>';
    }
    return $symbol;
}, 20, 2);

// Replace literal "تومان" with the styled label (covers any text emission).
add_filter('gettext', function ($translated, $text, $domain) {
    if ($domain === 'woocommerce' && $text === 'تومان') {
        return '<span class="pd-currency">تومان</span>';
    }
    return $translated;
}, 30, 3);

// Price suffix: wrap تومان in the styled label.
add_filter('woocommerce_get_price_suffix', function ($suffix, $product) {
    return str_replace('تومان', '<span class="pd-currency">تومان</span>', $suffix);
}, 20, 2);


/* ─── Live cart badge fragments ─────────────────────── */
// Keep the header + bottom-nav cart badges in sync after any AJAX add/remove.
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

    // Header badge
    $fragments['.cart-badge[data-cart-count]'] = sprintf(
        '<span class="cart-badge%s" data-cart-count>%d</span>',
        $count > 0 ? '' : ' is-hidden',
        $count
    );

    // Bottom nav badge
    $fragments['.mbn-badge[data-cart-count]'] = sprintf(
        '<span class="mbn-badge%s" data-cart-count>%d</span>',
        $count > 0 ? '' : ' is-hidden',
        $count
    );

    return $fragments;
});


/* ══════════════════════════════════════════════════════
   CHECKOUT — minimal fields (name, phone, province, address)
══════════════════════════════════════════════════════ */

// Iran provinces (state field options) + a default, ordering-friendly list.
function senoobar_iran_provinces() {
    return [
        'تهران' => 'تهران',
        'البرز' => 'البرز',
        'اصفهان' => 'اصفهان',
        'فارس' => 'فارس',
        'خراسان رضوی' => 'خراسان رضوی',
        'خراسان شمالی' => 'خراسان شمالی',
        'خراسان جنوبی' => 'خراسان جنوبی',
        'آذربایجان شرقی' => 'آذربایجان شرقی',
        'آذربایجان غربی' => 'آذربایجان غربی',
        'اردبیل' => 'اردبیل',
        'ایلام' => 'ایلام',
        'بوشهر' => 'بوشهر',
        'چهارمحال و بختیاری' => 'چهارمحال و بختیاری',
        'خوزستان' => 'خوزستان',
        'زنجان' => 'زنجان',
        'سمنان' => 'سمنان',
        'سیستان و بلوچستان' => 'سیستان و بلوچستان',
        'قزوین' => 'قزوین',
        'قم' => 'قم',
        'کردستان' => 'کردستان',
        'کرمان' => 'کرمان',
        'کرمانشاه' => 'کرمانشاه',
        'کهگیلویه و بویراحمد' => 'کهگیلویه و بویراحمد',
        'گلستان' => 'گلستان',
        'گیلان' => 'گیلان',
        'لرستان' => 'لرستان',
        'مازندران' => 'مازندران',
        'مرکزی' => 'مرکزی',
        'هرمزگان' => 'هرمزگان',
        'همدان' => 'همدان',
        'یزد' => 'یزد',
    ];
}

add_filter('woocommerce_checkout_fields', function ($fields) {

    // ── Billing: keep only name, last name, phone, province, address ──
    $billing = [
        'billing_first_name' => [
            'type'        => 'text',
            'label'       => 'نام',
            'placeholder' => 'نام شما',
            'required'    => true,
            'class'       => ['form-row-first', 'senoobar-field'],
            'priority'    => 10,
        ],
        'billing_last_name' => [
            'type'        => 'text',
            'label'       => 'نام خانوادگی',
            'placeholder' => 'نام خانوادگی شما',
            'required'    => true,
            'class'       => ['form-row-last', 'senoobar-field'],
            'priority'    => 20,
        ],
        'billing_phone' => [
            'type'        => 'tel',
            'label'       => 'شماره موبایل',
            'placeholder' => '۰۹۱۲ ۳۴۵ ۶۷۸۹',
            'required'    => true,
            'class'       => ['form-row-wide', 'senoobar-field'],
            'priority'    => 30,
            'input_class' => ['senoobar-input'],
        ],
        'billing_state' => [
            'type'        => 'state',
            'label'       => 'استان',
            'placeholder' => 'انتخاب استان',
            'required'    => true,
            'class'       => ['form-row-wide', 'senoobar-field'],
            'priority'    => 40,
            'options'     => senoobar_iran_provinces(),
            'country'     => 'IR',
        ],
        'billing_postcode' => [
            'type'        => 'text',
            'label'       => 'کد پستی',
            'placeholder' => 'کد پستی ۱۰ رقمی',
            'required'    => true,
            'class'       => ['form-row-wide', 'senoobar-field'],
            'priority'    => 45,
            'input_class' => ['senoobar-input'],
            'maxlength'   => 10,
        ],
        'billing_address_1' => [
            'type'        => 'textarea',
            'label'       => 'آدرس',
            'placeholder' => 'آدرس کامل پستی شما',
            'required'    => true,
            'class'       => ['form-row-wide', 'senoobar-field'],
            'priority'    => 50,
        ],
    ];

    // Country is required by core for address (state/postcode) validation.
    // We hide it and force it to IR (Iran).
    $billing['billing_country'] = [
        'type'        => 'hidden',
        'label'       => '',
        'required'    => false,
        'default'     => 'IR',
        'class'       => ['form-row-wide', 'senoobar-hidden-field'],
        'priority'    => 55,
    ];

    // Email is required by core but we hide it from the UI and auto-fill it
    // from the phone number when the order is created (see below).
    $billing['billing_email'] = [
        'type'        => 'hidden',
        'label'       => '',
        'required'    => false,
        'class'       => ['form-row-wide', 'senoobar-hidden-field'],
        'priority'    => 60,
    ];

    $fields['billing'] = $billing;

    // ── Shipping: not collected (the bill-to address is the ship-to address).
    // Removing the shipping fields entirely prevents WooCommerce from
    // validating shipping address fields that our form never renders.
    if (isset($fields['shipping'])) {
        $fields['shipping'] = [];
    }

    // ── Account: drop the optional account section on checkout ──
    if (isset($fields['account'])) {
        $fields['account'] = [];
    }

    return $fields;
}, 30);

// Force ship-to = bill-to so no separate shipping address is ever needed.
add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );

// Auto-fill a synthetic email from the phone so core order checks pass even
// though the customer never sees or types an email.
add_action('woocommerce_checkout_process', function () {
    if (empty($_POST['billing_email'])) {
        $phone = sanitize_text_field($_POST['billing_phone'] ?? '');
        $phone = preg_replace('/\D+/', '', $phone);
        if ($phone !== '') {
            $_POST['billing_email'] = $phone . '@senoobar.local';
        }
    }
}, 10);
