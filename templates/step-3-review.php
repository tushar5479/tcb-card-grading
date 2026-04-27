<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sgc-step3-page">

    <div class="sgc-progress-bar">
        <div class="sgc-order-number">ORDER 1263480</div>

        <div class="sgc-progress-steps">
            <div class="sgc-step-item sgc-step-item-completed">Add Cards</div>
            <div class="sgc-step-item sgc-step-item-completed">Service</div>
            <div class="sgc-step-item sgc-step-item-completed">Review</div>
            <div class="sgc-step-item">Shipping</div>
            <div class="sgc-step-item">Shipping Method</div>
            <div class="sgc-step-item">Payment</div>
            <div class="sgc-step-item">Confirm</div>
        </div>
           <div class="sgc-progress-home">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="sgc-progress-home-btn">Back to Home</a>
       </div>
    </div>

    <div class="sgc-step3-inner">
        <h2 class="sgc-main-title">STEP 3. REVIEW YOUR ORDER</h2>

        <div class="sgc-step3-top-summary">
            <div class="sgc-step3-summary-card">
                <div class="sgc-step3-summary-label">SELECTED TIER</div>
                <div class="sgc-step3-summary-value" id="sgc-step3-tier-name">Pristine 10</div>
            </div>

            <div class="sgc-step3-summary-card">
                <div class="sgc-step3-summary-label">PRICE PER CARD</div>
                <div class="sgc-step3-summary-value" id="sgc-step3-tier-price">$25.00</div>
            </div>

            <div class="sgc-step3-summary-card">
                <div class="sgc-step3-summary-label">TURNAROUND</div>
                <div class="sgc-step3-summary-value" id="sgc-step3-tier-days">7–10 business days</div>
            </div>
        </div>

        <div class="sgc-step3-cards-wrap">
            <div id="sgc-step3-review-list"></div>
        </div>
    </div>

    <div class="sgc-bottom-fixed-bar">
        <button type="button" class="sgc-back-btn" id="sgc-step3-back-btn">BACK</button>

        <div class="sgc-summary-center">
            <div class="sgc-summary-box">
                <div class="sgc-summary-label"># CARDS</div>
                <div class="sgc-summary-value" id="sgc-step3-total-cards">0</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">TOTAL DV</div>
                <div class="sgc-summary-value" id="sgc-step3-total-dv">$0.00</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">GRADING FEE</div>
                <div class="sgc-summary-value" id="sgc-step3-grading-fee">$0.00</div>
            </div>
        </div>

        <button type="button" class="sgc-next-btn" id="sgc-step3-next-btn">NEXT</button>
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