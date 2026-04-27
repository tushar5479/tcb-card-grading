<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Ajax_Helper {

    public static function get_tier_map() {
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

    public static function get_shipping_methods() {
        return [
            'usps_priority_mail' => [
                'key'   => 'usps_priority_mail',
                'label' => 'USPS Priority Mail',
                'price' => 15,
            ],
            'fedex_ground' => [
                'key'   => 'fedex_ground',
                'label' => 'FedEx Ground',
                'price' => 25,
            ],
            'fedex_2_day' => [
                'key'   => 'fedex_2_day',
                'label' => 'FedEx 2-Day',
                'price' => 45,
            ],
            'fedex_standard_overnight' => [
                'key'   => 'fedex_standard_overnight',
                'label' => 'FedEx Standard Overnight',
                'price' => 60,
            ],
        ];
    }

    public static function get_selected_tier_data($draft) {
        $map  = self::get_tier_map();
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

    public static function get_selected_shipping_method($draft) {
        $methods  = self::get_shipping_methods();
        $selected = !empty($draft['shipping_method']) ? $draft['shipping_method'] : 'usps_priority_mail';

        if (!isset($methods[$selected])) {
            $selected = 'usps_priority_mail';
        }

        return $methods[$selected];
    }

    public static function get_payment_summary_for_stripe($draft) {
        $selected_cards  = !empty($draft['selected_cards']) && is_array($draft['selected_cards']) ? $draft['selected_cards'] : [];
        $tier_data       = self::get_selected_tier_data($draft);
        $shipping_method = self::get_selected_shipping_method($draft);

        $total_cards = count($selected_cards);
        $total_dv    = 0;

        foreach ($selected_cards as $card) {
            $total_dv += isset($card['declared_value']) ? (float) $card['declared_value'] : 0;
        }

        $grading_fee  = $tier_data['price'] * $total_cards;
        $shipping_fee = (float) $shipping_method['price'];
        $grand_total  = $grading_fee + $shipping_fee;

        return [
            'tier_key'       => $tier_data['key'],
            'tier_label'     => $tier_data['label'],
            'tier_days'      => $tier_data['days'],
            'tier_price'     => (float) $tier_data['price'],
            'total_cards'    => $total_cards,
            'total_dv'       => (float) $total_dv,
            'grading_fee'    => (float) $grading_fee,
            'shipping_key'   => $shipping_method['key'],
            'shipping_label' => $shipping_method['label'],
            'shipping_fee'   => (float) $shipping_fee,
            'grand_total'    => (float) $grand_total,
        ];
    }
}