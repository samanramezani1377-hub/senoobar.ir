<?php
/**
 * Archive Product — Shop Page Template (v2)
 * 
 * Fully custom WooCommerce shop with:
 * - Category tabs (filter by product category)
 * - Price range filter (min / max slider)
 * - Sort dropdown (newest, price low→high, price high→low, popular)
 * - Grid / List view toggle
 * - AJAX filtering (no page reload)
 * - Mobile-friendly filter panel
 */

if (!defined('ABSPATH')) exit;

get_header();

// Get all product categories that have products
$product_categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'orderby'    => 'name',
]);

// Current active category
$current_cat_id = is_product_category() ? get_queried_object()->term_id : 0;
$current_cat_slug = is_product_category() ? get_queried_object()->slug : '';
?>

<!-- Shop accessibility: keep the global skip link visually hidden until keyboard focus. -->
<style id="senoobar-shop-accessibility">
  .skip-link.screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
  .skip-link.screen-reader-text:focus {
    position: fixed;
    top: 12px;
    left: 12px;
    width: auto;
    height: auto;
    margin: 0;
    padding: 10px 14px;
    overflow: visible;
    clip: auto;
    white-space: normal;
    z-index: 999999;
    background: #fff;
    color: #111;
    border: 2px solid currentColor;
    border-radius: 6px;
    box-shadow: 0 4px 16px rgba(0,0,0,.18);
  }
</style>

