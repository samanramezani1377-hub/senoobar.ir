<?php
/**
 * Senoobar — Dynamic SEO Engine (title + meta description + OG tags)
 *
 * به‌صورت خودکار و داینامیک، تایتل و متا دیسکریپشنِ همه‌ی صفحه‌ها را با طول
 * استاندارد تولید می‌کند. شامل صفحه‌های آینده (محصول جدید، دسته، برگه، نوشته و …)
 * هم می‌شود، چون بر اساس «نوع صفحه» ساخته می‌شود نه به‌صورت دستی.
 *
 * استانداردهای طول (برای نمایش کامل در نتایج گوگل):
 *   - Title: حداکثر ۶۰ کاراکتر
 *   - Meta description: بین ۷۰ تا ۱۶۰ کاراکتر (بهینه ۱۵۰~۱۶۰)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* -------------------------------------------------------------------------
 * ابزارهای کمکی
 * ---------------------------------------------------------------------- */

function senoobar_seo_plugin_active() {
    return ( function_exists( 'YoastSEO' ) || class_exists( 'WPSEO_Frontend' ) || defined( 'WPSEO_VERSION' ) );
}

function senoobar_brand() {
    static $brand = null;
    if ( null === $brand ) {
        $brand = trim( get_bloginfo( 'name' ) );
        if ( empty( $brand ) || $brand === 'senoobar' ) {
            $brand = 'صنوبر';
        }
    }
    return $brand;
}

function senoobar_clip( $text, $max ) {
    $text = trim( $text );
    if ( mb_strlen( $text ) <= $max ) {
        return $text;
    }
    return mb_substr( $text, 0, $max - 1 ) . '…';
}

function senoobar_clean_archive_title( $title ) {
    $title = wp_strip_all_tags( $title );
    $prefixes = array(
        'بایگانی‌ها:', 'بایگانی‌های', 'بایگانی:', 'آرشیو:', 'دسته:', 'برچسب:',
        'Archives:', 'Archive:', 'Category:', 'Tag:', 'Author:',
        'فروشگاه:', 'محصولات:',
    );
    foreach ( $prefixes as $p ) {
        if ( 0 === mb_strpos( $title, $p ) ) {
            $title = trim( mb_substr( $title, mb_strlen( $p ) ) );
            break;
        }
    }
    return trim( $title );
}

function senoobar_build_description( $text ) {
    $text = strip_shortcodes( (string) $text );
    $text = wp_strip_all_tags( $text );
    $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
    $text = preg_replace( '/\s+/u', ' ', $text );
    $text = trim( $text, " \t\n\r\0\x0B|—-" );
    $text = trim( $text );

    if ( mb_strlen( $text ) > 160 ) {
        $text = mb_substr( $text, 0, 157 ) . '…';
    }

    if ( mb_strlen( $text ) < 70 ) {
        $text .= ' | ' . senoobar_brand() . ' — فروشگاه تخصصی تشک، سرویس خواب و مبلمان.';
        if ( mb_strlen( $text ) > 160 ) {
            $text = mb_substr( $text, 0, 157 ) . '…';
        }
    }

    return trim( $text, " \t\n\r\0\x0B|—-" );
}

/* -------------------------------------------------------------------------
 * متا دیسکریپشن
 * ---------------------------------------------------------------------- */

function senoobar_get_meta_description() {
    $desc = '';

    // 1) مقدار دستی (بالاترین اولویت).
    if ( is_singular() ) {
        $manual = get_post_meta( get_queried_object_id(), '_senoobar_meta_description', true );
        if ( ! empty( $manual ) ) {
            return senoobar_build_description( $manual );
        }
    }

    // 2) صفحه اصلی.
    if ( is_front_page() ) {
        $desc = get_bloginfo( 'description' );
        if ( empty( $desc ) || $desc === 'senoobar' ) {
            $desc = 'خرید تشک طبی فنری، سرویس خواب و مبلمان با ضمانت و ارسال سریع | فروشگاه صنوبر';
        }
        return senoobar_build_description( $desc );
    }

    // 3) برگه / نوشته / محصول.
    if ( is_singular() ) {
        $post = get_post( get_queried_object_id() );
        $desc = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
        if ( ! empty( $desc ) ) {
            return senoobar_build_description( $desc );
        }
    }

    // 4) صفحه فروشگاه ووکامرس (shop).
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $shop_id = wc_get_page_id( 'shop' );
        $desc    = get_post_meta( $shop_id, '_senoobar_meta_description', true );
        if ( empty( $desc ) ) {
            $desc = 'انواع محصولات ' . senoobar_brand() . ' شامل تشک طبی، سرویس خواب، تخت خواب و مبلمان با کیفیت و قیمت مناسب.';
        }
        return senoobar_build_description( $desc );
    }

    // 5) دسته‌بندی / برچسب / اصطلاح.
    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            return senoobar_build_description( $term->description );
        }
        if ( $term ) {
            return senoobar_build_description( 'انواع ' . $term->name . ' با کیفیت و قیمت مناسب در فروشگاه ' . senoobar_brand() . '.' );
        }
    }

    // 6) بایگانی نویسنده.
    if ( is_author() ) {
        return senoobar_build_description( 'نوشته‌های این نویسنده را در فروشگاه ' . senoobar_brand() . ' بخوانید.' );
    }

    // 7) نتایج جستجو.
    if ( is_search() ) {
        return senoobar_build_description( 'نتایج جستجو برای عبارت «' . get_search_query() . '» در فروشگاه ' . senoobar_brand() . '.' );
    }

    // 8) پیش‌فرض.
    return senoobar_build_description( get_bloginfo( 'description' ) );
}

