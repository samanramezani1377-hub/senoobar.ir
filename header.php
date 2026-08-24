<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e3a2f">
    <link rel="manifest" href="<?php echo home_url('manifest.json'); ?>">
    <?php $custom_logo_id = get_theme_mod('custom_logo'); if($custom_logo_id){echo '<link rel="apple-touch-icon" href="'.esc_url(wp_get_attachment_url($custom_logo_id)).'">';} ?>
    
    <!-- Preconnect to critical origins -->
    <link rel="preconnect" href="<?php echo esc_url(home_url('/')); ?>" crossorigin>
    <link rel="preconnect" href="<?php echo esc_url(SENOOBAR_URI); ?>" crossorigin>
    <link rel="dns-prefetch" href="<?php echo esc_url(home_url('/')); ?>">
    <link rel="dns-prefetch" href="<?php echo esc_url(SENOOBAR_URI); ?>">
    
    <!-- Preload critical font -->
    <link rel="preload" as="font" type="font/woff2" crossorigin href="<?php echo esc_url(SENOOBAR_URI . '/assets/fonts/vazirmatn-arabic.woff2'); ?>">
    
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('پرش به محتوا', 'senoobar'); ?></a>

<!-- Announcement Bar -->
<div class="ann-bar">
    <div class="container ann-bar__inner">
        <span><?php echo esc_html(get_theme_mod('senoobar_announcement', '🚚 ارسال به سراسر کشور | 💳 خرید اقساطی ۳ ماهه بدون کارمزد | 🕐 شنبه تا پنجشنبه ۱۰ صبح تا ۹ شب')); ?></span>
    </div>
</div>

<!-- Site Header -->
<header id="masthead" class="site-header">
    <div class="container header-inner">

        <!-- Branding -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-branding" style="display:flex;align-items:center;gap:8px;text-decoration:none;color:inherit;">
            <?php
            $logo_id = get_theme_mod('custom_logo');
            if ($logo_id):
                // Get logo dimensions for CLS prevention
                $logo_w = 180; $logo_h = 60;
                $logo_meta = wp_get_attachment_metadata($logo_id);
                if ($logo_meta && isset($logo_meta['width'], $logo_meta['height'])) {
                    $logo_w = $logo_meta['width'];
                    $logo_h = $logo_meta['height'];
                }
                echo wp_get_attachment_image($logo_id, 'full', false, ['class' => 'site-logo', 'alt' => get_bloginfo('name'), 'width' => $logo_w, 'height' => $logo_h]);
            else:
            ?>
            <div class="logo-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L8 8H4l4 4-1.5 6L12 15l5.5 3L16 12l4-4h-4L12 2z"/>
                </svg>
            </div>
            <div>
                <div class="logo-text"><?php bloginfo('name'); ?></div>
                <div class="logo-sub"><?php bloginfo('description'); ?></div>
            </div>
            <?php endif; ?>
        </a>

        <!-- Search (Desktop) -->
        <div class="header-search">
            <form role="search" method="get" class="header-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
                <button type="submit" aria-label="<?php esc_attr_e('جستجو', 'senoobar'); ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <?php if (class_exists('WooCommerce')): ?>
            <!-- Cart -->
            <a href="<?php echo wc_get_cart_url(); ?>" class="header-action-btn">
                <div style="position:relative;" data-cart-fly="header">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <?php $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
                    <span class="cart-badge<?php echo $count > 0 ? '' : ' is-hidden'; ?>" data-cart-count><?php echo $count; ?></span>
                </div>
                <span class="action-label">سبد خرید</span>
            </a>
            <!-- Wishlist -->
            <?php $senoobar_wishlist_url = function_exists('senoobar_wishlist_page_url') ? senoobar_wishlist_page_url() : ''; if (empty($senoobar_wishlist_url)) $senoobar_wishlist_url = home_url('/wishlist/'); ?>
            <a href="<?php echo esc_url($senoobar_wishlist_url); ?>" class="header-action-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
                <span class="action-label">علاقه‌مندی‌ها</span>
            </a>
            <!-- Account -->
            <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="header-action-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <span class="action-label">حساب کاربری</span>
            </a>
            <?php endif; ?>
            <!-- Push Notifications -->
            <button id="js-push-subscribe" class="header-action-btn push-action-btn" aria-label="نوتیفیکیشن">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <span class="action-label" id="pushActionLabel">دریافت نوتیفیکیشن</span>
            </button>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" aria-label="منو" id="menuToggle" type="button">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Main Navigation (Desktop) -->
    <nav class="main-navigation" style="border-top:1px solid var(--color-gray-100);">
        <div class="container">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'main-menu',
                'container'      => false,
                'fallback_cb'    => false,
            ]);
            ?>
        </div>
    </nav>
