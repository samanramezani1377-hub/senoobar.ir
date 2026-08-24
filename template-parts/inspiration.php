<?php
/**
 * گالری ایده‌ها — ۴ آخرین ایده (عکس/ویدیو محور).
 *
 * پست‌های نوع «ایده» را به‌صورت کارت‌های بصری (کاور + تیتر + نشان ویدیو)
 * نمایش می‌دهد. اگر هنوز ایده‌ای ثبت نشده باشد، نمونه‌های پیش‌فرض نشان می‌دهد.
 */
$title = get_theme_mod( 'senoobar_section_gallery_title', 'ایده‌هایی برای خانه شما' );

$ideas = senoobar_ideas_query( 4 );

$archive_url = senoobar_ideas_page_url();

// ── نمونه‌های پیش‌فرض (وقتی هنوز ایده‌ای ثبت نشده) ──
$fallback = [
	[ 'label' => 'اتاق نشیمن مدرن',     'img' => SENOOBAR_URI . '/assets/images/hero-1.jpg', 'img_w' => 800, 'img_h' => 1000 ],
	[ 'label' => 'اتاق خواب آرامش‌بخش',  'img' => SENOOBAR_URI . '/assets/images/hero-2.jpg', 'img_w' => 800, 'img_h' => 1000 ],
	[ 'label' => 'ناهارخوری شیک',       'img' => SENOOBAR_URI . '/assets/images/featured-dining.jpg', 'img_w' => 900, 'img_h' => 593 ],
	[ 'label' => 'پذیرایی مینیمال',     'img' => SENOOBAR_URI . '/assets/images/inspiration-living.jpg', 'img_w' => 900, 'img_h' => 593 ],
];
?>

<section class="section">
	<div class="container">
		<div class="flex-between mb-6">
			<h2 class="section__title" style="margin-bottom:0;"><?php echo esc_html( $title ); ?></h2>
			<?php if ( $archive_url ) : ?>
				<a href="<?php echo esc_url( $archive_url ); ?>" class="section-link">گالری ایده‌ها</a>
			<?php else : ?>
				<a href="#" class="section-link">گالری ایده‌ها</a>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $ideas ) ) : ?>
		<div class="idea-grid">
			<?php foreach ( $ideas as $idea ) : ?>
			<a href="<?php echo esc_url( $idea['link'] ); ?>" class="idea-card">
				<div class="idea-card__media">
					<?php if ( $idea['cover'] ) : ?>
						<?php 
						$cover_w = 800; $cover_h = 800;
						if (preg_match('/wp-content\/uploads/', $idea['cover'])) {
						    $attachment_id = attachment_url_to_postid($idea['cover']);
						    if ($attachment_id) {
						        $meta = wp_get_attachment_metadata($attachment_id);
						        if ($meta && isset($meta['width'], $meta['height'])) {
						            $cover_w = $meta['width'];
						            $cover_h = $meta['height'];
						        }
						    }
						}
						?>
						<img src="<?php echo esc_url( $idea['cover'] ); ?>" alt="<?php echo esc_attr( $idea['title'] ); ?>" loading="lazy" width="<?php echo $cover_w; ?>" height="<?php echo $cover_h; ?>">
					<?php else : ?>
						<img src="<?php echo esc_url( SENOOBAR_URI . '/assets/images/hero-1.jpg' ); ?>" alt="<?php echo esc_attr( $idea['title'] ); ?>" loading="lazy" width="800" height="800">
					<?php endif; ?>
					<?php if ( ! empty( $idea['video'] ) ) : ?>
						<span class="idea-card__badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
							ویدیو
						</span>
					<?php endif; ?>
				</div>
				<div class="idea-card__body">
					<h3 class="idea-card__title"><?php echo esc_html( $idea['title'] ); ?></h3>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
		<?php else : ?>
		<div class="gallery-grid">
			<?php foreach ( $fallback as $idea ) : ?>
			<div class="gallery-item">
				<?php echo senoobar_img( $idea['img'], [ 'alt' => $idea['label'], 'loading' => 'lazy', 'width' => $idea['img_w'], 'height' => $idea['img_h'] ] ); ?>
				<div class="gallery-item__overlay"></div>
				<div class="gallery-item__label"><?php echo esc_html( $idea['label'] ); ?></div>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
