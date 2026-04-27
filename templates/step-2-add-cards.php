<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="sgc-step2-page">

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

    <div class="sgc-step2-inner">
        <h2 class="sgc-main-title">STEP 2. ADD YOUR CARDS</h2>

        <div class="sgc-step2-grid">
            <div class="sgc-left-column">
                <input type="text" id="sgc-card-search" class="sgc-search-input" placeholder="Search for cards">

                <div class="sgc-help-text">
                    START WITH A PLAYER NAME AND CARD NUMBER. THEN ADD THE INSERT AND/OR PARALLEL, IF APPLICABLE. IT IS OK IF YOUR CARD DOES NOT APPEAR IN THE SEARCH RESULTS. SIMPLY ENTER THE YEAR, MANUFACTURER, CARD NUMBER, PLAYER NAME, INSERT AND/OR PARALLEL TO THE BEST OF YOUR ABILITY. THEN SELECT THE “CLICK HERE TO ADD YOUR CARD” LINK BELOW. PLEASE NOTE, DURING PROCESSING, SGC WILL CORRECT ANY INACCURACIES SO THAT ALL CARDS ARE LABELED CORRECTLY.
                </div>

                <div class="sgc-add-card-inline" id="sgc-add-card-inline" style="display:none;">
                    I don't see my card listed.
                    <a href="#" id="sgc-open-custom-card-modal">Click here to add your card</a>
                </div>

                <div id="sgc-search-results"></div>

                <div class="sgc-load-more-wrap">
                    <button type="button" id="sgc-load-more" class="sgc-load-more-btn" style="display:none;">View More</button>
                </div>
            </div>

            <div class="sgc-right-column">

                <div class="sgc-pricing-box" id="sgc-pricing-box">
                    <table class="sgc-pricing-table" id="sgc-pricing-table">
                        <thead>
                            <tr>
                                <th colspan="2">SERVICES AND PRICING</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong id="sgc-selected-tier-name">Pristine 10</strong><br>
                                    <span id="sgc-selected-tier-days">7–10 business days</span>
                                </td>
                                <td>
                                    <strong>PRICE PER CARD</strong><br>
                                    <span id="sgc-selected-tier-price">$25.00</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <a href="#" id="sgc-toggle-pricing-table" class="sgc-toggle-pricing-link">Hide Pricing Table</a>
                </div>

                <div id="sgc-selected-cards"></div>
            </div>
        </div>
    </div>

    <div class="sgc-bottom-fixed-bar">
        <button type="button" class="sgc-back-btn">BACK</button>

        <div class="sgc-summary-center">
            <div class="sgc-summary-box">
                <div class="sgc-summary-label">CARD GRADING</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label"># CARDS</div>
                <div class="sgc-summary-value" id="sgc-total-cards">0</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">TOTAL DV</div>
                <div class="sgc-summary-value" id="sgc-total-dv">$0.00</div>
            </div>

            <div class="sgc-summary-box">
                <div class="sgc-summary-label">GRADING FEE</div>
                <div class="sgc-summary-value">$<span id="sgc-grading-fee">0.00</span></div>
            </div>
        </div>

        <button type="button" class="sgc-next-btn" id="sgc-next-btn">NEXT</button>
    </div>

    <!-- Custom Card Modal -->
    <div class="sgc-modal-overlay" id="sgc-custom-card-modal" style="display:none;">
        <div class="sgc-modal-dialog">
            <button type="button" class="sgc-modal-close" id="sgc-close-custom-card-modal">×</button>

            <h3 class="sgc-modal-title">Add Your Card</h3>

            <div class="sgc-modal-grid">
                <div class="sgc-modal-field">
                    <label for="sgc_custom_player_name">Player Name</label>
                    <input type="text" id="sgc_custom_player_name" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field">
                    <label for="sgc_custom_year">Year</label>
                    <input type="text" id="sgc_custom_year" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field">
                    <label for="sgc_custom_card_number">Card Number</label>
                    <input type="text" id="sgc_custom_card_number" class="sgc-modal-input">
                </div>

                <div class="sgc-modal-field">
                    <label for="sgc_custom_declared_value">Declared Value</label>
                    <input type="number" min="1" step="1" id="sgc_custom_declared_value" class="sgc-modal-input" value="1">
                    <div class="sgc-modal-error" id="sgc_custom_declared_value_error" style="display:none;">
                        Declared Value is required. Minimum $1.
                    </div>
                </div>




                    <div class="sgc-modal-field sgc-modal-field-full">
                 <div class="sgc-modal-preview-grid">

                        <div class="sgc-modal-preview-col">
                            <label for="sgc_custom_image_front">Upload Front Image</label>
                            <input type="file" id="sgc_custom_image_front" class="sgc-modal-input" accept="image/*">

                            <div class="sgc-modal-image-preview-wrap">
                                <img id="sgc_custom_image_preview_front" src="" alt="Front Preview" style="display:none;">
                                <div class="sgc-modal-preview-placeholder" id="sgc_preview_placeholder_front">Front Preview</div>
                            </div>
                        </div>

                        <div class="sgc-modal-preview-col">
                            <label for="sgc_custom_image_back">Upload Back Image</label>
                            <input type="file" id="sgc_custom_image_back" class="sgc-modal-input" accept="image/*">

                            <div class="sgc-modal-image-preview-wrap">
                                <img id="sgc_custom_image_preview_back" src="" alt="Back Preview" style="display:none;">
                                <div class="sgc-modal-preview-placeholder" id="sgc_preview_placeholder_back">Back Preview</div>
                            </div>
                        </div>

                    </div>
                 </div>















            <div class="sgc-modal-actions">
                <button type="button" class="sgc-modal-submit-btn" id="sgc-submit-custom-card">Submit</button>
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