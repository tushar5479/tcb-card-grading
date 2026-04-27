<?php
if (!defined('ABSPATH')) {
    exit;
}

$draft = class_exists('SGC_Order_Draft') ? SGC_Order_Draft::get_data() : [];
$order_number = !empty($draft['order_number']) ? $draft['order_number'] : 'PENDING';
?>

<div class="sgc-step7-page">
    <div class="sgc-progress-bar">
        <div class="sgc-order-number"><?php echo esc_html($order_number); ?></div>

        <div class="sgc-progress-steps">
            <div class="sgc-step-item sgc-step-item-completed">Order Type</div>
            <div class="sgc-step-item sgc-step-item-completed">Add Cards</div>
            <div class="sgc-step-item sgc-step-item-completed">Service</div>
            <div class="sgc-step-item sgc-step-item-completed">Review</div>
            <div class="sgc-step-item sgc-step-item-completed">Shipping</div>
            <div class="sgc-step-item sgc-step-item-completed">Shipping Method</div>
            <div class="sgc-step-item sgc-step-item-active">Payment</div>
            <div class="sgc-step-item">Confirm</div>
        </div>
           <div class="sgc-progress-home">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="sgc-progress-home-btn">Back to Home</a>
       </div>
    </div>

    <div class="sgc-step7-container">
        <div class="sgc-step7-left">
            <div class="sgc-step7-card">
                <div class="sgc-step7-header">
                    <h2 class="sgc-main-title">Step 7. Payment</h2>
                    <p class="sgc-step7-subtitle">
                        Choose express checkout or pay securely with your card.
                    </p>
                </div>

                <div class="sgc-payment-section">
                    <h3 class="sgc-payment-section-title">Express Checkout</h3>
                    <div class="sgc-express-box">
                        <div id="sgc-express-checkout-element"></div>
                    </div>
                </div>

                <div class="sgc-or-divider">
                    <span>OR PAY WITH CARD</span>
                </div>

                <div class="sgc-payment-section">
                    <h3 class="sgc-payment-section-title">Card Payment</h3>

                    <form id="sgc-card-payment-form" class="sgc-card-form">
                        <div class="sgc-card-element-wrap">
                            <div id="sgc-card-element"></div>
                        </div>

                        <div id="sgc-card-errors" class="sgc-payment-error"></div>

                        <button type="submit" id="sgc-pay-now-btn" class="sgc-pay-btn" style="display:none;">
                            Pay Now
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="sgc-step7-right">
            <div class="sgc-summary-card">
                <div class="sgc-summary-card-header">
                    <h3>Payment Summary</h3>
                    <div class="sgc-summary-order">Order: <?php echo esc_html($order_number); ?></div>
                </div>

                <div class="sgc-summary-total-wrap">
                    <div class="sgc-summary-label">Total Due</div>
                    <div class="sgc-summary-total" id="sgc-step7-total">$0.00</div>
                </div>

                <div class="sgc-summary-note">
                    Your payment is processed securely by Stripe. We do not store your full card information.
                </div>

                <button type="button" class="sgc-summary-btn" id="sgc-step7-disabled-btn" disabled>
                    Payment Required
                </button>
            </div>
        </div>
    </div>

    <div class="sgc-bottom-fixed-bar">
        <button type="button" class="sgc-back-btn" id="sgc-step7-back-btn">Back</button>

        <div class="sgc-summary-center">
            <div class="sgc-summary-box">
                <div class="sgc-summary-label">TOTAL</div>
                <div class="sgc-summary-value" id="sgc-step7-total-mobile">$0.00</div>
            </div>
        </div>

        <button type="button" class="sgc-next-btn" id="sgc-step7-disabled-btn-mobile" disabled>
            Payment Required
        </button>
    </div>
</div>

<style>

    .sgc-progress-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    background: #000;
    padding: 12px 18px;
}

.sgc-order-number {
    color: #fff;
    font-weight: 800;
    font-size: 18px;
    white-space: nowrap;
}

.sgc-progress-steps {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1;
    flex-wrap: wrap;
}

.sgc-progress-home {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

.sgc-progress-home-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 40px;
    padding: 10px 18px;
    border: 1px solid #E5A93D;
    color: #E5A93D;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    background: transparent;
    transition: all 0.25s ease;
}

.sgc-progress-home-btn:hover {
    background: #E5A93D;
    color: #000;
}

@media (max-width: 1024px) {
    .sgc-progress-bar {
        align-items: flex-start;
    }

    .sgc-progress-steps {
        width: 100%;
        order: 3;
    }

    .sgc-progress-home {
        margin-left: auto;
    }
}

@media (max-width: 767px) {
    .sgc-progress-bar {
        padding: 10px 12px;
        gap: 12px;
    }

    .sgc-order-number {
        font-size: 16px;
    }

    .sgc-progress-home {
        width: 100%;
        justify-content: flex-start;
    }

    .sgc-progress-home-btn {
        min-height: 36px;
        padding: 8px 14px;
        font-size: 12px;
    }

    .sgc-progress-steps {
        gap: 10px;
    }
}
</style>