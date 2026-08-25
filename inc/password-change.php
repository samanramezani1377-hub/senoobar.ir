<?php
/**
 * Senoobar — Secure account password change handler.
 * Uses admin-post.php so the custom account template/router cannot swallow
 * the password form submission.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

add_action( 'admin_post_senoobar_change_password', 'senoobar_handle_password_change' );

function senoobar_password_change_redirect( $status ) {
    $url = wc_get_account_endpoint_url( 'edit-account' );
    $url = add_query_arg( [ 'tab' => 'password', 'password_status' => $status ], $url );
    wp_safe_redirect( $url );
    exit;
}

function senoobar_handle_password_change() {
    if ( ! is_user_logged_in() ) {
        senoobar_password_change_redirect( 'login' );
    }

    $nonce = isset( $_POST['senoobar_change_password_nonce'] )
        ? sanitize_text_field( wp_unslash( $_POST['senoobar_change_password_nonce'] ) )
        : '';

    if ( ! wp_verify_nonce( $nonce, 'senoobar_change_password' ) ) {
        senoobar_password_change_redirect( 'invalid_request' );
    }

    $user_id = get_current_user_id();
    $user = get_userdata( $user_id );

    if ( ! $user ) {
        senoobar_password_change_redirect( 'account_not_found' );
    }

    $current_password = isset( $_POST['password_current'] ) ? (string) wp_unslash( $_POST['password_current'] ) : '';
    $new_password = isset( $_POST['password_1'] ) ? (string) wp_unslash( $_POST['password_1'] ) : '';
    $confirm_password = isset( $_POST['password_2'] ) ? (string) wp_unslash( $_POST['password_2'] ) : '';

    if ( $current_password === '' ) {
        senoobar_password_change_redirect( 'current_required' );
    }

    if ( ! wp_check_password( $current_password, $user->user_pass, $user_id ) ) {
        senoobar_password_change_redirect( 'current_invalid' );
    }

    if ( $new_password === '' ) {
        senoobar_password_change_redirect( 'new_required' );
    }

    $password_length = function_exists( 'mb_strlen' ) ? mb_strlen( $new_password, 'UTF-8' ) : strlen( $new_password );
    if ( $password_length < 6 ) {
        senoobar_password_change_redirect( 'too_short' );
    }

    if ( $new_password !== $confirm_password ) {
        senoobar_password_change_redirect( 'mismatch' );
    }

    if ( wp_check_password( $new_password, $user->user_pass, $user_id ) ) {
        senoobar_password_change_redirect( 'same_password' );
    }

    wp_set_password( $new_password, $user_id );
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, true, is_ssl() );

    senoobar_password_change_redirect( 'success' );
}
