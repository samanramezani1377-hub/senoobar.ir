<?php
/**
 * Single Product Page — Senoobar v2 Design
 * Based on senoobar2 Figma design — ProductDetail.tsx
 * Deep Green Palette (#1e3a2f) + Vazirmatn
 *
 * OVERRIDES: WooCommerce single-product.php
 */
defined('ABSPATH') || exit;

get_header();

if (!have_posts()) {
    get_footer();
    return;
}

while (have_posts()) : the_post();

global $product;
if (!is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}
if (!is_a($product, 'WC_Product')) continue;

// The custom template never fires `woocommerce_single_product_summary`, which is
// the hook WooCommerce uses to build its Product JSON-LD (price, availability,
// rating, reviews). Generate it explicitly; it is printed later in wp_footer.
if (function_exists('WC') && WC()->structured_data) {
    WC()->structured_data->generate_product_data($product);
}

$product_id     = $product->get_id();
$product_name   = $product->get_name();
$product_desc   = $product->get_description();
$short_desc     = $product->get_short_description();
$regular_price  = $product->get_regular_price();
$sale_price     = $product->get_sale_price();
$average_rating = $product->get_average_rating();
$review_count   = $product->get_review_count();
$sku            = $product->get_sku();

// Gallery images
$attachment_ids = $product->get_gallery_image_ids();
$main_image_id  = get_post_thumbnail_id($product_id);
$all_image_ids  = array_filter(array_merge($main_image_id ? [$main_image_id] : [], $attachment_ids));

// Categories & Tags
$categories = get_the_terms($product_id, 'product_cat');
$tags       = get_the_terms($product_id, 'product_tag');

// Related products
$related_ids      = wc_get_related_products($product_id, 4);
$related_products = array_filter(array_map('wc_get_product', $related_ids));

// Recently viewed (from WooCommerce cookie)
$recently_ids = !empty($_COOKIE['woocommerce_recently_viewed'])
    ? array_filter(array_map('absint', explode('|', $_COOKIE['woocommerce_recently_viewed'])))
    : [];
$recently_ids      = array_diff($recently_ids, [$product_id]);
$recently_products = [];
foreach (array_slice($recently_ids, 0, 5) as $rid) {
    $rp = wc_get_product($rid);
    if ($rp && $rp->is_visible()) $recently_products[] = $rp;
}

// Reviews
$comments = get_comments([
    'post_id' => $product_id,
    'status'  => 'approve',
    'type'    => 'review',
]);

// Rating distribution
$rating_counts = array_fill(1, 5, 0);
foreach ($comments as $c) {
    $r = (int) get_comment_meta($c->comment_ID, 'rating', true);
    if ($r >= 1 && $r <= 5) $rating_counts[$r]++;
}
$total_ratings = array_sum($rating_counts) ?: 1;

// Attributes for spec table
$attributes = $product->get_attributes();

wp_enqueue_style('senoobar-product-detail', get_template_directory_uri() . '/assets/css/product-detail.css', [], SENOOBAR_VERSION);
?>

