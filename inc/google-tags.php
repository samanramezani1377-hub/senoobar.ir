<?php
/**
 * Senoobar — Safe Google tag integration.
 *
 * Supports exactly one active installation mode:
 * - disabled: output no Google tag code
 * - gtm: output the GTM container snippet in <head> and noscript after <body>
 * - ga4: output the GA4 gtag.js snippet directly
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict the active installation mode to the supported options.
 *
 * @param mixed $value Submitted mode.
 * @return string
 */
function senoobar_google_tags_sanitize_mode( $value ) {
	$allowed = array( 'disabled', 'gtm', 'ga4' );
	$value   = is_string( $value ) ? $value : 'disabled';

	return in_array( $value, $allowed, true ) ? $value : 'disabled';
}

/**
 * Validate a Google Tag Manager container ID.
 *
 * @param mixed $value Submitted container ID.
 * @return string
 */
function senoobar_google_tags_sanitize_gtm_id( $value ) {
	$value = strtoupper( trim( sanitize_text_field( is_scalar( $value ) ? (string) $value : '' ) ) );

	return preg_match( '/^GTM-[A-Z0-9]+$/', $value ) ? $value : '';
}

/**
 * Validate a GA4 measurement ID.
 *
 * @param mixed $value Submitted measurement ID.
 * @return string
 */
function senoobar_google_tags_sanitize_ga4_id( $value ) {
	$value = strtoupper( trim( sanitize_text_field( is_scalar( $value ) ? (string) $value : '' ) ) );

	return preg_match( '/^G-[A-Z0-9]+$/', $value ) ? $value : '';
}

/**
 * Add a dedicated Google tags section beside the theme's existing Customizer sections.
 *
 * @param WP_Customize_Manager $customize Customizer manager.
 * @return void
 */
function senoobar_google_tags_customize_register( $customize ) {
	$customize->add_section(
		'senoobar_google_tags',
		array(
			'title'       => '📊 تگ‌های گوگل و آمارگیر',
			'description' => 'نصب تگ‌ها را از اینجا مدیریت کنید. این بخش به‌صورت پیش‌فرض غیرفعال است. پیش از فعال‌سازی، کدهای تکراری GTM یا GA4 را از قالب، افزونه‌ها و ابزارهای درج کد حذف یا غیرفعال کنید. در هر لحظه فقط یکی از روش‌های نصب زیر خروجی داده می‌شود.',
			'priority'    => 120,
		)
	);

	$customize->add_setting(
		'senoobar_google_tag_mode',
		array(
			'default'           => 'disabled',
			'type'              => 'theme_mod',
			'capability'         => 'edit_theme_options',
			'sanitize_callback' => 'senoobar_google_tags_sanitize_mode',
		)
	);
	$customize->add_control(
		'senoobar_google_tag_mode',
		array(
			'label'       => 'روش نصب تگ',
			'description' => 'GTM را انتخاب کنید تا تگ‌ها از کانتینر مدیریت شوند؛ یا GA4 مستقیم را انتخاب کنید. هر دو روش هم‌زمان اجرا نمی‌شوند.',
			'section'     => 'senoobar_google_tags',
			'type'        => 'select',
			'choices'     => array(
				'disabled' => 'غیرفعال — هیچ کدی درج نشود',
				'gtm'      => 'Google Tag Manager (پیشنهادی برای مدیریت چند تگ)',
				'ga4'      => 'Google Analytics 4 مستقیم (gtag.js)',
			),
		)
	);

	$customize->add_setting(
		'senoobar_gtm_container_id',
		array(
			'default'           => 'GTM-W4L97HQM',
			'type'              => 'theme_mod',
			'capability'         => 'edit_theme_options',
			'sanitize_callback' => 'senoobar_google_tags_sanitize_gtm_id',
		)
	);
	$customize->add_control(
		'senoobar_gtm_container_id',
		array(
			'label'       => 'شناسه کانتینر GTM',
			'description' => 'قالب معتبر: GTM-XXXXXXX. این فیلد فقط در حالت Google Tag Manager استفاده می‌شود.',
			'section'     => 'senoobar_google_tags',
			'type'        => 'text',
			'input_attrs' => array(
				'placeholder' => 'GTM-XXXXXXX',
				'autocomplete' => 'off',
				'spellcheck' => 'false',
			),
		)
	);

	$customize->add_setting(
		'senoobar_ga4_measurement_id',
		array(
			'default'           => 'G-Q093T1BJZE',
			'type'              => 'theme_mod',
			'capability'         => 'edit_theme_options',
			'sanitize_callback' => 'senoobar_google_tags_sanitize_ga4_id',
		)
	);
	$customize->add_control(
		'senoobar_ga4_measurement_id',
		array(
			'label'       => 'شناسه اندازه‌گیری GA4',
			'description' => 'قالب معتبر: G-XXXXXXXXXX. این فیلد فقط در حالت GA4 مستقیم استفاده می‌شود. اگر GA4 را داخل GTM تنظیم می‌کنید، این حالت را انتخاب نکنید.',
			'section'     => 'senoobar_google_tags',
			'type'        => 'text',
			'input_attrs' => array(
				'placeholder' => 'G-XXXXXXXXXX',
				'autocomplete' => 'off',
				'spellcheck' => 'false',
			),
		)
	);
}
add_action( 'customize_register', 'senoobar_google_tags_customize_register', 20 );

