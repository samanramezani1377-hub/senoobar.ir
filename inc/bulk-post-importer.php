<?php
/**
 * Senoobar — ایمپورت گروهی نوشته از فایل (ZIP / تکی).
 *
 * قابلیت‌ها:
 *   ۱. آپلود ZIP شامل چند فایل متنی (.txt / .md / .markdown / .html / .htm)
 *      — هر فایل به یک نوشته تبدیل می‌شود (عنوان = اولین خط غیرخالی).
 *   ۲. آپلود تکی با عنوان، دسته، خلاصه و تصویر شاخص.
 *   ۳. تبدیل Markdown → HTML (تیتر، لیست، پاراگراف، بولد، ایتالیک، لینک، عکس).
 *   ۴. پردازش کامل عکس‌های درون متن با هر تعداد و هر موقعیتی:
 *      - شناسایی `![alt](src)` و `![alt](src "title")`.
 *      - عکس‌های نسبی از داخل ZIP استخراج و در کتابخانه رسانه آپلود می‌شوند.
 *      - URL واقعی جایگزین مسیر نسبی می‌شود.
 *      - تمام URLهای مطلق (http/https) دانلود و در کتابخانه رسانه ذخیره می‌شوند؛ در صورت شکست دانلود، لینک اصلی برای جلوگیری از شکستن متن حفظ می‌شود.
 *   ۵. اولین عکس مقاله به‌صورت تصویر شاخص (featured image) ست می‌شود.
 *   ۶. پشتیبانی از WEBP / PNG / JPG / GIF.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'senoobar_bulk_import_menu' );

function senoobar_bulk_import_menu() {
	add_posts_page(
		'ایمپورت گروهی نوشته‌ها',
		'ایمپورت گروهی',
		'edit_posts',
		'senoobar-bulk-import',
		'senoobar_bulk_import_page'
	);
}

function senoobar_bulk_import_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$notice = get_transient( 'senoobar_bulk_import_notice' );
	if ( $notice ) {
		delete_transient( 'senoobar_bulk_import_notice' );
	}
	?>
	<div class="wrap" dir="rtl">
		<h1>📥 ایمپورت گروهی نوشته‌ها</h1>

		<div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px 20px;margin-bottom:20px;">
			<h2 style="margin:0 0 10px;">📖 راهنمای ساخت فایل (برای کپی به هوش مصنوعی)</h2>
			<p style="margin:0 0 14px;color:#475569;font-size:.9rem;">این متن را کپی کنید و به هر هوش مصنوعی بدهید تا فایل نوشته را دقیقاً با همین ساختار برایتان بسازد.</p>

			<details style="margin-bottom:12px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;">
				<summary style="cursor:pointer;font-weight:600;color:#0f172a;">🟢 راهنمای فایل تکی (Markdown / TXT / HTML)</summary>
				<div style="margin-top:10px;">
					<button type="button" class="button" onclick="senoobar_copy_guide('senoobar-guide-single')">📋 کپی متن راهنما</button>
					<pre id="senoobar-guide-single" style="direction:ltr;text-align:left;background:#0f172a;color:#e2e8f0;padding:14px;border-radius:8px;overflow:auto;font-size:.82rem;line-height:1.6;white-space:pre-wrap;">این فایل را برای ایمپورت یک مقالهٔ کامل و آمادهٔ انتشار در وردپرس بساز. فرمت خروجی یکی از این‌ها باشد: .md / .markdown / .txt / .html / .htm.

قواعد فایل:
۱. اولین خط غیرخالی عنوان مقاله باشد؛ اگر عنوان با # شروع می‌شود، همان تیتر اول محسوب می‌شود.
۲. پس از خط عنوان، متن کامل مقاله را قرار بده؛ مقدمه، تیترهای منظم، توضیحات کاربردی، جمع‌بندی و در صورت نیاز پرسش‌های متداول.
۳. برای پیدا کردن عکس از منابع معتبر تصاویر رایگان مانند Unsplash (unsplash.com) و Pexels (pexels.com) استفاده کن؛ عکس باید واقعاً با موضوع همان بخش و محصولات/فضای مبلمان، تخت خواب، تشک یا دکوراسیون مرتبط باشد. مجاز بودن استفاده و شرایط مجوز تصویر را رعایت کن. تصاویر باکیفیت، واضح و بدون واترمارک انتخاب کن؛ از عکس نامرتبط، تکراری یا صرفاً تزئینی پرهیز کن.
۴. هر تصویر را دقیقاً در جای مرتبط با توضیحش داخل متن قرار بده؛ تصاویر را فقط در ابتدای مقاله فهرست نکن.
۵. هیچ تصویری را از بدنه حذف نکن و تصویر اول را جداگانه تکرار نکن. اولین تصویر موفق به‌طور خودکار تصویر شاخص هم می‌شود، اما در همان جای متن باقی می‌ماند و قالب سایت تصویر شاخص را دوباره بالای مقاله نمایش نمی‌دهد.
۶. برای فایل تکی که امکان ضمیمه‌کردن عکس جداگانه ندارد، URL مستقیم و قابل‌دسترسی فایل تصویر را درج کن، نه لینک صفحهٔ جست‌وجو یا صفحهٔ معرفی عکس. اگر امکان دانلود فایل تصویر را داری، آن را برای ZIP آماده کن.
۷. آدرس تصاویر خارجی در صورت دریافت موفق، به فایل محلی کتابخانه رسانه وردپرس تبدیل می‌شود. اگر دریافت تصویر شکست بخورد، ممکن است آدرس اصلی باقی بماند؛ پس URL معتبر انتخاب کن.
۸. از عکس تکراری برای چند بخش استفاده نکن. هر عکس باید واقعاً با همان بخش مقاله مرتبط باشد؛ از URL صفحهٔ وب به‌جای URL مستقیم فایل تصویر استفاده نکن.

قواعد Markdown:
- # تا ###### برای تیترها
- - یا * یا + برای فهرست نامرتب
- 1. یا 2. برای فهرست مرتب
- **متن** برای بولد و *متن* برای ایتالیک
- [متن لینک](https://example.com) برای لینک
- ![توضیح دقیق تصویر](images/photo.webp) یا ![توضیح](https://example.com/image.webp) برای تصویر

نمونه:
# راهنمای انتخاب تشک مناسب

انتخاب تشک مناسب به وضعیت خواب، وزن و نیاز بدن بستگی دارد.

![تشک دونفره در اتاق خواب روشن](https://example.com/mattress.webp)

## به چه نکاتی توجه کنیم؟
تراکم، پشتیبانی بدن و ابعاد مناسب را بررسی کنید.

## جمع‌بندی
پیش از خرید، ابعاد تخت و نیازهای خواب خود را مشخص کنید.</pre>
				</div>
			</details>

			<details style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:12px 16px;">
				<summary style="cursor:pointer;font-weight:600;color:#0f172a;">📦 راهنمای فایل گروهی (ZIP)</summary>
				<div style="margin-top:10px;">
					<button type="button" class="button" onclick="senoobar_copy_guide('senoobar-guide-zip')">📋 کپی متن راهنما</button>
					<pre id="senoobar-guide-zip" style="direction:ltr;text-align:left;background:#0f172a;color:#e2e8f0;padding:14px;border-radius:8px;overflow:auto;font-size:.82rem;line-height:1.6;white-space:pre-wrap;">یک فایل ZIP آمادهٔ ایمپورت بساز که شامل چند مقالهٔ کامل و فایل‌های تصویری آن‌ها باشد. فرمت مقاله‌ها: .txt / .md / .markdown / .html / .htm. هر فایل متنی به یک نوشتهٔ جداگانه تبدیل می‌شود.

قواعد مهم:
۱. اولین خط غیرخالی هر فایل عنوان همان مقاله است.
۲. مقاله باید کامل، مفید، خوش‌ساختار و آمادهٔ انتشار باشد؛ از مقدمه، تیترهای مناسب، توضیحات کاربردی و جمع‌بندی استفاده کن.
۳. برای پیدا کردن تصاویر از منابع معتبر عکس‌های رایگان مانند Unsplash (unsplash.com) و Pexels (pexels.com) استفاده کن و شرایط مجوز هر عکس را رعایت کن. برای هر بخش عکس باکیفیت، واضح، بدون واترمارک و دقیقاً مرتبط با موضوع انتخاب کن؛ از عکس نامرتبط، تکراری یا تصاویر صرفاً تزئینی استفاده نکن. برای مقالات صنوبر، عکس واقعی و متناسب با مبلمان، تخت خواب، تشک و دکوراسیون انتخاب کن.
۴. تصاویر متفاوت را در محل دقیق و مرتبط داخل متن قرار بده؛ تصاویر را فقط ابتدای مقاله نچین.
۵. هیچ تصویر را از بدنه حذف نکن و تصویر اول را دوباره در جای دیگری تکرار نکن. اولین تصویر موفق به‌صورت خودکار تصویر شاخص هم می‌شود، اما در همان جای متن باقی می‌ماند. قالب سایت تصویر شاخص را جداگانه بالای مقاله نشان نمی‌دهد.
۶. عکس‌های منتخب از Unsplash یا Pexels را در صورت امکان واقعاً دانلود کن و فایل‌های تصویر را داخل ZIP قرار بده؛ سپس در متن با مسیر نسبی دقیق به آن‌ها ارجاع بده، مثل ![توضیح دقیق](images/photo.webp). مسیر باید با نام و حروف پوشه و فایل داخل ZIP یکسان باشد. فقط نوشتن نام سایت یا قراردادن لینک صفحهٔ عکس کافی نیست.
۷. عکس‌های دارای URL مستقیم HTTP/HTTPS هم پشتیبانی می‌شوند و اگر دریافت موفق باشد در کتابخانه رسانه وردپرس ذخیره می‌شوند؛ بااین‌حال، برای ZIP بهتر است فایل تصاویر را داخل ZIP قرار بده تا به در دسترس بودن سایت خارجی وابسته نباشی.
۸. برای بخش‌های مختلف از عکس‌های متفاوت و واقعاً مرتبط استفاده کن؛ یک عکس را بی‌دلیل تکرار نکن.
۹. فرمت‌های پیشنهادی تصویر: WEBP / PNG / JPG / JPEG / GIF. از SVG استفاده نکن.
۱۰. فقط فایل‌های مقاله و پوشهٔ تصاویر را در ZIP بگذار؛ هر مقاله یک فایل جداگانه داشته باشد.

ساختار پیشنهادی ZIP:
blog.zip
├── 01-roye-takht.md
├── 02-mobl-rahmati.md
└── images/
    ├── takht.webp
    ├── mattress.webp
    └── mobl.webp

نمونه محتوای 01-roye-takht.md:
# راهنمای انتخاب تخت خواب مناسب

انتخاب تخت باید با ابعاد اتاق، سبک دکوراسیون و نیازهای استفاده هماهنگ باشد.

![تخت خواب در اتاقی با چیدمان روشن](images/takht.webp "تخت خواب مناسب اتاق خواب")

## بررسی ابعاد و فضا
پیش از خرید، فضای رفت‌وآمد اطراف تخت را اندازه بگیرید.

## جمع‌بندی
مدل، ابعاد و کیفیت ساخت را هم‌زمان در نظر بگیرید.</pre>
				</div>
			</details>
		</div>

		<script>
		function senoobar_copy_guide( id ) {
			var el = document.getElementById( id );
			if ( ! el ) return;
			var range = document.createRange();
			range.selectNodeContents( el );
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange( range );
			try {
				document.execCommand( 'copy' );
				alert( '✔ متن راهنما کپی شد!' );
			} catch ( e ) {
				alert( 'کپی نشد؛ لطفاً متن را دستی انتخاب و کپی کنید.' );
			}
			sel.removeAllRanges();
		}
		</script>

		<?php if ( $notice ) : ?>
			<div class="notice <?php echo esc_attr( $notice['type'] ); ?> is-dismissible"><p><?php echo wp_kses_post( $notice['msg'] ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:16px;">
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📦 آپلود دسته‌ای (ZIP)</h2>
				<p style="color:#555;font-size:.9rem;">یک فایل ZIP شامل چند فایل متنی (txt / md / html) را بارگذاری کنید تا هر فایل به یک نوشته تبدیل شود.</p>
				<p style="color:#1e3a2f;font-size:.9rem;background:#f0f7f4;padding:10px;border-radius:8px;">
					🖼️ عکس‌های داخل ZIP و همه لینک‌های تصویری خارجی (از هر دامنه‌ای) به کتابخانه رسانه منتقل می‌شوند؛ اولین تصویر موفق، تصویر شاخص می‌شود و بقیه با آدرس محلی نمایش داده می‌شوند.
				</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_zip_import', 'senoobar_zip_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_zip_import">
					<p>
						<label><strong>فایل ZIP:</strong><br>
						<input type="file" name="zip_file" accept=".zip" required style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>دسته (اختیاری):</strong><br>
						<?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'zip_cat', 'taxonomy' => 'category' ) ); ?></label>
					</p>
					<p>
						<label><strong>وضعیت انتشار:</strong><br>
						<select name="zip_status" style="margin-top:6px;">
							<option value="publish">منتشر شده</option>
							<option value="draft">پیش‌نویس</option>
						</select></label>
					</p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته‌ها</button>
				</form>
			</div>

			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📄 افزودن تک‌نوشته</h2>
				<p style="color:#555;font-size:.9rem;">یک فایل متنی (txt / md / html) بارگذاری کنید. عنوان از اولین خط گرفته می‌شود مگر اینکه خودتان عنوان بنویسید.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_single_import', 'senoobar_single_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_single_import">
					<p>
						<label><strong>فایل نوشته:</strong><br>
						<input type="file" name="post_file" accept=".txt,.md,.html,.htm" required style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>عنوان (اختیاری — خالی بماند تا از فایل خوانده شود):</strong><br>
						<input type="text" name="post_title" class="regular-text" style="margin-top:6px;" dir="rtl"></label>
					</p>
					<p>
						<label><strong>دسته (اختیاری):</strong><br>
						<?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'single_cat', 'taxonomy' => 'category' ) ); ?></label>
					</p>
					<p>
						<label><strong>خلاصه (اختیاری):</strong><br>
						<textarea name="post_excerpt" rows="2" class="large-text" style="margin-top:6px;" dir="rtl"></textarea></label>
					</p>
					<p>
						<label><strong>تصویر شاخص (اختیاری):</strong><br>
						<input type="file" name="post_image" accept="image/*" style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>وضعیت انتشار:</strong><br>
						<select name="single_status" style="margin-top:6px;">
							<option value="publish">منتشر شده</option>
							<option value="draft">پیش‌نویس</option>
						</select></label>
					</p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته</button>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/* ════════════════════════════════════════════════════════════════
   پسوندهای مجاز
   ════════════════════════════════════════════════════════════════ */
