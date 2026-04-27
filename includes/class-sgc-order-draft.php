<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Order_Draft {

    public static function get_key() {
        if (is_user_logged_in()) {
            return 'sgc_order_draft_' . get_current_user_id();
        }

        $cookie_name = 'sgc_guest_draft_key';

        if (empty($_COOKIE[$cookie_name])) {
            $guest_key = wp_generate_uuid4();
            setcookie($cookie_name, $guest_key, time() + WEEK_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN);
            $_COOKIE[$cookie_name] = $guest_key;
        }

        return 'sgc_order_draft_guest_' . sanitize_text_field(wp_unslash($_COOKIE[$cookie_name]));
    }

    public static function get_data() {
        $data = get_option(self::get_key(), []);
        return is_array($data) ? $data : [];
    }

    public static function save_data($data) {
        update_option(self::get_key(), $data, false);
    }

    public static function clear_data() {
        delete_option(self::get_key());
    }

    public static function save_data_by_key($option_key, $data) {
        if ($option_key === '') {
            return;
        }

        update_option($option_key, $data, false);
    }

    public static function get_data_by_key($option_key) {
        if ($option_key === '') {
            return [];
        }

        $data = get_option($option_key, []);
        return is_array($data) ? $data : [];
    }

    public static function find_draft_by_payment_intent_id($payment_intent_id) {
        global $wpdb;

        $payment_intent_id = trim((string) $payment_intent_id);

        if ($payment_intent_id === '') {
            return false;
        }

        $like = $wpdb->esc_like('sgc_order_draft_') . '%';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value 
                 FROM {$wpdb->options}
                 WHERE option_name LIKE %s",
                $like
            ),
            ARRAY_A
        );

        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            $value = maybe_unserialize($row['option_value']);

            if (!is_array($value)) {
                continue;
            }

            if (
                !empty($value['stripe_payment_intent_id']) &&
                $value['stripe_payment_intent_id'] === $payment_intent_id
            ) {
                return [
                    'option_key' => $row['option_name'],
                    'draft'      => $value,
                ];
            }
        }

        return false;
    }

    public static function find_draft_by_order_number($order_number) {
        global $wpdb;

        $order_number = trim((string) $order_number);

        if ($order_number === '') {
            return false;
        }

        $like = $wpdb->esc_like('sgc_order_draft_') . '%';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value 
                 FROM {$wpdb->options}
                 WHERE option_name LIKE %s",
                $like
            ),
            ARRAY_A
        );

        if (empty($rows)) {
            return false;


            
        }

        foreach ($rows as $row) {
            $value = maybe_unserialize($row['option_value']);

            if (!is_array($value)) {
                continue;
            }

            if (
                !empty($value['order_number']) &&
                $value['order_number'] === $order_number
            ) {
                return [
                    'option_key' => $row['option_name'],
                    'draft'      => $value,
                ];
            }
        }

        return false;
    }
}