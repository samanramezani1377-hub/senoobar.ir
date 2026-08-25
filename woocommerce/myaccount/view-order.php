<?php
/**
 * Senoobar — Order detail view.
 * Args: order.
 */

defined( 'ABSPATH' ) || exit;

$order = $args['order'] ?? null;
if ( ! $order ) { return; }

$oid             = $order->get_id();
$status          = $order->get_status();
$status_label    = wc_get_order_status_name( $status );
$date            = $order->get_date_created();
$date_str        = $date ? wc_format_datetime( $date ) : '';
$total           = $order->get_total();
$subtotal        = $order->get_subtotal();
$shipping        = $order->get_shipping_total();
$shipping_method = $order->get_shipping_method();
$discount        = $order->get_discount_total();
$payment         = $order->get_payment_method_title();
$items           = $order->get_items();

$name     = $order->get_formatted_billing_full_name();
$phone    = $order->get_billing_phone();
$state    = $order->get_billing_state();
$postcode = $order->get_billing_postcode();
$address  = $order->get_billing_address_1();

$shipping_display = $shipping > 0
    ? number_format_i18n( $shipping ) . ' تومان'
    : ( $shipping_method ? $shipping_method : 'رایگان' );

$steps = [ 'ثبت سفارش', 'پردازش', 'ارسال', 'تحویل' ];
$step_index = 0;
if ( in_array( $status, [ 'processing', 'on-hold' ], true ) ) { $step_index = 1; }
elseif ( in_array( $status, [ 'shipped', 'completed' ], true ) ) { $step_index = 2; }
if ( $status === 'completed' ) { $step_index = 3; }
if ( in_array( $status, [ 'cancelled', 'failed', 'refunded' ], true ) ) { $step_index = -1; }
?>

<div class="snb-order-detail">
    <header class="snb-dash-header snb-detail-header">
        <div>
            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="snb-back">→ بازگشت به سفارش‌ها</a>
            <h2>سفارش #<?php echo esc_html( $oid ); ?></h2>
            <p>ثبت شده در <?php echo esc_html( $date_str ); ?></p>
        </div>
        <span class="snb-status snb-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status_label ); ?></span>
    </header>

    <?php if ( $step_index >= 0 ) : ?>
        <div class="snb-timeline">
            <?php foreach ( $steps as $i => $step ) : $done = $i <= $step_index; ?>
                <div class="snb-timeline-step<?php echo $done ? ' is-done' : ''; ?>">
                    <div class="snb-timeline-dot"><?php echo $done ? '✓' : ( $i + 1 ); ?></div>
                    <span><?php echo esc_html( $step ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="snb-order-detail-grid">
        <div class="snb-card">
            <h3>محصولات سفارش</h3>
            <div class="snb-order-items">
                <?php foreach ( $items as $item ) :
                    $product = $item->get_product();
                    $img = $product && $product->get_image_id() ? wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' ) : wc_placeholder_img_src();
                ?>
                    <div class="snb-order-item">
                        <img src="<?php echo esc_url( $img ); ?>" alt="" class="snb-item-img">
                        <div class="snb-item-info">
                            <a href="<?php echo $product ? esc_url( $product->get_permalink() ) : '#'; ?>" class="snb-item-name"><?php echo esc_html( $item->get_name() ); ?></a>
                            <span class="snb-item-qty">تعداد: <?php echo esc_html( $item->get_quantity() ); ?></span>
                        </div>
                        <span class="snb-item-total"><?php echo number_format_i18n( $item->get_total() ); ?> تومان</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="snb-card snb-order-side">
            <h3>اطلاعات ارسال</h3>
            <div class="snb-kv"><span>نام گیرنده</span><strong><?php echo esc_html( $name ); ?></strong></div>
            <div class="snb-kv"><span>تلفن</span><strong><?php echo esc_html( $phone ); ?></strong></div>
            <div class="snb-kv"><span>استان</span><strong><?php echo esc_html( $state ); ?></strong></div>
            <?php if ( $postcode ) : ?><div class="snb-kv"><span>کد پستی</span><strong><?php echo esc_html( $postcode ); ?></strong></div><?php endif; ?>
            <div class="snb-kv"><span>آدرس</span><strong><?php echo esc_html( $address ); ?></strong></div>
        </div>
    </div>

    <div class="snb-card snb-totals-card">
        <h3>خلاصه پرداخت</h3>
        <div class="snb-kv"><span>جمع محصولات</span><strong><?php echo number_format_i18n( $subtotal ); ?> تومان</strong></div>
        <div class="snb-kv"><span>هزینه ارسال</span><strong><?php echo esc_html( $shipping_display ); ?></strong></div>
        <?php if ( $discount > 0 ) : ?><div class="snb-kv"><span>تخفیف</span><strong>-<?php echo number_format_i18n( $discount ); ?> تومان</strong></div><?php endif; ?>
        <div class="snb-kv snb-grand"><span>مبلغ نهایی</span><strong><?php echo number_format_i18n( $total ); ?> تومان</strong></div>
        <?php if ( $payment ) : ?><div class="snb-kv"><span>روش پرداخت</span><strong><?php echo esc_html( $payment ); ?></strong></div><?php endif; ?>
    </div>

    <style>
        .snb-order-detail .snb-kv {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }
        .snb-order-detail .snb-kv > span {
            flex: 0 0 auto;
        }
        .snb-order-detail .snb-kv > span::after {
            content: ':';
            margin-right: 4px;
        }
        .snb-order-detail .snb-kv > strong {
            min-width: 0;
        }
    </style>
</div>