function senoobar_bulk_allowed_ext() {
	return array( 'txt', 'md', 'markdown', 'html', 'htm' );
}

/* تبدیل فایل → HTML */
function senoobar_file_to_html( $content, $ext ) {
	$content = trim( (string) $content );

	if ( 'md' === $ext || 'markdown' === $ext ) {
		$content = senoobar_simple_markdown( $content );
	} elseif ( 'html' === $ext || 'htm' === $ext ) {
		if ( preg_match( '/<body[^>]*>(.*?)<\/body>/is', $content, $m ) ) {
			$content = $m[1];
		}
		return wp_kses_post( $content );
	} else {
		$content = wp_strip_all_tags( $content );
		$content = esc_html( $content );
		$content = preg_replace( "/\r?\n/", "\n", $content );
		$paras   = preg_split( "/\n\s*\n/", $content );
		$paras   = array_map( function ( $p ) {
			return '<p>' . nl2br( trim( $p ) ) . '</p>';
		}, array_filter( $paras, 'trim' ) );
		$content = implode( "\n", $paras );
	}

	return wp_kses_post( $content );
}

/* markdown ساده با پشتیبانی از عکس درون‌متن */
function senoobar_simple_markdown( $text ) {
	$text  = str_replace( "\r\n", "\n", $text );
	$lines = explode( "\n", $text );

	$html = array();
	$list = null;
	$list_buf = array();

	$flush_list = function () use ( &$list, &$list_buf, &$html ) {
		if ( $list !== null ) {
			$html[]    = '<' . $list . '>' . implode( '', $list_buf ) . '</' . $list . '>';
			$list      = null;
			$list_buf  = array();
		}
	};

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		if ( '' === $trim ) {
			$flush_list();
			$html[] = '';
			continue;
		}

		if ( preg_match( '/^(#{1,6})\s+(.*)$/', $trim, $m ) ) {
			$flush_list();
			$lvl     = min( 6, strlen( $m[1] ) );
			$html[]  = '<h' . $lvl . '>' . senoobar_md_inline( $m[2] ) . '</h' . $lvl . '>';
			continue;
		}

		if ( preg_match( '/^[-*+]\s+(.*)$/', $trim, $m ) ) {
			if ( $list !== 'ul' ) { $flush_list(); $list = 'ul'; }
			$list_buf[] = '<li>' . senoobar_md_inline( $m[1] ) . '</li>';
			continue;
		}

		if ( preg_match( '/^\d+[.)]\s+(.*)$/', $trim, $m ) ) {
			if ( $list !== 'ol' ) { $flush_list(); $list = 'ol'; }
			$list_buf[] = '<li>' . senoobar_md_inline( $m[1] ) . '</li>';
			continue;
		}

		$flush_list();
		$html[] = '<p>' . senoobar_md_inline( $trim ) . '</p>';
	}
	$flush_list();
	return implode( "\n", $html );
}