/**
 * Output the selected Google tag integration in the document head.
 *
 * @return void
 */
function senoobar_google_tags_render_head() {
	if ( is_admin() ) {
		return;
	}

	$mode = senoobar_google_tags_sanitize_mode( get_theme_mod( 'senoobar_google_tag_mode', 'disabled' ) );

	if ( 'gtm' === $mode ) {
		$container_id = senoobar_google_tags_sanitize_gtm_id( get_theme_mod( 'senoobar_gtm_container_id', 'GTM-W4L97HQM' ) );
		if ( '' === $container_id ) {
			return;
		}
		?>
		<!-- Google Tag Manager — managed by Senoobar theme -->
		<script>
		(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_js( $container_id ); ?>');
		</script>
		<!-- End Google Tag Manager -->
		<?php
		return;
	}

	if ( 'ga4' === $mode ) {
		$measurement_id = senoobar_google_tags_sanitize_ga4_id( get_theme_mod( 'senoobar_ga4_measurement_id', 'G-Q093T1BJZE' ) );
		if ( '' === $measurement_id ) {
			return;
		}
		$script_url = 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $measurement_id );
		?>
		<!-- Google tag (gtag.js) — managed by Senoobar theme -->
		<script async src="<?php echo esc_url( $script_url ); ?>"></script>
		<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo esc_js( $measurement_id ); ?>');
		</script>
		<!-- End Google tag -->
		<?php
	}
}
add_action( 'wp_head', 'senoobar_google_tags_render_head', 1 );

/**
 * Output the GTM fallback immediately after the opening body tag.
 *
 * @return void
 */
function senoobar_google_tags_render_body() {
	if ( is_admin() || 'gtm' !== senoobar_google_tags_sanitize_mode( get_theme_mod( 'senoobar_google_tag_mode', 'disabled' ) ) ) {
		return;
	}

	$container_id = senoobar_google_tags_sanitize_gtm_id( get_theme_mod( 'senoobar_gtm_container_id', 'GTM-W4L97HQM' ) );
	if ( '' === $container_id ) {
		return;
	}

	$noscript_url = 'https://www.googletagmanager.com/ns.html?id=' . rawurlencode( $container_id );
	echo '<!-- Google Tag Manager (noscript) -->';
	echo '<noscript><iframe src="' . esc_url( $noscript_url ) . '" height="0" width="0" style="display:none;visibility:hidden" title="Google Tag Manager"></iframe></noscript>';
	echo '<!-- End Google Tag Manager (noscript) -->';
}
add_action( 'wp_body_open', 'senoobar_google_tags_render_body', 1 );