/* -------------------------------------------------------------------------
 * تایتل صفحه
 * ---------------------------------------------------------------------- */

function senoobar_get_document_title() {
    // مقدار دستی (بالاترین اولویت).
    if ( is_singular() ) {
        $manual = get_post_meta( get_queried_object_id(), '_senoobar_meta_title', true );
        if ( ! empty( $manual ) ) {
            return senoobar_clip( $manual, 60 );
        }
    }

    $brand = senoobar_brand();

    if ( is_front_page() ) {
        $tagline = trim( get_bloginfo( 'description' ) );
        if ( empty( $tagline ) || $tagline === 'senoobar' ) {
            return $brand . ' | فروشگاه تشک طبی و فنری، سرویس خواب و مبلمان';
        }
        return senoobar_clip( $brand . ' | ' . $tagline, 60 );
    }

    if ( is_singular() ) {
        return senoobar_clip( get_the_title( get_queried_object_id() ) . ' | ' . $brand, 60 );
    }

    // صفحه فروشگاه ووکامرس.
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $shop_id = wc_get_page_id( 'shop' );
        $name    = get_the_title( $shop_id );
        if ( empty( $name ) || strtolower( $name ) === 'shop' ) {
            $name = 'فروشگاه';
        }
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        $name = $term ? $term->name : '';
        if ( empty( $name ) ) {
            $name = senoobar_clean_archive_title( get_the_archive_title() );
        }
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_author() ) {
        return senoobar_clip( get_the_author() . ' | ' . $brand, 60 );
    }

    if ( is_search() ) {
        return senoobar_clip( 'جستجوی «' . get_search_query() . '» | ' . $brand, 60 );
    }

    if ( is_archive() ) {
        $name = senoobar_clean_archive_title( get_the_archive_title() );
        if ( empty( $name ) ) {
            $name = 'بایگانی';
        }
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_404() ) {
        return 'صفحه پیدا نشد | ' . $brand;
    }

    return senoobar_clip( get_the_title() . ' | ' . $brand, 60 );
}

/* -------------------------------------------------------------------------
 * خروجی در هدر
 * ---------------------------------------------------------------------- */

function senoobar_render_seo_meta() {
    $use_yoast = senoobar_seo_plugin_active();

    // اگر Yoast فعال نباشد، خودمان همه‌چیز را چاپ می‌کنیم.
    if ( ! $use_yoast ) {
        $desc  = senoobar_get_meta_description();
        $title = senoobar_get_document_title();
        if ( ! empty( $desc ) ) {
            echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
        }
        echo '<title>' . esc_html( $title ) . "</title>\n";
        echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
    }
    // وقتی Yoast فعال است، <title>، meta description و همه‌ی تگ‌های OG/Twitter
    // (og:site_name، og:image، og:url، ...) را خودش چاپ می‌کند؛ چیزی اضافه
    // نمی‌کنیم تا تگ تکراری ساخته نشود.

    // تصویر OG برای صفحات تک‌محتوا (فقط وقتی Yoast فعال نیست).
    if ( ! $use_yoast && is_singular() ) {
        $id    = get_queried_object_id();
        $og_ok = (bool) post_type_supports( get_post_type( $id ), 'thumbnail' ) && has_post_thumbnail( $id );
        if ( $og_ok ) {
            $img = wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'large' );
            if ( $img ) {
                echo '<meta property="og:image" content="' . esc_url( $img ) . '" />' . "\n";
            }
        }
    }
}
add_action( 'wp_head', 'senoobar_render_seo_meta', 1 );