/* اینلاین markdown */
function senoobar_md_inline( $text ) {
	$text = esc_html( $text );

	$text = preg_replace_callback(
		'/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/',
		function ( $m ) {
			$alt        = $m[1];
			$src        = $m[2];
			$title      = isset( $m[3] ) ? $m[3] : '';
			$title_attr = $title ? ' title="' . esc_attr( $title ) . '"' : '';
			return '<img src="' . esc_attr( $src ) . '" alt="' . esc_attr( $alt ) . '"' . $title_attr . ' class="senoobar-bpi-img" data-src="' . esc_attr( $src ) . '" />';
		},
		$text
	);

	$text = preg_replace_callback(
		'/\[([^\]\[]+)\]\(([^)]+)\)/',
		function ( $m ) {
			return '<a href="' . esc_url( $m[2] ) . '" target="_blank" rel="noopener">' . esc_html( $m[1] ) . '</a>';
		},
		$text
	);

	$text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );
	$text = preg_replace( '/\*([^*]+)\*/s', '<em>$1</em>', $text );

	return $text;
}

/* جداسازی عنوان و بدنه */
function senoobar_split_title_body( $raw ) {
	$raw   = str_replace( "\r\n", "\n", (string) $raw );
	$raw   = str_replace( "\r", "\n", $raw );
	$lines = preg_split( '/\n/', $raw );
	$title = '';
	$body_start = 0;

	foreach ( $lines as $i => $line ) {
		$t = trim( $line );
		if ( '' !== $t ) {
			if ( preg_match( '/^#{1,6}\s+(.*)$/', $t, $m ) ) {
				$title = trim( $m[1] );
			} else {
				$title = $t;
			}
			$body_start = $i + 1;
			break;
		}
	}
	$body = implode( "\n", array_slice( $lines, $body_start ) );

	if ( function_exists( 'mb_strlen' ) && mb_strlen( $title ) > 200 ) {
		$title = mb_substr( $title, 0, 200 );
	}

	return array( $title, $body );
}

