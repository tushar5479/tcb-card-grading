<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Card_CPT {

    public static function register() {
        register_post_type('sgc_card', [
            'labels' => [
                'name'               => __('Add Cards', 'sgc-card-grading'),
                'singular_name'      => __('Add Card', 'sgc-card-grading'),
                'add_new'            => __('Add New', 'sgc-card-grading'),
                'add_new_item'       => __('Add New Card', 'sgc-card-grading'),
                'edit_item'          => __('Edit Card', 'sgc-card-grading'),
                'new_item'           => __('New Card', 'sgc-card-grading'),
                'view_item'          => __('View Card', 'sgc-card-grading'),
                'search_items'       => __('Search Cards', 'sgc-card-grading'),
                'not_found'          => __('No cards found', 'sgc-card-grading'),
                'not_found_in_trash' => __('No cards found in Trash', 'sgc-card-grading'),
                'menu_name'          => __('Add Cards', 'sgc-card-grading'),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-id-alt',
            'supports'           => ['title'],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ]);

        add_action('add_meta_boxes', [__CLASS__, 'add_meta_box']);
        add_action('save_post_sgc_card', [__CLASS__, 'save_meta']);
        add_filter('manage_sgc_card_posts_columns', [__CLASS__, 'columns']);
        add_action('manage_sgc_card_posts_custom_column', [__CLASS__, 'column_content'], 10, 2);
    }

    public static function add_meta_box() {
        add_meta_box(
            'sgc_card_details',
            __('Card Details', 'sgc-card-grading'),
            [__CLASS__, 'render_meta_box'],
            'sgc_card',
            'normal',
            'default'
        );
    }

    public static function render_meta_box($post) {
        wp_nonce_field('sgc_card_meta_nonce_action', 'sgc_card_meta_nonce');

        $list_name = get_post_meta($post->ID, '_sgc_list_name', true);
        $card_type = get_post_meta($post->ID, '_sgc_card_type', true);
        $services  = get_post_meta($post->ID, '_sgc_services', true);

        if (!is_array($services)) {
            $services = ['Raw Card Grading'];
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="sgc_list_name"><?php esc_html_e('List Name', 'sgc-card-grading'); ?></label></th>
                <td>
                    <input type="text" name="sgc_list_name" id="sgc_list_name" value="<?php echo esc_attr($list_name); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="sgc_card_type"><?php esc_html_e('Type', 'sgc-card-grading'); ?></label></th>
                <td>
                    <input type="text" name="sgc_card_type" id="sgc_card_type" value="<?php echo esc_attr($card_type); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="sgc_services"><?php esc_html_e('Services', 'sgc-card-grading'); ?></label></th>
                <td>
                    <input type="text" name="sgc_services" id="sgc_services" value="<?php echo esc_attr(implode(', ', $services)); ?>" class="regular-text">
                    <p class="description">
                        <?php esc_html_e('Comma separated. Example: Raw Card Grading, Premium Card Grading', 'sgc-card-grading'); ?>
                    </p>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_meta($post_id) {
        if (!isset($_POST['sgc_card_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sgc_card_meta_nonce'])), 'sgc_card_meta_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $list_name = isset($_POST['sgc_list_name']) ? sanitize_text_field(wp_unslash($_POST['sgc_list_name'])) : '';
        $card_type = isset($_POST['sgc_card_type']) ? sanitize_text_field(wp_unslash($_POST['sgc_card_type'])) : '';
        $services  = isset($_POST['sgc_services']) ? sanitize_text_field(wp_unslash($_POST['sgc_services'])) : '';

        $services_array = array_filter(array_map('trim', explode(',', $services)));

        if (empty($services_array)) {
            $services_array = ['Raw Card Grading'];
        }

        update_post_meta($post_id, '_sgc_list_name', $list_name);
        update_post_meta($post_id, '_sgc_card_type', $card_type);
        update_post_meta($post_id, '_sgc_services', array_values($services_array));
    }

    public static function columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'] ?? '';
        $new['title'] = __('Title', 'sgc-card-grading');
        $new['sgc_list_name'] = __('List Name', 'sgc-card-grading');
        $new['sgc_card_type'] = __('Type', 'sgc-card-grading');
        $new['date'] = __('Date', 'sgc-card-grading');
        return $new;
    }

    public static function column_content($column, $post_id) {
        if ($column === 'sgc_list_name') {
            echo esc_html(get_post_meta($post_id, '_sgc_list_name', true));
        }

        if ($column === 'sgc_card_type') {
            echo esc_html(get_post_meta($post_id, '_sgc_card_type', true));
        }
    }
}