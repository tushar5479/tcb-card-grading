<?php
/**
 * Plugin Name: TCB Card Grading
 * Description: Multi-step card grading workflow - Step 2 Add Cards
 * Version: 1.0.1
 * Author: TCB DEV TEAM
 * Text Domain: sgc-card-grading
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SGC_CG_VERSION', '1.0.1');
define('SGC_CG_PATH', plugin_dir_path(__FILE__));
define('SGC_CG_URL', plugin_dir_url(__FILE__));

require_once SGC_CG_PATH . 'includes/class-sgc-activator.php';
require_once SGC_CG_PATH . 'includes/class-sgc-card-cpt.php';
require_once SGC_CG_PATH . 'includes/class-sgc-customer-card-cpt.php';
require_once SGC_CG_PATH . 'includes/class-sgc-order-draft.php';
require_once SGC_CG_PATH . 'includes/helpers-payment.php';
require_once SGC_CG_PATH . 'includes/class-sgc-stripe-settings.php';
require_once SGC_CG_PATH . 'includes/class-sgc-stripe-service.php';
require_once SGC_CG_PATH . 'includes/class-sgc-stripe-webhook.php';
require_once SGC_CG_PATH . 'includes/class-sgc-ajax.php';
require_once SGC_CG_PATH . 'includes/class-sgc-shortcode.php';
require_once SGC_CG_PATH . 'includes/class-sgc-order-db.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-sgc-customer-order-cpt.php';

register_activation_hook(__FILE__, ['SGC_Activator', 'activate']);
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

add_action('init', ['SGC_Card_CPT', 'register']);
add_action('init', ['SGC_Customer_Card_CPT', 'register']);
add_action('init', ['SGC_Shortcode', 'init']);

add_action('plugins_loaded', function () {
    SGC_Ajax::init();
    SGC_Stripe_Settings::init();
    SGC_Stripe_Webhook::init();
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'sgc-frontend-style',
        SGC_CG_URL . 'assets/css/frontend.css',
        [],
        SGC_CG_VERSION
    );

    wp_enqueue_script(
        'stripe-js',
        'https://js.stripe.com/v3/',
        [],
        null,
        true
    );

    wp_enqueue_script(
        'sgc-frontend-script',
        SGC_CG_URL . 'assets/js/frontend.js',
        ['jquery', 'stripe-js'],
        SGC_CG_VERSION,
        true
    );

    wp_localize_script('sgc-frontend-script', 'sgcStep2Data', [
        'ajaxUrl'              => admin_url('admin-ajax.php'),
        'nonce'                => wp_create_nonce('sgc_nonce'),
        'stripePublishableKey' => class_exists('SGC_Stripe_Service') ? SGC_Stripe_Service::get_publishable_key() : '',

        'step1NextUrl'         => add_query_arg('sgc_step', '2', home_url('/form/')),
        'nextStepUrl'          => add_query_arg('sgc_step', '3', home_url('/form/')),
        'step2BackUrl'         => add_query_arg('sgc_step', '1', home_url('/form/')),
        'step3BackUrl'         => add_query_arg('sgc_step', '2', home_url('/form/')),
        'step3NextUrl'         => add_query_arg('sgc_step', '4', home_url('/form/')),
        'step4BackUrl'         => add_query_arg('sgc_step', '3', home_url('/form/')),
        'step4NextUrl'         => add_query_arg('sgc_step', '5', home_url('/form/')),
        'step5BackUrl'         => add_query_arg('sgc_step', '4', home_url('/form/')),
        'step5NextUrl'         => add_query_arg('sgc_step', '7', home_url('/form/')),
        'step7BackUrl'         => add_query_arg('sgc_step', '5', home_url('/form/')),
        'selectPlanMessage'    => __('Please complete required fields before continuing.', 'sgc-card-grading'),
    ]);
});


add_action('admin_init', 'sgc_update_order_cards_table_schema');
function sgc_update_order_cards_table_schema() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sgc_order_cards';
    
    
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name) {
        
        $missing_columns = [];
        $columns = $wpdb->get_results("SHOW COLUMNS FROM {$table_name}", ARRAY_A);
        $existing_columns = wp_list_pluck($columns, 'Field');

     
        $expected_columns = [
            'player_name'     => "VARCHAR(255) DEFAULT ''",
            'year'            => "VARCHAR(50) DEFAULT ''",
            'card_number'     => "VARCHAR(100) DEFAULT ''",
            'image_front_url' => "TEXT",
            'image_back_url'  => "TEXT",
            'is_custom'       => "TINYINT(1) DEFAULT 0"
        ];

        foreach ($expected_columns as $col => $type) {
            if (!in_array($col, $existing_columns, true)) {
                $missing_columns[] = "ADD COLUMN {$col} {$type}";
            }
        }

        if (!empty($missing_columns)) {
            $alter_query = "ALTER TABLE {$table_name} " . implode(', ', $missing_columns);
            $wpdb->query($alter_query);
        }
    }
}