/* ════════════════════════════════════════════════════════════════
   ساخت نوشته از فایل
   ════════════════════════════════════════════════════════════════ */
function senoobar_create_post_from_file( $content, $ext, $args = array(), $images_map = array() ) {
	list( $auto_title, $body ) = senoobar_split_title_body( $content );

	$title = isset( $args['title'] ) && trim( $args['title'] ) !== '' ? trim( $args['title'] ) : $auto_title;
	if ( '' === $title ) {
		$title = 'نوشته بدون عنوان ' . gmdate( 'Y-m-d H:i:s' );
	}

	$html = senoobar_file_to_html( $body, $ext );

	$processed = senoobar_process_inline_images( $html, $images_map );
	$html      = $processed['html'];

	$postarr = array(
		'post_type'    => 'post',
		'post_status'  => isset( $args['status'] ) ? $args['status'] : 'publish',
		'post_title'   => wp_strip_all_tags( $title ),
		'post_content' => $html,
		'post_excerpt' => isset( $args['excerpt'] ) ? $args['excerpt'] : '',
	);

	$post_id = wp_insert_post( $postarr, true );

	if ( is_wp_error( $post_id ) ) {
		return array( false, $post_id->get_error_message() );
	}

	if ( ! empty( $args['category'] ) ) {
		wp_set_post_categories( $post_id, array( (int) $args['category'] ) );
	}

	if ( ! empty( $processed['first_image_id'] ) ) {
		set_post_thumbnail( $post_id, (int) $processed['first_image_id'] );
	} elseif ( ! empty( $args['image_id'] ) ) {
		set_post_thumbnail( $post_id, (int) $args['image_id'] );
	}

	return array( true, $post_id );
}

