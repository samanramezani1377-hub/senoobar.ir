<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                <div style="width:40px;height:40px;border-radius:var(--radius-xl);background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;"><svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24" style="color:var(--color-white);"><path d="M12 2L8 8H4l4 4-1.5 6L12 15l5.5 3L16 12l4-4h-4L12 2z"/></svg></div>
                <div><div style="font-weight:700;font-size:1.1rem;">فروشگاه صنوبر</div><div style="font-size:0.7rem;opacity:0.6;">کیفیت خواب بهتر</div></div>
            </div>
            <p style="opacity:0.6;font-size:0.85rem;line-height:2;"><?php echo esc_html(get_theme_mod('senoobar_footer_about','فروشگاه صنوبر با ارائه محصولات باکیفیت برای خواب راحت، تجربه خریدی لذت‌بخش و مطمئن برای شما فراهم می‌کند. ما اینجا هستیم تا با محصولات بادوام، خدمات عالی و ارسال سریع، بهترین را برای شما رقم بزنیم — هدف ما رضایت شماست.')); ?></p>
            <div class="footer-social"><?php foreach(['instagram'=>'I','telegram'=>'T','pinterest'=>'P','linkedin'=>'L'] as $k=>$l): ?><a href="<?php echo esc_url(get_theme_mod("senoobar_footer_{$k}",'#')); ?>" aria-label="<?php echo $k; ?>"><?php echo $l; ?></a><?php endforeach; ?></div>
        </div>
        <div class="footer-col"><h4>دسته‌بندی محصولات</h4><ul><?php
// Dynamic product category links (top-level WooCommerce categories).
if ( function_exists('wc_get_page_permalink') && function_exists('get_terms') ) {
    $snb_footer_cats = get_terms(array('taxonomy'=>'product_cat','parent'=>0,'hide_empty'=>false));
    if ( ! is_wp_error($snb_footer_cats) && ! empty($snb_footer_cats) ) {
        foreach ( $snb_footer_cats as $snb_cat ) {
            echo '<li><a href="' . esc_url( get_term_link($snb_cat) ) . '">' . esc_html($snb_cat->name) . '</a></li>';
        }
    } else {
        echo '<li><a href="' . esc_url(home_url('/shop/')) . '">همه محصولات</a></li>';
    }
} else {
    echo '<li><a href="' . esc_url(home_url('/shop/')) . '">همه محصولات</a></li>';
}
?></ul></div>
<?php
// Resolve legal-page URLs (created via legal-pages-setup.php).
$snb_f_about   = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('about')   ? senoobar_legal_page_url('about')   : home_url('/about/');
$snb_f_contact = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('contact') ? senoobar_legal_page_url('contact') : home_url('/contact/');
$snb_f_faq     = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('faq')     ? senoobar_legal_page_url('faq')     : home_url('/faq/');
$snb_f_terms   = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('terms')   ? senoobar_legal_page_url('terms')   : home_url('/terms-and-conditions/');
$snb_f_privacy = function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('privacy') ? senoobar_legal_page_url('privacy') : home_url('/privacy-policy/');
?>
        <div class="footer-col"><h4>دسترسی سریع</h4><ul><li><a href="<?php echo esc_url($snb_f_about); ?>">درباره ما</a></li><li><a href="<?php echo esc_url($snb_f_contact); ?>">تماس با ما</a></li><li><a href="<?php echo esc_url($snb_f_faq); ?>">سوالات متداول</a></li><li><a href="<?php echo esc_url($snb_f_terms); ?>">شرایط و قوانین</a></li><li><a href="<?php echo esc_url($snb_f_privacy); ?>">حریم خصوصی</a></li></ul></div>
        <?php
        // شماره‌های تماس با پیش‌فرض صحیح (هماهنگ با page-contact.php)
        $snb_f_phone1 = get_theme_mod('senoobar_footer_phone1', '۰۹۱۳۰۲۰۵۸۹۸');
        $snb_f_phone2 = get_theme_mod('senoobar_footer_phone2', '۰۹۱۳۰۲۰۵۸۶۸');
        $snb_f_phone3 = get_theme_mod('senoobar_footer_phone3', '۰۹۱۳۰۲۰۵۳۲۳');
        $snb_f_address = get_theme_mod('senoobar_footer_address', 'اصفهان، شهرک صنعتی دولت‌آباد، خیابان شماره ۱۰ (خیام)، فروشگاه صنوبر');
        $snb_f_hours   = get_theme_mod('senoobar_footer_hours', 'شنبه تا پنجشنبه، ۱۰ صبح تا ۹ شب');
        ?>
        <div class="footer-col"><h4>اطلاعات تماس</h4><div class="footer-contact"><div><span>📞</span><a href="tel:<?php echo esc_attr( senoobar_tel_href( $snb_f_phone1 ) ); ?>"><?php echo esc_html( $snb_f_phone1 ); ?></a></div><div><span>📲</span><a href="tel:<?php echo esc_attr( senoobar_tel_href( $snb_f_phone2 ) ); ?>"><?php echo esc_html( $snb_f_phone2 ); ?></a></div><div><span>☎️</span><a href="tel:<?php echo esc_attr( senoobar_tel_href( $snb_f_phone3 ) ); ?>"><?php echo esc_html( $snb_f_phone3 ); ?></a></div><div><span>📍</span><span><?php echo esc_html( $snb_f_address ); ?></span></div><div><span>🕐</span><span><?php echo esc_html( $snb_f_hours ); ?></span></div></div></div>
    </div>
    <div class="footer-bottom">
        <p>&#169; <?php echo date('Y'); ?> فروشگاه صنوبر — تمامی حقوق برای فروشگاه صنوبر است.</p>
        <div class="footer-bottom__badges">
            <?php
            $enamad_code = get_theme_mod('senoobar_enamad_code', '');
            if (!empty($enamad_code)) :
                // Serve a locally-hosted copy of the e-Namad badge image
                // instead of loading it from enamad.ir's servers on every
                // page view (which was slowing the site down). Only the
                // <img src> is swapped — the <a href> link to enamad.ir's
                // verification page is left exactly as configured.
                $enamad_local_logo = SENOOBAR_URI . '/assets/images/enamad-badge.png';
                $enamad_code = preg_replace(
                    '/(<img\b[^>]*\ssrc=)(["\'])[^"\']*\2/i',
                    '$1$2' . esc_url($enamad_local_logo) . '$2',
                    $enamad_code
                );
                // Add accessibility attributes for well-formed accessibility tree
                // 1. Add alt, width, height, role to img
                $enamad_code = preg_replace(
                    '/(<img\b[^>]*)>/i',
                    '$1 width="80" height="80" alt="نماد اعتماد الکترونیکی اینماد" role="img">',
                    $enamad_code
                );
                // 2. Add aria-label to parent <a> if missing
                $enamad_code = preg_replace_callback(
                    '/<a\b([^>]*)><img/i',
                    function ($matches) {
                        $attrs = $matches[1];
                        if (stripos($attrs, 'aria-label') === false && stripos($attrs, 'aria-labelledby') === false) {
                            return '<a' . $attrs . ' aria-label="نماد اعتماد الکترونیکی اینماد - کلیک برای تأیید"><img';
                        }
                        return $matches[0];
                    },
                    $enamad_code
                );
                // 3. Add rel="noopener noreferrer" to <a> with target="_blank"
                $enamad_code = preg_replace_callback(
                    '/<a\b([^>]*\btarget=["\']_blank["\'][^>]*)>/i',
                    function ($matches) {
                        $attrs = $matches[1];
                        if (stripos($attrs, 'rel=') === false) {
                            return '<a' . $attrs . ' rel="noopener noreferrer">';
                        }
                        if (stripos($attrs, 'noopener') === false || stripos($attrs, 'noreferrer') === false) {
                            return preg_replace('/\srel=(["\'])[^"\']*\1/i', ' rel=$1noopener noreferrer$1', $matches[0]);
                        }
                        return $matches[0];
                    },
                    $enamad_code
                );
                echo '<div class="enamad-badge">' . $enamad_code . '</div>';
            endif;
            ?>
            <span>استفاده از مطالب با ذکر منبع مجاز</span>
        </div>
    </div>
