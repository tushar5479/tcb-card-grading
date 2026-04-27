<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Stripe_Settings {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'menu']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
    }

    public static function menu() {
        add_options_page(
            'SGC Stripe Settings',
            'SGC Stripe',
            'manage_options',
            'sgc-stripe-settings',
            [__CLASS__, 'render_page']
        );
    }

    public static function register_settings() {
        register_setting(
            'sgc_stripe_settings_group',
            'sgc_stripe_settings',
            [__CLASS__, 'sanitize_settings']
        );

        add_settings_section(
            'sgc_stripe_main_section',
            'Stripe Configuration',
            '__return_false',
            'sgc-stripe-settings'
        );

        $fields = [
            'test_mode'               => 'Enable Test Mode',
            'test_publishable_key'    => 'Test Publishable Key',
            'test_secret_key'         => 'Test Secret Key',
            'test_webhook_secret'     => 'Test Webhook Secret',
            'live_publishable_key'    => 'Live Publishable Key',
            'live_secret_key'         => 'Live Secret Key',
            'live_webhook_secret'     => 'Live Webhook Secret',
            'currency'                => 'Currency',
        ];

        foreach ($fields as $key => $label) {
            add_settings_field(
                $key,
                $label,
                [__CLASS__, 'render_field'],
                'sgc-stripe-settings',
                'sgc_stripe_main_section',
                ['key' => $key, 'label' => $label]
            );
        }
    }

    public static function sanitize_settings($input) {
        $input = is_array($input) ? $input : [];

        return [
            'test_mode'            => !empty($input['test_mode']) ? 1 : 0,
            'test_publishable_key' => isset($input['test_publishable_key']) ? trim(sanitize_text_field($input['test_publishable_key'])) : '',
            'test_secret_key'      => isset($input['test_secret_key']) ? trim(sanitize_text_field($input['test_secret_key'])) : '',
            'test_webhook_secret'  => isset($input['test_webhook_secret']) ? trim(sanitize_text_field($input['test_webhook_secret'])) : '',
            'live_publishable_key' => isset($input['live_publishable_key']) ? trim(sanitize_text_field($input['live_publishable_key'])) : '',
            'live_secret_key'      => isset($input['live_secret_key']) ? trim(sanitize_text_field($input['live_secret_key'])) : '',
            'live_webhook_secret'  => isset($input['live_webhook_secret']) ? trim(sanitize_text_field($input['live_webhook_secret'])) : '',
            'currency'             => isset($input['currency']) ? strtolower(trim(sanitize_text_field($input['currency']))) : 'usd',
        ];
    }

    public static function render_field($args) {
        $opts  = get_option('sgc_stripe_settings', []);
        $key   = $args['key'];
        $value = isset($opts[$key]) ? $opts[$key] : '';

        if ($key === 'test_mode') {
            echo '<label><input type="checkbox" name="sgc_stripe_settings[test_mode]" value="1" ' . checked(!empty($value), true, false) . '> Use Stripe Test Mode</label>';
            return;
        }

        if ($key === 'currency') {
            echo '<input type="text" name="sgc_stripe_settings[currency]" value="' . esc_attr($value ?: 'usd') . '" class="regular-text" maxlength="3">';
            return;
        }

        $type = in_array($key, ['test_secret_key', 'live_secret_key', 'test_webhook_secret', 'live_webhook_secret'], true) ? 'password' : 'text';

        echo '<input type="' . esc_attr($type) . '" name="sgc_stripe_settings[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text">';
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1>SGC Stripe Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('sgc_stripe_settings_group');
                do_settings_sections('sgc-stripe-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public static function get_settings() {
        $opts = get_option('sgc_stripe_settings', []);
        $is_test = !empty($opts['test_mode']);

        return [
            'test_mode'       => $is_test,
            'publishable_key' => $is_test ? ($opts['test_publishable_key'] ?? '') : ($opts['live_publishable_key'] ?? ''),
            'secret_key'      => $is_test ? ($opts['test_secret_key'] ?? '') : ($opts['live_secret_key'] ?? ''),
            'webhook_secret'  => $is_test ? ($opts['test_webhook_secret'] ?? '') : ($opts['live_webhook_secret'] ?? ''),
            'currency'        => !empty($opts['currency']) ? strtolower($opts['currency']) : 'usd',
        ];
    }
}