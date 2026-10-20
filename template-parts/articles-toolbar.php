<?php
/**
 * نوار ابزار مقالات: جستجو، مرتب‌سازی و چیپ‌های دسته‌بندی.
 *
 * پارامترها ($args):
 *   active_cat  (int)    شناسه‌ی دسته‌ی فعال، ۰ = همه
 *   search      (string) عبارت جستجوی فعلی
 *   sort        (string) کلید مرتب‌سازی فعلی
 *
 * فرم همیشه به صفحه‌ی مقالات GET می‌شود تا جستجو فقط بین نوشته‌ها انجام شود
 * (جستجوی عمومی سایت فقط محصولات را برمی‌گرداند).
 *
 * @package Senoobar
 */

$active_cat = isset( $args['active_cat'] ) ? (int) $args['active_cat'] : 0;
$search     = isset( $args['search'] ) ? (string) $args['search'] : '';
$sort       = isset( $args['sort'] ) ? (string) $args['sort'] : 'new';
$base_url   = senoobar_articles_url();

$cats = get_categories( [
	'hide_empty' => true,
	'exclude'    => [ (int) get_option( 'default_category' ) ],
	'orderby'    => 'count',
	'order'      => 'DESC',
] );
$active_slug = '';
if ( $active_cat ) {
	$term = get_term( $active_cat, 'category' );
	if ( $term && ! is_wp_error( $term ) ) {
		$active_slug = $term->slug;
	}
}
?>
<div class="articles-toolbar">
	<form class="articles-search" role="search" method="get" action="<?php echo esc_url( $base_url ); ?>">
		<label class="screen-reader-text" for="articles-search-input">جستجو در مقالات</label>
		<svg class="articles-search__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
		<input id="articles-search-input" type="search" name="aq" value="<?php echo esc_attr( $search ); ?>" placeholder="جستجو در مقالات...">
		<?php if ( $active_slug ) : ?>
			<input type="hidden" name="acat" value="<?php echo esc_attr( $active_slug ); ?>">
		<?php endif; ?>
		<?php if ( 'new' !== $sort ) : ?>
			<input type="hidden" name="asort" value="<?php echo esc_attr( $sort ); ?>">
		<?php endif; ?>
		<button type="submit" class="articles-search__btn">جستجو</button>
	</form>

	<?php if ( ! empty( $cats ) ) : ?>
	<nav class="articles-cats" aria-label="دسته‌بندی مقالات">
		<a href="<?php echo esc_url( $base_url ); ?>" class="articles-cats__chip<?php echo $active_cat ? '' : ' is-active'; ?>"<?php echo $active_cat ? '' : ' aria-current="page"'; ?>>همه مقالات</a>
		<?php foreach ( $cats as $c ) : ?>
			<?php $is_active = ( (int) $c->term_id === $active_cat ); ?>
			<a href="<?php echo esc_url( get_category_link( $c->term_id ) ); ?>" class="articles-cats__chip<?php echo $is_active ? ' is-active' : ''; ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
				<?php echo esc_html( $c->name ); ?>
				<span class="articles-cats__count"><?php echo esc_html( number_format_i18n( $c->count ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</nav>
	<?php endif; ?>

	<div class="articles-sort" role="group" aria-label="مرتب‌سازی">
		<span class="articles-sort__label">مرتب‌سازی:</span>
		<?php foreach ( senoobar_articles_sort_options() as $key => $label ) : ?>
			<?php
			$url = add_query_arg(
				array_filter( [
					'aq'    => $search,
					'acat'  => ( is_page() && $active_slug ) ? $active_slug : '',
					'asort' => ( 'new' !== $key ) ? $key : '',
				] ),
				( is_category() || is_tag() ) ? get_term_link( get_queried_object() ) : $base_url
			);
			?>
			<a href="<?php echo esc_url( $url ); ?>" class="articles-sort__opt<?php echo ( $sort === $key ) ? ' is-active' : ''; ?>"<?php echo ( $sort === $key ) ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</div>
</div>
