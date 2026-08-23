<?php
/**
 * Senoobar - Order Review Template
 * Styled order summary table matching cart/summary design
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

$cart = WC()->cart;
?>

<div class="senoobar-order-review" id="order_review">

    <table class="senoobar-review-table" cellspacing="0">
        <thead>
            <tr>
                <th class="product-name"><?php esc_html_e( 'محصول', 'woocommerce' ); ?></th>
                <th class="product-total"><?php esc_html_e( 'مجموع', 'woocommerce' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ( $cart->get_cart_contents_count() > 0 ) {
                foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
                    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

                    if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                        ?>
                        <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'woocommerce-checkout-review-item', $cart_item, $cart_item_key ) ); ?>">
                            <td class="product-name">
                                <?php
                                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( array( 48, 48 ) ), $cart_item, $cart_item_key );
                                $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                                $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
                                ?>
                                <div class="senoobar-review-product">
                                    <div class="senoobar-review-thumb">
                                        <?php if ( $product_permalink ) : ?>
                                            <a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; ?></a>
                                        <?php else : ?>
                                            <?php echo $thumbnail; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="senoobar-review-info">
                                        <?php if ( $product_permalink ) : ?>
                                            <a href="<?php echo esc_url( $product_permalink ); ?>" class="senoobar-review-name"><?php echo esc_html( $product_name ); ?></a>
                                        <?php else : ?>
                                            <span class="senoobar-review-name"><?php echo esc_html( $product_name ); ?></span>
                                        <?php endif; ?>
                                        <?php
                                        echo wc_get_formatted_cart_item_data( $cart_item );
                                        if ( $_product->get_sku() ) {
                                            echo '<span class="senoobar-review-sku">کد: ' . esc_html( $_product->get_sku() ) . '</span>';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <span class="senoobar-review-qty"><?php echo esc_html( $cart_item['quantity'] ); ?> عدد</span>
                            </td>
                            <td class="product-total">
                                <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
            }

            do_action( 'woocommerce_checkout_order_review_items' );
            ?>
        </tbody>
    </table>

    <div class="senoobar-order-totals">
        <?php
        // Subtotal
        ?>
        <div class="senoobar-summary-row">
            <span>جمع جزء</span>
            <strong><?php echo wc_price( $cart->get_subtotal() ); ?></strong>
        </div>

        <?php if ( $cart->get_cart_discount_total() > 0 ) : ?>
        <div class="senoobar-summary-row discount">
            <span>تخفیف</span>
            <strong>−<?php echo wc_price( $cart->get_cart_discount_total() ); ?></strong>
        </div>
        <?php endif; ?>

        <?php
        $applied_coupons = WC()->cart->get_applied_coupons();
        if ( wc_coupons_enabled() && ! empty( $applied_coupons ) ) :
            foreach ( $applied_coupons as $coupon_code ) :
                $coupon = new WC_Coupon( $coupon_code );
        ?>
        <div class="senoobar-summary-row discount">
            <span>کوپن (<?php echo esc_html( $coupon_code ); ?>)</span>
            <strong>−<?php wc_cart_totals_coupon_html( $coupon ); ?></strong>
        </div>
        <?php
            endforeach;
        endif;
        ?>

        <?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
        <div class="senoobar-summary-row">
            <span>ارسال</span>
            <strong><?php wc_cart_totals_shipping_html(); ?></strong>
        </div>
        <?php endif; ?>

        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
        <div class="senoobar-summary-row">
            <span><?php echo esc_html( $fee->name ); ?></span>
            <strong><?php wc_cart_totals_fee_html( $fee ); ?></strong>
        </div>
        <?php endforeach; ?>

        <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
            <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                <div class="senoobar-summary-row">
                    <span><?php echo esc_html( $tax->label ); ?></span>
                    <strong><?php echo wp_kses_post( $tax->formatted_amount ); ?></strong>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
            <div class="senoobar-summary-row">
                <span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span>
                <strong><?php wc_cart_totals_taxes_total_html(); ?></strong>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="senoobar-summary-total">
            <span>جمع کل</span>
            <strong><?php wc_cart_totals_order_total_html(); ?></strong>
        </div>
    </div>

    <div class="senoobar-payment-section">
        <?php
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( $available_gateways ) {
            wc_get_template( 'checkout/payment-method.php', array( 'available_gateways' => $available_gateways ) );
        } else {
            echo '<p class="woocommerce-notice woocommerce-info">' . esc_html__( 'متاسفانه هیچ روش پرداختی در دسترس نیست.', 'woocommerce' ) . '</p>';
        }
        ?>
    </div>

    <div id="place-order-wrap" class="senoobar-place-order-wrap">
        <?php do_action( 'woocommerce_review_order_before_submit' ); ?>
        <button type="submit" class="senoobar-place-order" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'ثبت سفارش', 'woocommerce' ); ?>" data-value="<?php esc_attr_e( 'ثبت سفارش', 'woocommerce' ); ?>">
            <?php esc_html_e( 'ثبت سفارش', 'woocommerce' ); ?>
        </button>
        <?php do_action( 'woocommerce_review_order_after_submit' ); ?>
        <?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
    </div>

</div>

<style>
/* Order Review Table */
.senoobar-review-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
.senoobar-review-table th {
    text-align: right;
    padding: 12px 16px;
    font-size: 13px;
    font-weight: 700;
    color: #777d79;
    background: #fafbfa;
    border-bottom: 1px solid #e9ecea;
}
.senoobar-review-table td { padding: 16px; border-bottom: 1px solid #f0f1f0; vertical-align: middle; }
.senoobar-review-table tr:last-child td { border-bottom: none; }
.senoobar-review-table td.product-name { width: auto; }
.senoobar-review-table td.product-total { width: 1%; white-space: nowrap; text-align: left; }

.senoobar-review-product { display: flex; align-items: flex-start; gap: 12px; }
.senoobar-review-thumb { width: 52px; height: 52px; flex: 0 0 52px; border-radius: 10px; overflow: hidden; background: #f4f5f4; }
.senoobar-review-thumb img { width: 100%; height: 100%; object-fit: cover; }
.senoobar-review-info { display: flex; flex-direction: column; gap: 4px; min-width: 0; flex: 1; }
.senoobar-review-name { font-weight: 600; font-size: 13px; color: #171a18; text-decoration: none; line-height: 1.7; word-break: break-word; overflow-wrap: anywhere; }
.senoobar-review-name:hover { color: #1e3a2f; }
.senoobar-review-info .variation { font-size: 11px; color: #8a908c; margin: 0; }
.senoobar-review-info .variation dt, .senoobar-review-info .variation dd { display: inline; }
.senoobar-review-sku { font-size: 10px; color: #9b9f9c; }
.senoobar-review-qty { display: inline-block; margin-top: 6px; padding: 2px 8px; background: #f0f7f4; color: #1e3a2f; font-size: 11px; font-weight: 600; border-radius: 6px; }

.senoobar-order-totals { border-top: 1px solid #e9ecea; padding-top: 16px; }
.senoobar-summary-row { display: flex; align-items: center; justify-content: space-between; gap: 15px; padding: 10px 0; border-bottom: 1px solid #f0f1f0; font-size: 13px; color: #666c68; }
.senoobar-summary-row strong { color: #333835; font-size: 13px; }
.senoobar-summary-row.discount strong { color: #2e805d; }
.senoobar-summary-total { display: flex; align-items: center; justify-content: space-between; gap: 15px; padding: 16px 0 18px; font-size: 14px; font-weight: 700; }
.senoobar-summary-total strong { color: #1e3a2f; font-size: 21px; font-weight: 800; }

/* Payment Section */
.senoobar-payment-section { margin-top: 24px; padding-top: 20px; border-top: 1px solid #e9ecea; }
.senoobar-payment-methods { display: flex; flex-direction: column; gap: 10px; }
.senoobar-payment-method { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border: 1px solid #e9ecea; border-radius: 12px; cursor: pointer; transition: all .15s ease; }
.senoobar-payment-method:hover { border-color: #1e3a2f; background: #f0f7f4; }
.senoobar-payment-method.selected { border-color: #1e3a2f; background: #f0f7f4; }
.senoobar-payment-method input { width: 20px; height: 20px; accent-color: #1e3a2f; }
.senoobar-payment-icon { width: 40px; height: 28px; display: flex; align-items: center; justify-content: center; background: #fafbfa; border-radius: 8px; font-size: 16px; }
.senoobar-payment-info { flex: 1; }
.senoobar-payment-name { font-weight: 600; font-size: 14px; color: #171a18; }
.senoobar-payment-desc { font-size: 11px; color: #777d79; margin-top: 2px; }

/* Place Order */
.senoobar-place-order-wrap { margin-top: 24px; }
.senoobar-place-order { width: 100%; min-height: 52px; border: none; border-radius: 13px; background: #1e3a2f; color: #fff; font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer; transition: background .2s ease, transform .2s ease; }
.senoobar-place-order:hover { background: #152a21; transform: translateY(-1px); }
.senoobar-place-order:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

@media (max-width: 760px) {
    .senoobar-review-table th { display: none; }
    .senoobar-review-table tr { display: block; border-bottom: 2px solid #e9ecea; padding: 16px 0; }
    .senoobar-review-table td { display: block; padding: 6px 0; border: none; text-align: right; }
    .senoobar-review-table .product-name { display: flex; flex-direction: column; gap: 8px; }
    .senoobar-review-product { width: 100%; }
    .senoobar-review-qty { align-self: flex-start; }
}
</style>