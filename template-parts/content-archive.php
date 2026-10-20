<?php
/**
 * آرشیو‌ها: دسته/برچسب نوشته‌ها و صفحه‌ی نوشته‌ها با همان ظاهر صفحه‌ی مقالات.
 * سایر آرشیوها (نویسنده، تاریخ و ...) ظاهر ساده‌ی قبلی را حفظ می‌کنند.
 */
if ( is_category() || is_tag() || is_home() ) :
	$term       = ( is_category() || is_tag() ) ? get_queried_object() : null;
	$active_cat = is_category() && $term ? (int) $term->term_id : 0;
	$heading    = $term ? single_term_title( '', false ) : 'مقالات';
	$desc       = $term ? term_description() : '';
	?>
	<section class="articles-page">
		<nav class="articles-breadcrumb" aria-label="مسیر">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a>
			<span aria-hidden="true">/</span>
			<?php if ( $term ) : ?>
				<a href="<?php echo esc_url( senoobar_articles_url() ); ?>">مقالات</a>
				<span aria-hidden="true">/</span>
			<?php endif; ?>
			<span><?php echo esc_html( $heading ); ?></span>
		</nav>

		<header class="articles-hero">
			<div class="articles-hero__icon" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2V4z"/><path d="M18 8h2v10a2 2 0 0 1-2 2"/><path d="M8 9h6M8 13h6"/></svg>
			</div>
			<div>
				<h1 class="section__title articles-hero__title"><?php echo esc_html( $heading ); ?></h1>
				<p class="articles-hero__sub"><?php echo esc_html( number_format_i18n( (int) $GLOBALS['wp_query']->found_posts ) ); ?> مقاله</p>
			</div>
		</header>

		<?php if ( $desc ) : ?>
			<div class="articles-term-desc entry-content"><?php echo wp_kses_post( $desc ); ?></div>
		<?php endif; ?>

		<?php
		get_template_part( 'template-parts/articles-toolbar', null, [
			'active_cat' => $active_cat,
			'search'     => '',
			'sort'       => senoobar_articles_current_sort(),
		] );
		?>

		<?php if ( have_posts() ) : ?>
			<div class="articles-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'template-parts/article-card' ); ?>
				<?php endwhile; ?>
			</div>
			<?php
			$links = paginate_links( [
				'prev_text' => 'قبلی',
				'next_text' => 'بعدی',
				'mid_size'  => 1,
				'type'      => 'list',
			] );
			if ( $links ) :
				?>
				<nav class="articles-pagination" aria-label="صفحه‌بندی مقالات"><?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
			<?php endif; ?>
		<?php else : ?>
			<div class="articles-empty">
				<h2>مقاله‌ای پیدا نشد</h2>
				<p>هنوز مقاله‌ای در این بخش منتشر نشده است.</p>
				<a href="<?php echo esc_url( senoobar_articles_url() ); ?>" class="btn btn--primary">همه مقالات</a>
			</div>
		<?php endif; ?>
	</section>
<?php else : ?>
	<div class="blog-grid"><?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?><a href="<?php the_permalink(); ?>" class="blog-card"><div class="blog-card__img"><?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium', [ 'loading' => 'lazy' ] ); else : ?><img src="<?php echo SENOOBAR_URI; ?>/assets/images/blog-1.jpg" alt="" loading="lazy"><?php endif; ?></div><div class="blog-card__body"><div class="blog-card__meta"><span><?php echo get_the_date(); ?></span><span>·</span><span><?php the_author(); ?></span></div><h2 class="blog-card__title"><?php the_title(); ?></h2></div></a><?php endwhile; else : get_template_part( 'template-parts/content', 'none' ); endif; ?></div>
<?php endif; ?>
