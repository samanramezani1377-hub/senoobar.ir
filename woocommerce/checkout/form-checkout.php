<?php
/**
 * Senoobar - WooCommerce Checkout Template (minimal)
 * Only asks: name, last name, phone, province, postal code, address.
 * Renders its own header/footer so it works even with an empty page content.
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $checkout ) || ! is_object( $checkout ) ) {
    $checkout = WC()->checkout();
}

// Order received / thank-you view.
if ( is_wc_endpoint_url( 'order-received' ) ) {
    $order_id = absint( get_query_var( 'order-received' ) );
    get_header();
    echo '<main id="primary" class="site-main"><div class="container page-content">';
    wc_get_template( 'checkout/thankyou.php', array( 'order' => wc_get_order( $order_id ) ) );
    echo '</div></main>';
    get_footer();
    return;
}

get_header(); ?>

<main id="primary" class="site-main">
<div class="container page-content">

<?php
do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! is_user_logged_in() && $checkout->is_registration_required() ) {
    echo '<div class="senoobar-auth-section">';
    wc_get_template_part( 'checkout/login' );
    echo '</div>';
}
?>

<div class="senoobar-checkout-page" dir="rtl">
    <div class="senoobar-checkout-container">

        <div class="senoobar-checkout-breadcrumb">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a>
            <span>/</span>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>">سبد خرید</a>
            <span>/</span>
            <span>تسویه حساب</span>
        </div>

        <div class="senoobar-checkout-heading">
            <div>
                <h1>تسویه حساب</h1>
                <p>اطلاعات ارسال را تکمیل کنید تا سفارش نهایی شود</p>
            </div>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>" class="senoobar-btn senoobar-btn-outline">
                <span>←</span> بازگشت به سبد
            </a>
        </div>

        <?php wc_print_notices(); ?>

        <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" novalidate>

            <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

            <div class="senoobar-checkout-layout">

                <section class="senoobar-form-card" aria-labelledby="billing-heading">
                    <h2 id="billing-heading" class="senoobar-section-title">اطلاعات سفارش</h2>
                    <?php wc_get_template( 'checkout/form-billing.php' ); ?>
                </section>

                <aside>
                    <div class="senoobar-order-summary-card" aria-labelledby="order-summary-heading">
                        <h2 id="order-summary-heading">خلاصه سفارش</h2>

                        <div class="senoobar-checkout-section">
                            <?php wc_get_template( 'checkout/review-order.php' ); ?>
                        </div>

                        <!-- Payment gateways must be rendered inside the same checkout form. -->
                        <div class="senoobar-checkout-section senoobar-payment-section">
                            <?php wc_get_template( 'checkout/payment.php', array( 'checkout' => $checkout ) ); ?>
                        </div>
                    </div>

                    <div class="senoobar-trust-badges">
                        <h4>امنیت و اطمینان</h4>
                        <div class="senoobar-trust-list">
                            <span class="senoobar-trust-item">🔒 پرداخت امن</span>
                            <span class="senoobar-trust-item">🛡️ ضمانت کیفیت</span>
                            <span class="senoobar-trust-item">🚚 ارسال سریع</span>
                            <span class="senoobar-trust-item">🔄 مرجوعی آسان</span>
                        </div>
                    </div>
                </aside>

            </div>

            

        </form>
    </div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

</div>
</main>

<?php get_footer();
