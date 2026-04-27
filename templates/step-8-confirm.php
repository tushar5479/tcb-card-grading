<?php
if (!defined('ABSPATH')) {
    exit;
}

$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;

if (!$order_id && class_exists('SGC_Order_Draft')) {
    $draft = SGC_Order_Draft::get_data();
    $order_id = !empty($draft['saved_order_id']) ? absint($draft['saved_order_id']) : 0;
}

$order = class_exists('SGC_Order_DB') ? SGC_Order_DB::get_order_by_id($order_id) : null;
$cards = class_exists('SGC_Order_DB') ? SGC_Order_DB::get_cards_by_order_id($order_id) : [];

if (!$order) : ?>
    <div class="sgc-step8-page">
        <div class="sgc-step8-empty">
            <h2>No order found.</h2>
            <p>We couldn't find your completed order details.</p>
        </div>
    </div>
<?php return; endif; ?>

<div class="sgc-step8-page">
    <div class="sgc-step8-wrapper">
        
        <div class="sgc-step8-header-card">
            <!-- NEW: Flexbox Container for Badge and Button -->
            <div class="sgc-step8-header-top">
                <div class="sgc-step8-success-badge">Payment Successful</div>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="sgc-back-to-home-btn">← Back to Home</a>
            </div>
            
            <div class="sgc-step8-header-content">
                <h1>Thank you for your order</h1>
                <p>Your card grading submission has been received successfully.</p>
            </div>
        </div>

        <div class="sgc-step8-grid">
            <div class="sgc-step8-main">
                <div class="sgc-step8-section-card">
                    <h3>Order Details</h3>
                    <div class="sgc-step8-detail-grid">
                        <div><strong>Order ID:</strong> <?php echo esc_html($order['id']); ?></div>
                        <div><strong>Order Number:</strong> <?php echo esc_html($order['order_number']); ?></div>
                        <div><strong>Payment Status:</strong> <?php echo esc_html(ucfirst($order['payment_status'])); ?></div>
                        <div><strong>Payment Method:</strong> <?php echo esc_html($order['payment_method_type']); ?></div>
                        <div><strong>Currency:</strong> <?php echo esc_html($order['payment_currency']); ?></div>
                        <div><strong>Payment Date:</strong> <?php echo esc_html($order['payment_received_at']); ?></div>
                    </div>
                </div>

                <div class="sgc-step8-section-card">
                    <h3>Service Summary</h3>
                    <div class="sgc-step8-detail-grid">
                        <div><strong>Tier:</strong> <?php echo esc_html($order['service_level_label']); ?></div>
                        <div><strong>Estimated Time:</strong> <?php echo esc_html($order['service_days']); ?></div>
                        <div><strong>Shipping Method:</strong> <?php echo esc_html($order['shipping_method_label']); ?></div>
                        <div><strong>Total Cards:</strong> <?php echo esc_html($order['total_cards']); ?></div>
                    </div>
                </div>

                <div class="sgc-step8-section-card">
                    <h3>Shipping Address</h3>
                    <div class="sgc-step8-address">
                        <div><?php echo esc_html($order['shipping_street']); ?></div>
                        <?php if (!empty($order['shipping_apartment'])) : ?>
                            <div><?php echo esc_html($order['shipping_apartment']); ?></div>
                        <?php endif; ?>
                        <div>
                            <?php
                            echo esc_html(
                                trim($order['shipping_city'] . ', ' . $order['shipping_state_label'] . ' ' . $order['shipping_zip'])
                            );
                            ?>
                        </div>
                        <div><?php echo esc_html($order['shipping_country_label']); ?></div>
                        <div><?php echo esc_html($order['shipping_phone']); ?></div>
                    </div>
                </div>

                <div class="sgc-step8-section-card">
                    <h3>Submitted Cards</h3>

                    <?php if (!empty($cards)) : ?>
                        <div class="sgc-step8-cards-list">
                            <?php foreach ($cards as $card) : ?>
                                <div class="sgc-step8-card-item">
                                    <div class="sgc-step8-card-top">
                                        <div>
                                            <h4><?php echo esc_html($card['title']); ?></h4>
                                            <?php if (!empty($card['type'])) : ?>
                                                <div class="sgc-step8-card-type"><?php echo esc_html($card['type']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="sgc-step8-card-content">
                                        
                                        <?php if (!empty($card['image_front_url']) || !empty($card['image_back_url'])) : ?>
                                        <div class="sgc-step8-card-images">
                                            <?php if (!empty($card['image_front_url'])) : ?>
                                                <div class="sgc-step8-image-box">
                                                    <div class="sgc-step8-image-label">Front</div>
                                                    <img src="<?php echo esc_url($card['image_front_url']); ?>" alt="Front Image">
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($card['image_back_url'])) : ?>
                                                <div class="sgc-step8-image-box">
                                                    <div class="sgc-step8-image-label">Back</div>
                                                    <img src="<?php echo esc_url($card['image_back_url']); ?>" alt="Back Image">
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>

                                        <div class="sgc-step8-card-meta">
                                            <?php if (!empty($card['player_name'])) : ?>
                                                <div><strong>Player Name:</strong> <?php echo esc_html($card['player_name']); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($card['year'])) : ?>
                                                <div><strong>Year:</strong> <?php echo esc_html($card['year']); ?></div>
                                            <?php endif; ?>

                                            <?php if (!empty($card['card_number'])) : ?>
                                                <div><strong>Card Number:</strong> <?php echo esc_html($card['card_number']); ?></div>
                                            <?php endif; ?>

                                            <div><strong>Declared Value:</strong> $<?php echo esc_html(number_format((float) $card['declared_value'], 2)); ?></div>
                                            
                                            <?php 
                                            // Handling different key names for service
                                            $service_name = !empty($card['selected_service']) ? $card['selected_service'] : (!empty($card['service']) ? $card['service'] : 'Raw Card Grading'); 
                                            ?>
                                            <div><strong>Service:</strong> <?php echo esc_html($service_name); ?></div>
                                            
                                            <div><strong>Encapsulate if altered:</strong> <?php echo !empty($card['encapsulate']) ? 'Yes' : 'No'; ?></div>
                                            <div><strong>Oversized:</strong> <?php echo !empty($card['oversized']) ? 'Yes' : 'No'; ?></div>
                                            <div><strong>Authentic:</strong> <?php echo !empty($card['authentic']) ? 'Yes' : 'No'; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p>No cards found for this order.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="sgc-step8-sidebar">
                <div class="sgc-step8-summary-card">
                    <h3>Payment Summary</h3>

                    <div class="sgc-step8-summary-row">
                        <span>Total Cards</span>
                        <strong><?php echo esc_html($order['total_cards']); ?></strong>
                    </div>

                    <div class="sgc-step8-summary-row">
                        <span>Total Declared Value</span>
                        <strong>$<?php echo esc_html(number_format((float) $order['total_declared_value'], 2)); ?></strong>
                    </div>

                    <div class="sgc-step8-summary-row">
                        <span>Grading Fee</span>
                        <strong>$<?php echo esc_html(number_format((float) $order['grading_fee'], 2)); ?></strong>
                    </div>

                    <div class="sgc-step8-summary-row">
                        <span>Shipping Fee</span>
                        <strong>$<?php echo esc_html(number_format((float) $order['shipping_fee'], 2)); ?></strong>
                    </div>

                    <div class="sgc-step8-summary-total">
                        <span>Total Paid</span>
                        <strong>$<?php echo esc_html(number_format((float) $order['grand_total'], 2)); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<style>
 /* Flexbox Container for Header Top (Badge & Button) */
.sgc-step8-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap; /* মোবাইলের জন্য র্যাপিং */
    gap: 15px;
    margin-bottom: 25px; /* নিচের লেখার সাথে স্পেস */
}

/* Header Content Alignment */
.sgc-step8-header-content h1,
.sgc-step8-header-content p {
    margin-top: 0;
}

/* Back to Home Button Style */
.sgc-back-to-home-btn {
    background-color: #111;
    color: #fff !important;
    padding: 10px 20px;
    text-decoration: none !important;
    border-radius: 4px;
    font-weight: 700;
    font-size: 14px;
    transition: 0.3s ease;
    border: 1px solid #111;
    display: inline-block;
}

.sgc-back-to-home-btn:hover {
    background-color: transparent;
    color: #111 !important;
}

/* Mobile Responsiveness */
@media (max-width: 576px) {
    .sgc-step8-header-top {
        flex-direction: column-reverse; /* মোবাইলে বাটন উপরে এবং ব্যাজ নিচে দেখাবে */
        align-items: flex-start;
    }
}   
</style>