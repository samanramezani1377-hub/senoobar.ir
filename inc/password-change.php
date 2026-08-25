<?php
/**
 * Senoobar — Secure account password change handler.
 *
 * Handles the custom password form used by the My Account page.
 * The handler deliberately uses a separate POST action from the profile
 * details form so WooCommerce's default account-details handler cannot
 * accidentally swallow the password fields.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

add_action( 'template_redirect', function () {
    if ( ! isset( $_POST['senoobar_change_password'] ) || ! is_user_logged_in() ) {
        return;
    }

    $nonce = isset( $_POST['senoobar_change_password_nonce'] )
        ? sanitize_text_field( wp_unslash( $_POST['senoobar_change_password_nonce'] ) )
        : '';

    if ( ! wp_verify_nonce( $nonce, 'senoobar_change_password' ) ) {
        wc_add_notice( 'درخواست تغییر رمز معتبر نیست. لطفاً دوباره تلاش کنید.', 'error' );
        return;
    }

    $user_id = get_current_user_id();
    $user    = get_userdata( $user_id );

    if ( ! $user ) {
        wc_add_notice( 'حساب کاربری پیدا نشد. لطفاً دوباره وارد حساب شوید.', 'error' );
        return;
    }

    $current_password = isset( $_POST['password_current'] )
        ? (string) wp_unslash( $_POST['password_current'] )
        : '';
    $new_password = isset( $_POST['password_1'] )
        ? (string) wp_unslash( $_POST['password_1'] )
        : '';
    $confirm_password = isset( $_POST['password_2'] )
        ? (string) wp_unslash( $_POST['password_2'] )
        : '';

    if ( $current_password === '' ) {
        wc_add_notice( 'لطفاً رمز عبور فعلی را وارد کنید.', 'error' );
        return;
    }

    if ( ! wp_check_password( $current_password, $user->user_pass, $user_id ) ) {
        wc_add_notice( 'رمز عبور فعلی اشتباه است.', 'error' );
        return;
    }

    if ( $new_password === '' ) {
        wc_add_notice( 'لطفاً رمز عبور جدید را وارد کنید.', 'error' );
        return;
    }

    $password_length = function_exists( 'mb_strlen' )
        ? mb_strlen( $new_password, 'UTF-8' )
        : strlen( $new_password );

    if ( $password_length < 6 ) {
        wc_add_notice( 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.', 'error' );
        return;
    }

    if ( $new_password !== $confirm_password ) {
        wc_add_notice( 'تکرار رمز عبور جدید با رمز عبور جدید یکسان نیست.', 'error' );
        return;
    }

    if ( hash_equals( $user->user_pass, $new_password ) ) {
        // This can only be true in unusual legacy setups where the stored value
        // is not a normal WordPress hash. Keep the real comparison below too.
    }

    if ( wp_check_password( $new_password, $user->user_pass, $user_id ) ) {
        wc_add_notice( 'رمز عبور جدید باید با رمز عبور فعلی متفاوت باشد.', 'error' );
        return;
    }

    // wp_set_password() hashes the password using WordPress's password API.
    // Do not hash it manually here.
    wp_set_password( $new_password, $user_id );

    // Keep the customer logged in after changing the password.
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, true, is_ssl() );

    wc_add_notice( 'رمز عبور شما با موفقیت تغییر کرد.', 'success' );

    $account_url = wc_get_account_endpoint_url( 'edit-account' );
    wp_safe_redirect( add_query_arg( 'tab', 'password', $account_url ) );
    exit;
} );
