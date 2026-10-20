<?php
/**
 * کارت مقاله (مشترک بین صفحه‌ی مقالات و آرشیو دسته‌ها).
 *
 * استفاده: داخل لوپ وردپرس. پارامتر اختیاری $args['featured'] = true برای کارت بزرگ.
 *
 * @package Senoobar
 */

$featured = ! empty( $args['featured'] );
$cats     = get_the_category();
$cat      = ! empty( $cats ) ? $cats[0] : null;
$minutes  = function_exists( 'senoobar_reading_time' ) ? senoobar_reading_time() : 1;
$size     = $featured ? 'large' : 'medium_large';
?>
<article class="article-card<?php echo $featured ? ' article-card--featured' : ''; ?>">
	<a href="<?php the_permalink(); ?>" class="article-card__link" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
		<div class="article-card__img">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( $size, [ 'loading' => $featured ? 'eager' : 'lazy', 'alt' => esc_attr( get_the_title() ) ] ); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( SENOOBAR_URI ); ?>/assets/images/blog-1.jpg" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
			<?php endif; ?>
			<?php if ( $cat ) : ?>
				<span class="article-card__badge"><?php echo esc_html( $cat->name ); ?></span>
			<?php endif; ?>
		</div>
		<div class="article-card__body">
			<div class="article-card__meta">
				<span><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></span>
				<span aria-hidden="true">·</span>
				<span><?php echo esc_html( $minutes ); ?> دقیقه مطالعه</span>
			</div>
			<h2 class="article-card__title"><?php the_title(); ?></h2>
			<p class="article-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), $featured ? 40 : 22, '…' ) ); ?></p>
			<span class="article-card__more">ادامه مطلب
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
			</span>
		</div>
	</a>
</article>
