<?php $bedroom_url = function_exists('senoobar_showroom_page_url') ? senoobar_showroom_page_url('bedroom') : '#'; if(empty($bedroom_url)) $bedroom_url='#'; $furniture_url = function_exists('senoobar_showroom_page_url') ? senoobar_showroom_page_url('furniture') : '#'; if(empty($furniture_url)) $furniture_url='#'; $i1=get_theme_mod('senoobar_promo_img1');$i2=get_theme_mod('senoobar_promo_img2');$b1=$i1?wp_get_attachment_url($i1):SENOOBAR_URI . '/assets/images/promo-bedroom.jpg';$b2=$i2?wp_get_attachment_url($i2):SENOOBAR_URI . '/assets/images/cat-table.jpg';

// Get image dimensions for CLS prevention
$b1_w = 900; $b1_h = 593;
$b2_w = 900; $b2_h = 593;
if (preg_match('/wp-content\/uploads/', $b1)) {
    $attachment_id = attachment_url_to_postid($b1);
    if ($attachment_id) {
        $meta = wp_get_attachment_metadata($attachment_id);
        if ($meta && isset($meta['width'], $meta['height'])) {
            $b1_w = $meta['width'];
            $b1_h = $meta['height'];
        }
    }
}
if (preg_match('/wp-content\/uploads/', $b2)) {
    $attachment_id = attachment_url_to_postid($b2);
    if ($attachment_id) {
        $meta = wp_get_attachment_metadata($attachment_id);
        if ($meta && isset($meta['width'], $meta['height'])) {
            $b2_w = $meta['width'];
            $b2_h = $meta['height'];
        }
    }
}
?><section class="section"><div class="container"><div class="promo-grid"><div class="promo-card"><div class="promo-card__bg"><?php echo senoobar_img($b1, ["alt"=>"اتاق خواب", "loading"=>"lazy", "width"=>$b1_w, "height"=>$b1_h]); ?></div><div class="promo-card__overlay"></div><div class="promo-card__content"><p class="promo-card__kicker">اتاق خواب رویایی شما</p><h3 class="promo-card__title">با طراحی‌های خاص صنوبر</h3><a href="<?php echo esc_url($bedroom_url); ?>" class="promo-card__btn">مشاهده مجموعه <span>←</span></a></div></div><div class="promo-card"><div class="promo-card__bg"><?php echo senoobar_img($b2, ["alt"=>"مبلمان", "loading"=>"lazy", "width"=>$b2_w, "height"=>$b2_h]); ?></div><div class="promo-card__overlay"></div><div class="promo-card__content"><p class="promo-card__kicker">مبلمان مدرن و راحت</p><h3 class="promo-card__title">برای خانه‌های امروزی</h3><a href="<?php echo esc_url($furniture_url); ?>" class="promo-card__btn">مشاهده مجموعه <span>←</span></a></div></div></div></div></section>