/* ════════════════════════════════════════════════════════════════
   پردازش عکس‌های درون متن
   ════════════════════════════════════════════════════════════════ */
function senoobar_process_inline_images( $html, $images_map = array() ) {
	$first_image_id = 0;
	$image_count    = 0;

	$html = preg_replace_callback(
		'/<img\\b[^>]*>/i',
		function ( $m ) use ( $images_map, &$first_image_id, &$image_count ) {
			$full_tag = $m[0];
			// Markdown-generated tags use data-src; raw HTML usually uses src.
			if ( preg_match( '/\\bdata-src="([^"]+)"/i', $full_tag, $src_match ) || preg_match( '/\\bsrc="([^"]+)"/i', $full_tag, $src_match ) ) {
				$src = html_entity_decode( $src_match[1], ENT_QUOTES, 'UTF-8' );
			} else {
				return $full_tag;
			}
			$is_absolute = (bool) preg_match( '/^https?:\\/\\//i', $src );
			$attach_id   = 0;

			if ( $is_absolute ) {
				// Download every external image, not only the featured image.
				$attach_id = senoobar_import_image_from_url( $src );
				if ( ! $attach_id ) {
					// If a remote host blocks the download, do not break the article.
					return $full_tag;
				}
			} else {
				$clean_src = ltrim( $src, './\\\\' );
				if ( isset( $images_map[ $clean_src ] ) ) {
					$attach_id = (int) $images_map[ $clean_src ];
				}
				if ( ! $attach_id ) {
					// Unresolvable relative path — omit the broken image.
					return '';
				}
			}

			$final_url = wp_get_attachment_url( $attach_id );
			if ( ! $final_url ) {
				return $full_tag;
			}

			$image_count++;

			// The first successfully imported/resolved image becomes featured,
			// but every image remains at its original position in the article body.
			if ( ! $first_image_id ) {
				$first_image_id = $attach_id;
			}

			// Keep the image inline and use its local Media Library URL.
			$clean_tag = preg_replace( '/\\s+class="senoobar-bpi-img"/i', '', $full_tag );
			$clean_tag = preg_replace( '/\\s+data-src="[^"]*"/i', '', $clean_tag );
			$clean_tag = preg_replace( '/\\s+src="[^"]*"/i', ' src="' . esc_url( $final_url ) . '"', $clean_tag );
			return $clean_tag;
		},
		$html
	);

	return array(
		'html'           => $html,
		'first_image_id' => $first_image_id,
		'image_count'    => $image_count,
	);
}

