<?php
/**
 * Newsletter Subscription Handler — Senoobar
 */

// Create subscribers table on activation.
add_action('after_switch_theme', 'senoobar_create_subscribers_table');
function senoobar_create_subscribers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'senoobar_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        email varchar(191) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        source varchar(50) DEFAULT 'frontend',
        ip varchar(45) DEFAULT '',
        user_agent text DEFAULT '',
        subscribed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        confirmed_at datetime DEFAULT NULL,
        unsubscribed_at datetime DEFAULT NULL,
        confirmation_token varchar(64) DEFAULT '',
        PRIMARY KEY (id),
        UNIQUE KEY email (email),
        KEY status (status)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Mark the schema as initialized so normal frontend requests do not need
    // to query SHOW TABLES on every page load.
    update_option('senoobar_subscribers_table_version', '1', false);
}

// One-time safety check for sites upgraded from an older theme version.
// The previous implementation ran SHOW TABLES on every request, adding an
// unnecessary database query to the critical path.
add_action('init', function() {
    if (get_option('senoobar_subscribers_table_version') !== '1') {
        senoobar_create_subscribers_table();
    }
}, 1);

// AJAX Subscribe
add_action('wp_ajax_senoobar_newsletter_subscribe', 'senoobar_newsletter_subscribe');
add_action('wp_ajax_nopriv_senoobar_newsletter_subscribe', 'senoobar_newsletter_subscribe');

function senoobar_newsletter_subscribe(): void {
    check_ajax_referer('senoobar_newsletter_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');

    if (empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => 'آدرس ایمیل معتبر نیست.']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'senoobar_subscribers';

    // Check for existing subscription
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, status FROM $table_name WHERE email = %s",
        $email
    ));

    if ($existing) {
        if ($existing->status === 'active') {
            wp_send_json_error(['message' => 'این ایمیل قبلاً در خبرنامه ثبت شده است.']);
        } elseif ($existing->status === 'unsubscribed') {
            // Reactivate
            $wpdb->update($table_name, [
                'status' => 'active',
                'unsubscribed_at' => null,
                'subscribed_at' => current_time('mysql'),
            ], ['id' => $existing->id]);
            wp_send_json_success(['message' => 'اشتراک شما مجدداً فعال شد.']);
        } else {
            wp_send_json_error(['message' => 'این ایمیل در حال پردازش است.']);
        }
        return;
    }

    // Generate confirmation token
    $token = bin2hex(random_bytes(32));

    // Insert new subscriber
    $result = $wpdb->insert($table_name, [
        'email' => $email,
        'status' => 'active',
        'source' => 'frontend',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'confirmation_token' => $token,
    ]);

    if ($result === false) {
        wp_send_json_error(['message' => 'خطا در ثبت‌نام. لطفاً مجدداً تلاش کنید.']);
    }

    // TODO: Send confirmation email if double opt-in is required
    // For now, mark as confirmed immediately
    $wpdb->update($table_name, [
        'confirmed_at' => current_time('mysql'),
    ], ['id' => $wpdb->insert_id]);

    wp_send_json_success(['message' => 'با موفقیت در خبرنامه ثبت‌نام کردید.']);
}

// AJAX Unsubscribe (optional - for footer unsubscribe link)
add_action('wp_ajax_senoobar_newsletter_unsubscribe', 'senoobar_newsletter_unsubscribe');
add_action('wp_ajax_nopriv_senoobar_newsletter_unsubscribe', 'senoobar_newsletter_unsubscribe');

function senoobar_newsletter_unsubscribe(): void {
    check_ajax_referer('senoobar_newsletter_nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $token = sanitize_text_field($_POST['token'] ?? '');

    if (empty($email) || !is_email($email)) {
        wp_send_json_error(['message' => 'آدرس ایمیل معتبر نیست.']);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'senoobar_subscribers';

    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table_name WHERE email = %s",
        $email
    ));

    if (!$existing) {
        wp_send_json_error(['message' => 'این ایمیل در سیستم یافت نشد.']);
    }

    // Verify token if provided
    if (!empty($token)) {
        $stored_token = $wpdb->get_var($wpdb->prepare(
            "SELECT confirmation_token FROM $table_name WHERE id = %d",
            $existing->id
        ));
        if (!hash_equals($stored_token, $token)) {
            wp_send_json_error(['message' => 'توکن انصراف نامعتبر است.']);
        }
    }

    $wpdb->update($table_name, [
        'status' => 'unsubscribed',
        'unsubscribed_at' => current_time('mysql'),
    ], ['id' => $existing->id]);

    wp_send_json_success(['message' => 'شما از خبرنامه لغو گردید.']);
}