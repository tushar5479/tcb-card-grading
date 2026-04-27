<?php
if (!defined('ABSPATH')) {
    exit;
}

class SGC_Customer_Card_CPT {

    public static function register() {
        register_post_type('sgc_customer_card', [
            'labels' => [
                'name'               => __('Customer Cards', 'sgc-card-grading'),
                'singular_name'      => __('Customer Card', 'sgc-card-grading'),
                'add_new'            => __('Add New', 'sgc-card-grading'),
                'add_new_item'       => __('Add New Customer Card', 'sgc-card-grading'),
                'edit_item'          => __('Edit Customer Card', 'sgc-card-grading'),
                'new_item'           => __('New Customer Card', 'sgc-card-grading'),
                'view_item'          => __('View Customer Card', 'sgc-card-grading'),
                'search_items'       => __('Search Customer Cards', 'sgc-card-grading'),
                'not_found'          => __('No customer cards found', 'sgc-card-grading'),
                'not_found_in_trash' => __('No customer cards found in Trash', 'sgc-card-grading'),
                'menu_name'          => __('Customer Cards', 'sgc-card-grading'),
            ],
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-groups',
            'supports'           => ['title'],
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
        ]);

        add_action('add_meta_boxes', [__CLASS__, 'add_meta_box']);
        add_action('save_post_sgc_customer_card', [__CLASS__, 'save_meta']);
        add_filter('manage_sgc_customer_card_posts_columns', [__CLASS__, 'columns']);
        add_action('manage_sgc_customer_card_posts_custom_column', [__CLASS__, 'column_content'], 10, 2);
        add_filter('post_row_actions', [__CLASS__, 'add_approve_action'], 10, 2);
        add_action('admin_init', [__CLASS__, 'handle_approve_action']);
    }

    public static function add_meta_box() {
        add_meta_box(
            'sgc_customer_card_details',
            __('Customer Card Details', 'sgc-card-grading'),
            [__CLASS__, 'render_meta_box'],
            'sgc_customer_card',
            'normal',
            'default'
        );
    }

