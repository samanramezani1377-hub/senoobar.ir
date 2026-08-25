<?php
/**
 * Senoobar - Order Review Template
 * Styled order summary table matching cart/summary design.
 */
defined( 'ABSPATH' ) || exit;
$cart = WC()->cart;
?>
<div class="senoobar-order-review" id="order_review">
    <table class="senoobar-review-table" cellspacing="0">
        <thead><tr><th class="product-name">محصول</th><th class="product-total">مجموع</th></tr></thead>
        <tbody>
        <?php if ( $cart->get_cart_contents_count() > 0 ) : foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) : ?>
                <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'woocommerce-checkout-review-item', $cart_item, $cart_item_key ) ); ?>">
                    <td class="product-name">
                        <?php $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( array( 48, 48 ) ), $cart_item, $cart_item_key ); $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ); $product_permalink = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : ''; ?>
                        <div class="senoobar-review-product"><div class="senoobar-review-thumb"><?php if ( $product_permalink ) : ?><a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo $thumbnail; ?></a><?php else : ?><?php echo $thumbnail; ?><?php endif; ?></div><div class="senoobar-review-info"><?php if ( $product_permalink ) : ?><a href="<?php echo esc_url( $product_permalink ); ?>" class="senoobar-review-name"><?php echo esc_html( $product_name ); ?></a><?php else : ?><span class="senoobar-review-name"><?php echo esc_html( $product_name ); ?></span><?php endif; ?><?php echo wc_get_formatted_cart_item_data( $cart_item ); if ( $_product->get_sku() ) echo '<span class="senoobar-review-sku">کد: ' . esc_html( $_product->get_sku() ) . '</span>'; ?></div></div>
                        <span class="senoobar-review-qty"><?php echo esc_html( $cart_item['quantity'] ); ?> عدد</span>
                    </td><td class="product-total"><?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?></td>
                </tr>
            <?php endif; endforeach; endif; do_action( 'woocommerce_checkout_order_review_items' ); ?>
        </tbody>
    </table>

    <div class="senoobar-order-totals">
        <div class="senoobar-summary-row"><span>جمع جزء</span><strong><?php echo wc_price( $cart->get_subtotal() ); ?></strong></div>
        <?php if ( $cart->get_cart_discount_total() > 0 ) : ?><div class="senoobar-summary-row discount"><span>تخفیف</span><strong>−<?php echo wc_price( $cart->get_cart_discount_total() ); ?></strong></div><?php endif; ?>
        <?php
        $shipping_title = function_exists( 'senoobar_get_shipping_method_title' ) ? senoobar_get_shipping_method_title() : '';
        $shipping_html = '';
        ob_start();
        wc_cart_totals_shipping_html();
        $shipping_html = trim( ob_get_clean() );
        if ( $cart->needs_shipping() ) :
        ?>
            <div class="senoobar-summary-row senoobar-shipping-row">
                <span>حمل و نقل</span>
                <div class="senoobar-shipping-methods">
                    <?php if ( $shipping_html !== '' ) : ?>
                        <?php echo $shipping_html; ?>
                    <?php elseif ( $shipping_title !== '' ) : ?>
                        <span class="senoobar-shipping-fallback"><?php echo esc_html( $shipping_title ); ?></span>
                    <?php else : ?>
                        <span class="senoobar-shipping-fallback">پس‌کرایه</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php foreach ( WC()->cart->get_fees() as $fee ) : ?><div class="senoobar-summary-row"><span><?php echo esc_html( $fee->name ); ?></span><strong><?php wc_cart_totals_fee_html( $fee ); ?></strong></div><?php endforeach; ?>
        <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?><div class="senoobar-summary-row"><span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span><strong><?php wc_cart_totals_taxes_total_html(); ?></strong></div><?php endif; ?>
        <div class="senoobar-summary-total"><span>جمع کل</span><strong><?php wc_cart_totals_order_total_html(); ?></strong></div>
    </div>

    <div class="senoobar-payment-section">
        <?php $available_gateways = WC()->payment_gateways->get_available_payment_gateways(); if ( $available_gateways ) wc_get_template( 'checkout/payment-method.php', array( 'available_gateways' => $available_gateways ) ); else echo '<p class="woocommerce-notice woocommerce-info">متاسفانه هیچ روش پرداختی در دسترس نیست.</p>'; ?>
    </div>
    <div id="place-order-wrap" class="senoobar-place-order-wrap">
        <?php do_action( 'woocommerce_review_order_before_submit' ); ?><button type="submit" class="senoobar-place-order" name="woocommerce_checkout_place_order" id="place_order" value="ثبت سفارش" data-value="ثبت سفارش">ثبت سفارش</button><?php do_action( 'woocommerce_review_order_after_submit' ); ?><?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
    </div>
