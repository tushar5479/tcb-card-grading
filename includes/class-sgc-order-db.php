<?php
if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('SGC_Order_DB')) {
    return;
}

class SGC_Order_DB {

    public static function get_orders_table() {
        global $wpdb;
        return $wpdb->prefix . 'sgc_orders';
    }

    public static function get_order_cards_table() {
        global $wpdb;
        return $wpdb->prefix . 'sgc_order_cards';
    }

    public static function get_selected_address($draft) {
        $addresses   = !empty($draft['shipping_addresses']) && is_array($draft['shipping_addresses']) ? $draft['shipping_addresses'] : [];
        $selected_id = !empty($draft['selected_shipping_address_id']) ? $draft['selected_shipping_address_id'] : '';

        foreach ($addresses as $address) {
            if (!empty($address['id']) && $address['id'] === $selected_id) {
                return $address;
            }
        }

        return [];
    }

    public static function get_order_by_id($order_id) {
        global $wpdb;

        $table = self::get_orders_table();
        $order_id = absint($order_id);

        if (!$order_id) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d LIMIT 1", $order_id),
            ARRAY_A
        );
    }

    public static function get_order_by_payment_intent($payment_intent_id) {
        global $wpdb;

        $table = self::get_orders_table();
        $payment_intent_id = trim((string) $payment_intent_id);

        if ($payment_intent_id === '') {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE stripe_payment_intent_id = %s LIMIT 1", $payment_intent_id),
            ARRAY_A
        );
    }

    public static function get_order_by_order_number($order_number) {
        global $wpdb;

        $table = self::get_orders_table();
        $order_number = trim((string) $order_number);

        if ($order_number === '') {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE order_number = %s LIMIT 1", $order_number),
            ARRAY_A
        );
    }

    public static function get_cards_by_order_id($order_id) {
        global $wpdb;

        $table = self::get_order_cards_table();
        $order_id = absint($order_id);

        if (!$order_id) {
            return [];
        }

        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE order_id = %d ORDER BY id ASC", $order_id),
            ARRAY_A
        );
    }

    public static function save_order_from_draft($draft) {
        global $wpdb;

        if (!is_array($draft)) {
            return new WP_Error('sgc_invalid_draft', __('Invalid draft data.', 'sgc-card-grading'));
        }

        if (empty($draft['selected_cards']) || !is_array($draft['selected_cards'])) {
            return new WP_Error('sgc_missing_cards', __('No cards found in draft.', 'sgc-card-grading'));
        }

        if (!class_exists('SGC_Ajax_Helper')) {
            return new WP_Error('sgc_missing_helper', __('Payment helper class not found.', 'sgc-card-grading'));
        }

        $orders_table = self::get_orders_table();
        $cards_table  = self::get_order_cards_table();

        $summary = SGC_Ajax_Helper::get_payment_summary_for_stripe($draft);
        $tier    = SGC_Ajax_Helper::get_selected_tier_data($draft);
        $ship    = SGC_Ajax_Helper::get_selected_shipping_method($draft);
        $address = self::get_selected_address($draft);

        $order_number      = !empty($draft['order_number']) ? sanitize_text_field($draft['order_number']) : '';
        $payment_intent_id = !empty($draft['stripe_payment_intent_id']) ? sanitize_text_field($draft['stripe_payment_intent_id']) : '';

        $existing_order = null;

        if ($payment_intent_id !== '') {
            $existing_order = self::get_order_by_payment_intent($payment_intent_id);
        }

        if (!$existing_order && $order_number !== '') {
            $existing_order = self::get_order_by_order_number($order_number);
        }

        $user_id   = get_current_user_id();
        $guest_key = '';

        if (!$user_id && class_exists('SGC_Order_Draft') && method_exists('SGC_Order_Draft', 'get_key')) {
            $guest_key = SGC_Order_Draft::get_key();
        }

        $order_data = [
            'order_number'             => $order_number,
            'user_id'                  => $user_id,
            'guest_key'                => $guest_key,
            'service_level_key'        => !empty($tier['key']) ? sanitize_text_field($tier['key']) : '',
            'service_level_label'      => !empty($tier['label']) ? sanitize_text_field($tier['label']) : '',
            'service_days'             => !empty($tier['days']) ? sanitize_text_field($tier['days']) : '',
            'shipping_method_key'      => !empty($ship['key']) ? sanitize_text_field($ship['key']) : '',
            'shipping_method_label'    => !empty($ship['label']) ? sanitize_text_field($ship['label']) : '',
            'shipping_country'         => !empty($address['country']) ? sanitize_text_field($address['country']) : '',
            'shipping_country_label'   => !empty($address['country_label']) ? sanitize_text_field($address['country_label']) : '',
            'shipping_street'          => !empty($address['street']) ? sanitize_text_field($address['street']) : '',
            'shipping_apartment'       => !empty($address['apartment']) ? sanitize_text_field($address['apartment']) : '',
            'shipping_city'            => !empty($address['city']) ? sanitize_text_field($address['city']) : '',
            'shipping_state'           => !empty($address['state']) ? sanitize_text_field($address['state']) : '',
            'shipping_state_label'     => !empty($address['state_label']) ? sanitize_text_field($address['state_label']) : '',
            'shipping_zip'             => !empty($address['zip']) ? sanitize_text_field($address['zip']) : '',
            'shipping_phone'           => !empty($address['phone']) ? sanitize_text_field($address['phone']) : '',
            'total_cards'              => !empty($summary['total_cards']) ? (int) $summary['total_cards'] : 0,
            'total_declared_value'     => !empty($summary['total_dv']) ? (float) $summary['total_dv'] : 0,
            'grading_fee'              => !empty($summary['grading_fee']) ? (float) $summary['grading_fee'] : 0,
            'shipping_fee'             => !empty($summary['shipping_fee']) ? (float) $summary['shipping_fee'] : 0,
            'grand_total'              => !empty($summary['grand_total']) ? (float) $summary['grand_total'] : 0,
            'stripe_payment_intent_id' => $payment_intent_id,
            'payment_status'           => !empty($draft['payment_status']) ? sanitize_text_field($draft['payment_status']) : 'pending',
            'payment_method_type'      => !empty($draft['payment_method_type']) ? sanitize_text_field($draft['payment_method_type']) : '',
            'payment_currency'         => !empty($draft['payment_currency']) ? sanitize_text_field($draft['payment_currency']) : '',
            'amount_received'          => !empty($draft['amount_received']) ? (float) $draft['amount_received'] : 0,
            'payment_received_at'      => !empty($draft['payment_received_at']) ? sanitize_text_field($draft['payment_received_at']) : current_time('mysql'),
            'created_at'               => current_time('mysql'),
        ];

        if ($existing_order && !empty($existing_order['id'])) {
            $order_id = (int) $existing_order['id'];

            $wpdb->update(
                $orders_table,
                $order_data,
                ['id' => $order_id]
            );

            $wpdb->delete($cards_table, ['order_id' => $order_id]);
        } else {
            $inserted = $wpdb->insert($orders_table, $order_data);

            if (!$inserted) {
                return new WP_Error('sgc_order_insert_failed', __('Failed to save order.', 'sgc-card-grading'));
            }

            $order_id = (int) $wpdb->insert_id;
        }

        foreach ($draft['selected_cards'] as $card) {
            $card_data = [
                'order_id'        => $order_id,
                'row_id'          => !empty($card['row_id']) ? sanitize_text_field($card['row_id']) : '',
                'card_post_id'    => !empty($card['card_id']) ? (int) $card['card_id'] : 0,
                'title'           => !empty($card['title']) ? sanitize_text_field($card['title']) : '',
                'type'            => !empty($card['type']) ? sanitize_text_field($card['type']) : '',
                'player_name'     => !empty($card['player_name']) ? sanitize_text_field($card['player_name']) : '',
                'year'            => !empty($card['year']) ? sanitize_text_field($card['year']) : '',
                'card_number'     => !empty($card['card_number']) ? sanitize_text_field($card['card_number']) : '',
                'declared_value'  => !empty($card['declared_value']) ? (float) $card['declared_value'] : 0,
                'encapsulate'     => !empty($card['encapsulate']) ? 1 : 0,
                'oversized'       => !empty($card['oversized']) ? 1 : 0,
                'authentic'       => !empty($card['authentic']) ? 1 : 0,
                'selected_service'=> !empty($card['service']) ? sanitize_text_field($card['service']) : '',
                'image_front_url' => !empty($card['image_front_url']) ? esc_url_raw($card['image_front_url']) : '',
                'image_back_url'  => !empty($card['image_back_url']) ? esc_url_raw($card['image_back_url']) : '',
                'is_custom'       => !empty($card['is_custom']) ? 1 : 0,
                'created_at'      => current_time('mysql'),
            ];

            $wpdb->insert($cards_table, $card_data);
        }

        return $order_id;
    }
}