<?php
/**
 * Template Name: گالری ایده‌ها
 *
 * صفحه‌ی «گالری ایده‌ها» — مثل یک پیج (پروفایل) با گرید عکس/ویدیو.
 */
get_header();

$ideas = senoobar_ideas_query( 60 ); // همه ایده‌ها (تا مقدار معقول)
?>
<main id="primary" class="site-main">
	<section class="section">
		<div class="container">

			<header class="ideas-page__header">
				<div class="ideas-page__avatar">
					<svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L8 8H4l4 4-1.5 6L12 15l5.5 3L16 12l4-4h-4L12 2z"/></svg>
				</div>
				<div class="ideas-page__info">
					<h1 class="section__title" style="margin:0;">گالری ایده‌ها</h1>
					<p class="ideas-page__sub">ایده‌هایی برای خانه شما</p>
				</div>
			</header>

			<?php if ( ! empty( $ideas ) ) : ?>
			<div class="idea-grid">
				<?php foreach ( $ideas as $idea ) : ?>
				<a href="<?php echo esc_url( $idea['link'] ); ?>" class="idea-card">
					<div class="idea-card__media">
						<?php if ( $idea['cover'] ) : ?>
							<img src="<?php echo esc_url( $idea['cover'] ); ?>" alt="<?php echo esc_attr( $idea['title'] ); ?>" loading="lazy">
						<?php else : ?>
							<img src="<?php echo esc_url( SENOOBAR_URI . '/assets/images/hero-1.jpg' ); ?>" alt="<?php echo esc_attr( $idea['title'] ); ?>" loading="lazy">
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
			<div class="ideas-page__empty">
				<p>هنوز ایده‌ای ثبت نشده است. از منوی «گالری ایده‌ها» اولین ایده را اضافه کنید.</p>
			</div>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer();
