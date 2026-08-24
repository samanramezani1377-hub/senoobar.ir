<?php
/**
 * Featured Products — senoobar2 style (4 products, WooCommerce)
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
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="section-link">مشاهده همه</a>
            <?php endif; ?>
        </div>

        <?php if (class_exists('WooCommerce')): ?>
            <?php
            $args = [
                'post_type'      => 'product',
                'posts_per_page' => 4,
                'tax_query'      => [
                    [
                        'taxonomy' => 'product_visibility',
                        'field'    => 'name',
                        'terms'    => 'featured',
                        'operator' => 'IN',
                    ],
                ],
            ];
            $featured = new WP_Query($args);
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
            <!-- Fallback static products -->
            <div class="products-grid">
                <?php
                $fallback = [
                    ['name' => 'مبل راحتی مگالنیتم فلورانس', 'price' => '۲۵,۹۰۰,۰۰۰', 'old' => '۳۲,۰۰۰,۰۰۰', 'discount' => '۱۰٪', 'img' => SENOOBAR_URI . '/assets/images/featured-sofa.jpg'],
                    ['name' => 'سرویس خواب ویتز',             'price' => '۶۷,۹۰۰,۰۰۰', 'old' => '۷۹,۰۰۰,۰۰۰', 'discount' => '۱۵٪', 'img' => SENOOBAR_URI . '/assets/images/hero-2.jpg'],
                    ['name' => 'میز ناهارخوری آریا',          'price' => '۲۸,۹۰۰,۰۰۰', 'old' => '۳۴,۰۰۰,۰۰۰', 'discount' => '۱۵٪', 'badge' => 'جدید', 'img' => SENOOBAR_URI . '/assets/images/featured-dining.jpg'],
                    ['name' => 'میز تلویزیون روما',           'price' => '۱۴,۶۰۰,۰۰۰', 'old' => '۱۷,۰۰۰,۰۰۰', 'discount' => '۴٪', 'img' => SENOOBAR_URI . '/assets/images/featured-tv-table.jpg'],
                ];
                foreach ($fallback as $p): ?>
                <div class="woocommerce"><ul class="products"><li class="product">
                    <div style="position:relative;overflow:hidden;">
                        <?php echo senoobar_img($p['img'], ["alt"=>$p['name'], "loading"=>"lazy", "width"=>400, "height"=>400, "style"=>"aspect-ratio:1;object-fit:cover;width:100%;"]); ?>
                        <?php if (!empty($p['discount'])): ?>
                        <span class="discount-badge"><?php echo $p['discount']; ?></span>
                        <?php endif; ?>
                        <?php if (!empty($p['badge'])): ?>
                        <span class="new-badge"><?php echo $p['badge']; ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="woocommerce-loop-product__title"><?php echo $p['name']; ?></h2>
                    <span class="price"><?php echo $p['price']; ?> <span style="font-size:0.7rem;color:var(--color-gray-500);">تومان</span></span>
                    <?php if (!empty($p['old'])): ?>
                    <span class="price" style="display:block;padding-top:0;"><del style="font-size:0.7rem;color:var(--color-gray-400);"><?php echo $p['old']; ?></del></span>
                    <?php endif; ?>
                </li></ul></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        <?php else: ?>
            <p style="text-align:center;color:var(--color-text-muted);">برای نمایش محصولات، ووکامرس را نصب کنید.</p>
        <?php endif; ?>
    </div>
</section>
