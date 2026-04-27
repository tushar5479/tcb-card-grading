<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Customer_Order_CPT {

    public static function init() {
        add_action('init', array(__CLASS__, 'register_cpt'));
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_order_action'));

        add_filter('post_row_actions', array(__CLASS__, 'modify_row_actions'), 10, 2);

        add_filter('manage_sgc_order_posts_columns', array(__CLASS__, 'add_custom_columns'));
        add_action('manage_sgc_order_posts_custom_column', array(__CLASS__, 'render_custom_columns'), 10, 2);

        add_action('admin_head', array(__CLASS__, 'admin_column_styles'));
    }

    public static function register_cpt() {
        $labels = array(
            'name'               => 'Customer Orders',
            'singular_name'      => 'Customer Order',
            'menu_name'          => 'SGC Orders',
            'add_new'            => 'Add New Order',
            'add_new_item'       => 'Add New Order',
            'edit_item'          => 'View Order',
            'view_item'          => 'View Order',
            'search_items'       => 'Search Orders',
            'not_found'          => 'No orders found',
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-clipboard',
            'supports'            => array('title'),
            'capability_type'     => 'post',
            'has_archive'         => false,
        );

        register_post_type('sgc_order', $args);
    }

    public static function modify_row_actions($actions, $post) {
        if ($post->post_type === 'sgc_order') {
            if (isset($actions['edit'])) {
                $actions['edit'] = str_replace('>Edit<', '>View<', $actions['edit']);
                $actions['edit'] = str_replace('aria-label="Edit', 'aria-label="View', $actions['edit']);
            }

            if (isset($actions['inline hide-if-no-js'])) {
                unset($actions['inline hide-if-no-js']);
            }

            if (isset($actions['view'])) {
                unset($actions['view']);
            }
        }

        return $actions;
    }

    public static function add_custom_columns($columns) {
        $new_columns = array();

        foreach ($columns as $key => $title) {
            if ($key === 'date') {
                $new_columns['order_status'] = 'Order Status';
                $new_columns['estimated_delivery'] = 'Estimated Delivery';
            }

            $new_columns[$key] = $title;
        }

        return $new_columns;
    }

    public static function render_custom_columns($column, $post_id) {
        if ($column === 'order_status') {
            $is_confirmed = get_post_meta($post_id, '_sgc_is_confirmed', true);

            if ($is_confirmed === 'yes') {
                echo '<span style="color:#155724;background-color:#d4edda;border:1px solid #c3e6cb;padding:5px 10px;border-radius:4px;font-weight:bold;display:inline-block;">
                        <span class="dashicons dashicons-yes-alt" style="vertical-align:middle;margin-top:-2px;"></span> Confirmed
                      </span>';
            } else {
                echo '<span style="color:#856404;background-color:#fff3cd;border:1px solid #ffeeba;padding:5px 10px;border-radius:4px;font-weight:bold;display:inline-block;">
                        <span class="dashicons dashicons-clock" style="vertical-align:middle;margin-top:-2px;"></span> Pending
                      </span>';
            }
        }

        if ($column === 'estimated_delivery') {
            $estimated_delivery_date = self::get_estimated_delivery_date($post_id);

            if ($estimated_delivery_date === 'Not confirmed yet') {
                echo '<span style="color:#856404;font-weight:600;">Not confirmed yet</span>';
            } elseif (
                $estimated_delivery_date === 'Tier not found' ||
                $estimated_delivery_date === 'Tier mapping missing' ||
                $estimated_delivery_date === 'Tier data not found' ||
                $estimated_delivery_date === 'N/A'
            ) {
                echo '<span style="color:#b02a37;font-weight:600;">' . esc_html($estimated_delivery_date) . '</span>';
            } else {
                echo '<span style="color:#0f5132;font-weight:700;">' . esc_html($estimated_delivery_date) . '</span>';
            }
        }
    }
public static function admin_column_styles() {
    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== 'sgc_order') {
        return;
    }

    echo '<style>
        .wp-list-table .check-column,
        .wp-list-table th.check-column,
        .wp-list-table td.check-column,
        .wp-list-table .column-cb {
            width: 34px !important;
            padding-left: 5px !important;
            padding-right: 0 !important;
        }

        .wp-list-table .column-title {
            width: 41% !important;
        }

        .wp-list-table .column-order_status {
            width: 15% !important;
        }

        .wp-list-table .column-estimated_delivery {
            width: 18% !important;
        }

        .wp-list-table .column-date {
            width: 16% !important;
        }
    </style>';
}

    public static function add_meta_boxes() {
        add_meta_box(
            'sgc_order_details',
            'Order Details & Cards',
            array(__CLASS__, 'render_details_meta_box'),
            'sgc_order',
            'normal',
            'high'
        );

        add_meta_box(
            'sgc_order_actions',
            'Order Actions (Email Confirm)',
            array(__CLASS__, 'render_actions_meta_box'),
            'sgc_order',
            'side',
            'high'
        );
    }

    public static function render_details_meta_box($post) {
        $order_data      = get_post_meta($post->ID, '_sgc_order_data', true);
        $cards           = get_post_meta($post->ID, '_sgc_order_cards', true);
        $is_confirmed    = get_post_meta($post->ID, '_sgc_is_confirmed', true);
        $confirmed_date  = get_post_meta($post->ID, '_sgc_confirmed_date', true);
        $estimated_date  = self::get_estimated_delivery_date($post->ID);

        if (!$order_data) {
            echo '<p>No data found.</p>';
            return;
        }

        echo '<h3>Shipping Address</h3>';
        echo '<p><strong>Name/Email:</strong> ' . esc_html(self::get_customer_display_name($order_data)) . '</p>';
        echo '<p><strong>Address:</strong> ' . esc_html(self::build_address_line($order_data)) . '</p>';
        echo '<p><strong>Phone:</strong> ' . esc_html($order_data['shipping_phone'] ?? 'N/A') . '</p>';

        echo '<hr><h3>Order Tracking Info</h3>';
        echo '<p><strong>Order Number:</strong> ' . esc_html(get_the_title($post->ID)) . '</p>';
        echo '<p><strong>Order Status:</strong> ' . ($is_confirmed === 'yes'
            ? '<span style="color:green;font-weight:bold;">Confirmed</span>'
            : '<span style="color:#b8860b;font-weight:bold;">Pending</span>') . '</p>';

        if ($is_confirmed === 'yes' && !empty($confirmed_date)) {
            echo '<p><strong>Confirmed Date:</strong> ' . esc_html(self::format_display_date($confirmed_date)) . '</p>';
        } else {
            echo '<p><strong>Confirmed Date:</strong> Not confirmed yet</p>';
        }

        echo '<p><strong>Estimated Delivery Date:</strong> ' . esc_html($estimated_date) . '</p>';

        echo '<hr><h3>Payment & Shipping</h3>';
        echo '<p><strong>Status:</strong> ' . esc_html($order_data['payment_status'] ?? 'N/A') . '</p>';
        echo '<p><strong>Total Paid:</strong> $' . esc_html(number_format((float)($order_data['grand_total'] ?? 0), 2)) . '</p>';
        echo '<p><strong>Shipping Method:</strong> ' . esc_html($order_data['shipping_method_label'] ?? 'N/A') . '</p>';

        echo '<hr><h3>Submitted Cards</h3>';

        if (!empty($cards) && is_array($cards)) {
            echo '<div style="display:flex; flex-wrap:wrap; gap:20px;">';

            foreach ($cards as $card) {
                echo '<div style="border:1px solid #ddd;padding:15px;width:auto;min-width:280px;background:#f9f9f9;border-radius:5px;box-shadow:0 1px 3px rgba(0,0,0,0.05);">';
                echo '<strong style="font-size:15px;display:block;margin-bottom:8px;color:#111;">' . esc_html($card['title'] ?? '') . '</strong>';
                echo '<p style="margin:0 0 5px;font-size:13px;"><strong>Service:</strong> ' . esc_html($card['service'] ?? ($card['selected_service'] ?? 'Raw Grading')) . '</p>';
                echo '<p style="margin:0 0 15px;font-size:13px;"><strong>DV:</strong> $' . esc_html($card['declared_value'] ?? '0') . '</p>';

                echo '<div style="display:flex; gap:15px;">';

                if (!empty($card['image_front_url'])) {
                    echo '<div style="text-align:center;">';
                    echo '<span style="display:block;margin-bottom:5px;font-size:12px;font-weight:bold;color:#555;">FRONT</span>';
                    echo '<a href="' . esc_url($card['image_front_url']) . '" target="_blank" title="Click to preview full image">';
                    echo '<img src="' . esc_url($card['image_front_url']) . '" style="width:110px;height:auto;border:1px solid #ccc;border-radius:4px;display:block;margin-bottom:8px;transition:opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1">';
                    echo '</a>';
                    echo '<a href="' . esc_url($card['image_front_url']) . '" download style="text-decoration:none;background:#2271b1;color:#fff;padding:4px 10px;border-radius:3px;font-size:11px;display:inline-block;">⬇ Download</a>';
                    echo '</div>';
                }

                if (!empty($card['image_back_url'])) {
                    echo '<div style="text-align:center;">';
                    echo '<span style="display:block;margin-bottom:5px;font-size:12px;font-weight:bold;color:#555;">BACK</span>';
                    echo '<a href="' . esc_url($card['image_back_url']) . '" target="_blank" title="Click to preview full image">';
                    echo '<img src="' . esc_url($card['image_back_url']) . '" style="width:110px;height:auto;border:1px solid #ccc;border-radius:4px;display:block;margin-bottom:8px;transition:opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=1">';
                    echo '</a>';
                    echo '<a href="' . esc_url($card['image_back_url']) . '" download style="text-decoration:none;background:#2271b1;color:#fff;padding:4px 10px;border-radius:3px;font-size:11px;display:inline-block;">⬇ Download</a>';
                    echo '</div>';
                }

                echo '</div></div>';
            }

            echo '</div>';
        } else {
            echo '<p>No cards submitted.</p>';
        }
    }

    public static function render_actions_meta_box($post) {
        $is_confirmed   = get_post_meta($post->ID, '_sgc_is_confirmed', true);
        $confirmed_date = get_post_meta($post->ID, '_sgc_confirmed_date', true);

        wp_nonce_field('sgc_save_order_action', 'sgc_order_nonce');

        if ($is_confirmed === 'yes') {
            echo '<div style="background:#d4edda;color:#155724;padding:10px;border-radius:4px;border:1px solid #c3e6cb;margin-bottom:10px;"><strong>✔ Confirmation Email Sent!</strong></div>';

            if (!empty($confirmed_date)) {
                echo '<p><strong>Confirmed Date:</strong><br>' . esc_html(self::format_display_date($confirmed_date)) . '</p>';
            }

            echo '<p><strong>Estimated Delivery:</strong><br>' . esc_html(self::get_estimated_delivery_date($post->ID)) . '</p>';
        } else {
            echo '<p>Check this box and click <strong>Update</strong> to send a confirmation email to the customer.</p>';
            echo '<label><input type="checkbox" name="sgc_send_confirm_email" value="1"> <strong>Confirm Order & Send Email</strong></label>';
        }
    }

    public static function save_order_action($post_id) {
        if (!isset($_POST['sgc_order_nonce']) || !wp_verify_nonce($_POST['sgc_order_nonce'], 'sgc_save_order_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (get_post_type($post_id) !== 'sgc_order') {
            return;
        }

        if (isset($_POST['sgc_send_confirm_email']) && $_POST['sgc_send_confirm_email'] == '1') {
            $is_confirmed = get_post_meta($post_id, '_sgc_is_confirmed', true);

            if ($is_confirmed !== 'yes') {
                $order_data   = get_post_meta($post_id, '_sgc_order_data', true);
                $user_email   = isset($order_data['user_email']) ? $order_data['user_email'] : '';
                $order_number = get_the_title($post_id);

                if (!empty($user_email)) {
                    $subject = 'Your Card Grading Order Has Been Confirmed - ' . $order_number;
                    $message = '<h2>Order Confirmed!</h2>';
                    $message .= '<p>Hello,</p>';
                    $message .= '<p>Great news! Your card grading submission (<strong>' . esc_html($order_number) . '</strong>) has been successfully confirmed by our team.</p>';
                    $message .= '<p>We will notify you once the grading process begins.</p>';
                    $message .= '<p>Thank you,<br>Theory Card Breeze Team</p>';

                    $headers = array('Content-Type: text/html; charset=UTF-8');

                    wp_mail($user_email, $subject, $message, $headers);
                }

                update_post_meta($post_id, '_sgc_is_confirmed', 'yes');
                update_post_meta($post_id, '_sgc_confirmed_date', current_time('mysql'));
            }
        }
    }

    private static function get_customer_display_name($order_data = array()) {
        if (!empty($order_data['user_name'])) {
            return $order_data['user_name'];
        }

        if (!empty($order_data['full_name'])) {
            return $order_data['full_name'];
        }

        if (!empty($order_data['customer_name'])) {
            return $order_data['customer_name'];
        }

        if (!empty($order_data['billing_name'])) {
            return $order_data['billing_name'];
        }

        if (!empty($order_data['shipping_name'])) {
            return $order_data['shipping_name'];
        }

        if (!empty($order_data['user_email'])) {
            return $order_data['user_email'];
        }

        return 'N/A';
    }

    private static function build_address_line($order_data = array()) {
        $parts = array_filter(array(
            $order_data['shipping_street'] ?? '',
            $order_data['shipping_city'] ?? '',
            $order_data['shipping_state_label'] ?? '',
            !empty($order_data['shipping_zip']) ? '- ' . $order_data['shipping_zip'] : '',
        ));

        return !empty($parts) ? implode(', ', $parts) : 'N/A';
    }

    private static function format_display_date($date_value) {
        if (empty($date_value)) {
            return 'Not confirmed yet';
        }

        $timestamp = strtotime($date_value);

        if ($timestamp) {
            return date_i18n('F j, Y g:i a', $timestamp);
        }

        return $date_value;
    }
private static function get_selected_tier_slug($order_data = array()) {
    $possible_keys = array(
        'selected_service',
        'service_level',
        'tier',
        'selected_tier',
        'service_type',
        'selected_tier_slug',
        'grading_tier',
        'tier_name',
        'selected_level',
        'service',
        'tier_slug',
        'turnaround',
        'turnaround_time',
        'turnaround_label',
        'service_days',
        'tier_days',
        'business_days'
    );

    foreach ($possible_keys as $key) {
        if (!empty($order_data[$key])) {
            $value = strtolower(trim(wp_strip_all_tags((string) $order_data[$key])));

            // tier name based
            if (strpos($value, 'standard') !== false) {
                return 'standard';
            }

            if (strpos($value, 'pristine') !== false) {
                return 'pristine-10';
            }

            if (strpos($value, 'elite') !== false || strpos($value, 'rare') !== false) {
                return 'elite-rare';
            }

            if (strpos($value, 'vintage') !== false) {
                return 'vintage';
            }

            if (strpos($value, 'ultra') !== false) {
                return 'ultra-1-of-1';
            }

            // days label based
            if (strpos($value, '15-20') !== false || strpos($value, '15–20') !== false) {
                return 'standard';
            }

            if (strpos($value, '7-10') !== false || strpos($value, '7–10') !== false) {
                return 'pristine-10';
            }

            if (strpos($value, '5-10') !== false || strpos($value, '5–10') !== false) {
                return 'elite-rare';
            }

            if (strpos($value, '10-15') !== false || strpos($value, '10–15') !== false) {
                return 'vintage';
            }

            if (strpos($value, '2-5') !== false || strpos($value, '2–5') !== false) {
                return 'ultra-1-of-1';
            }

            return sanitize_title($value);
        }
    }

    return '';
}

    private static function get_tier_calendar_days($tier_slug) {
        $tier_slug = strtolower(trim((string) $tier_slug));

        $aliases = array(
            'standard'       => 'standard',
            'pristine-10'    => 'pristine-10',
            'pristine10'     => 'pristine-10',
            'pristine'       => 'pristine-10',
            'elite-rare'     => 'elite-rare',
            'elite_rare'     => 'elite-rare',
            'elite / rare'   => 'elite-rare',
            'elite'          => 'elite-rare',
            'rare'           => 'elite-rare',
            'vintage'        => 'vintage',
            'ultra-1-of-1'   => 'ultra-1-of-1',
            'ultra_1_of_1'   => 'ultra-1-of-1',
            'ultra / 1 of 1' => 'ultra-1-of-1',
            'ultra'          => 'ultra-1-of-1',
        );

        if (isset($aliases[$tier_slug])) {
            $tier_slug = $aliases[$tier_slug];
        }

        $map = array(
            'standard'     => array('min' => 15, 'max' => 20, 'label' => '15–20 days'),
            'pristine-10'  => array('min' => 7,  'max' => 10, 'label' => '7–10 days'),
            'elite-rare'   => array('min' => 5,  'max' => 10, 'label' => '5–10 days'),
            'vintage'      => array('min' => 10, 'max' => 15, 'label' => '10–15 days'),
            'ultra-1-of-1' => array('min' => 2,  'max' => 5,  'label' => '2–5 days'),
        );

        return $map[$tier_slug] ?? array('min' => 0, 'max' => 0, 'label' => 'N/A');
    }

    private static function add_max_days($date_string, $days) {
        if (empty($date_string) || empty($days)) {
            return '';
        }

        try {
            $date = new DateTime($date_string, wp_timezone());
        } catch (Exception $e) {
            return '';
        }

        $days_to_add = max(0, intval($days) - 1);
        $date->modify('+' . $days_to_add . ' days');

        return $date->format('F j, Y');
    }

    private static function get_estimated_delivery_date($post_id) {
        $order_data     = get_post_meta($post_id, '_sgc_order_data', true);
        $confirmed_date = get_post_meta($post_id, '_sgc_confirmed_date', true);
        $is_confirmed   = get_post_meta($post_id, '_sgc_is_confirmed', true);

        if ($is_confirmed !== 'yes' || empty($confirmed_date)) {
            return 'Not confirmed yet';
        }

        if (empty($order_data) || !is_array($order_data)) {
            return 'Tier data not found';
        }

        $tier_slug = self::get_selected_tier_slug($order_data);

        if (empty($tier_slug)) {
            return 'Tier not found';
        }

        $tier_days = self::get_tier_calendar_days($tier_slug);

        if (empty($tier_days['max'])) {
            return 'Tier mapping missing';
        }

        $delivery_date = self::add_max_days($confirmed_date, (int) $tier_days['max']);

        return !empty($delivery_date) ? $delivery_date : 'N/A';
    }
}

SGC_Customer_Order_CPT::init();