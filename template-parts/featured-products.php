<?php
/**
 * Featured Products — real WooCommerce products.
 *
 * Explicitly featured products are preferred. If none are marked featured,
 * show the latest published catalog products instead of permanently showing
 * the theme's demo cards after a product import.
 */
$title = get_theme_mod('senoobar_section_featured_title', 'محصولات ویژه');
$desc  = get_theme_mod('senoobar_section_featured_desc', 'بهترین انتخاب‌های هفته با تخفیف‌های استثنایی');
?>
<section class="section">
    <div class="container">
        <div class="flex-between mb-6">
            <div>
                <h2 class="section__title"><?php echo esc_html($title); ?></h2>
                <p class="section__desc" style="margin:4px 0 0;text-align:right;"><?php echo esc_html($desc); ?></p>
            </div>
            <?php if (class_exists('WooCommerce')): ?>
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="section-link">مشاهده همه</a>
            <?php endif; ?>
        </div>

        <?php if (class_exists('WooCommerce')): ?>
            <?php
            $featured = new WP_Query([
                'post_type'           => 'product',
                'post_status'         => 'publish',
                'posts_per_page'      => 4,
                'ignore_sticky_posts' => true,
                'tax_query'           => [
                    [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => ['featured'],
                        'operator' => 'IN',
                    ],
                ],
            ]);

            // Imported products are not necessarily marked as "Featured".
            // When the featured query is empty, fall back to published products
            // visible in the catalog so the home page shows real inventory.
            if (!$featured->have_posts()) {
                wp_reset_postdata();
                $featured = new WP_Query([
                    'post_type'           => 'product',
                    'post_status'         => 'publish',
                    'posts_per_page'      => 4,
                    'orderby'             => 'date',
                    'order'               => 'DESC',
                    'ignore_sticky_posts' => true,
                    'tax_query'           => [
                        [
                            'taxonomy' => 'product_visibility',
                            'field'    => 'name',
                            'terms'    => ['exclude-from-catalog'],
                            'operator' => 'NOT IN',
                        ],
                    ],
                ]);
            }
            ?>
            <?php if ($featured->have_posts()): ?>
            <div class="products-grid shop-main" data-home-loop="featured">
                <ul class="products columns-4">
                <?php while ($featured->have_posts()): $featured->the_post(); ?>
                    <?php wc_get_template_part('content', 'product'); ?>
                <?php endwhile; ?>
                </ul>
            </div>
            <?php else: ?>
            <p class="woocommerce-info" style="text-align:center;">هنوز محصول منتشرشده‌ای برای نمایش وجود ندارد.</p>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        <?php else: ?>
            <p style="text-align:center;color:var(--color-text-muted);">برای نمایش محصولات، ووکامرس را نصب کنید.</p>
        <?php endif; ?>
    </div>
</section>
