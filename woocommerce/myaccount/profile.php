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

$show_password   = isset( $_GET['tab'] ) && $_GET['tab'] === 'password';
$password_status = isset( $_GET['password_status'] ) ? sanitize_key( wp_unslash( $_GET['password_status'] ) ) : '';

$password_messages = [
    'success'           => [ 'success', 'رمز عبور شما با موفقیت تغییر کرد.' ],
    'current_required'  => [ 'error', 'لطفاً رمز عبور فعلی را وارد کنید.' ],
    'current_invalid'   => [ 'error', 'رمز عبور فعلی اشتباه است.' ],
    'new_required'      => [ 'error', 'لطفاً رمز عبور جدید را وارد کنید.' ],
    'too_short'         => [ 'error', 'رمز عبور جدید باید حداقل ۶ کاراکتر باشد.' ],
    'mismatch'          => [ 'error', 'تکرار رمز عبور جدید با رمز عبور جدید یکسان نیست.' ],
    'same_password'     => [ 'error', 'رمز عبور جدید باید با رمز عبور فعلی متفاوت باشد.' ],
    'invalid_request'   => [ 'error', 'درخواست تغییر رمز معتبر نیست. لطفاً دوباره تلاش کنید.' ],
    'account_not_found' => [ 'error', 'حساب کاربری پیدا نشد. لطفاً دوباره وارد شوید.' ],
];
?>

<div class="snb-profile">
    <header class="snb-dash-header">
        <h2><?php echo $show_password ? 'تغییر رمز عبور' : 'ویرایش پروفایل'; ?></h2>
        <p><?php echo $show_password ? 'رمز عبور خود را به‌روزرسانی کنید.' : 'اطلاعات حساب خود را ویرایش کنید.'; ?></p>
    </header>

    <?php if ( $show_password && isset( $password_messages[ $password_status ] ) ) : ?>
        <?php $notice = $password_messages[ $password_status ]; ?>
        <div class="snb-password-notice snb-password-notice-<?php echo esc_attr( $notice[0] ); ?>" role="alert" aria-live="polite">
            <?php echo esc_html( $notice[1] ); ?>
        </div>
    <?php endif; ?>

    <?php if ( ! $show_password ) : ?>
        <form method="post" action="" class="snb-form">
            <div class="snb-card">
                <div class="snb-form-grid">
                    <div class="snb-field"><label>نام</label><input type="text" name="account_first_name" value="<?php echo esc_attr( $first_name ); ?>"></div>
                    <div class="snb-field"><label>نام خانوادگی</label><input type="text" name="account_last_name" value="<?php echo esc_attr( $last_name ); ?>"></div>
                    <div class="snb-field snb-field-full"><label>شماره موبایل</label><input type="tel" name="mobile" value="<?php echo esc_attr( $mobile ); ?>" dir="ltr" readonly class="snb-input-readonly"></div>
                    <div class="snb-field snb-field-full"><label>ایمیل</label><input type="email" name="account_email" value="<?php echo esc_attr( $email ); ?>" dir="ltr" style="text-align:right"></div>
                </div>
            </div>
            <?php do_action( 'woocommerce_edit_account_form' ); ?>
            <?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
            <button type="submit" name="save_account_details" value="1" class="snb-btn snb-btn-primary">ذخیره تغییرات</button>
        </form>
    <?php else : ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="snb-form snb-password-form" autocomplete="off">
            <input type="hidden" name="action" value="senoobar_change_password">
            <?php wp_nonce_field( 'senoobar_change_password', 'senoobar_change_password_nonce' ); ?>

            <div class="snb-card">
                <div class="snb-form-grid">
                    <?php
                    $password_fields = [
                        [ 'snb-password-current', 'password_current', 'رمز عبور فعلی', 'current-password' ],
                        [ 'snb-password-1', 'password_1', 'رمز عبور جدید', 'new-password' ],
                        [ 'snb-password-2', 'password_2', 'تکرار رمز عبور جدید', 'new-password' ],
                    ];
                    foreach ( $password_fields as $field ) :
                        [ $field_id, $field_name, $label, $autocomplete ] = $field;
                        ?>
                        <div class="snb-field snb-field-full snb-password-field">
                            <label for="<?php echo esc_attr( $field_id ); ?>"><?php echo esc_html( $label ); ?></label>
                            <div class="snb-password-input-wrap">
                                <input id="<?php echo esc_attr( $field_id ); ?>" type="password" name="<?php echo esc_attr( $field_name ); ?>" dir="ltr" style="text-align:right" autocomplete="<?php echo esc_attr( $autocomplete ); ?>" minlength="6" required>
                                <button type="button" class="snb-password-toggle" data-password-target="<?php echo esc_attr( $field_id ); ?>" aria-label="نمایش <?php echo esc_attr( $label ); ?>" aria-pressed="false">نمایش</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="snb-note">🔒 رمز عبور باید حداقل ۶ کاراکتر باشد.</div>
            </div>

            <button type="submit" class="snb-btn snb-btn-primary">تغییر رمز عبور</button>
        </form>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.snb-password-toggle').forEach(function (button) {
                button.addEventListener('click', function () {
                    var input = document.getElementById(button.getAttribute('data-password-target'));
                    if (!input) return;
                    var visible = input.type === 'text';
                    input.type = visible ? 'password' : 'text';
                    button.textContent = visible ? 'نمایش' : 'مخفی کردن';
                    button.setAttribute('aria-pressed', visible ? 'false' : 'true');
                    button.setAttribute('aria-label', visible ? 'نمایش رمز عبور' : 'مخفی کردن رمز عبور');
                });
            });
        });
        </script>

        <style>
        .snb-password-input-wrap { position: relative; width: 100%; }
        .snb-password-input-wrap input { width: 100%; padding-left: 78px; box-sizing: border-box; }
        .snb-password-toggle {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: calc(100% - 2px);
            min-width: 68px;
            padding: 0 10px;
            border: 0;
            background: transparent;
            color: inherit;
            cursor: pointer;
            font: inherit;
            z-index: 2;
        }
        .snb-password-toggle:hover { opacity: .7; }

        .snb-password-notice {
            width: 100%;
            box-sizing: border-box;
            margin: 0 0 18px;
            padding: 12px 16px;
            border-radius: 10px;
            border: 1px solid transparent;
            font-size: 14px;
            line-height: 1.8;
            font-weight: 500;
        }
        .snb-password-notice-error {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #be123c;
        }
        .snb-password-notice-success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }
        </style>
    <?php endif; ?>
</div>