/* ════════════════════════════════════════════════════════════════
   آپلود تصویر به کتابخانه رسانه (با پشتیبانی WEBP)
   ════════════════════════════════════════════════════════════════ */
function senoobar_import_image( $file ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	senoobar_allow_webp();

	$id = media_handle_sideload( $file, 0 );
	return is_wp_error( $id ) ? false : (int) $id;
}

/**
 * دانلود عکس از URL خارجی و افزودن به کتابخانه رسانه.
 * برای دانلود همه تصاویر خارجی (http/https) و افزودن امن آن‌ها به کتابخانه رسانه استفاده می‌شود.
 *
 * @param string $url آدرس عکس.
 * @return int|false شناسه پیوست یا false در صورت خطا.
 */
function senoobar_import_image_from_url( $url ) {
	$url = esc_url_raw( trim( (string) $url ) );
	if ( '' === $url || ! wp_http_validate_url( $url ) ) {
		return false;
	}

	$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );
	if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	senoobar_allow_webp();

	// download_url() uses WordPress safe HTTP handling and follows WordPress
	// HTTP validation rules, reducing the risk of importing private-network URLs.
	$tmp = download_url( $url, 30 );
	if ( is_wp_error( $tmp ) ) {
		return false;
	}

	// Accept actual raster image data only; never trust the URL extension or
	// remote Content-Type. SVG is deliberately excluded because it can contain
	// active content and is not enabled by default in WordPress.
	$image_info = @getimagesize( $tmp );
	$mime_to_ext = array(
		'image/jpeg' => 'jpg',
		'image/png'  => 'png',
		'image/gif'  => 'gif',
		'image/webp' => 'webp',
		'image/avif' => 'avif',
	);

	if ( ! is_array( $image_info ) || empty( $image_info['mime'] ) || ! isset( $mime_to_ext[ $image_info['mime'] ] ) ) {
		@unlink( $tmp );
		return false;
	}

	$ext = $mime_to_ext[ $image_info['mime'] ];
	$file_array = array(
		'name'     => 'senoobar-import-' . wp_generate_password( 12, false, false ) . '.' . $ext,
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file_array, 0 );
	if ( is_wp_error( $id ) ) {
		@unlink( $tmp );
		return false;
	}

	return (int) $id;
}

