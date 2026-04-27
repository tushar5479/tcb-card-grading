<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sgc-step5-page">

    <div class="sgc-progress-bar">
        <div class="sgc-order-number">ORDER 1265402</div>

        <div class="sgc-progress-steps">
            <div class="sgc-step-item sgc-step-item-completed">Order Type</div>
            <div class="sgc-step-item sgc-step-item-completed">Add Cards</div>
            <div class="sgc-step-item sgc-step-item-completed">Service</div>
            <div class="sgc-step-item sgc-step-item-completed">Review</div>
            <div class="sgc-step-item sgc-step-item-completed">Shipping</div>
            <div class="sgc-step-item sgc-step-item-completed">Shipping Method</div>
            <div class="sgc-step-item">Payment</div>
            <div class="sgc-step-item">Confirm</div>


        </div>

           <div class="sgc-progress-home">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="sgc-progress-home-btn">Back to Home</a>
            </div>
    </div>

    <div class="sgc-step5-inner">
        <h2 class="sgc-main-title">STEP 5. SELECT RETURN SHIPPING METHOD</h2>

        <div class="sgc-step5-grid">
            <div class="sgc-step5-panel">
                <div class="sgc-step5-panel-title">SELECTED RETURN SHIPPING ADDRESS</div>
                <div id="sgc-step5-address-preview"></div>
            </div>

            <div class="sgc-step5-panel">
                <div class="sgc-step5-panel-title">ORDER SUMMARY</div>
                <div class="sgc-step5-summary-list">
                    <div class="sgc-step5-summary-row">
                        <span>Tier</span>
                        <strong id="sgc-step5-tier-label">-</strong>
                    </div>
                    <div class="sgc-step5-summary-row">
                        <span>Turnaround</span>
                        <strong id="sgc-step5-tier-days">-</strong>
                    </div>
                    <div class="sgc-step5-summary-row">
                        <span>Price Per Card</span>
                        <strong id="sgc-step5-tier-price">$0.00</strong>
                    </div>
                    <div class="sgc-step5-summary-row">
                        <span># Cards</span>
                        <strong id="sgc-step5-total-cards">0</strong>
                    </div>
                    <div class="sgc-step5-summary-row">
                        <span>Total DV</span>
                        <strong id="sgc-step5-total-dv">$0.00</strong>
                    </div>
                    <div class="sgc-step5-summary-row">
                        <span>Grading Fee</span>
                        <strong id="sgc-step5-grading-fee">$0.00</strong>
                    </div>
                    <div class="sgc-step5-summary-row">
                        <span>Shipping</span>
                        <strong id="sgc-step5-shipping-fee">$0.00</strong>
                    </div>
                    <div class="sgc-step5-summary-row sgc-step5-summary-total">
                        <span>Total</span>
                        <strong id="sgc-step5-grand-total">$0.00</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="sgc-step5-shipping-methods-wrap">
            <div class="sgc-step5-panel-title">SELECT YOUR SHIPPING METHOD</div>
            <div id="sgc-step5-shipping-method-list"></div>
        </div>
    </div>

    <div class="sgc-bottom-fixed-bar">
        <button type="button" class="sgc-back-btn" id="sgc-step5-back-btn">BACK</button>

        <div class="sgc-summary-center">
            <div class="sgc-summary-box">
                <div class="sgc-summary-label">SELECTED SHIPPING</div>
                <div class="sgc-summary-value" id="sgc-step5-bottom-shipping-text">USPS Priority Mail</div>
            </div>
        </div>

        <button type="button" class="sgc-next-btn" id="sgc-step5-next-btn">NEXT</button>
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