</div>
<style>
.senoobar-review-table{width:100%;border-collapse:collapse;margin-bottom:20px}.senoobar-review-table th{text-align:right;padding:12px 16px;font-size:13px;font-weight:700;color:#777d79;background:#fafbfa;border-bottom:1px solid #e9ecea}.senoobar-review-table td{padding:16px;border-bottom:1px solid #f0f1f0;vertical-align:middle}.senoobar-review-table tr:last-child td{border-bottom:none}.senoobar-review-table td.product-total{width:1%;white-space:nowrap;text-align:left}.senoobar-review-product{display:flex;align-items:flex-start;gap:12px}.senoobar-review-thumb{width:52px;height:52px;flex:0 0 52px;border-radius:10px;overflow:hidden;background:#f4f5f4}.senoobar-review-thumb img{width:100%;height:100%;object-fit:cover}.senoobar-review-info{display:flex;flex-direction:column;gap:4px;min-width:0;flex:1}.senoobar-review-name{font-weight:600;font-size:13px;color:#171a18;text-decoration:none;line-height:1.7;word-break:break-word;overflow-wrap:anywhere}.senoobar-review-info .variation{font-size:11px;color:#8a908c;margin:0}.senoobar-review-sku{font-size:10px;color:#9b9f9c}.senoobar-review-qty{display:inline-block;margin-top:6px;padding:2px 8px;background:#f0f7f4;color:#1e3a2f;font-size:11px;font-weight:600;border-radius:6px}.senoobar-order-totals{border-top:1px solid #e9ecea;padding-top:16px}.senoobar-summary-row{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:10px 0;border-bottom:1px solid #f0f1f0;font-size:13px;color:#666c68}.senoobar-summary-row strong{color:#333835;font-size:13px}.senoobar-summary-row.discount strong{color:#2e805d}.senoobar-summary-total{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:16px 0 18px;font-size:14px;font-weight:700}.senoobar-summary-total strong{color:#1e3a2f;font-size:21px;font-weight:800}.senoobar-shipping-row{align-items:flex-start}.senoobar-shipping-methods{flex:1;min-width:0}.senoobar-shipping-methods ul#shipping_method{margin:0;padding:0;list-style:none}.senoobar-shipping-methods ul#shipping_method li{margin:0 0 8px;padding:0;display:flex;align-items:flex-start;gap:8px}.senoobar-shipping-methods ul#shipping_method li:last-child{margin-bottom:0}.senoobar-shipping-methods ul#shipping_method li input[type=radio]{margin-top:4px;flex:0 0 auto}.senoobar-shipping-methods ul#shipping_method li label{display:block;cursor:pointer;line-height:1.7}.senoobar-shipping-fallback{display:inline-block;font-weight:600;color:#333835}.senoobar-payment-section{margin-top:24px;padding-top:20px;border-top:1px solid #e9ecea}.senoobar-place-order-wrap{margin-top:24px}.senoobar-place-order{width:100%;min-height:52px;border:none;border-radius:13px;background:#1e3a2f;color:#fff;font-family:inherit;font-size:15px;font-weight:800;cursor:pointer}.senoobar-place-order:disabled{opacity:.6;cursor:not-allowed}@media(max-width:760px){.senoobar-review-table th{display:none}.senoobar-review-table tr{display:block;border-bottom:2px solid #e9ecea;padding:16px 0}.senoobar-review-table td{display:block;padding:6px 0;border:none;text-align:right}.senoobar-review-table .product-name{display:flex;flex-direction:column;gap:8px}.senoobar-review-product{width:100%}.senoobar-review-qty{align-self:flex-start}.senoobar-summary-row.senoobar-shipping-row{display:block}.senoobar-shipping-methods{margin-top:8px;width:100%}}
</style>
