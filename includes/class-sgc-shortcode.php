<?php
if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('SGC_Shortcode')) {
    return;
}

class SGC_Shortcode {

    public static function init() {
        add_shortcode('sgc_checkout_flow', [__CLASS__, 'render_checkout_flow']);

        add_shortcode('sgc_step_1_service_level', [__CLASS__, 'render_step_1']);
        add_shortcode('sgc_step_2_add_cards', [__CLASS__, 'render_step_2']);
        add_shortcode('sgc_step_3_review', [__CLASS__, 'render_step_3']);
        add_shortcode('sgc_step_4_return_shipping', [__CLASS__, 'render_step_4']);
        add_shortcode('sgc_step_5_review_shipping', [__CLASS__, 'render_step_5']);
        add_shortcode('sgc_step_7_payment', [__CLASS__, 'render_step_7']);
        add_shortcode('sgc_step_8_confirm', [__CLASS__, 'render_step_8']);

        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function enqueue_assets() {
        wp_register_style(
            'sgc-frontend-style',
            SGC_CG_URL . 'assets/css/frontend.css',
            [],
            SGC_CG_VERSION
        );

        if (!wp_script_is('stripe-js', 'registered')) {
            wp_register_script(
                'stripe-js',
                'https://js.stripe.com/v3/',
                [],
                null,
                true
            );
        }

        wp_register_script(
            'sgc-frontend-script',
            SGC_CG_URL . 'assets/js/frontend.js',
            ['jquery', 'stripe-js'],
            SGC_CG_VERSION,
            true
        );
    }

    public static function get_current_flow_url() {
        global $wp;

        $request_path = isset($wp->request) ? $wp->request : '';
        return home_url('/' . ltrim($request_path, '/'));
    }

    private static function build_step_url($step, $base_url, $extra_args = []) {
        $args = array_merge(['sgc_step' => (int) $step], $extra_args);
        return esc_url(add_query_arg($args, $base_url));
    }

    private static function localize_frontend($args = []) {
        $defaults = [
            'ajaxUrl'              => admin_url('admin-ajax.php'),
            'nonce'                => wp_create_nonce('sgc_nonce'),
            'currentStep'          => 1,
            'flowBaseUrl'          => '',
            'step1NextUrl'         => '',
            'nextStepUrl'          => '',
            'step2BackUrl'         => '',
            'step3BackUrl'         => '',
            'step3NextUrl'         => '',
            'step4BackUrl'         => '',
            'step4NextUrl'         => '',
            'step5BackUrl'         => '',
            'step5NextUrl'         => '',
            'step7BackUrl'         => '',
            'step7NextUrl'         => '',
            'stripePublishableKey' => '',
            'selectPlanMessage'    => __('Please complete required fields before continuing.', 'sgc-card-grading'),
        ];

        $data = wp_parse_args($args, $defaults);

        wp_localize_script('sgc-frontend-script', 'sgcStep2Data', $data);
    }

    private static function maybe_enqueue_for_step($step) {
        wp_enqueue_style('sgc-frontend-style');
        wp_enqueue_script('sgc-frontend-script');

        if ((int) $step === 7) {
            wp_enqueue_script('stripe-js');
        }
    }

    private static function get_template_for_step($step) {
        $map = [
            1 => 'step-1-service-level.php',
            2 => 'step-2-add-cards.php',
            3 => 'step-3-review.php',
            4 => 'step-4-return-shipping.php',
            5 => 'step-5-review-shipping.php',
            7 => 'step-7-payment.php',
            8 => 'step-8-confirm.php',
        ];

        if (!isset($map[$step])) {
            return '';
        }

        return SGC_CG_PATH . 'templates/' . $map[$step];
    }

    public static function render_checkout_flow($atts = []) {
        $step = isset($_GET['sgc_step']) ? absint($_GET['sgc_step']) : 1;
        if ($step < 1) {
            $step = 1;
        }

        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        $localize = [
            'currentStep'          => $step,
            'flowBaseUrl'          => esc_url($base_url),
            'step1NextUrl'         => self::build_step_url(2, $base_url),
            'nextStepUrl'          => self::build_step_url(3, $base_url),
            'step2BackUrl'         => self::build_step_url(1, $base_url),
            'step3BackUrl'         => self::build_step_url(2, $base_url),
            'step3NextUrl'         => self::build_step_url(4, $base_url),
            'step4BackUrl'         => self::build_step_url(3, $base_url),
            'step4NextUrl'         => self::build_step_url(5, $base_url),
            'step5BackUrl'         => self::build_step_url(4, $base_url),
            'step5NextUrl'         => self::build_step_url(7, $base_url),
            'step7BackUrl'         => self::build_step_url(5, $base_url),
            'step7NextUrl'         => self::build_step_url(8, $base_url),
            'stripePublishableKey' => class_exists('SGC_Stripe_Service') ? SGC_Stripe_Service::get_publishable_key() : '',
        ];

        self::localize_frontend($localize);

        $template = self::get_template_for_step($step);

        ob_start();

        if ($template && file_exists($template)) {
            include $template;
        } else {
            echo '<div class="sgc-step-placeholder" style="padding:40px;font-size:24px;font-weight:700;">Step ' . esc_html($step) . ' coming soon.</div>';
        }

        return ob_get_clean();
    }

    public static function render_step_1($atts = []) {
        $step = 1;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep'  => $step,
            'flowBaseUrl'  => esc_url($base_url),
            'step1NextUrl' => self::build_step_url(2, $base_url),
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-1-service-level.php';
        return ob_get_clean();
    }

    public static function render_step_2($atts = []) {
        $step = 2;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep' => $step,
            'flowBaseUrl' => esc_url($base_url),
            'nextStepUrl' => self::build_step_url(3, $base_url),
            'step2BackUrl'=> self::build_step_url(1, $base_url),
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-2-add-cards.php';
        return ob_get_clean();
    }

    public static function render_step_3($atts = []) {
        $step = 3;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep' => $step,
            'flowBaseUrl' => esc_url($base_url),
            'step3BackUrl'=> self::build_step_url(2, $base_url),
            'step3NextUrl'=> self::build_step_url(4, $base_url),
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-3-review.php';
        return ob_get_clean();
    }

    public static function render_step_4($atts = []) {
        $step = 4;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep' => $step,
            'flowBaseUrl' => esc_url($base_url),
            'step4BackUrl'=> self::build_step_url(3, $base_url),
            'step4NextUrl'=> self::build_step_url(5, $base_url),
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-4-return-shipping.php';
        return ob_get_clean();
    }

    public static function render_step_5($atts = []) {
        $step = 5;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep' => $step,
            'flowBaseUrl' => esc_url($base_url),
            'step5BackUrl'=> self::build_step_url(4, $base_url),
            'step5NextUrl'=> self::build_step_url(7, $base_url),
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-5-review-shipping.php';
        return ob_get_clean();
    }

    public static function render_step_7($atts = []) {
        $step = 7;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep'          => $step,
            'flowBaseUrl'          => esc_url($base_url),
            'step7BackUrl'         => self::build_step_url(5, $base_url),
            'step7NextUrl'         => self::build_step_url(8, $base_url),
            'stripePublishableKey' => class_exists('SGC_Stripe_Service') ? SGC_Stripe_Service::get_publishable_key() : '',
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-7-payment.php';
        return ob_get_clean();
    }

    public static function render_step_8($atts = []) {
        $step = 8;
        self::maybe_enqueue_for_step($step);

        $base_url = self::get_current_flow_url();

        self::localize_frontend([
            'currentStep' => $step,
            'flowBaseUrl' => esc_url($base_url),
        ]);

        ob_start();
        include SGC_CG_PATH . 'templates/step-8-confirm.php';
        return ob_get_clean();
    }
}