</header>

<!-- Mobile Menu -->
<?php
// ── Mobile Sidebar data (configurable via Customizer → ☰ سایدبار موبایل) ──

$snb_shop_url = function_exists('wc_get_page_permalink') && class_exists('WooCommerce')
    ? wc_get_page_permalink('shop') : home_url('/shop/');

// Section visibility + order
$snb_sec_enabled = [];
$snb_sec_order   = [];
foreach (['search','categories','quicklinks','newsletter'] as $k) {
    $snb_sec_enabled[$k] = (bool) get_theme_mod("senoobar_menu_{$k}_enabled", '1');
    $snb_sec_order[$k]   = (int) get_theme_mod("senoobar_menu_{$k}_order", ['search'=>10,'categories'=>20,'quicklinks'=>30,'newsletter'=>40][$k]);
}

// Build ordered list of enabled sections
$snb_sections = [];
foreach (['search','categories','quicklinks','newsletter'] as $k) {
    if ($snb_sec_enabled[$k]) {
        $snb_sections[$k] = $snb_sec_order[$k];
    }
}
asort($snb_sections); // sort by order asc

// Categories (hierarchical, filtered + ordered)
$snb_cat_tree = [];
$snb_children_map = [];
if (class_exists('WooCommerce') && $snb_sec_enabled['categories']) {
    $snb_all_cats = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);
    if (!is_wp_error($snb_all_cats)) {
        $snb_by_parent = [];
        foreach ($snb_all_cats as $c) {
            // filter: only show categories enabled in customizer
            if (!get_theme_mod("senoobar_menu_cat_{$c->term_id}_enabled", '1')) {
                continue;
            }
            $snb_by_parent[(int)$c->parent][] = $c;
        }
        $snb_children_map = $snb_by_parent;
        $snb_cat_tree = isset($snb_by_parent[0]) ? $snb_by_parent[0] : [];
        // sort top-level by order
        usort($snb_cat_tree, function($a, $b) {
            $oa = (int) get_theme_mod("senoobar_menu_cat_{$a->term_id}_order", 99);
            $ob = (int) get_theme_mod("senoobar_menu_cat_{$b->term_id}_order", 99);
            return $oa <=> $ob;
        });
    }
}