    public static function render_meta_box($post) {
        wp_nonce_field('sgc_customer_card_meta_nonce_action', 'sgc_customer_card_meta_nonce');

        $list_name = get_post_meta($post->ID, '_sgc_customer_list_name', true);
        $card_type = get_post_meta($post->ID, '_sgc_customer_card_type', true);
        $status    = get_post_meta($post->ID, '_sgc_customer_status', true);

        if (!$status) {
            $status = 'pending';
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="sgc_customer_list_name"><?php esc_html_e('List Name', 'sgc-card-grading'); ?></label></th>
                <td>
                    <input type="text" name="sgc_customer_list_name" id="sgc_customer_list_name" value="<?php echo esc_attr($list_name); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="sgc_customer_card_type"><?php esc_html_e('Type', 'sgc-card-grading'); ?></label></th>
                <td>
                    <input type="text" name="sgc_customer_card_type" id="sgc_customer_card_type" value="<?php echo esc_attr($card_type); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="sgc_customer_status"><?php esc_html_e('Status', 'sgc-card-grading'); ?></label></th>
                <td>
                    <select name="sgc_customer_status" id="sgc_customer_status">
                        <option value="pending" <?php selected($status, 'pending'); ?>><?php esc_html_e('Pending', 'sgc-card-grading'); ?></option>
                        <option value="approved" <?php selected($status, 'approved'); ?>><?php esc_html_e('Approved', 'sgc-card-grading'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    public static function save_meta($post_id) {
        if (!isset($_POST['sgc_customer_card_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['sgc_customer_card_meta_nonce'])), 'sgc_customer_card_meta_nonce_action')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $list_name = isset($_POST['sgc_customer_list_name']) ? sanitize_text_field(wp_unslash($_POST['sgc_customer_list_name'])) : '';
        $card_type = isset($_POST['sgc_customer_card_type']) ? sanitize_text_field(wp_unslash($_POST['sgc_customer_card_type'])) : '';
        $status    = isset($_POST['sgc_customer_status']) ? sanitize_text_field(wp_unslash($_POST['sgc_customer_status'])) : 'pending';

        update_post_meta($post_id, '_sgc_customer_list_name', $list_name);
        update_post_meta($post_id, '_sgc_customer_card_type', $card_type);
        update_post_meta($post_id, '_sgc_customer_status', $status);

        if ($status === 'approved') {
            self::approve_card_to_main_list($post_id);
        }
    }

    public static function columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'] ?? '';
        $new['title'] = __('Title', 'sgc-card-grading');
        $new['sgc_customer_list_name'] = __('List Name', 'sgc-card-grading');
        $new['sgc_customer_card_type'] = __('Type', 'sgc-card-grading');
        $new['sgc_customer_status'] = __('Status', 'sgc-card-grading');
        $new['date'] = __('Date', 'sgc-card-grading');
        return $new;
    }

    public static function column_content($column, $post_id) {
        if ($column === 'sgc_customer_list_name') {
            echo esc_html(get_post_meta($post_id, '_sgc_customer_list_name', true));
        }

        if ($column === 'sgc_customer_card_type') {
            echo esc_html(get_post_meta($post_id, '_sgc_customer_card_type', true));
        }

        if ($column === 'sgc_customer_status') {
            echo esc_html(get_post_meta($post_id, '_sgc_customer_status', true));
        }
    }

    public static function add_approve_action($actions, $post) {
        if ($post->post_type !== 'sgc_customer_card') {
            return $actions;
        }

        $status = get_post_meta($post->ID, '_sgc_customer_status', true);

        if ($status !== 'approved') {
            $approve_url = wp_nonce_url(
                admin_url('edit.php?post_type=sgc_customer_card&sgc_approve_customer_card=' . absint($post->ID)),
                'sgc_approve_customer_card_' . absint($post->ID)
            );

            $actions['sgc_approve'] = '<a href="' . esc_url($approve_url) . '">' . esc_html__('Approve', 'sgc-card-grading') . '</a>';
        }

        return $actions;
    }

    public static function handle_approve_action() {
        if (!is_admin()) {
            return;
        }

        if (!isset($_GET['sgc_approve_customer_card'])) {
            return;
        }

        $post_id = absint($_GET['sgc_approve_customer_card']);

        if (!$post_id) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'sgc_approve_customer_card_' . $post_id)) {
            return;
        }

        update_post_meta($post_id, '_sgc_customer_status', 'approved');
        self::approve_card_to_main_list($post_id);

        wp_safe_redirect(admin_url('edit.php?post_type=sgc_customer_card'));
        exit;
    }

    private static function approve_card_to_main_list($customer_post_id) {
        $already_synced = get_post_meta($customer_post_id, '_sgc_synced_main_card_id', true);
        if ($already_synced) {
            return;
        }

        $title     = get_the_title($customer_post_id);
        $list_name = get_post_meta($customer_post_id, '_sgc_customer_list_name', true);
        $card_type = get_post_meta($customer_post_id, '_sgc_customer_card_type', true);

        $existing = get_posts([
            'post_type'      => 'sgc_card',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'title'          => $title,
        ]);

        if (!empty($existing)) {
            update_post_meta($customer_post_id, '_sgc_synced_main_card_id', $existing[0]->ID);
            return;
        }

        $new_card_id = wp_insert_post([
            'post_type'   => 'sgc_card',
            'post_title'  => $title,
            'post_status' => 'publish',
        ]);

        if ($new_card_id && !is_wp_error($new_card_id)) {
            update_post_meta($new_card_id, '_sgc_list_name', $list_name);
            update_post_meta($new_card_id, '_sgc_card_type', $card_type);
            update_post_meta($new_card_id, '_sgc_services', ['Raw Card Grading']);
            update_post_meta($customer_post_id, '_sgc_synced_main_card_id', $new_card_id);
        }
    }
}