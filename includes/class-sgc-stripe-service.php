<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Stripe_Service {

    const STRIPE_API_BASE = 'https://api.stripe.com/v1/';

    public static function get_settings() {
        if (!class_exists('SGC_Stripe_Settings')) {
            return [
                'test_mode'       => true,
                'publishable_key' => '',
                'secret_key'      => '',
                'webhook_secret'  => '',
                'currency'        => 'usd',
            ];
        }

        return SGC_Stripe_Settings::get_settings();
    }

    public static function get_secret_key() {
        $settings = self::get_settings();
        return !empty($settings['secret_key']) ? trim($settings['secret_key']) : '';
    }

    public static function get_publishable_key() {
        $settings = self::get_settings();
        return !empty($settings['publishable_key']) ? trim($settings['publishable_key']) : '';
    }

    public static function get_currency() {
        $settings = self::get_settings();
        return !empty($settings['currency']) ? strtolower(trim($settings['currency'])) : 'usd';
    }

    public static function is_configured() {
        return self::get_secret_key() !== '' && self::get_publishable_key() !== '';
    }

    public static function request($method, $endpoint, $body = [], $headers = []) {
        $secret = self::get_secret_key();

        if ($secret === '') {
            return new WP_Error('stripe_missing_secret', __('Stripe secret key is missing.', 'sgc-card-grading'));
        }

        $endpoint = ltrim($endpoint, '/');

        $default_headers = [
            'Authorization' => 'Bearer ' . $secret,
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];

        $args = [
            'method'  => strtoupper($method),
            'headers' => array_merge($default_headers, $headers),
            'timeout' => 45,
        ];

        if (!empty($body)) {
            $args['body'] = http_build_query($body);
        }

        $response = wp_remote_request(self::STRIPE_API_BASE . $endpoint, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $raw  = wp_remote_retrieve_body($response);
        $json = json_decode($raw, true);

        if ($code < 200 || $code >= 300) {
            $message = __('Stripe API error.', 'sgc-card-grading');

            if (!empty($json['error']['message'])) {
                $message = $json['error']['message'];
            } elseif (!empty($raw)) {
                $message = $raw;
            }

            return new WP_Error('stripe_api_error', $message, [
                'status_code' => $code,
                'response'    => $json,
            ]);
        }

        if (!is_array($json)) {
            return new WP_Error('stripe_invalid_response', __('Invalid response from Stripe.', 'sgc-card-grading'));
        }

        return $json;
    }

    public static function create_or_update_payment_intent($draft) {
        if (!class_exists('SGC_Ajax_Helper')) {
            return new WP_Error('sgc_missing_helper', __('Payment summary helper is missing.', 'sgc-card-grading'));
        }

        $summary = SGC_Ajax_Helper::get_payment_summary_for_stripe($draft);

        $amount = isset($summary['grand_total']) ? (float) $summary['grand_total'] : 0;
        $amount = (int) round($amount * 100);

        if ($amount < 50) {
            return new WP_Error('stripe_invalid_amount', __('Payment amount is invalid.', 'sgc-card-grading'));
        }

        $order_number = !empty($draft['order_number']) ? sanitize_text_field($draft['order_number']) : ('TCB-' . time());
        $draft_key    = method_exists('SGC_Order_Draft', 'get_key') ? SGC_Order_Draft::get_key() : '';

        // --- FIX FOR CONFLICT ERROR ---
        // পুরনো ড্রাফট সেশনের existing ID ইগনোর করে দিচ্ছি। 
        $existing_id = ''; 

        $payload = [
            'amount'                             => $amount,
            'currency'                           => self::get_currency(),
            'payment_method_types[0]'            => 'card', // ম্যানুয়াল কার্ড পেমেন্ট মেথড
            'metadata[order_number]'             => $order_number,
            'metadata[draft_key]'                => $draft_key,
            'metadata[tier_label]'               => !empty($summary['tier_label']) ? $summary['tier_label'] : '',
            'metadata[shipping_label]'           => !empty($summary['shipping_label']) ? $summary['shipping_label'] : '',
            'metadata[total_cards]'              => !empty($summary['total_cards']) ? (int) $summary['total_cards'] : 0,
        ];

        if (!empty($draft['customer_email'])) {
            $payload['receipt_email'] = sanitize_email($draft['customer_email']);
        }

        // Idempotency Key-তে uniqid() যুক্ত করা হয়েছে যাতে স্ট্রাইপ আগের এরর দেওয়া সেশন রিটার্ন না করে সম্পূর্ণ নতুন রিকোয়েস্ট তৈরি করে
        $idempotency_key = 'sgc_pi_' . md5($order_number . '|' . $amount . '|' . uniqid());

        $result = self::request(
            'POST',
            'payment_intents',
            $payload,
            ['Idempotency-Key' => $idempotency_key]
        );

        return $result;
    }

    public static function retrieve_payment_intent($payment_intent_id) {
        $payment_intent_id = trim((string) $payment_intent_id);

        if ($payment_intent_id === '') {
            return new WP_Error('stripe_missing_payment_intent', __('Payment intent ID is missing.', 'sgc-card-grading'));
        }

        return self::request('GET', 'payment_intents/' . rawurlencode($payment_intent_id));
    }

    public static function cancel_payment_intent($payment_intent_id) {
        $payment_intent_id = trim((string) $payment_intent_id);

        if ($payment_intent_id === '') {
            return new WP_Error('stripe_missing_payment_intent', __('Payment intent ID is missing.', 'sgc-card-grading'));
        }

        return self::request('POST', 'payment_intents/' . rawurlencode($payment_intent_id) . '/cancel');
    }
}