/* -------------------------------------------------------------------------
 * مدیریت افزونه‌های سئو (Yoast) — تا title/desc خراب یا تکراری نشود.
 * ---------------------------------------------------------------------- */

// وقتی Yoast فعال است، عنوان و توضیحاتی که در باکس Yoast نوشته می‌شود (یا
// قالب‌های پیش‌فرض Yoast) باید اعمال شود؛ پس هیچ فیلتری روی wpseo_title،
// wpseo_metadesc یا pre_get_document_title نمی‌گذاریم. موتور داخلی قالب فقط
// وقتی Yoast نیست فعال است.
if ( ! senoobar_seo_plugin_active() ) {
    // جلوگیری از چاپ تایتل پیش‌فرض وردپرس.
    add_filter( 'pre_get_document_title', 'senoobar_get_document_title', 998 );
}

/* -------------------------------------------------------------------------
 * رفع تداخل تگ <title>: وقتی افزونه‌ی سئو (Yoast) فعال نیست، موتور سئوی
 * خودمان <title> اختصاصی چاپ می‌کند (senoobar_render_seo_meta). در این حالت
 * باید خروجی پیش‌فرض title-tag وردپرس را غیرفعال کنیم تا دو <title> چاپ نشود.
 * وقتی Yoast فعال است، title-tag دست نخورده می‌ماند (Yoast خودش مدیریت می‌کند).
 * ---------------------------------------------------------------------- */
function senoobar_reconcile_title_tag() {
    if ( senoobar_seo_plugin_active() ) {
        return; // Yoast مسئول title است؛ title-tag را رها کنید.
    }
    // موتور سئوی ما <title> را چاپ می‌کند، پس خروجی پیش‌فرض وردپرس حذف شود.
    remove_action( 'wp_head', '_wp_render_title_tag', 1 );
}
add_action( 'after_setup_theme', 'senoobar_reconcile_title_tag', 20 );

/* -------------------------------------------------------------------------
 * داده ساختاریافته‌ی کسب‌وکار (Organization / فروشگاه مبلمان)
 *
 * Yoast رایگان فقط Organization ساده (نام و لوگو) می‌سازد؛ آدرس و تلفن را
 * از تنظیمات قالب (همان مقادیر فوتر و صفحه تماس) به همان گره اضافه می‌کنیم تا
 * موتورهای جستجو و دستیارهای هوشمند بتوانند فروشگاه را به‌عنوان یک کسب‌وکار
 * محلی بشناسند. اگر Yoast فعال نباشد، یک JSON-LD ساده در صفحه اصلی چاپ می‌شود.
 * ---------------------------------------------------------------------- */

/**
 * تبدیل شماره تلفن ایرانی به قالب بین‌المللی (E.164).
 */
function senoobar_schema_phone( $num ) {
    $digits = function_exists( 'senoobar_tel_href' ) ? senoobar_tel_href( $num ) : preg_replace( '/[^0-9+]/', '', (string) $num );
    if ( '' === $digits ) {
        return '';
    }
    if ( 0 === strpos( $digits, '+' ) ) {
        return $digits;
    }
    if ( 0 === strpos( $digits, '00' ) ) {
        return '+' . substr( $digits, 2 );
    }
    if ( 0 === strpos( $digits, '0' ) ) {
        return '+98' . substr( $digits, 1 );
    }
    return '+98' . $digits;
}

/**
 * اطلاعات تماس فروشگاه از تنظیمات قالب (مقادیر پیش‌فرض هماهنگ با footer.php).
 */
function senoobar_contact_info() {
    $phones = array();
    $defaults = array(
        'senoobar_footer_phone1' => '۰۹۱۳۰۲۰۵۸۹۸',
        'senoobar_footer_phone2' => '۰۹۱۳۰۲۰۵۸۶۸',
        'senoobar_footer_phone3' => '۰۹۱۳۰۲۰۵۳۲۳',
    );
    foreach ( $defaults as $key => $default ) {
        $phone = senoobar_schema_phone( get_theme_mod( $key, $default ) );
        if ( '' !== $phone && ! in_array( $phone, $phones, true ) ) {
            $phones[] = $phone;
        }
    }
    return array(
        'phones'  => $phones,
        'address' => trim( (string) get_theme_mod( 'senoobar_footer_address', 'اصفهان، شهرک صنعتی دولت‌آباد، خیابان شماره ۱۰ (خیام)، فروشگاه صنوبر' ) ),
        'hours'   => trim( (string) get_theme_mod( 'senoobar_footer_hours', '' ) ),
    );
}

