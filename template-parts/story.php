<?php 
$title=get_theme_mod('senoobar_story_title','داستان صنوبر');
$text=get_theme_mod('senoobar_story_text','همراه شما در ساختن خانه‌ای زیباتر از مبلمان با کیفیت و طراحی مدرن');
$btn=get_theme_mod('senoobar_story_btn','تماشای ویدیو');
$tid=get_theme_mod('senoobar_video_thumb');
$thumb=$tid?wp_get_attachment_url($tid):SENOOBAR_URI . '/assets/images/story.jpg';

// Get image dimensions for CLS prevention
$thumb_w = 1200; $thumb_h = 675;
if (preg_match('/wp-content\/uploads/', $thumb)) {
    $attachment_id = attachment_url_to_postid($thumb);
    if ($attachment_id) {
        $meta = wp_get_attachment_metadata($attachment_id);
        if ($meta && isset($meta['width'], $meta['height'])) {
            $thumb_w = $meta['width'];
            $thumb_h = $meta['height'];
        }
    }
}
?><section class="story-section"><div class="container"><div class="story__wrap"><div class="story__bg"><?php echo senoobar_img($thumb, ["alt"=>"داستان صنوبر", "loading"=>"lazy", "width"=>$thumb_w, "height"=>$thumb_h]); ?></div><div class="story__overlay"></div><div class="story__content"><div class="story__text"><h2><?php echo esc_html($title);?></h2><p><?php echo nl2br(esc_html($text));?></p><a href="#" class="story__watch-btn"><?php echo esc_html($btn);?></a></div><button class="story__play" aria-label="پخش ویدیو"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></button></div></div></div></section>