// Recursive category renderer (supports unlimited nesting levels)
function senoobar_render_menu_cat( $term, $children_map, $depth = 0 ) {
    $kids = isset( $children_map[ $term->term_id ] ) ? $children_map[ $term->term_id ] : [];
    if ( $kids ) {
        usort( $kids, function( $a, $b ) {
            $oa = (int) get_theme_mod( "senoobar_menu_cat_{$a->term_id}_order", 99 );
            $ob = (int) get_theme_mod( "senoobar_menu_cat_{$b->term_id}_order", 99 );
            return $oa <=> $ob;
        });
    }
    $is_top  = ( $depth === 0 );
    $has_kids = ! empty( $kids );
    $cls = $is_top ? 'mobile-cat-link' : 'mobile-cat-child';
    ?>
    <div class="<?php echo $is_top ? 'mobile-cat-group' : 'mobile-subcat-group'; ?>">
        <a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="<?php echo esc_attr( $cls ); ?>">
            <span><?php echo esc_html( $term->name ); ?></span>
            <?php if ( $is_top ): ?>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            <?php endif; ?>
        </a>
        <?php if ( $has_kids ): ?>
            <div class="mobile-cat-children">
                <?php foreach ( $kids as $kid ): ?>
                    <?php senoobar_render_menu_cat( $kid, $children_map, $depth + 1 ); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

// Quick links
$snb_account_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
$snb_cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
$snb_wish_url = function_exists('senoobar_wishlist_page_url') ? senoobar_wishlist_page_url() : home_url('/wishlist/');

// Helper: resolve a legal page URL (created via legal-pages-setup.php),
// falling back to the canonical slug.
function senoobar_menu_legal_url( $key, $fallback ) {
    if ( function_exists('senoobar_legal_page_url') ) {
        $u = senoobar_legal_page_url( $key );
        if ( $u ) {
            return $u;
        }
    }
    return home_url( $fallback );
}

// Quick links config (label => [key, icon, auto_url, default_url])
$snb_quick_defs = [
    'account'  => ['حساب کاربری', 'account', $snb_account_url],
    'wishlist' => ['علاقه‌مندی‌ها', 'wishlist', $snb_wish_url],
    'cart'     => ['سبد خرید', 'cart', $snb_cart_url],
    'about'    => ['درباره ما', 'text', senoobar_menu_legal_url('about', '/about/')],
    'contact'  => ['تماس با ما', 'text', senoobar_menu_legal_url('contact', '/contact/')],
    'faq'      => ['سوالات متداول', 'text', senoobar_menu_legal_url('faq', '/faq/')],
    'terms'    => ['شرایط و ضوابط', 'text', senoobar_menu_legal_url('terms', '/terms-and-conditions/')],
    'privacy'  => ['حریم خصوصی', 'text', senoobar_menu_legal_url('privacy', '/privacy-policy/')],
];

$snb_quick_links = [];
foreach ($snb_quick_defs as $key => $def) {
    if (!get_theme_mod("senoobar_menu_link_{$key}_enabled", '1')) {
        continue;
    }
    $url = get_theme_mod("senoobar_menu_link_{$key}_url", '');
    if (empty($url)) {
        $url = $def[2]; // auto/default URL
    }
    $snb_quick_links[] = [
        'label' => $def[0],
        'type'  => $def[1],
        'url'   => $url,
    ];
}

// Newsletter
$snb_nl_title = get_theme_mod('senoobar_menu_newsletter_title', '📬 خبرنامه صنوبر');
$snb_nl_desc  = get_theme_mod('senoobar_menu_newsletter_desc', 'از تخفیف‌ها و جدیدترین محصولات باخبر شوید.');
$snb_newsletter_nonce = wp_create_nonce('senoobar_newsletter_nonce');
?>
<div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="منوی موبایل">
    <div class="mobile-menu__head">
        <?php
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id):
            echo wp_get_attachment_image($logo_id, 'full', false, ['class' => 'site-logo', 'style' => 'height:40px;width:auto', 'alt' => get_bloginfo('name'), 'width' => 180, 'height' => 60]);
        else:
        ?>
        <span class="logo-text"><?php bloginfo('name'); ?></span>
        <?php endif; ?>
        <button class="mobile-close" id="menuClose" aria-label="بستن منو">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="mobile-menu__body">
        <?php foreach ($snb_sections as $section => $order): ?>

            <?php if ($section === 'search'): ?>
                <div class="mobile-menu__search">
                    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
                        <button type="submit" aria-label="جستجو">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($section === 'categories'): ?>
                <nav class="mobile-cats" aria-label="دسته‌بندی محصولات">
                    <div class="mobile-cats__label">دسته‌بندی محصولات</div>
                    <a href="<?php echo esc_url($snb_shop_url); ?>" class="mobile-cat-link mobile-cat-link--all">
                        <span>همه محصولات</span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <?php foreach ($snb_cat_tree as $cat): ?>
                        <?php senoobar_render_menu_cat( $cat, $snb_children_map, 0 ); ?>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <?php if ($section === 'quicklinks'): ?>
                <nav class="mobile-links" aria-label="صفحات">
                    <?php foreach ($snb_quick_links as $link): ?>
                        <a href="<?php echo esc_url($link['url']); ?>" class="mobile-link-item">
                            <?php if ($link['type'] === 'account'): ?>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            <?php elseif ($link['type'] === 'wishlist'): ?>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                            <?php elseif ($link['type'] === 'cart'): ?>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272"/></svg>
                            <?php endif; ?>
                            <span><?php echo esc_html($link['label']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>
            <?php endif; ?>

            <?php if ($section === 'newsletter'): ?>
                <div class="mobile-newsletter">
                    <div class="mobile-newsletter__title"><?php echo esc_html($snb_nl_title); ?></div>
                    <p class="mobile-newsletter__desc"><?php echo esc_html($snb_nl_desc); ?></p>
                    <form class="mobile-newsletter-form" method="post" action="#" data-nonce="<?php echo esc_attr($snb_newsletter_nonce); ?>">
                        <input type="email" name="email" placeholder="ایمیل خود را وارد کنید..." required autocomplete="email">
                        <button type="submit">عضویت</button>
                        <div class="mobile-newsletter-message" role="alert" aria-live="polite"></div>
                    </form>
                </div>
            <?php endif; ?>

        <?php endforeach; ?>
    </div>
</div>
<div class="menu-overlay" id="menuOverlay"></div>
