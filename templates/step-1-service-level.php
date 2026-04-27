<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sgc-step1-page">

    <div class="sgc-progress-bar">
        <div class="sgc-order-number">ORDER 1263480</div>

        <div class="sgc-progress-steps">
            <div class="sgc-step-item sgc-step-item-completed">Add Cards</div>
            <div class="sgc-step-item">Service</div>
            <div class="sgc-step-item">Review</div>
            <div class="sgc-step-item">Shipping</div>
            <div class="sgc-step-item">Shipping Method</div>
            <div class="sgc-step-item">Payment</div>
            <div class="sgc-step-item">Confirm</div>
        </div>

     <div class="sgc-progress-home">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="sgc-progress-home-btn">Back to Home</a>
       </div>
    </div>

    <div class="sgc-step1-inner">
        <h2 class="sgc-main-title">STEP 1. SERVICE LEVEL SELECTION</h2>

        <div class="sgc-tier-grid" id="sgc-tier-grid">

            <!-- STANDARD -->
            <div class="sgc-tier-card sgc-tier-standard" data-tier="standard">
                <div class="sgc-tier-image-wrap sgc-tier-image-wrap-standard">
                    <img src="<?php echo esc_url(SGC_CG_URL . 'assets/images/standard.webp'); ?>" class="sgc-tier-image" alt="Standard">
                </div>

                <div class="sgc-tier-meta sgc-tier-meta-standard">
                    <div class="sgc-tier-price sgc-tier-price-standard">$15 per card</div>
                    <div class="sgc-tier-days sgc-tier-days-standard">15–20 business days</div>
                </div>

                <div class="sgc-tier-label sgc-tier-label-standard">STANDARD</div>
            </div>

            <!-- PRISTINE 10 -->
            <div class="sgc-tier-card sgc-tier-pristine is-selected" data-tier="pristine-10">
                <div class="sgc-tier-image-wrap sgc-tier-image-wrap-pristine">
                    <img src="<?php echo esc_url(SGC_CG_URL . 'assets/images/pristine.webp'); ?>" class="sgc-tier-image" alt="Pristine 10">
                </div>

                <div class="sgc-tier-meta sgc-tier-meta-pristine">
                    <div class="sgc-tier-price sgc-tier-price-pristine">$25 per card</div>
                    <div class="sgc-tier-days sgc-tier-days-pristine">7–10 business days</div>
                </div>

                <div class="sgc-tier-label sgc-tier-label-pristine">PRISTINE 10</div>
            </div>

            <!-- ELITE / RARE -->
            <div class="sgc-tier-card sgc-tier-elite" data-tier="elite-rare">
                <div class="sgc-tier-image-wrap sgc-tier-image-wrap-elite">
                    <img src="<?php echo esc_url(SGC_CG_URL . 'assets/images/elite.webp'); ?>" class="sgc-tier-image" alt="Elite Rare">
                </div>

                <div class="sgc-tier-meta sgc-tier-meta-elite">
                    <div class="sgc-tier-price sgc-tier-price-elite">$65 per card</div>
                    <div class="sgc-tier-days sgc-tier-days-elite">5–10 business days</div>
                </div>

                <div class="sgc-tier-label sgc-tier-label-elite">ELITE / RARE</div>
            </div>

            <!-- VINTAGE -->
            <div class="sgc-tier-card sgc-tier-vintage" data-tier="vintage">
                <div class="sgc-tier-image-wrap sgc-tier-image-wrap-vintage">
                    <img src="<?php echo esc_url(SGC_CG_URL . 'assets/images/vintage.webp'); ?>" class="sgc-tier-image" alt="Vintage">
                </div>

                <div class="sgc-tier-meta sgc-tier-meta-vintage">
                    <div class="sgc-tier-price sgc-tier-price-vintage">$49 per card</div>
                    <div class="sgc-tier-days sgc-tier-days-vintage">10–15 business days</div>
                </div>

                <div class="sgc-tier-label sgc-tier-label-vintage">VINTAGE</div>
            </div>

            <!-- ULTRA / 1 OF 1 -->
            <div class="sgc-tier-card sgc-tier-ultra" data-tier="ultra-1of1">
                <div class="sgc-tier-image-wrap sgc-tier-image-wrap-ultra">
                    <img src="<?php echo esc_url(SGC_CG_URL . 'assets/images/ultra.webp'); ?>" class="sgc-tier-image" alt="Ultra 1 of 1">
                </div>

                <div class="sgc-tier-meta sgc-tier-meta-ultra">
                    <div class="sgc-tier-price sgc-tier-price-ultra">$120 per card</div>
                    <div class="sgc-tier-days sgc-tier-days-ultra">2–5 business days</div>
                </div>

                <div class="sgc-tier-label sgc-tier-label-ultra">ULTRA / 1 OF 1</div>
            </div>

        </div>
    </div>

    <div class="sgc-bottom-fixed-bar">
        <button type="button" class="sgc-back-btn" disabled>BACK</button>

        <div class="sgc-summary-center">
            <div class="sgc-summary-box">
                <div class="sgc-summary-label">SERVICE LEVEL</div>
                <div class="sgc-summary-value" id="sgc-selected-tier-label">Pristine 10</div>
            </div>
        </div>

        <button type="button" class="sgc-next-btn" id="sgc-step1-next-btn">NEXT</button>
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