<main class="product-detail-page">

    <!-- Breadcrumb -->
    <div class="pd-container">
        <nav class="pd-breadcrumb">
            <a href="<?php echo home_url('/'); ?>">خانه</a>
            <span>/</span>
            <?php if (!empty($categories)): ?>
                <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                <span>/</span>
            <?php endif; ?>
            <span class="pd-breadcrumb--current"><?php echo esc_html($product_name); ?></span>
        </nav>
    </div>

    <!-- Main Product Grid -->
    <div class="pd-container">
        <div class="pd-main-grid">

            <!-- RIGHT: Images -->
            <div class="pd-images-col">
                <div class="pd-main-image">
                    <?php if (!empty($all_image_ids)): ?>
                        <img id="pdMainImage"
                             src="<?php echo esc_url(wp_get_attachment_image_url($all_image_ids[0], 'large')); ?>"
                             alt="<?php echo esc_attr($product_name); ?>"
                             class="pd-main-image__img"/>
                    <?php else: ?>
                        <div class="pd-main-image__placeholder">تصویر محصول</div>
                    <?php endif; ?>

                    <?php if (count($all_image_ids) > 1): ?>
                    <button class="pd-img-arrow pd-img-arrow--right" id="pdImgPrev" aria-label="قبلی">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    <button class="pd-img-arrow pd-img-arrow--left" id="pdImgNext" aria-label="بعدی">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <?php endif; ?>

                    <button class="pd-zoom-btn" aria-label="بزرگنمایی">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
                    </button>
                </div>

                <?php if (count($all_image_ids) > 1): ?>
                <div class="pd-thumbnails" id="pdThumbnails">
                    <?php foreach ($all_image_ids as $idx => $img_id): ?>
                        <button class="pd-thumb <?php echo $idx === 0 ? 'pd-thumb--active' : ''; ?>"
                                data-idx="<?php echo $idx; ?>"
                                data-full="<?php echo esc_url(wp_get_attachment_image_url($img_id, 'large')); ?>"
                                aria-label="تصویر <?php echo $idx + 1; ?>">
                            <?php $thumb_alt = trim((string) get_post_meta($img_id, '_wp_attachment_image_alt', true)); if ($thumb_alt === '') { $thumb_alt = $product_name . ' - تصویر ' . ($idx + 1); } ?>
                            <img src="<?php echo esc_url(wp_get_attachment_image_url($img_id, 'thumbnail')); ?>" alt="<?php echo esc_attr($thumb_alt); ?>" loading="lazy"/>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- LEFT: Info -->
            <div class="pd-info-col">
                <!-- Badges -->
                <div class="pd-badges">
                    <?php if ($product->is_featured()): ?>
                        <span class="pd-badge pd-badge--orange">پرفروش</span>
                    <?php endif; ?>
                    <?php if ((time() - strtotime($product->get_date_created())) < 30 * DAY_IN_SECONDS): ?>
                        <span class="pd-badge pd-badge--brand">جدید</span>
                    <?php endif; ?>
                    <?php if ($product->is_on_sale()): ?>
                        <span class="pd-badge pd-badge--red"><?php echo round((($regular_price - $sale_price) / $regular_price) * 100); ?>٪ تخفیف</span>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <h1 class="pd-title"><?php echo esc_html($product_name); ?></h1>

                <!-- Rating -->
                <?php if ($review_count > 0): ?>
                <div class="pd-rating">
                    <div class="pd-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <svg class="pd-star <?php echo $i <= round($average_rating) ? 'pd-star--filled' : ''; ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        <?php endfor; ?>
                    </div>
                    <span class="pd-rating-text">(<?php echo $review_count; ?> نظر)</span>
                    <a href="#reviews-section" class="pd-rating-link">دیدن نظرات</a>
                </div>
                <?php endif; ?>

                <!-- Price -->
                <div class="pd-price">
                    <?php if ($product->is_on_sale()): ?>
                        <span class="pd-price--sale"><?php echo wc_price($sale_price); ?></span>
                        <span class="pd-price--regular"><?php echo wc_price($regular_price); ?></span>
                    <?php else: ?>
                        <span class="pd-price--current"><?php echo $product->get_price_html(); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <?php if ($short_desc): ?>
                <div class="pd-short-desc"><?php echo wp_kses_post($short_desc); ?></div>
                <?php endif; ?>

                <!-- Sticky Buy Box (price + add-to-cart) -->
                <div class="pd-buy-box">
                    <div class="pd-buy-box__price">
                        <?php if ($product->is_on_sale()): ?>
                            <span class="pd-price--sale"><?php echo wc_price($sale_price); ?></span>
                            <span class="pd-price--regular"><?php echo wc_price($regular_price); ?></span>
                        <?php else: ?>
                            <span class="pd-price--current"><?php echo $product->get_price_html(); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- Wishlist + Compare -->
                <div class="pd-actions-extras">
                    <button class="pd-action-btn" id="pdCompareBtn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        مقایسه
                    </button>
                    <button class="pd-action-btn pd-wishlist-btn" id="pdWishlistBtn" data-wishlist-btn="<?php echo esc_attr($product_id); ?>">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span data-wishlist-label>افزودن به علاقه‌مندی</span>
                    </button>
                </div>

                <!-- Meta -->
                <div class="pd-meta">
                    <?php if ($sku): ?>
                    <div class="pd-meta__row"><span class="pd-meta__label">کد محصول:</span> <?php echo esc_html($sku); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($categories)): ?>
                    <div class="pd-meta__row">
                        <span class="pd-meta__label">دسته‌بندی:</span>
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?php echo esc_url(get_term_link($cat)); ?>"><?php echo esc_html($cat->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($tags)): ?>
                    <div class="pd-meta__row">
                        <span class="pd-meta__label">برچسب‌ها:</span>
                        <?php foreach ($tags as $tag): ?>
                            <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="pd-tag"><?php echo esc_html($tag->name); ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Bar -->
    <div class="pd-container">
        <div class="pd-service-bar">
            <div class="pd-service-bar__grid">
                <div class="pd-service-item">
                    <span class="pd-service-icon">🚚</span>
                    <div><p class="pd-service-title">ارسال حرفه‌ای</p><p class="pd-service-desc">بسته‌بندی مطمئن و ارسال سریع</p></div>
                </div>
                <div class="pd-service-item">
                    <span class="pd-service-icon">🛡️</span>
                    <div><p class="pd-service-title">ضمانت بازگشت کالا</p><p class="pd-service-desc">تا ۷ روز پس از دریافت</p></div>
                </div>
                <div class="pd-service-item">
                    <span class="pd-service-icon">📞</span>
                    <div><p class="pd-service-title">پشتیبانی ۲۴ ساعته</p><p class="pd-service-desc">در تمام روزهای هفته</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="pd-container" id="reviews-section">
        <div class="pd-tabs">
            <div class="pd-tab-headers">
                <button class="pd-tab-header pd-tab-header--active" data-tab="tab-desc">توضیحات</button>
                <button class="pd-tab-header" data-tab="tab-spec">مشخصات فنی</button>
                <button class="pd-tab-header" data-tab="tab-reviews">نظرات (<?php echo $review_count; ?>)</button>
                <button class="pd-tab-header" data-tab="tab-qa">پرسش و پاسخ</button>
            </div>
            <div class="pd-tab-content">
                <!-- Description -->
                <div class="pd-tab-panel pd-tab-panel--active" id="tab-desc">
                    <?php if ($product_desc): ?>
                        <div class="pd-desc-content"><?php echo wp_kses_post(wpautop($product_desc)); ?></div>
                    <?php else: ?>
                        <p class="pd-empty-text">توضیحات این محصول به زودی اضافه خواهد شد.</p>
                    <?php endif; ?>
                </div>

                <!-- Specs -->
                <div class="pd-tab-panel" id="tab-spec">
                    <?php if (!empty($attributes)): ?>
                        <table class="pd-spec-table"><tbody>
                            <?php foreach ($attributes as $attr):
                                $name = wc_attribute_label($attr->get_name());
                                $vals = $attr->is_taxonomy()
                                    ? wc_get_product_terms($product_id, $attr->get_name(), ['fields' => 'names'])
                                    : $attr->get_options();
                            ?>
                                <tr><td class="pd-spec-key"><?php echo esc_html($name); ?></td><td class="pd-spec-val"><?php echo esc_html(implode('، ', (array) $vals)); ?></td></tr>
                            <?php endforeach; ?>
                            <?php if ($sku): ?>
                                <tr><td class="pd-spec-key">کد محصول</td><td class="pd-spec-val"><?php echo esc_html($sku); ?></td></tr>
                            <?php endif; ?>
                        </tbody></table>
                    <?php else: ?>
                        <p class="pd-empty-text">مشخصات فنی این محصول به زودی اضافه خواهد شد.</p>
                    <?php endif; ?>
                </div>

                <!-- Reviews -->
                <div class="pd-tab-panel" id="tab-reviews">
                    <?php if (!empty($comments)): ?>
                        <div class="pd-reviews-summary">
                            <div class="pd-reviews-score">
                                <div class="pd-reviews-score__num"><?php echo number_format($average_rating, 1); ?></div>
                                <div class="pd-stars"><?php for ($i = 1; $i <= 5; $i++): ?><svg class="pd-star pd-star--md <?php echo $i <= round($average_rating) ? 'pd-star--filled' : ''; ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><?php endfor; ?></div>
                                <div class="pd-reviews-score__label">از ۵ امتیاز</div>
                            </div>
                            <div class="pd-reviews-bars">
                                <?php for ($star = 5; $star >= 1; $star--): $pct = round(($rating_counts[$star] / $total_ratings) * 100); ?>
                                <div class="pd-reviews-bar">
                                    <span class="pd-reviews-bar__label"><?php echo $star; ?></span>
                                    <svg class="pd-star pd-star--sm pd-star--filled" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    <div class="pd-reviews-bar__track"><div class="pd-reviews-bar__fill" style="width:<?php echo $pct; ?>%"></div></div>
                                    <span class="pd-reviews-bar__pct"><?php echo $pct; ?>٪</span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="pd-reviews-list">
                            <?php foreach ($comments as $comment): $rv = (int) get_comment_meta($comment->comment_ID, 'rating', true); ?>
                            <div class="pd-review-item">
                                <div class="pd-review-header">
                                    <?php echo get_avatar($comment->comment_author_email, 40, '', '', ['class' => 'pd-review-avatar']); ?>
                                    <div>
                                        <div class="pd-review-author"><?php echo esc_html($comment->comment_author); ?></div>
                                        <div class="pd-review-date"><?php echo mysql2date('j F Y', $comment->comment_date); ?></div>
                                    </div>
                                    <div class="pd-stars" style="margin-right:auto;"><?php for ($i = 1; $i <= 5; $i++): ?><svg class="pd-star pd-star--sm <?php echo $i <= $rv ? 'pd-star--filled' : ''; ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><?php endfor; ?></div>
                                </div>
                                <p class="pd-review-text"><?php echo esc_html($comment->comment_content); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="pd-empty-text">هنوز نظری ثبت نشده است.</p>
                    <?php endif; ?>
                </div>

                <!-- Q&A -->
                <div class="pd-tab-panel" id="tab-qa">
                    <div class="pd-qa-list">
                        <div class="pd-qa-item">
                            <div class="pd-qa-question"><span class="pd-qa-badge pd-qa-badge--q">پ</span><p>آیا این محصول گارانتی دارد؟</p></div>
                            <div class="pd-qa-answer"><span class="pd-qa-badge pd-qa-badge--a">ج</span><p>بله، تمام محصولات فروشگاه صنوبر دارای گارانتی هستند. برای جزئیات با پشتیبانی تماس بگیرید.</p></div>
                        </div>
                        <div class="pd-qa-item">
                            <div class="pd-qa-question"><span class="pd-qa-badge pd-qa-badge--q">پ</span><p>مدت زمان تحویل چقدر است؟</p></div>
                            <div class="pd-qa-answer"><span class="pd-qa-badge pd-qa-badge--a">ج</span><p>زمان ارسال برای تشک ها 3 الی 5 و برای سرویس خواب ها 10 الی 15 روز کاری میباشد.</p></div>
                        </div>
                        <div class="pd-qa-item">
                            <div class="pd-qa-question"><span class="pd-qa-badge pd-qa-badge--q">پ</span><p>آیا امکان سفارش با رنگ دلخواه وجود دارد؟</p></div>
                            <div class="pd-qa-answer"><span class="pd-qa-badge pd-qa-badge--a">ج</span><p>بله، از طریق پشتیبانی می‌توانید رنگ‌های سفارشی درخواست دهید.</p></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if (!empty($related_products)): ?>
    <div class="pd-container">
        <div class="pd-section">
            <h2 class="pd-section-title">محصولات مرتبط</h2>
            <div class="pd-related-grid">
                <?php foreach ($related_products as $rp):
                    if (!$rp || !$rp->is_visible()) continue; ?>
                    <a href="<?php echo esc_url(get_permalink($rp->get_id())); ?>" class="pd-card">
                        <div class="pd-card__image">
                            <?php echo $rp->get_image('medium', ['class' => 'pd-card__img', 'loading' => 'lazy']); ?>
                            <span class="pd-card__wishlist" onclick="event.preventDefault();">♥</span>
                        </div>
                        <div class="pd-card__body">
                            <h4 class="pd-card__title"><?php echo esc_html($rp->get_name()); ?></h4>
                            <?php if ($rp->get_average_rating()): ?>
                            <div class="pd-stars"><?php for ($i = 1; $i <= 5; $i++): ?><svg class="pd-star pd-star--xs <?php echo $i <= round($rp->get_average_rating()) ? 'pd-star--filled' : ''; ?>" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg><?php endfor; ?></div>
                            <?php endif; ?>
                            <div class="pd-card__footer">
                                <p class="pd-card__price"><?php echo $rp->get_price_html(); ?></p>
                                <span class="pd-card__cart-btn">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recently Viewed -->
    <?php if (!empty($recently_products)): ?>
    <div class="pd-container">
        <div class="pd-section">
            <div class="pd-section-header">
                <h2 class="pd-section-title">اخیراً مشاهده شده</h2>
            </div>
            <div class="pd-recent-grid">
                <?php foreach (array_slice($recently_products, 0, 4) as $rp): ?>
                    <a href="<?php echo esc_url(get_permalink($rp->get_id())); ?>" class="pd-card pd-card--recent">
                        <?php echo $rp->get_image('medium', ['class' => 'pd-card__img', 'loading' => 'lazy']); ?>
                        <div class="pd-card__body">
                            <p class="pd-card__title"><?php echo esc_html($rp->get_name()); ?></p>
                            <p class="pd-card__price"><?php echo $rp->get_price_html(); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Trust Badges -->
    <div class="pd-trust-section">
        <div class="pd-container">
            <div class="pd-trust-grid">
                <div class="pd-trust-item"><span class="pd-trust-icon">🚚</span><div><p class="pd-trust-title">ارسال سریع</p><p class="pd-trust-desc">سریع و مطمئن به سراسر کشور</p></div></div>
                <div class="pd-trust-item"><span class="pd-trust-icon">🛡️</span><div><p class="pd-trust-title">ضمانت اصالت کالا</p><p class="pd-trust-desc">کیفیت و اصالت محصولات</p></div></div>
                <div class="pd-trust-item"><span class="pd-trust-icon">🔒</span><div><p class="pd-trust-title">پرداخت امن</p><p class="pd-trust-desc">پرداخت امن با بالاترین استاندارد</p></div></div>
                <div class="pd-trust-item"><span class="pd-trust-icon">💬</span><div><p class="pd-trust-title">پشتیبانی مشتریان</p><p class="pd-trust-desc">پاسخگویی در سریع‌ترین زمان</p></div></div>
            </div>
        </div>
    </div>

