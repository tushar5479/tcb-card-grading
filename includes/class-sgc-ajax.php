<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Ajax {

    public static function init() {
        add_action('wp_ajax_sgc_search_cards', [__CLASS__, 'search_cards']);
        add_action('wp_ajax_nopriv_sgc_search_cards', [__CLASS__, 'search_cards']);

        add_action('wp_ajax_sgc_get_selected_cards', [__CLASS__, 'get_selected_cards']);
        add_action('wp_ajax_nopriv_sgc_get_selected_cards', [__CLASS__, 'get_selected_cards']);

        add_action('wp_ajax_sgc_add_selected_card', [__CLASS__, 'add_selected_card']);
        add_action('wp_ajax_nopriv_sgc_add_selected_card', [__CLASS__, 'add_selected_card']);

        add_action('wp_ajax_sgc_remove_selected_card', [__CLASS__, 'remove_selected_card']);
        add_action('wp_ajax_nopriv_sgc_remove_selected_card', [__CLASS__, 'remove_selected_card']);

        add_action('wp_ajax_sgc_update_selected_card', [__CLASS__, 'update_selected_card']);
        add_action('wp_ajax_nopriv_sgc_update_selected_card', [__CLASS__, 'update_selected_card']);

        add_action('wp_ajax_sgc_save_service_level', [__CLASS__, 'save_service_level']);
        add_action('wp_ajax_nopriv_sgc_save_service_level', [__CLASS__, 'save_service_level']);

        add_action('wp_ajax_sgc_get_service_level', [__CLASS__, 'get_service_level']);
        add_action('wp_ajax_nopriv_sgc_get_service_level', [__CLASS__, 'get_service_level']);

        add_action('wp_ajax_sgc_add_custom_card', [__CLASS__, 'add_custom_card']);
        add_action('wp_ajax_nopriv_sgc_add_custom_card', [__CLASS__, 'add_custom_card']);

        add_action('wp_ajax_sgc_get_shipping_addresses', [__CLASS__, 'get_shipping_addresses']);
        add_action('wp_ajax_nopriv_sgc_get_shipping_addresses', [__CLASS__, 'get_shipping_addresses']);

        add_action('wp_ajax_sgc_get_states_by_country', [__CLASS__, 'get_states_by_country']);
        add_action('wp_ajax_nopriv_sgc_get_states_by_country', [__CLASS__, 'get_states_by_country']);

        add_action('wp_ajax_sgc_save_shipping_address', [__CLASS__, 'save_shipping_address']);
        add_action('wp_ajax_nopriv_sgc_save_shipping_address', [__CLASS__, 'save_shipping_address']);

        add_action('wp_ajax_sgc_select_shipping_address', [__CLASS__, 'select_shipping_address']);
        add_action('wp_ajax_nopriv_sgc_select_shipping_address', [__CLASS__, 'select_shipping_address']);

        add_action('wp_ajax_sgc_get_shipping_address_detail', [__CLASS__, 'get_shipping_address_detail']);
        add_action('wp_ajax_nopriv_sgc_get_shipping_address_detail', [__CLASS__, 'get_shipping_address_detail']);

        add_action('wp_ajax_sgc_update_shipping_address', [__CLASS__, 'update_shipping_address']);
        add_action('wp_ajax_nopriv_sgc_update_shipping_address', [__CLASS__, 'update_shipping_address']);

        add_action('wp_ajax_sgc_delete_shipping_address', [__CLASS__, 'delete_shipping_address']);
        add_action('wp_ajax_nopriv_sgc_delete_shipping_address', [__CLASS__, 'delete_shipping_address']);

        add_action('wp_ajax_sgc_get_step5_review', [__CLASS__, 'get_step5_review']);
        add_action('wp_ajax_nopriv_sgc_get_step5_review', [__CLASS__, 'get_step5_review']);

        add_action('wp_ajax_sgc_save_shipping_method', [__CLASS__, 'save_shipping_method']);
        add_action('wp_ajax_nopriv_sgc_save_shipping_method', [__CLASS__, 'save_shipping_method']);

        add_action('wp_ajax_sgc_create_payment_intent', [__CLASS__, 'create_payment_intent']);
        add_action('wp_ajax_nopriv_sgc_create_payment_intent', [__CLASS__, 'create_payment_intent']);

        add_action('wp_ajax_sgc_payment_success', [__CLASS__, 'payment_success']);
        add_action('wp_ajax_nopriv_sgc_payment_success', [__CLASS__, 'payment_success']);
    }

    private static function get_tier_map() {
        return [
            'standard' => [
                'label' => 'Standard',
                'price' => 15,
                'days'  => '15–20 business days',
            ],
            'pristine-10' => [
                'label' => 'Pristine 10',
                'price' => 25,
                'days'  => '7–10 business days',
            ],
            'elite-rare' => [
                'label' => 'Elite / Rare',
                'price' => 65,
                'days'  => '5–10 business days',
            ],
            'vintage' => [
                'label' => 'Vintage',
                'price' => 49,
                'days'  => '10–15 business days',
            ],
            'ultra-1of1' => [
                'label' => 'Ultra / 1 of 1',
                'price' => 120,
                'days'  => '2–5 business days',
            ],
        ];
    }

    private static function get_selected_tier_data($draft = null) {
        if ($draft === null) {
            $draft = SGC_Order_Draft::get_data();
        }

        $map = self::get_tier_map();
        $tier = !empty($draft['service_level']) ? $draft['service_level'] : 'pristine-10';

        if (!isset($map[$tier])) {
            $tier = 'pristine-10';
        }

        return [
            'key'   => $tier,
            'label' => $map[$tier]['label'],
            'price' => $map[$tier]['price'],
            'days'  => $map[$tier]['days'],
        ];
    }

    private static function get_shipping_methods() {
        return [
            'usps_priority_mail' => [
                'key'   => 'usps_priority_mail',
                'label' => 'USPS Priority Mail',
                'price' => 15,
                'logo'  => SGC_CG_URL . 'assets/images/usps.png',
            ],
            'fedex_ground' => [
                'key'   => 'fedex_ground',
                'label' => 'FedEx Ground',
                'price' => 25,
                'logo'  => SGC_CG_URL . 'assets/images/fedex.png',
            ],
            'fedex_2_day' => [
                'key'   => 'fedex_2_day',
                'label' => 'FedEx 2-Day',
                'price' => 45,
                'logo'  => SGC_CG_URL . 'assets/images/fedex.png',
            ],
            'fedex_standard_overnight' => [
                'key'   => 'fedex_standard_overnight',
                'label' => 'FedEx Standard Overnight',
                'price' => 60,
                'logo'  => SGC_CG_URL . 'assets/images/fedex.png',
            ],
        ];
    }

    private static function get_selected_shipping_method($draft = null) {
        if ($draft === null) {
            $draft = SGC_Order_Draft::get_data();
        }

        $methods = self::get_shipping_methods();
        $selected = !empty($draft['shipping_method']) ? $draft['shipping_method'] : 'usps_priority_mail';

        if (!isset($methods[$selected])) {
            $selected = 'usps_priority_mail';
        }

        return $methods[$selected];
    }

    private static function get_countries_list() {
        if (class_exists('WooCommerce') && function_exists('WC')) {
            $countries = WC()->countries->get_countries();
            if (!empty($countries)) {
                return $countries;
            }
        }

        return [
            'US' => 'United States',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'GB' => 'United Kingdom',
            'BD' => 'Bangladesh',
            'IN' => 'India',
            'AE' => 'United Arab Emirates',
            'SA' => 'Saudi Arabia',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
        ];
    }

    private static function get_states_list($country_code = '') {
        if (class_exists('WooCommerce') && function_exists('WC')) {
            $states = WC()->countries->get_states($country_code);
            if (is_array($states) && !empty($states)) {
                return $states;
            }
        }

        $fallback = [
            'US' => [
                'AL' => 'Alabama',
                'CA' => 'California',
                'FL' => 'Florida',
                'GA' => 'Georgia',
                'NY' => 'New York',
                'TX' => 'Texas',
            ],
            'CA' => [
                'AB' => 'Alberta',
                'BC' => 'British Columbia',
                'ON' => 'Ontario',
                'QC' => 'Quebec',
            ],
            'AU' => [
                'NSW' => 'New South Wales',
                'QLD' => 'Queensland',
                'VIC' => 'Victoria',
                'WA'  => 'Western Australia',
            ],
            'BD' => [
                'DHA' => 'Dhaka',
                'CTG' => 'Chattogram',
                'KHU' => 'Khulna',
                'RAJ' => 'Rajshahi',
            ],
            'IN' => [
                'DL' => 'Delhi',
                'MH' => 'Maharashtra',
                'WB' => 'West Bengal',
                'KA' => 'Karnataka',
            ],
            'GB' => [
                'ENG' => 'England',
                'SCT' => 'Scotland',
                'WLS' => 'Wales',
                'NIR' => 'Northern Ireland',
            ],
        ];

        return isset($fallback[$country_code]) ? $fallback[$country_code] : [];
    }

    private static function get_shipping_summary($draft) {
        $selected_cards   = !empty($draft['selected_cards']) && is_array($draft['selected_cards']) ? $draft['selected_cards'] : [];
        $tier_data        = self::get_selected_tier_data($draft);
        $shipping_method  = self::get_selected_shipping_method($draft);

        $total_cards = count($selected_cards);
        $total_dv    = 0;

        foreach ($selected_cards as $card) {
            $total_dv += isset($card['declared_value']) ? (float) $card['declared_value'] : 0;
        }

        $grading_fee  = $tier_data['price'] * $total_cards;
        $shipping_fee = $shipping_method['price'];
        $grand_total  = $grading_fee + $shipping_fee;

        return [
            'tier_label'              => $tier_data['label'],
            'tier_days'               => $tier_data['days'],
            'tier_price'              => $tier_data['price'],
            'total_cards'             => $total_cards,
            'total_dv'                => $total_dv,
            'grading_fee'             => $grading_fee,
            'shipping_fee'            => $shipping_fee,
            'grand_total'             => $grand_total,
            'selected_shipping_key'   => $shipping_method['key'],
            'selected_shipping_label' => $shipping_method['label'],
        ];
    }

    public static function search_cards() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $keyword  = isset($_POST['keyword']) ? sanitize_text_field(wp_unslash($_POST['keyword'])) : '';
        $page     = isset($_POST['page']) ? max(1, absint($_POST['page'])) : 1;
        $per_page = 6;

        if ($keyword === '') {
            wp_send_json_success([
                'items'    => [],
                'has_more' => false,
            ]);
        }

        $ids = get_posts([
            'post_type'      => 'sgc_card',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'fields'         => 'ids',
        ]);

        $matched_ids = [];
        $needle = mb_strtolower($keyword);

        foreach ($ids as $id) {
            $title     = mb_strtolower((string) get_the_title($id));
            $list_name = mb_strtolower((string) get_post_meta($id, '_sgc_list_name', true));
            $type      = mb_strtolower((string) get_post_meta($id, '_sgc_card_type', true));

            $haystack = $title . ' ' . $list_name . ' ' . $type;

            if (strpos($haystack, $needle) !== false) {
                $matched_ids[] = $id;
            }
        }

        $offset    = ($page - 1) * $per_page;
        $paged_ids = array_slice($matched_ids, $offset, $per_page);
        $items     = [];

        foreach ($paged_ids as $post_id) {
            $services = get_post_meta($post_id, '_sgc_services', true);
            if (!is_array($services) || empty($services)) {
                $services = ['Raw Card Grading'];
            }

            $items[] = [
                'id'        => $post_id,
                'title'     => get_the_title($post_id),
                'list_name' => get_post_meta($post_id, '_sgc_list_name', true),
                'type'      => get_post_meta($post_id, '_sgc_card_type', true),
                'services'  => array_values($services),
            ];
        }

        $has_more = count($matched_ids) > ($offset + $per_page);

        wp_send_json_success([
            'items'    => $items,
            'has_more' => $has_more,
        ]);
    }

    public static function get_selected_cards() {
        check_ajax_referer('sgc_nonce', 'nonce');
        $draft = SGC_Order_Draft::get_data();
        wp_send_json_success(self::format_selected_cards_response($draft));
    }

    public static function add_selected_card() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $card_id = isset($_POST['card_id']) ? absint($_POST['card_id']) : 0;

        if (!$card_id) {
            wp_send_json_error([
                'message' => __('Invalid card ID.', 'sgc-card-grading'),
            ]);
        }

        $draft = SGC_Order_Draft::get_data();

        if (empty($draft['selected_cards']) || !is_array($draft['selected_cards'])) {
            $draft['selected_cards'] = [];
        }

        foreach ($draft['selected_cards'] as $existing_card) {
            if (!empty($existing_card['card_id']) && (int) $existing_card['card_id'] === $card_id) {
                wp_send_json_success(self::format_selected_cards_response($draft));
            }
        }

        $services = get_post_meta($card_id, '_sgc_services', true);
        if (!is_array($services) || empty($services)) {
            $services = ['Raw Card Grading'];
        }

        $draft['selected_cards'][] = [
            'row_id'          => wp_generate_uuid4(),
            'card_id'         => $card_id,
            'title'           => get_the_title($card_id),
            'type'            => get_post_meta($card_id, '_sgc_card_type', true),
            'player_name'     => '',
            'year'            => '',
            'card_number'     => '',
            'declared_value'  => 1,
            'encapsulate'     => 0,
            'oversized'       => 0,
            'authentic'       => 0,
            'service'         => $services[0],
            'services'        => array_values($services),
            'image_front_url' => '',
            'image_back_url'  => '',
            'is_custom'       => 0,
        ];

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success(self::format_selected_cards_response($draft));
    }

    public static function add_custom_card() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $player_name    = isset($_POST['player_name']) ? sanitize_text_field(wp_unslash($_POST['player_name'])) : '';
        $year           = isset($_POST['year']) ? sanitize_text_field(wp_unslash($_POST['year'])) : '';
        $card_number    = isset($_POST['card_number']) ? sanitize_text_field(wp_unslash($_POST['card_number'])) : '';
        $declared_value = isset($_POST['declared_value']) ? (float) $_POST['declared_value'] : 0;

        if ($declared_value < 1) {
            wp_send_json_error([
                'message' => __('Declared value must be at least $1.', 'sgc-card-grading'),
            ]);
        }

        $title_parts = array_filter([$year, $player_name, $card_number]);
        $title = !empty($title_parts) ? implode(' - ', $title_parts) : 'Custom Card';

        $image_front_url = '';
        $image_back_url  = '';

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        if (!empty($_FILES['custom_image_front']) && !empty($_FILES['custom_image_front']['name'])) {
            $attachment_id_front = media_handle_upload('custom_image_front', 0);

            if (is_wp_error($attachment_id_front)) {
                wp_send_json_error([
                    'message' => $attachment_id_front->get_error_message(),
                ]);
            }

            $image_front_url = wp_get_attachment_url($attachment_id_front);
        }

        if (!empty($_FILES['custom_image_back']) && !empty($_FILES['custom_image_back']['name'])) {
            $attachment_id_back = media_handle_upload('custom_image_back', 0);

            if (is_wp_error($attachment_id_back)) {
                wp_send_json_error([
                    'message' => $attachment_id_back->get_error_message(),
                ]);
            }

            $image_back_url = wp_get_attachment_url($attachment_id_back);
        }

        $draft = SGC_Order_Draft::get_data();

        if (empty($draft['selected_cards']) || !is_array($draft['selected_cards'])) {
            $draft['selected_cards'] = [];
        }

        $draft['selected_cards'][] = [
            'row_id'          => wp_generate_uuid4(),
            'card_id'         => 0,
            'title'           => $title,
            'type'            => 'Custom Card',
            'player_name'     => $player_name,
            'year'            => $year,
            'card_number'     => $card_number,
            'declared_value'  => $declared_value,
            'encapsulate'     => 0,
            'oversized'       => 0,
            'authentic'       => 0,
            'service'         => 'Raw Card Grading',
            'services'        => ['Raw Card Grading'],
            'image_front_url' => $image_front_url,
            'image_back_url'  => $image_back_url,
            'is_custom'       => 1,
        ];

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success(self::format_selected_cards_response($draft));
    }

    public static function remove_selected_card() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $row_id = isset($_POST['row_id']) ? sanitize_text_field(wp_unslash($_POST['row_id'])) : '';
        $draft  = SGC_Order_Draft::get_data();

        if (!empty($draft['selected_cards']) && is_array($draft['selected_cards'])) {
            $draft['selected_cards'] = array_values(array_filter($draft['selected_cards'], function ($card) use ($row_id) {
                return empty($card['row_id']) || $card['row_id'] !== $row_id;
            }));
        }

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success(self::format_selected_cards_response($draft));
    }

    public static function update_selected_card() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $row_id         = isset($_POST['row_id']) ? sanitize_text_field(wp_unslash($_POST['row_id'])) : '';
        $declared_value = isset($_POST['declared_value']) ? (float) $_POST['declared_value'] : 0;
        $encapsulate    = !empty($_POST['encapsulate']) ? 1 : 0;
        $oversized      = !empty($_POST['oversized']) ? 1 : 0;
        $authentic      = !empty($_POST['authentic']) ? 1 : 0;
        $service        = isset($_POST['service']) ? sanitize_text_field(wp_unslash($_POST['service'])) : '';
        $draft          = SGC_Order_Draft::get_data();

        if ($declared_value < 1) {
            wp_send_json_error([
                'message' => __('Declared value must be at least $1.', 'sgc-card-grading'),
            ]);
        }

        if (!empty($draft['selected_cards']) && is_array($draft['selected_cards'])) {
            foreach ($draft['selected_cards'] as &$card) {
                if (!empty($card['row_id']) && $card['row_id'] === $row_id) {
                    $services = !empty($card['services']) && is_array($card['services']) ? $card['services'] : ['Raw Card Grading'];

                    if (empty($service) || !in_array($service, $services, true)) {
                        $service = $services[0];
                    }

                    $card['declared_value'] = $declared_value;
                    $card['encapsulate']    = $encapsulate;
                    $card['oversized']      = $oversized;
                    $card['authentic']      = $authentic;
                    $card['service']        = $service;
                    break;
                }
            }
            unset($card);
        }

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success(self::format_selected_cards_response($draft));
    }

    public static function save_service_level() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $tier = isset($_POST['tier']) ? sanitize_text_field(wp_unslash($_POST['tier'])) : '';
        $map  = self::get_tier_map();

        if (!isset($map[$tier])) {
            wp_send_json_error([
                'message' => __('Invalid service level.', 'sgc-card-grading'),
            ]);
        }

        $draft = SGC_Order_Draft::get_data();
        $draft['service_level'] = $tier;
        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'key'   => $tier,
            'label' => $map[$tier]['label'],
            'price' => $map[$tier]['price'],
            'days'  => $map[$tier]['days'],
        ]);
    }

    public static function get_service_level() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $draft = SGC_Order_Draft::get_data();
        $tier_data = self::get_selected_tier_data($draft);

        wp_send_json_success($tier_data);
    }

    public static function get_shipping_addresses() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $draft = SGC_Order_Draft::get_data();

        $addresses = !empty($draft['shipping_addresses']) && is_array($draft['shipping_addresses'])
            ? $draft['shipping_addresses']
            : [];

        $selected_address_id = !empty($draft['selected_shipping_address_id'])
            ? $draft['selected_shipping_address_id']
            : '';

        if (empty($selected_address_id) && !empty($addresses)) {
            $selected_address_id = $addresses[0]['id'];
            $draft['selected_shipping_address_id'] = $selected_address_id;
            SGC_Order_Draft::save_data($draft);
        }

        wp_send_json_success([
            'addresses'           => $addresses,
            'selected_address_id' => $selected_address_id,
            'countries'           => self::get_countries_list(),
            'summary'             => self::get_shipping_summary($draft),
        ]);
    }

    public static function get_states_by_country() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $country_code = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
        $states = self::get_states_list($country_code);

        wp_send_json_success([
            'states' => $states,
        ]);
    }

    public static function save_shipping_address() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $country    = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
        $street     = isset($_POST['street']) ? sanitize_text_field(wp_unslash($_POST['street'])) : '';
        $apartment  = isset($_POST['apartment']) ? sanitize_text_field(wp_unslash($_POST['apartment'])) : '';
        $city       = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '';
        $state      = isset($_POST['state']) ? sanitize_text_field(wp_unslash($_POST['state'])) : '';
        $zip        = isset($_POST['zip']) ? sanitize_text_field(wp_unslash($_POST['zip'])) : '';
        $phone      = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $is_default = !empty($_POST['is_default']) ? 1 : 0;

        if ($country === '' || $street === '' || $city === '' || $state === '' || $zip === '' || $phone === '') {
            wp_send_json_error([
                'message' => __('Please fill all required address fields.', 'sgc-card-grading'),
            ]);
        }

        $countries = self::get_countries_list();
        $states    = self::get_states_list($country);

        $country_label = isset($countries[$country]) ? $countries[$country] : $country;
        $state_label   = isset($states[$state]) ? $states[$state] : $state;

        $draft = SGC_Order_Draft::get_data();

        if (empty($draft['shipping_addresses']) || !is_array($draft['shipping_addresses'])) {
            $draft['shipping_addresses'] = [];
        }

        $address_id = wp_generate_uuid4();

        $address = [
            'id'            => $address_id,
            'country'       => $country,
            'country_label' => $country_label,
            'street'        => $street,
            'apartment'     => $apartment,
            'city'          => $city,
            'state'         => $state,
            'state_label'   => $state_label,
            'zip'           => $zip,
            'phone'         => $phone,
        ];

        $draft['shipping_addresses'][] = $address;

        if ($is_default || empty($draft['selected_shipping_address_id'])) {
            $draft['selected_shipping_address_id'] = $address_id;
        }

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'addresses'           => $draft['shipping_addresses'],
            'selected_address_id' => $draft['selected_shipping_address_id'],
            'summary'             => self::get_shipping_summary($draft),
        ]);
    }

    public static function select_shipping_address() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $address_id = isset($_POST['address_id']) ? sanitize_text_field(wp_unslash($_POST['address_id'])) : '';
        $draft = SGC_Order_Draft::get_data();

        if (empty($draft['shipping_addresses']) || !is_array($draft['shipping_addresses'])) {
            wp_send_json_error([
                'message' => __('No shipping addresses found.', 'sgc-card-grading'),
            ]);
        }

        $found = false;
        foreach ($draft['shipping_addresses'] as $address) {
            if (!empty($address['id']) && $address['id'] === $address_id) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            wp_send_json_error([
                'message' => __('Invalid address selection.', 'sgc-card-grading'),
            ]);
        }

        $draft['selected_shipping_address_id'] = $address_id;
        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'addresses'           => $draft['shipping_addresses'],
            'selected_address_id' => $draft['selected_shipping_address_id'],
            'summary'             => self::get_shipping_summary($draft),
        ]);
    }

    public static function get_shipping_address_detail() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $address_id = isset($_POST['address_id']) ? sanitize_text_field(wp_unslash($_POST['address_id'])) : '';
        $draft = SGC_Order_Draft::get_data();

        $addresses = !empty($draft['shipping_addresses']) && is_array($draft['shipping_addresses']) ? $draft['shipping_addresses'] : [];

        foreach ($addresses as $address) {
            if (!empty($address['id']) && $address['id'] === $address_id) {
                wp_send_json_success([
                    'address'   => $address,
                    'countries' => self::get_countries_list(),
                    'states'    => self::get_states_list($address['country']),
                ]);
            }
        }

        wp_send_json_error([
            'message' => __('Address not found.', 'sgc-card-grading'),
        ]);
    }

    public static function update_shipping_address() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $address_id = isset($_POST['address_id']) ? sanitize_text_field(wp_unslash($_POST['address_id'])) : '';
        $country    = isset($_POST['country']) ? sanitize_text_field(wp_unslash($_POST['country'])) : '';
        $street     = isset($_POST['street']) ? sanitize_text_field(wp_unslash($_POST['street'])) : '';
        $apartment  = isset($_POST['apartment']) ? sanitize_text_field(wp_unslash($_POST['apartment'])) : '';
        $city       = isset($_POST['city']) ? sanitize_text_field(wp_unslash($_POST['city'])) : '';
        $state      = isset($_POST['state']) ? sanitize_text_field(wp_unslash($_POST['state'])) : '';
        $zip        = isset($_POST['zip']) ? sanitize_text_field(wp_unslash($_POST['zip'])) : '';
        $phone      = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $is_default = !empty($_POST['is_default']) ? 1 : 0;

        if ($address_id === '' || $country === '' || $street === '' || $city === '' || $state === '' || $zip === '' || $phone === '') {
            wp_send_json_error([
                'message' => __('Please fill all required address fields.', 'sgc-card-grading'),
            ]);
        }

        $countries = self::get_countries_list();
        $states    = self::get_states_list($country);

        $country_label = isset($countries[$country]) ? $countries[$country] : $country;
        $state_label   = isset($states[$state]) ? $states[$state] : $state;

        $draft = SGC_Order_Draft::get_data();

        if (empty($draft['shipping_addresses']) || !is_array($draft['shipping_addresses'])) {
            wp_send_json_error([
                'message' => __('No shipping addresses found.', 'sgc-card-grading'),
            ]);
        }

        $updated = false;

        foreach ($draft['shipping_addresses'] as &$address) {
            if (!empty($address['id']) && $address['id'] === $address_id) {
                $address['country']       = $country;
                $address['country_label'] = $country_label;
                $address['street']        = $street;
                $address['apartment']     = $apartment;
                $address['city']          = $city;
                $address['state']         = $state;
                $address['state_label']   = $state_label;
                $address['zip']           = $zip;
                $address['phone']         = $phone;
                $updated = true;
                break;
            }
        }
        unset($address);

        if (!$updated) {
            wp_send_json_error([
                'message' => __('Address not found.', 'sgc-card-grading'),
            ]);
        }

        if ($is_default || empty($draft['selected_shipping_address_id'])) {
            $draft['selected_shipping_address_id'] = $address_id;
        }

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'addresses'           => $draft['shipping_addresses'],
            'selected_address_id' => $draft['selected_shipping_address_id'],
            'summary'             => self::get_shipping_summary($draft),
        ]);
    }

    public static function delete_shipping_address() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $address_id = isset($_POST['address_id']) ? sanitize_text_field(wp_unslash($_POST['address_id'])) : '';
        $draft = SGC_Order_Draft::get_data();

        $addresses = !empty($draft['shipping_addresses']) && is_array($draft['shipping_addresses']) ? $draft['shipping_addresses'] : [];

        if (empty($addresses)) {
            wp_send_json_error([
                'message' => __('No shipping addresses found.', 'sgc-card-grading'),
            ]);
        }

        $addresses = array_values(array_filter($addresses, function ($address) use ($address_id) {
            return empty($address['id']) || $address['id'] !== $address_id;
        }));

        $draft['shipping_addresses'] = $addresses;

        if (!empty($draft['selected_shipping_address_id']) && $draft['selected_shipping_address_id'] === $address_id) {
            $draft['selected_shipping_address_id'] = !empty($addresses[0]['id']) ? $addresses[0]['id'] : '';
        }

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'addresses'           => $draft['shipping_addresses'],
            'selected_address_id' => !empty($draft['selected_shipping_address_id']) ? $draft['selected_shipping_address_id'] : '',
            'summary'             => self::get_shipping_summary($draft),
        ]);
    }

    public static function get_step5_review() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $draft = SGC_Order_Draft::get_data();
        $addresses = !empty($draft['shipping_addresses']) && is_array($draft['shipping_addresses']) ? $draft['shipping_addresses'] : [];
        $selected_id = !empty($draft['selected_shipping_address_id']) ? $draft['selected_shipping_address_id'] : '';

        $selected_address = null;
        foreach ($addresses as $address) {
            if (!empty($address['id']) && $address['id'] === $selected_id) {
                $selected_address = $address;
                break;
            }
        }

        wp_send_json_success([
            'tier'                     => self::get_selected_tier_data($draft),
            'cards'                    => !empty($draft['selected_cards']) && is_array($draft['selected_cards']) ? $draft['selected_cards'] : [],
            'selected_address'         => $selected_address,
            'summary'                  => self::get_shipping_summary($draft),
            'shipping_methods'         => array_values(self::get_shipping_methods()),
            'selected_shipping_method' => self::get_selected_shipping_method($draft),
        ]);
    }

    public static function save_shipping_method() {
        check_ajax_referer('sgc_nonce', 'nonce');

        $method = isset($_POST['shipping_method']) ? sanitize_text_field(wp_unslash($_POST['shipping_method'])) : '';
        $methods = self::get_shipping_methods();

        if (!isset($methods[$method])) {
            wp_send_json_error([
                'message' => __('Invalid shipping method.', 'sgc-card-grading'),
            ]);
        }

        $draft = SGC_Order_Draft::get_data();
        $draft['shipping_method'] = $method;
        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'summary'                  => self::get_shipping_summary($draft),
            'selected_shipping_method' => self::get_selected_shipping_method($draft),
            'shipping_methods'         => array_values(self::get_shipping_methods()),
        ]);
    }

    private static function generate_order_number() {
        return 'TCB-' . gmdate('YmdHis') . '-' . wp_rand(1000, 9999);
    }

    private static function normalize_order_number_prefix($order_number) {
        $order_number = sanitize_text_field((string) $order_number);

        if ($order_number === '') {
            return '';
        }

        return preg_replace('/^SGC-/', 'TCB-', $order_number);
    }

    public static function create_payment_intent() {
        check_ajax_referer('sgc_nonce', 'nonce');

        if (!class_exists('SGC_Stripe_Service')) {
            wp_send_json_error([
                'message' => __('Stripe service class not found.', 'sgc-card-grading'),
            ]);
        }

        if (!SGC_Stripe_Service::is_configured()) {
            wp_send_json_error([
                'message' => __('Stripe keys are not configured yet.', 'sgc-card-grading'),
            ]);
        }

        $draft = SGC_Order_Draft::get_data();

        if (empty($draft['selected_cards']) || !is_array($draft['selected_cards'])) {
            wp_send_json_error([
                'message' => __('Please add at least one card before payment.', 'sgc-card-grading'),
            ]);
        }

        if (empty($draft['selected_shipping_address_id'])) {
            wp_send_json_error([
                'message' => __('Please select a shipping address before payment.', 'sgc-card-grading'),
            ]);
        }

        if (empty($draft['shipping_method'])) {
            wp_send_json_error([
                'message' => __('Please select a shipping method before payment.', 'sgc-card-grading'),
            ]);
        }

        if (empty($draft['order_number'])) {
            $draft['order_number'] = self::generate_order_number();
            SGC_Order_Draft::save_data($draft);
        } else {
            $normalized_order_number = self::normalize_order_number_prefix($draft['order_number']);

            if ($normalized_order_number !== $draft['order_number']) {
                $draft['order_number'] = $normalized_order_number;
                SGC_Order_Draft::save_data($draft);
            }
        }

        $result = SGC_Stripe_Service::create_or_update_payment_intent($draft);

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }

        if (empty($result['id']) || empty($result['client_secret'])) {
            wp_send_json_error([
                'message' => __('Stripe did not return a valid payment intent.', 'sgc-card-grading'),
            ]);
        }

        $summary = class_exists('SGC_Ajax_Helper')
            ? SGC_Ajax_Helper::get_payment_summary_for_stripe($draft)
            : ['grand_total' => 0];

        $draft['stripe_payment_intent_id'] = sanitize_text_field($result['id']);
        $draft['stripe_client_secret']     = sanitize_text_field($result['client_secret']);
        $draft['payment_status']           = 'pending';
        $draft['payment_intent_status']    = !empty($result['status']) ? sanitize_text_field($result['status']) : 'requires_payment_method';

        if (!empty($result['metadata']['order_number'])) {
            $draft['order_number'] = self::normalize_order_number_prefix($result['metadata']['order_number']);
        }

        SGC_Order_Draft::save_data($draft);

        wp_send_json_success([
            'payment_intent_id' => $result['id'],
            'client_secret'     => $result['client_secret'],
            'amount'            => !empty($summary['grand_total']) ? (float) $summary['grand_total'] : 0,
            'order_number'      => !empty($draft['order_number']) ? $draft['order_number'] : '',
        ]);
    }

    public static function payment_success() {
        check_ajax_referer('sgc_nonce', 'nonce');

        if (!class_exists('SGC_Stripe_Service')) {
            wp_send_json_error([
                'message' => __('Stripe service class not found.', 'sgc-card-grading'),
            ]);
        }

        $payment_intent_id   = isset($_POST['payment_intent_id']) ? sanitize_text_field(wp_unslash($_POST['payment_intent_id'])) : '';
        $payment_method_type = isset($_POST['payment_method_type']) ? sanitize_text_field(wp_unslash($_POST['payment_method_type'])) : 'card';

        if ($payment_intent_id === '') {
            wp_send_json_error([
                'message' => __('Payment intent ID is missing.', 'sgc-card-grading'),
            ]);
        }

        $draft = SGC_Order_Draft::get_data();

        // --- NEW FIX: ডাবল সাবমিশন চেকার ---
        if (!empty($draft['payment_status']) && $draft['payment_status'] === 'paid' && !empty($draft['stripe_payment_intent_id']) && $draft['stripe_payment_intent_id'] === $payment_intent_id) {
            
            $redirect_args = [
                'sgc_step' => 8,
            ];
            
            if (!empty($draft['saved_order_id'])) {
                $redirect_args['order_id'] = $draft['saved_order_id'];
            }

            $redirect_url = add_query_arg($redirect_args, home_url('/submission-form/'));

            wp_send_json_success([
                'message'      => __('Payment already processed successfully.', 'sgc-card-grading'),
                'redirect_url' => $redirect_url,
            ]);
            exit;
        }
        // --- NEW FIX END ---

        if (empty($draft['stripe_payment_intent_id']) || $draft['stripe_payment_intent_id'] !== $payment_intent_id) {
            wp_send_json_error([
                'message' => __('Payment intent mismatch.', 'sgc-card-grading'),
            ]);
        }

        $intent = SGC_Stripe_Service::retrieve_payment_intent($payment_intent_id);

        if (is_wp_error($intent)) {
            wp_send_json_error([
                'message' => $intent->get_error_message(),
            ]);
        }

        if (empty($intent['status']) || $intent['status'] !== 'succeeded') {
            wp_send_json_error([
                'message' => __('Payment is not completed yet.', 'sgc-card-grading'),
            ]);
        }

        $draft['payment_status']        = 'paid';
        $draft['payment_method_type']   = $payment_method_type;
        $draft['payment_intent_status'] = sanitize_text_field($intent['status']);
        $draft['payment_intent_id']     = sanitize_text_field($payment_intent_id);
        $draft['payment_received_at']   = current_time('mysql');

        if (!empty($intent['amount_received'])) {
            $draft['amount_received'] = ((float) $intent['amount_received']) / 100;
        }

        if (!empty($intent['currency'])) {
            $draft['payment_currency'] = strtoupper(sanitize_text_field($intent['currency']));
        }

        if (!empty($intent['metadata']['order_number'])) {
            $draft['order_number'] = self::normalize_order_number_prefix($intent['metadata']['order_number']);
        }

        $saved_order_id = 0;

        // Custom DB Table e Save korar logic
        if (class_exists('SGC_Order_DB')) {
            $save_result = SGC_Order_DB::save_order_from_draft($draft);

            if (is_wp_error($save_result)) {
                wp_send_json_error([
                    'message' => $save_result->get_error_message(),
                ]);
            }

            $saved_order_id = (int) $save_result;
            $draft['saved_order_id'] = $saved_order_id;
        }

        // ==============================================================
        // NEW FEATURE: SAVE TO WORDPRESS CUSTOM POST TYPE (SGC Orders)
        // ==============================================================
        $current_user = wp_get_current_user();
        $user_email = $current_user->exists() ? $current_user->user_email : '';

        // Email na pele shipping phone ba name default hisebe use kora
        if (empty($user_email)) {
            $user_email = !empty($draft['selected_shipping_address']['phone']) ? 'Customer (Phone: '.$draft['selected_shipping_address']['phone'].')' : 'Guest Customer';
        }

        // Add email to draft so it can be saved
        $draft['user_email'] = $user_email;

        // Create WordPress Custom Post
        $post_data = array(
            'post_title'   => !empty($draft['order_number']) ? sanitize_text_field($draft['order_number']) : 'Order #' . time(),
            'post_type'    => 'sgc_order',
            'post_status'  => 'publish',
            'post_author'  => get_current_user_id()
        );

        $post_id = wp_insert_post($post_data);

        // Save metadata if post was created successfully
        if ($post_id && !is_wp_error($post_id)) {
            // Organize address nicely for saving
            if(!empty($draft['selected_shipping_address_id']) && !empty($draft['shipping_addresses'])) {
                foreach($draft['shipping_addresses'] as $address) {
                    if($address['id'] === $draft['selected_shipping_address_id']) {
                        $draft['shipping_street'] = $address['street'];
                        $draft['shipping_city'] = $address['city'];
                        $draft['shipping_state_label'] = $address['state_label'];
                        $draft['shipping_zip'] = $address['zip'];
                        $draft['shipping_phone'] = $address['phone'];
                        break;
                    }
                }
            }

            // Get shipping & pricing summary
            $summary = self::get_shipping_summary($draft);
            $draft['shipping_method_label'] = $summary['selected_shipping_label'];
            $draft['grand_total'] = $summary['grand_total'];

            // Save order info & cards
            update_post_meta($post_id, '_sgc_order_data', $draft);
            update_post_meta($post_id, '_sgc_order_cards', $draft['selected_cards']);
            update_post_meta($post_id, '_sgc_is_confirmed', 'no'); 
        }
        // ==============================================================

        SGC_Order_Draft::save_data($draft);

        $redirect_args = [
            'sgc_step' => 8,
        ];

        if ($saved_order_id) {
            $redirect_args['order_id'] = $saved_order_id;
        }

        $redirect_url = add_query_arg($redirect_args, home_url('/submission-form/'));

        wp_send_json_success([
            'message'      => __('Payment successful.', 'sgc-card-grading'),
            'redirect_url' => $redirect_url,
        ]);
    }

    private static function format_selected_cards_response($draft) {
        $selected_cards = !empty($draft['selected_cards']) && is_array($draft['selected_cards']) ? $draft['selected_cards'] : [];
        $tier_data      = self::get_selected_tier_data($draft);

        $total_cards = count($selected_cards);
        $total_dv    = 0;

        foreach ($selected_cards as $card) {
            $declared_value = isset($card['declared_value']) ? (float) $card['declared_value'] : 0;
            $total_dv += $declared_value;
        }

        $grading_fee = $tier_data['price'] * $total_cards;

        return [
            'selected_cards' => $selected_cards,
            'summary' => [
                'total_cards' => $total_cards,
                'total_dv'    => $total_dv,
                'grading_fee' => $grading_fee,
            ],
            'tier' => $tier_data,
        ];
    }
}