<?php
/**
 * Senoobar — Edit profile / change password.
 * Args: user_id.
 */

defined( 'ABSPATH' ) || exit;

$user_id    = $args['user_id'] ?? get_current_user_id();
$user       = get_userdata( $user_id );
$first_name = $user->first_name;
$last_name  = $user->last_name;
$email      = $user->user_email;
$mobile     = get_user_meta( $user_id, 'mobile', true ) ?: get_user_meta( $user_id, 'billing_phone', true );

$show_password = isset( $_GET['tab'] ) && $_GET['tab'] === 'password';
?>

<div class="snb-profile">
    <header class="snb-dash-header">
        <h2><?php echo $show_password ? 'تغییر رمز عبور' : 'ویرایش پروفایل'; ?></h2>
        <p><?php echo $show_password ? 'رمز عبور خود را به‌روزرسانی کنید.' : 'اطلاعات حساب خود را ویرایش کنید.'; ?></p>
    </header>

    <?php if ( ! $show_password ) : ?>
        <form method="post" action="" class="snb-form">
            <div class="snb-card">
                <div class="snb-form-grid">
                    <div class="snb-field">
                        <label>نام</label>
                        <input type="text" name="account_first_name" value="<?php echo esc_attr( $first_name ); ?>">
                    </div>
                    <div class="snb-field">
                        <label>نام خانوادگی</label>
                        <input type="text" name="account_last_name" value="<?php echo esc_attr( $last_name ); ?>">
                    </div>
                    <div class="snb-field snb-field-full">
                        <label>شماره موبایل</label>
                        <input type="tel" name="mobile" value="<?php echo esc_attr( $mobile ); ?>" dir="ltr" readonly class="snb-input-readonly">
                    </div>
                    <div class="snb-field snb-field-full">
                        <label>ایمیل</label>
                        <input type="email" name="account_email" value="<?php echo esc_attr( $email ); ?>" dir="ltr" style="text-align:right">
                    </div>
                </div>
            </div>
            <?php do_action( 'woocommerce_edit_account_form' ); ?>
            <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
            <button type="submit" name="save_account_details" value="1" class="snb-btn snb-btn-primary">ذخیره تغییرات</button>
        </form>
    <?php else : ?>
        <form method="post" action="" class="snb-form snb-password-form" autocomplete="off">
            <div class="snb-card">
                <div class="snb-form-grid">
                    <div class="snb-field snb-field-full">
                        <label for="snb-password-current">رمز عبور فعلی</label>
                        <input id="snb-password-current" type="password" name="password_current" dir="ltr" style="text-align:right" autocomplete="current-password" required>
                    </div>
                    <div class="snb-field snb-field-full">
                        <label for="snb-password-1">رمز عبور جدید</label>
                        <input id="snb-password-1" type="password" name="password_1" dir="ltr" style="text-align:right" autocomplete="new-password" minlength="6" required>
                    </div>
                    <div class="snb-field snb-field-full">
                        <label for="snb-password-2">تکرار رمز عبور جدید</label>
                        <input id="snb-password-2" type="password" name="password_2" dir="ltr" style="text-align:right" autocomplete="new-password" minlength="6" required>
                    </div>
                </div>
                <div class="snb-note">🔒 رمز عبور باید حداقل ۶ کاراکتر باشد.</div>
            </div>
            <?php wp_nonce_field( 'senoobar_change_password', 'senoobar_change_password_nonce' ); ?>
            <button type="submit" name="senoobar_change_password" value="1" class="snb-btn snb-btn-primary">تغییر رمز عبور</button>
        </form>
    <?php endif; ?>
</div>