/**
 * فیلدهای کسب‌وکار محلی را به آرایه‌ی گره‌ی Organization اضافه می‌کند.
 *
 * @param array $data گره‌ی Organization.
 * @return array
 */
function senoobar_schema_add_business_fields( $data ) {
    if ( ! is_array( $data ) ) {
        $data = array();
    }
    $info = senoobar_contact_info();

    $types   = isset( $data['@type'] ) ? (array) $data['@type'] : array( 'Organization' );
    $types[] = 'FurnitureStore';
    $data['@type'] = array_values( array_unique( $types ) );

    if ( empty( $data['name'] ) ) {
        $data['name'] = senoobar_brand();
    }
    if ( empty( $data['url'] ) ) {
        $data['url'] = home_url( '/' );
    }
    if ( '' !== $info['address'] ) {
        $data['address'] = array(
            '@type'          => 'PostalAddress',
            'streetAddress'  => $info['address'],
            'addressCountry' => 'IR',
        );
    }
    if ( ! empty( $info['phones'] ) ) {
        $data['telephone']    = $info['phones'][0];
        $data['contactPoint'] = array();
        foreach ( $info['phones'] as $phone ) {
            $data['contactPoint'][] = array(
                '@type'             => 'ContactPoint',
                'telephone'         => $phone,
                'contactType'       => 'customer service',
                'areaServed'        => 'IR',
                'availableLanguage' => 'fa',
            );
        }
    }
    // لوکیشن و ساعت کاری (از Customizer ← «لوکیشن و ساعت کاری فروشگاه»).
    if ( function_exists( 'senoobar_business_location_data' ) ) {
        $loc = senoobar_business_location_data();

        if ( '' !== $loc['locality'] || '' !== $loc['region'] ) {
            if ( empty( $data['address'] ) || ! is_array( $data['address'] ) ) {
                $data['address'] = array(
                    '@type'          => 'PostalAddress',
                    'addressCountry' => 'IR',
                );
            }
            if ( '' !== $loc['locality'] ) {
                $data['address']['addressLocality'] = $loc['locality'];
            }
            if ( '' !== $loc['region'] ) {
                $data['address']['addressRegion'] = $loc['region'];
            }
        }
        if ( '' !== $loc['lat'] ) {
            $data['geo'] = array(
                '@type'     => 'GeoCoordinates',
                'latitude'  => $loc['lat'],
                'longitude' => $loc['lng'],
            );
        }
        if ( '' !== $loc['map_url'] ) {
            $data['hasMap'] = $loc['map_url'];
        }
        if ( ! empty( $loc['hours'] ) ) {
            $data['openingHoursSpecification'] = array( $loc['hours'] );
        }
    }

    return $data;
}

if ( senoobar_seo_plugin_active() ) {
    add_filter( 'wpseo_schema_organization', 'senoobar_schema_add_business_fields', 20 );
}

/**
 * بدون Yoast: Organization و WebSite (با جستجوی سایت) در صفحه اصلی.
 */
function senoobar_print_fallback_jsonld() {
    if ( senoobar_seo_plugin_active() || ! is_front_page() ) {
        return;
    }
    $org = array(
        '@type' => 'Organization',
        '@id'   => home_url( '/#organization' ),
        'name'  => senoobar_brand(),
        'url'   => home_url( '/' ),
    );
    $logo_id = get_theme_mod( 'custom_logo' );
    if ( $logo_id ) {
        $logo = wp_get_attachment_image_url( $logo_id, 'full' );
        if ( $logo ) {
            $org['logo'] = $logo;
        }
    }
    $org   = senoobar_schema_add_business_fields( $org );
    $graph = array(
        '@context' => 'https://schema.org',
        '@graph'   => array(
            $org,
            array(
                '@type'           => 'WebSite',
                '@id'             => home_url( '/#website' ),
                'url'             => home_url( '/' ),
                'name'            => senoobar_brand(),
                'inLanguage'      => get_bloginfo( 'language' ),
                'publisher'       => array( '@id' => home_url( '/#organization' ) ),
                'potentialAction' => array(
                    '@type'       => 'SearchAction',
                    'target'      => array(
                        '@type'       => 'EntryPoint',
                        'urlTemplate' => home_url( '/?s={search_term_string}' ),
                    ),
                    'query-input' => 'required name=search_term_string',
                ),
            ),
        ),
    );
    echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG ) . "</script>\n";
}
add_action( 'wp_head', 'senoobar_print_fallback_jsonld', 5 );
