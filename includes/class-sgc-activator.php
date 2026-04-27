<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Activator {

    public static function activate() {
        self::create_tables();
        flush_rewrite_rules();
    }

    private static function create_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $orders_table = $wpdb->prefix . 'sgc_orders';
        $cards_table  = $wpdb->prefix . 'sgc_order_cards';

        $sql_orders = "CREATE TABLE {$orders_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_number VARCHAR(100) NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            guest_key VARCHAR(190) NOT NULL DEFAULT '',
            service_level_key VARCHAR(100) NOT NULL DEFAULT '',
            service_level_label VARCHAR(255) NOT NULL DEFAULT '',
            service_days VARCHAR(255) NOT NULL DEFAULT '',
            shipping_method_key VARCHAR(100) NOT NULL DEFAULT '',
            shipping_method_label VARCHAR(255) NOT NULL DEFAULT '',
            shipping_country VARCHAR(50) NOT NULL DEFAULT '',
            shipping_country_label VARCHAR(255) NOT NULL DEFAULT '',
            shipping_street VARCHAR(255) NOT NULL DEFAULT '',
            shipping_apartment VARCHAR(255) NOT NULL DEFAULT '',
            shipping_city VARCHAR(255) NOT NULL DEFAULT '',
            shipping_state VARCHAR(100) NOT NULL DEFAULT '',
            shipping_state_label VARCHAR(255) NOT NULL DEFAULT '',
            shipping_zip VARCHAR(100) NOT NULL DEFAULT '',
            shipping_phone VARCHAR(100) NOT NULL DEFAULT '',
            total_cards INT NOT NULL DEFAULT 0,
            total_declared_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            grading_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            grand_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            stripe_payment_intent_id VARCHAR(255) NOT NULL DEFAULT '',
            payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
            payment_method_type VARCHAR(100) NOT NULL DEFAULT '',
            payment_currency VARCHAR(20) NOT NULL DEFAULT '',
            amount_received DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            payment_received_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY stripe_payment_intent_id (stripe_payment_intent_id),
            KEY user_id (user_id)
        ) {$charset_collate};";

        $sql_cards = "CREATE TABLE {$cards_table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            order_id BIGINT UNSIGNED NOT NULL,
            row_id VARCHAR(100) NOT NULL DEFAULT '',
            card_post_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
            title VARCHAR(255) NOT NULL DEFAULT '',
            type VARCHAR(255) NOT NULL DEFAULT '',
            player_name VARCHAR(255) NOT NULL DEFAULT '',
            year VARCHAR(50) NOT NULL DEFAULT '',
            card_number VARCHAR(100) NOT NULL DEFAULT '',
            declared_value DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            encapsulate TINYINT(1) NOT NULL DEFAULT 0,
            oversized TINYINT(1) NOT NULL DEFAULT 0,
            authentic TINYINT(1) NOT NULL DEFAULT 0,
            selected_service VARCHAR(255) NOT NULL DEFAULT '',
            image_front_url TEXT NULL,
            image_back_url TEXT NULL,
            is_custom TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY order_id (order_id)
        ) {$charset_collate};";

        dbDelta($sql_orders);
        dbDelta($sql_cards);
    }
}