<main class="shop-page" id="primary">
  <!-- ─── Page Header ─────────────────────────── -->
  <section class="shop-header">
    <div class="container">
      <!-- Breadcrumb -->
      <nav class="shop-breadcrumb" aria-label="مسیر صفحه">
        <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
        <span class="shop-breadcrumb__sep">/</span>
        <?php if (is_product_category()): ?>
          <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>">فروشگاه</a>
          <span class="shop-breadcrumb__sep">/</span>
          <span class="shop-breadcrumb__current"><?php echo esc_html(get_queried_object()->name); ?></span>
        <?php else: ?>
          <span class="shop-breadcrumb__current">فروشگاه</span>
        <?php endif; ?>
      </nav>

      <h1 class="shop-title">
        <?php woocommerce_page_title(); ?>
      </h1>

      <p class="shop-desc">
        <?php 
        if (is_product_category()) {
          echo get_queried_object()->description;
        } else {
          echo 'انواع سرویس خواب، تشک، تخت خواب، مبل و مبلمان با بهترین کیفیت و قیمت';
        }
        ?>
      </p>
    </div>
  </section>

  <div class="container">
    <div class="shop-layout">

      <!-- ─── Sidebar Filters ─────────────────────── -->
      <aside class="shop-sidebar" id="shopFilters">
        
        <!-- Filter Header (Mobile Toggle) -->
        <div class="filter-header">
          <h2 class="filter-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
            </svg>
            فیلترها
          </h2>
          <button type="button" class="filter-close" id="filterClose" aria-label="بستن فیلترها">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>

        <!-- Desktop-only "فیلتر" heading above the collapsible groups -->
        <h3 class="filter-sidebar-title">فیلتر</h3>

        <!-- Category Filter -->
        <div class="filter-group">
          <h4 class="filter-group-title">دسته‌بندی</h4>
          <ul class="category-filter-list">
            <li>
              <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" 
                 class="category-filter-item <?php echo !is_product_category() ? 'active' : ''; ?>"
                 data-cat="all">
                همه محصولات
              </a>
            </li>
            <?php foreach ($product_categories as $cat): ?>
              <li>
                <a href="<?php echo get_term_link($cat); ?>" 
                   class="category-filter-item <?php echo ($current_cat_id == $cat->term_id) ? 'active' : ''; ?>"
                   data-cat="<?php echo esc_attr($cat->slug); ?>">
                  <?php echo esc_html($cat->name); ?>
                  <span class="cat-count"><?php echo $cat->count; ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Price Range Filter -->
        <div class="filter-group">
          <h4 class="filter-group-title">محدوده قیمت</h4>
          <div class="price-filter">
            <div class="price-inputs">
              <div class="price-input-group">
                <label for="minPrice">از</label>
                <input type="number" id="minPrice" name="min_price" 
                       placeholder="۰" min="0" step="100000"
                       value="<?php echo isset($_GET['min_price']) ? intval($_GET['min_price']) : ''; ?>">
                <span class="price-unit">تومان</span>
              </div>
              <span class="price-separator">—</span>
              <div class="price-input-group">
                <label for="maxPrice">تا</label>
                <input type="number" id="maxPrice" name="max_price" 
                       placeholder="نامحدود" min="0" step="100000"
                       value="<?php echo isset($_GET['max_price']) ? intval($_GET['max_price']) : ''; ?>">
                <span class="price-unit">تومان</span>
              </div>
            </div>
            <button type="button" class="btn-filter-apply" id="applyPriceFilter">
              اعمال فیلتر قیمت
            </button>
          </div>
        </div>

        <!-- Reset Filters -->
        <button type="button" class="btn-reset-filters" id="resetFilters">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M1 4v6h6M23 20v-6h-6"/>
            <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/>
          </svg>
          حذف همه فیلترها
        </button>
      </aside>

      <!-- ─── Mobile Filter Overlay ──────────────── -->
      <div class="filter-overlay" id="filterOverlay"></div>

      <!-- ─── Main Content ───────────────────────── -->
      <div class="shop-main">
        
        <!-- Toolbar: Results count + Sort + View toggle -->
        <div class="shop-toolbar">
          <div class="toolbar-left">
            <?php
            global $wp_query;
            $total_products = $wp_query->found_posts;
            ?>
            <span class="results-count" id="resultsCount">
              <?php echo $total_products; ?> محصول
            </span>
            <!-- Mobile Filter Button -->
            <button type="button" class="btn-filter-toggle" id="filterToggle">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
              </svg>
              فیلترها
            </button>
          </div>

          <div class="toolbar-right">
            <!-- Sort -->
            <div class="sort-wrapper">
              <label for="sortBy" class="sort-label">مرتب‌سازی:</label>
              <select id="sortBy" name="orderby" class="sort-select" aria-label="مرتب‌سازی محصولات">
                <option value="menu_order" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'menu_order'); ?>>پیش‌فرض</option>
                <option value="popularity" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'popularity'); ?>>محبوب‌ترین</option>
                <option value="rating" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'rating'); ?>>بالاترین امتیاز</option>
                <option value="date" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'date'); ?>>جدیدترین</option>
                <option value="price" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'price'); ?>>قیمت: کم به زیاد</option>
                <option value="price-desc" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'price-desc'); ?>>قیمت: زیاد به کم</option>
              </select>
            </div>

            <!-- View Toggle -->
            <div class="view-toggle">
              <button type="button" class="view-btn active" data-view="grid" aria-label="نمایش جدولی">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <rect x="2" y="2" width="9" height="9" rx="1"/><rect x="13" y="2" width="9" height="9" rx="1"/>
                  <rect x="2" y="13" width="9" height="9" rx="1"/><rect x="13" y="13" width="9" height="9" rx="1"/>
                </svg>
              </button>
              <button type="button" class="view-btn" data-view="list" aria-label="نمایش لیستی">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                  <rect x="2" y="3" width="20" height="3" rx="1"/>
                  <rect x="2" y="10.5" width="20" height="3" rx="1"/>
                  <rect x="2" y="18" width="20" height="3" rx="1"/>
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- ─── Products Grid ────────────────────── -->
        <div class="products-wrapper" id="productsWrapper">
          <?php
          wc_set_loop_prop('columns', 3);

          if (woocommerce_product_loop()) {
            woocommerce_product_loop_start();

            if (wc_get_loop_prop('total')) {
              while (have_posts()) {
                the_post();
                do_action('woocommerce_shop_loop');
                wc_get_template_part('content', 'product');
              }
            }

            woocommerce_product_loop_end();
          } else {
            do_action('woocommerce_no_products_found');
          }
          ?>
        </div>

        <!-- ─── Pagination ───────────────────────── -->
        <div class="shop-pagination" id="shopPagination">
          <?php woocommerce_pagination(); ?>
        </div>

        <!-- ─── Loading Spinner ──────────────────── -->
        <div class="filter-loading" id="filterLoading" style="display:none;">
          <div class="spinner"></div>
          <span>در حال بارگذاری محصولات...</span>
        </div>

      </div><!-- /.shop-main -->
    </div><!-- /.shop-layout -->
  </div>
</main>

<?php
get_footer();