function senoobar_allow_webp() {
	if ( has_filter( 'upload_mimes', 'senoobar_add_webp_mime' ) ) {
		return;
	}
	add_filter( 'upload_mimes', 'senoobar_add_webp_mime', 20 );
}

function senoobar_add_webp_mime( $mimes ) {
	if ( ! isset( $mimes['webp'] ) ) {
		$mimes['webp'] = 'image/webp';
	}
	return $mimes;
}

/* ════════════════════════════════════════════════════════════════
   ریدایرکت با پیام
   ════════════════════════════════════════════════════════════════ */
function senoobar_import_redirect( $type, $msg ) {
	set_transient( 'senoobar_bulk_import_notice', array( 'type' => $type, 'msg' => $msg ), 60 );
	wp_safe_redirect( admin_url( 'edit.php?page=senoobar-bulk-import' ) );
	exit;
}

/* ════════════════════════════════════════════════════════════════
   پردازش ZIP
   ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_senoobar_zip_import', 'senoobar_zip_import_handler' );

function senoobar_zip_import_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی ناکافی.' );
	}
	check_admin_referer( 'senoobar_zip_import', 'senoobar_zip_nonce' );

	if ( empty( $_FILES['zip_file'] ) || UPLOAD_ERR_OK !== $_FILES['zip_file']['error'] ) {
		senoobar_import_redirect( 'error', 'هیچ فایل ZIP انتخاب نشده است.' );
	}

	$tmp = $_FILES['zip_file']['tmp_name'];
	$zip = new ZipArchive();

	if ( true !== $zip->open( $tmp ) ) {
		senoobar_import_redirect( 'error', 'فایل ZIP قابل باز کردن نیست.' );
	}

	$status = isset( $_POST['zip_status'] ) ? sanitize_key( $_POST['zip_status'] ) : 'publish';
	$cat    = isset( $_POST['zip_cat'] ) && $_POST['zip_cat'] ? (int) $_POST['zip_cat'] : 0;

	$extract_dir = senoobar_temp_extract_dir();
	$zip->extractTo( $extract_dir );
	$zip->close();

	$images_map = senoobar_upload_zip_images( $extract_dir );

	$created = 0;
	$errors  = array();

	$text_files = senoobar_find_text_files( $extract_dir );

	foreach ( $text_files as $rel_path ) {
		$full    = trailingslashit( $extract_dir ) . $rel_path;
		$content = @file_get_contents( $full );
		if ( false === $content ) {
			continue;
		}

		$ext = strtolower( pathinfo( $rel_path, PATHINFO_EXTENSION ) );
		$r = senoobar_create_post_from_file(
			$content,
			$ext,
			array( 'status' => $status, 'category' => $cat ),
			$images_map
		);
		if ( $r[0] ) {
			$created++;
		} else {
			$errors[] = $rel_path . ': ' . $r[1];
		}
	}

	senoobar_cleanup_dir( $extract_dir );

	if ( $created > 0 ) {
		$msg  = sprintf( '✔ %d نوشته با موفقیت اضافه شد.', $created );
		$msg .= $errors ? ' (خطا در ' . count( $errors ) . ' فایل)' : '';
		$msg .= $images_map ? ' — 🖼️ ' . count( $images_map ) . ' عکس آپلود شد.' : '';
		senoobar_import_redirect( 'success', $msg );
	} else {
		senoobar_import_redirect( 'error', 'هیچ فایل متنی (txt/md/html) در ZIP پیدا نشد.' );
	}
}

/* ════════════════════════════════════════════════════════════════
   پردازش تک‌فایل
   ════════════════════════════════════════════════════════════════ */
