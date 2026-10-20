<?php
get_header();
?>
<main id="primary" class="site-main">
<?php if (is_front_page() && is_home()) : ?>
    <?php get_template_part('template-parts/hero'); ?>
    <?php get_template_part('template-parts/categories-grid'); ?>
    <?php get_template_part('template-parts/featured-products'); ?>
    <?php get_template_part('template-parts/services'); ?>
    <?php get_template_part('template-parts/promo-banners'); ?>
    <?php get_template_part('template-parts/bestsellers'); ?>
    <?php get_template_part('template-parts/inspiration'); ?>
    <?php get_template_part('template-parts/testimonials'); ?>
    <?php get_template_part('template-parts/story'); ?>
    <?php get_template_part('template-parts/blog-section'); ?>
    <?php get_template_part('template-parts/newsletter'); ?>
<?php elseif (is_shop() || is_product_category()) : ?>
    <div class="container page-content"><?php if (function_exists('woocommerce_content')) { woocommerce_content(); } else { do_action('woocommerce_content'); } ?></div>
<?php elseif (is_single()) : ?>
    <div class="container page-content"><?php get_template_part('template-parts/content', 'single'); ?></div>
<?php elseif (is_page()) : ?>
    <div class="container page-content"><?php get_template_part('template-parts/content', 'page'); ?></div>
<?php elseif (is_archive() || is_home()) : ?>
    <div class="container page-content"><?php get_template_part('template-parts/content', 'archive'); ?></div>
<?php else : ?>
    <div class="container page-content"><?php get_template_part('template-parts/content', 'none'); ?></div>
<?php endif; ?>
</main>
<?php
get_footer();