</main>

<script>
(function(){
    var thumbs=document.querySelectorAll('.pd-thumb');
    var mainImg=document.getElementById('pdMainImage');
    var currentIdx=0;
    var total=thumbs.length;
    function setActive(idx){
        currentIdx=idx;
        thumbs.forEach(function(t,i){t.classList.toggle('pd-thumb--active',i===idx);});
        if(mainImg)mainImg.src=thumbs[idx].getAttribute('data-full');
    }
    thumbs.forEach(function(t){t.addEventListener('click',function(){setActive(parseInt(this.getAttribute('data-idx')));});});
    var prev=document.getElementById('pdImgPrev'),next=document.getElementById('pdImgNext');
    if(prev)prev.addEventListener('click',function(){setActive((currentIdx-1+total)%total);});
    if(next)next.addEventListener('click',function(){setActive((currentIdx+1)%total);});
    // Tabs
    document.querySelectorAll('.pd-tab-header').forEach(function(btn){
        btn.addEventListener('click',function(){
            document.querySelectorAll('.pd-tab-header').forEach(function(b){b.classList.remove('pd-tab-header--active');});
            document.querySelectorAll('.pd-tab-panel').forEach(function(p){p.classList.remove('pd-tab-panel--active');});
            this.classList.add('pd-tab-header--active');
            var target=document.getElementById(this.getAttribute('data-tab'));
            if(target)target.classList.add('pd-tab-panel--active');
        });
    });
})();
</script>

<?php
endwhile;
get_footer();
?>