</footer>

<?php if (class_exists('WooCommerce')): ?>
<?php
// Resolve wishlist page URL (auto-created via wishlist-page-setup.php).
$senoobar_wishlist_url = function_exists('senoobar_wishlist_page_url') ? senoobar_wishlist_page_url() : '#';
if (empty($senoobar_wishlist_url)) {
    $senoobar_wishlist_url = '#';
}
$senoobar_cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$senoobar_account_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
?>
<nav class="mobile-bottom-nav" aria-label="منوی پایین">
    <a href="<?php echo wc_get_cart_url(); ?>" class="mbn-item<?php echo is_cart() ? ' is-active' : ''; ?>">
        <span class="mbn-icon" data-cart-fly="bottom">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
            <span class="mbn-badge<?php echo $senoobar_cart_count > 0 ? '' : ' is-hidden'; ?>" data-cart-count><?php echo $senoobar_cart_count; ?></span>
        </span>
        <span class="mbn-label">سبد خرید</span>
    </a>
    <a href="<?php echo esc_url($senoobar_wishlist_url); ?>" class="mbn-item">
        <span class="mbn-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
        </span>
        <span class="mbn-label">علاقه‌مندی‌ها</span>
    </a>
    <a href="<?php echo home_url('/'); ?>" class="mbn-item mbn-home<?php echo is_front_page() ? ' is-active' : ''; ?>">
        <span class="mbn-home-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/></svg>
        </span>
        <span class="mbn-label">خانه</span>
    </a>
    <button id="js-push-subscribe-bottom" class="mbn-item mbn-push" aria-label="فعال‌سازی اعلان‌ها">
        <span class="mbn-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
        </span>
        <span class="mbn-label">اعلان‌ها</span>
    </button>
    <a href="<?php echo esc_url($senoobar_account_url); ?>" class="mbn-item<?php echo is_account_page() ? ' is-active' : ''; ?>">
        <span class="mbn-icon">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
        </span>
        <span class="mbn-label">حساب کاربری</span>
    </a>
</nav>
<?php endif; ?>

<button id="backToTop" class="back-to-top" aria-label="بازگشت به بالای صفحه">↑</button>
<?php wp_footer(); ?>
</body>
</html>