add_action( 'admin_post_senoobar_single_import', 'senoobar_single_import_handler' );

function senoobar_single_import_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی ناکافی.' );
	}
	check_admin_referer( 'senoobar_single_import', 'senoobar_single_nonce' );

	if ( empty( $_FILES['post_file'] ) || UPLOAD_ERR_OK !== $_FILES['post_file']['error'] ) {
		senoobar_import_redirect( 'error', 'هیچ فایل نوشته‌ای انتخاب نشده است.' );
	}

	$name = isset( $_FILES['post_file']['name'] ) ? $_FILES['post_file']['name'] : '';
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, senoobar_bulk_allowed_ext(), true ) ) {
		senoobar_import_redirect( 'error', 'فرمت فایل پشتیبانی نمی‌شود (مجاز: txt / md / html).' );
	}

	$content = (string) file_get_contents( $_FILES['post_file']['tmp_name'] );

	$args = array(
		'title'    => isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '',
		'status'   => isset( $_POST['single_status'] ) ? sanitize_key( $_POST['single_status'] ) : 'publish',
		'excerpt'  => isset( $_POST['post_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['post_excerpt'] ) ) : '',
		'category' => isset( $_POST['single_cat'] ) && $_POST['single_cat'] ? (int) $_POST['single_cat'] : 0,
	);

	if ( ! empty( $_FILES['post_image'] ) && UPLOAD_ERR_OK === $_FILES['post_image']['error'] ) {
		$image_id = senoobar_import_image( $_FILES['post_image'] );
		if ( $image_id ) {
			$args['image_id'] = $image_id;
		}
	}

	$r = senoobar_create_post_from_file( $content, $ext, $args, array() );

	if ( $r[0] ) {
		$link = get_edit_post_link( $r[1], 'raw' );
		senoobar_import_redirect( 'success', '✔ نوشته ساخته شد. ' . ( $link ? '<a href="' . esc_url( $link ) . '">ویرایش نوشته</a>' : '' ) );
	} else {
		senoobar_import_redirect( 'error', 'خطا در ساخت نوشته: ' . $r[1] );
	}
}

/* ════════════════════════════════════════════════════════════════
   ابزارهای کمکی
   ════════════════════════════════════════════════════════════════ */
function senoobar_temp_extract_dir() {
	$dir = wp_upload_dir();
	$tmp = trailingslashit( $dir['basedir'] ) . 'senoobar-bpi-' . uniqid();
	wp_mkdir_p( $tmp );
	return $tmp;
}

function senoobar_cleanup_dir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $it as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $dir );
}

function senoobar_find_text_files( $dir ) {
	$allowed = array( 'txt', 'md', 'markdown', 'html', 'htm' );
	$files   = array();
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $file ) {
		if ( $file->isFile() ) {
			$ext = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );
			if ( in_array( $ext, $allowed, true ) ) {
				$files[] = ltrim( str_replace( $dir, '', $file->getPathname() ), '/\\' );
			}
		}
	}
	sort( $files );
	return $files;
}

/* آپلود همه عکس‌های داخل ZIP و برگرداندن نگاشت مسیر-نسبی => attachment_id */
function senoobar_upload_zip_images( $extract_dir ) {
	$image_ext = array( 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' );
	$map = array();

	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $extract_dir, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}
		$ext = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, $image_ext, true ) ) {
			continue;
		}

		$full = $file->getPathname();
		$rel  = ltrim( str_replace( $extract_dir, '', $full ), '/\\' );

		$file_array = array(
			'name'     => $file->getFilename(),
			'tmp_name' => $full,
			'type'     => senoobar_image_mime( $ext ),
			'error'    => 0,
			'size'     => $file->getSize(),
		);

		$attach_id = senoobar_import_image( $file_array );
		if ( $attach_id ) {
			$map[ $rel ] = $attach_id;
		}
	}

	return $map;
}

function senoobar_image_mime( $ext ) {
	$m = array(
		'png'  => 'image/png',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'gif'  => 'image/gif',
		'webp' => 'image/webp',
		'svg'  => 'image/svg+xml',
	);
	return isset( $m[ $ext ] ) ? $m[ $ext ] : 'image/jpeg';
}
