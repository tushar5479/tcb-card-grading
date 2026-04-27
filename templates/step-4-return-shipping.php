<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sgc-step4-page">

    <div class="sgc-progress-bar">
        <div class="sgc-order-number">ORDER 1265402</div>

        <div class="sgc-progress-steps">
            <div class="sgc-step-item sgc-step-item-completed">Order Type</div>
            <div class="sgc-step-item sgc-step-item-completed">Add Cards</div>
            <div class="sgc-step-item sgc-step-item-completed">Service</div>
            <div class="sgc-step-item sgc-step-item-completed">Review</div>
            <div class="sgc-step-item sgc-step-item-completed">Shipping</div>
            <div class="sgc-step-item">Shipping Method</div>
            <div class="sgc-step-item">Payment</div>
            <div class="sgc-step-item">Confirm</div>
        </div>

           <div class="sgc-progress-home">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="sgc-progress-home-btn">Back to Home</a>
         </div>
    </div>

    <div class="sgc-step4-inner">
        <h2 class="sgc-main-title">STEP 4. SELECT RETURN SHIPPING ADDRESS</h2>

        <div class="sgc-step4-address-list" id="sgc-step4-address-list"></div>

        <button type="button" class="sgc-add-address-btn" id="sgc-open-address-modal">
            <span class="sgc-add-address-plus">+</span>
            ADD NEW SHIPPING ADDRESS
        </button>
    </div>

    <div class="sgc-bottom-fixed-bar">
        <button type="button" class="sgc-back-btn" id="sgc-step4-back-btn">BACK</button>

        <div class="sgc-summary-center">
            <div class="sgc-summary-box">
                <div class="sgc-summary-label">CARD GRADING</div>
                <div class="sgc-summary-value" id="sgc-step4-tier-label">-</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label"># CARDS</div>
                <div class="sgc-summary-value" id="sgc-step4-total-cards">0</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">TOTAL DV</div>
                <div class="sgc-summary-value" id="sgc-step4-total-dv">$0.00</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">GRADING FEE</div>
                <div class="sgc-summary-value" id="sgc-step4-grading-fee">$0.00</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">SHIPPING</div>
                <div class="sgc-summary-value" id="sgc-step4-shipping-fee">$15.00</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">TOTAL</div>
                <div class="sgc-summary-value" id="sgc-step4-grand-total">$0.00</div>
            </div>
        </div>

        <button type="button" class="sgc-next-btn" id="sgc-step4-next-btn">NEXT</button>
    </div>

    <div class="sgc-modal-overlay" id="sgc-address-modal" style="display:none;">
        <div class="sgc-modal-dialog sgc-address-modal-dialog">
            <button type="button" class="sgc-modal-close" id="sgc-close-address-modal">×</button>

            <h3 class="sgc-modal-title">USER ADDRESS</h3>

            <div class="sgc-modal-grid">
                <input type="hidden" id="sgc_address_id" value="">

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_country">Country</label>
                    <select id="sgc_address_country" class="sgc-modal-input"></select>
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_street">Street and number, P.O. box, c/o.</label>
                    <input type="text" id="sgc_address_street" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_apartment">Apartment, suite, unit, building, floor, etc.</label>
                    <input type="text" id="sgc_address_apartment" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_city">City</label>
                    <input type="text" id="sgc_address_city" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_state">State / Province</label>
                    <select id="sgc_address_state" class="sgc-modal-input"></select>
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_zip">Zip Code</label>
                    <input type="text" id="sgc_address_zip" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label for="sgc_address_phone">Phone Number</label>
                    <input type="text" id="sgc_address_phone" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <label class="sgc-address-default-wrap">
                        <input type="checkbox" id="sgc_address_default">
                        <span>Make it my default address</span>
                    </label>
                </div>

                <div class="sgc-modal-field sgc-modal-field-full">
                    <div class="sgc-modal-error" id="sgc-address-error" style="display:none;"></div>
                </div>
            </div>

            <div class="sgc-modal-actions sgc-address-modal-actions">
                <button type="button" class="sgc-back-btn" id="sgc-address-cancel-btn">CANCEL</button>
                <button type="button" class="sgc-next-btn" id="sgc-address-save-btn">SAVE</button>
            </div>
        </div>
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