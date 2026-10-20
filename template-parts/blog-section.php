<?php
/**
 * Latest articles section (home page).
 *
 * Renders only real posts. The old version fell back to four hard-coded fake
 * articles (dated 1403, href="#") when the blog was empty, which crawlers and
 * AI agents would index as real content. With no posts the section is skipped.
 */
$title = get_theme_mod( 'senoobar_section_blog_title', 'آخرین مقالات' );
$q     = new WP_Query( [ 'posts_per_page' => 4 ] );

if ( ! $q->have_posts() ) {
    wp_reset_postdata();
    return;
}

$posts_page_id  = (int) get_option( 'page_for_posts' );
$posts_page_url = $posts_page_id ? get_permalink( $posts_page_id ) : '';
?>
<section class="section section--white">
    <div class="container">
        <div class="flex-between mb-6">
            <h2 class="section__title" style="margin-bottom:0;"><?php echo esc_html( $title ); ?></h2>
            <?php if ( $posts_page_url ) : ?>
                <a href="<?php echo esc_url( $posts_page_url ); ?>" class="section-link">مشاهده همه</a>
            <?php endif; ?>
        </div>
        <div class="blog-grid">
            <?php while ( $q->have_posts() ) : $q->the_post(); ?>
                <a href="<?php the_permalink(); ?>" class="blog-card">
                    <div class="blog-card__img">
                        <?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'medium', [ 'loading' => 'lazy' ] ); else : ?>
                            <img src="<?php echo esc_url( SENOOBAR_URI ); ?>/assets/images/blog-1.jpg" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
                        <?php endif; ?>
                    </div>
                    <div class="blog-card__body">
                        <div class="blog-card__meta"><span><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></span><span>·</span><span><?php the_author(); ?></span></div>
                        <h3 class="blog-card__title"><?php the_title(); ?></h3>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<?php wp_reset_postdata(); ?>
