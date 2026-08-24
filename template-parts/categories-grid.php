<?php
/**
 * Categories Grid — Fully configurable from Customizer
 *
 * Each category has:
 *   senoobar_cat_{ID}_enabled  — checkbox (1 = show, empty = hide)
 *   senoobar_cat_{ID}_priority — number (lower = first)
 *
 * Columns per device:
 *   senoobar_cats_cols_desktop (default: 6)
 *   senoobar_cats_cols_tablet  (default: 3)
 *   senoobar_cats_cols_mobile  (default: 2)
 */

// Fallback static data (only when WooCommerce is not active or no categories selected)
$fallback_cats = [
  ['name' => 'سرویس خواب', 'img' => SENOOBAR_URI . '/assets/images/hero-2.jpg'],
  ['name' => 'تشک',       'img' => SENOOBAR_URI . '/assets/images/cat-mattress.jpg'],
  ['name' => 'تخت خواب',   'img' => SENOOBAR_URI . '/assets/images/cat-bed.jpg'],
  ['name' => 'مبل و مبلمان','img' => SENOOBAR_URI . '/assets/images/hero-1.jpg'],
  ['name' => 'میز جلو مبلی','img' => SENOOBAR_URI . '/assets/images/cat-table.jpg'],
  ['name' => 'کمد و باخاخت','img' => SENOOBAR_URI . '/assets/images/cat-wardrobe.jpg'],
];

$display = [];

if (class_exists('WooCommerce')):
    // Get ALL categories
    $all_terms = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'number'     => 20,
    ]);

    if (!is_wp_error($all_terms) && !empty($all_terms)):
        // Collect enabled cats with their priority
        $enabled = [];
        foreach ($all_terms as $t) {
            $is_enabled = get_theme_mod("senoobar_cat_{$t->term_id}_enabled", '');
            if ($is_enabled) {
                $priority = (int) get_theme_mod("senoobar_cat_{$t->term_id}_priority", 99);
                $enabled[] = [
                    'term'     => $t,
                    'priority' => $priority,
                ];
            }
        }

        // Sort by priority (lower = first), then by name as tiebreaker
        usort($enabled, function($a, $b) {
            if ($a['priority'] !== $b['priority']) {
                return $a['priority'] - $b['priority'];
            }
            return strcmp($a['term']->name, $b['term']->name);
        });

        // Build display array
        foreach ($enabled as $e) {
            $t = $e['term'];
            $tid = get_term_meta($t->term_id, 'thumbnail_id', true);
            $display[] = [
                'name' => $t->name,
                'link' => get_term_link($t),
                'img'  => $tid ? wp_get_attachment_url($tid) : '',
            ];
        }
    endif;
endif;

// Fallback if nothing selected
if (empty($display)):
    $display = $fallback_cats;
endif;

// Column settings per device
$cols_desktop = get_theme_mod('senoobar_cats_cols_desktop', 6);
$cols_tablet  = get_theme_mod('senoobar_cats_cols_tablet', 3);
$cols_mobile  = get_theme_mod('senoobar_cats_cols_mobile', 2);
$cat_title    = get_theme_mod('senoobar_section_cats_title', '');
?>

<style>
.cats-grid {
  display: grid;
  grid-template-columns: repeat(<?php echo (int) $cols_mobile; ?>, 1fr);
  gap: 12px;
}
@media (min-width: 640px) {
  .cats-grid {
    grid-template-columns: repeat(<?php echo (int) $cols_tablet; ?>, 1fr);
  }
}
@media (min-width: 1024px) {
  .cats-grid {
    grid-template-columns: repeat(<?php echo (int) $cols_desktop; ?>, 1fr);
  }
}
</style>

<section class="cats-section">
  <div class="container">
    <?php if (!empty($cat_title)): ?>
      <h2 class="section__title" style="text-align:right;margin-bottom:16px;"><?php echo esc_html($cat_title); ?></h2>
    <?php endif; ?>
    <div class="cats-grid">
      <?php foreach ($display as $c): ?>
        <a href="<?php echo isset($c['link']) ? esc_url($c['link']) : '#'; ?>" class="cat-item">
          <div class="cat-item__thumb">
            <?php if (!empty($c['img'])): ?>
              <?php 
              // Get image dimensions for CLS prevention
              $img_w = 90; $img_h = 90;
              if (preg_match('/wp-content\/uploads/', $c['img'])) {
                  $attachment_id = attachment_url_to_postid($c['img']);
                  if ($attachment_id) {
                      $meta = wp_get_attachment_metadata($attachment_id);
                      if ($meta && isset($meta['width'], $meta['height'])) {
                          $img_w = $meta['width'];
                          $img_h = $meta['height'];
                      }
                  }
              }
              echo senoobar_img($c['img'], ["alt"=>$c['name'], "loading"=>"lazy", "width"=>$img_w, "height"=>$img_h]); 
              ?>
            <?php else: ?>
              <div class="placeholder" style="width:90px;height:90px;">🛋️</div>
            <?php endif; ?>
          </div>
          <span class="cat-item__title"><?php echo esc_html($c['name']); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
