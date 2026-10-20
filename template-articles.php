<?php
/**
 * Template Name: مقالات
 *
 * صفحه‌ی اختصاصی مقالات: مقاله‌ی ویژه، جستجو، دسته‌بندی، مرتب‌سازی و صفحه‌بندی.
 */
get_header();

$per_page = 12;
$paged    = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$search   = isset( $_GET['aq'] ) ? sanitize_text_field( wp_unslash( $_GET['aq'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$sort     = senoobar_articles_current_sort();
$cat_slug = isset( $_GET['acat'] ) ? sanitize_title( wp_unslash( $_GET['acat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
$cat_term = $cat_slug ? get_category_by_slug( $cat_slug ) : null;

$args = array_merge(
	[
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
	],
	senoobar_articles_sort_args( $sort )
);
if ( $search !== '' ) {
	$args['s'] = $search;
}
if ( $cat_term ) {
	$args['cat'] = (int) $cat_term->term_id;
}

$q            = new WP_Query( $args );
$is_filtered  = ( $search !== '' || $cat_term || 'new' !== $sort );
$show_feature = ( 1 === $paged && ! $is_filtered );
$total_posts  = (int) wp_count_posts( 'post' )->publish;
$i            = 0;
?>
<main id="primary" class="site-main">
	<section class="section articles-page">
		<div class="container">

			<nav class="articles-breadcrumb" aria-label="مسیر">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a>
				<span aria-hidden="true">/</span>
				<span>مقالات</span>
			</nav>

			<header class="articles-hero">
				<div class="articles-hero__icon" aria-hidden="true">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h12a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2V4z"/><path d="M18 8h2v10a2 2 0 0 1-2 2"/><path d="M8 9h6M8 13h6"/></svg>
				</div>
				<div>
					<h1 class="section__title articles-hero__title"><?php the_title(); ?></h1>
					<p class="articles-hero__sub">راهنمای خرید و نگهداری تشک، تخت و کالای خواب<?php echo $total_posts ? ' · ' . esc_html( number_format_i18n( $total_posts ) ) . ' مقاله' : ''; ?></p>
				</div>
			</header>

			<?php
			get_template_part( 'template-parts/articles-toolbar', null, [
				'active_cat' => $cat_term ? (int) $cat_term->term_id : 0,
				'search'     => $search,
				'sort'       => $sort,
			] );
			?>

			<?php if ( $is_filtered ) : ?>
				<p class="articles-result-info">
					<?php echo esc_html( number_format_i18n( $q->found_posts ) ); ?> مقاله پیدا شد<?php echo $search !== '' ? ' برای «' . esc_html( $search ) . '»' : ''; ?><?php echo $cat_term ? ' در دسته «' . esc_html( $cat_term->name ) . '»' : ''; ?>
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="articles-result-info__clear">حذف فیلترها</a>
				</p>
			<?php endif; ?>

			<?php if ( $q->have_posts() ) : ?>
				<div class="articles-grid">
					<?php while ( $q->have_posts() ) : $q->the_post(); ?>
						<?php if ( 0 === $i && $show_feature ) : ?>
							<?php get_template_part( 'template-parts/article-card', null, [ 'featured' => true ] ); ?>
						<?php else : ?>
							<?php get_template_part( 'template-parts/article-card' ); ?>
						<?php endif; ?>
						<?php $i++; ?>
					<?php endwhile; ?>
				</div>

				<?php
				$links = paginate_links( [
					'base'      => trailingslashit( get_permalink( get_queried_object_id() ) ) . 'page/%#%/',
					'format'    => '',
					'current'   => $paged,
					'total'     => (int) $q->max_num_pages,
					'prev_text' => 'قبلی',
					'next_text' => 'بعدی',
					'mid_size'  => 1,
					'type'      => 'list',
					'add_args'  => array_filter( [
						'aq'    => $search,
						'acat'  => $cat_slug,
						'asort' => ( 'new' !== $sort ) ? $sort : '',
					] ),
				] );
				if ( $links ) :
					?>
					<nav class="articles-pagination" aria-label="صفحه‌بندی مقالات"><?php echo $links; // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
				<?php endif; ?>
			<?php else : ?>
				<div class="articles-empty">
					<h2>مقاله‌ای پیدا نشد</h2>
					<p><?php echo $is_filtered ? 'عبارت یا دسته‌ی دیگری را امتحان کنید.' : 'به‌زودی مقالات جدید منتشر می‌شوند.'; ?></p>
					<a href="<?php echo esc_url( get_permalink( get_queried_object_id() ) ); ?>" class="btn btn--primary">همه مقالات</a>
				</div>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>

		</div>
	</section>
	<?php get_template_part( 'template-parts/newsletter' ); ?>
</main>
<?php get_footer();
