<?php
$hero_title=get_theme_mod('senoobar_hero_title','میلمان خانه‌ای درخور شما');
$hero_subtitle=get_theme_mod('senoobar_hero_subtitle','تجربه‌ای متفاوت از راحتی و زیبایی');
$hero_img1=get_theme_mod('senoobar_hero_img1');
$hero_img2=get_theme_mod('senoobar_hero_img2');
$img1=$hero_img1?wp_get_attachment_url($hero_img1):SENOOBAR_URI . '/assets/images/hero-1.jpg';
$img2=$hero_img2?wp_get_attachment_url($hero_img2):SENOOBAR_URI . '/assets/images/hero-2.jpg';
/* When a Customizer image is set we don't know its intrinsic size; use the
   theme's hero ratio (800x1000, i.e. 4:5) so the browser can reserve layout
   space and prioritize the fetch without causing layout shift. */
$img1_w='800'; $img1_h='1000';
$img2_w='800'; $img2_h='1000';
if($hero_img1){ $s1=wp_get_attachment_image_src($hero_img1,'senoobar-hero'); if($s1){$img1=$s1[0];$img1_w=$s1[1];$img1_h=$s1[2];} }
if($hero_img2){ $s2=wp_get_attachment_image_src($hero_img2,'senoobar-hero'); if($s2){$img2=$s2[0];$img2_w=$s2[1];$img2_h=$s2[2];} }
/* webp sources: the theme ships pre-converted webp variants next to the jpg.
   We only emit a <picture> when using the theme's own static images (Customizer
   uploads already get optimized by the site's media pipeline /Converter plugin). */
$img1_webp = !$hero_img1 ? str_replace('.jpg', '.webp', $img1) : '';
$img2_webp = !$hero_img2 ? str_replace('.jpg', '.webp', $img2) : '';

// Generate responsive image sizes for hero
$img1_srcset = $img1_webp ? $img1_webp . ' 800w' : '';
$img2_srcset = $img2_webp ? $img2_webp . ' 800w' : '';
if ($hero_img1) {
    $sizes = [400, 600, 800, 1200];
    foreach ($sizes as $size) {
        $src = wp_get_attachment_image_url($hero_img1, ['width' => $size, 'height' => $size * 1.25]);
        if ($src) $img1_srcset .= ($img1_srcset ? ', ' : '') . $src . ' ' . $size . 'w';
    }
}
if ($hero_img2) {
    $sizes = [400, 600, 800, 1200];
    foreach ($sizes as $size) {
        $src = wp_get_attachment_image_url($hero_img2, ['width' => $size, 'height' => $size * 1.25]);
        if ($src) $img2_srcset .= ($img2_srcset ? ', ' : '') . $src . ' ' . $size . 'w';
    }
}
?><section class="hero"><div class="hero__grid"><div class="hero__image-wrap"><?php
if($img1_webp){ 
    echo '<picture><source type="image/webp" srcset="'.esc_attr($img1_srcset).'">'.senoobar_img($img1, ['alt'=>'نشیمن مدرن','width'=>$img1_w,'height'=>$img1_h,'fetchpriority'=>'high','decoding'=>'async','sizes'=>'(max-width: 767px) 100vw, 50vw','srcset'=>esc_attr($img1_srcset)]).'</picture>'; 
}
else { 
    echo senoobar_img($img1, ['alt'=>'نشیمن مدرن','width'=>$img1_w,'height'=>$img1_h,'fetchpriority'=>'high','decoding'=>'async','sizes'=>'(max-width: 767px) 100vw, 50vw','srcset'=>esc_attr($img1_srcset)]); 
}
?></div><div class="hero__image-wrap"><?php
if($img2_webp){ 
    echo '<picture><source type="image/webp" srcset="'.esc_attr($img2_srcset).'">'.senoobar_img($img2, ['alt'=>'اتاق خواب','width'=>$img2_w,'height'=>$img2_h,'loading'=>'lazy','fetchpriority'=>'low','decoding'=>'async','sizes'=>'(max-width: 767px) 100vw, 50vw','srcset'=>esc_attr($img2_srcset)]).'</picture>'; 
}
else { 
    echo senoobar_img($img2, ['alt'=>'اتاق خواب','width'=>$img2_w,'height'=>$img2_h,'loading'=>'lazy','fetchpriority'=>'low','decoding'=>'async','sizes'=>'(max-width: 767px) 100vw, 50vw','srcset'=>esc_attr($img2_srcset)]); 
}
?></div></div><div class="hero__content"><div class="hero__content-inner"><p class="hero__kicker">زیبایی در سادگی، کیفیت در جزئیات</p><h1 class="hero__title"><?php echo esc_html($hero_title); ?></h1><p class="hero__subtitle"><?php echo esc_html($hero_subtitle); ?></p><div class="hero__actions"><a href="<?php echo class_exists('WooCommerce')?get_permalink(wc_get_page_id('shop')):'#'; ?>" class="btn btn--primary">مشاهده محصولات</a><a href="#" class="hero__btn-outline">مشاهده مجموعه‌ها</a></div></div></div></section>
