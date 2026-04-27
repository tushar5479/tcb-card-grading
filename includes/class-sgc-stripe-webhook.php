<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Stripe_Webhook {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }

    public static function register_routes() {
        register_rest_route('sgc/v1', '/stripe-webhook', [
            'methods'             => 'POST',
            'callback'            => [__CLASS__, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function handle(WP_REST_Request $request) {
        $payload  = $request->get_body();
        $sig      = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? wp_unslash($_SERVER['HTTP_STRIPE_SIGNATURE']) : '';
        $settings = SGC_Stripe_Settings::get_settings();
        $secret   = !empty($settings['webhook_secret']) ? $settings['webhook_secret'] : '';

        if (empty($secret)) {
            return new WP_REST_Response(['error' => 'Webhook secret not configured'], 400);
        }

        if (empty($payload) || empty($sig)) {
            return new WP_REST_Response(['error' => 'Missing payload or signature'], 400);
        }

        $timestamp = '';
        $signature = '';

        foreach (explode(',', $sig) as $part) {
            $pieces = array_pad(explode('=', trim($part), 2), 2, '');
            $k = $pieces[0];
            $v = $pieces[1];

            if ($k === 't') {
                $timestamp = $v;
            }

            if ($k === 'v1') {
                $signature = $v;
            }
        }

        if ($timestamp === '' || $signature === '') {
            return new WP_REST_Response(['error' => 'Invalid Stripe signature header'], 400);
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return new WP_REST_Response(['error' => 'Expired Stripe signature'], 400);
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected       = hash_hmac('sha256', $signed_payload, $secret);

        if (!hash_equals($expected, $signature)) {
            return new WP_REST_Response(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if (!is_array($event) || empty($event['type'])) {
            return new WP_REST_Response(['error' => 'Invalid payload'], 400);
        }

        switch ($event['type']) {
            case 'payment_intent.succeeded':
                self::handle_payment_intent_succeeded($event);
                break;

            case 'payment_intent.payment_failed':
                self::handle_payment_intent_failed($event);
                break;
        }

        return new WP_REST_Response(['received' => true], 200);
    }

    private static function handle_payment_intent_succeeded($event) {
        if (empty($event['data']['object']) || !is_array($event['data']['object'])) {
            return;
        }

        $pi = $event['data']['object'];

        if (empty($pi['id'])) {
            return;
        }

        $found = SGC_Order_Draft::find_draft_by_payment_intent_id($pi['id']);

        if (!$found || empty($found['option_key']) || !is_array($found['draft'])) {
            return;
        }

        $draft = $found['draft'];

        $payment_method_type = '';
        if (!empty($pi['payment_method_types']) && is_array($pi['payment_method_types'])) {
            $payment_method_type = (string) $pi['payment_method_types'][0];
        }

        $draft['payment_status']        = 'paid';
        $draft['payment_method_type']   = $payment_method_type;
        $draft['payment_intent_status'] = !empty($pi['status']) ? (string) $pi['status'] : 'succeeded';
        $draft['payment_intent_id']     = !empty($pi['id']) ? (string) $pi['id'] : '';
        $draft['payment_received_at']   = current_time('mysql');

        if (!empty($pi['amount_received'])) {
            $draft['amount_received'] = ((float) $pi['amount_received']) / 100;
        }

        if (!empty($pi['currency'])) {
            $draft['payment_currency'] = strtoupper((string) $pi['currency']);
        }

        if (!empty($pi['metadata']['order_number']) && empty($draft['order_number'])) {
            $draft['order_number'] = sanitize_text_field($pi['metadata']['order_number']);
        }

        SGC_Order_Draft::save_data_by_key($found['option_key'], $draft);
    }

    private static function handle_payment_intent_failed($event) {
        if (empty($event['data']['object']) || !is_array($event['data']['object'])) {
            return;
        }

        $pi = $event['data']['object'];

        if (empty($pi['id'])) {
            return;
        }

        $found = SGC_Order_Draft::find_draft_by_payment_intent_id($pi['id']);

        if (!$found || empty($found['option_key']) || !is_array($found['draft'])) {
            return;
        }

        $draft = $found['draft'];

        $draft['payment_status']        = 'failed';
        $draft['payment_intent_status'] = !empty($pi['status']) ? (string) $pi['status'] : 'failed';

        if (!empty($pi['last_payment_error']['message'])) {
            $draft['payment_error_message'] = (string) $pi['last_payment_error']['message'];
        }

        SGC_Order_Draft::save_data_by_key($found['option_key'], $draft);
    }
}