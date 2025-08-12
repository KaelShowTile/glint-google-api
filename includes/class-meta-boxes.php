<?php
class Automation_Meta_Boxes {
    private static $services = ['google_sheet' => 'Google Sheet'];
    private static $sheet_actions = ['send_data_to_google_sheets' => 'Send Data to Google Sheets'];

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post', [__CLASS__, 'save_meta_boxes'], 10, 2);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'automation_settings',
            'Automation Settings',
            [__CLASS__, 'render_meta_box'],
            'google-automation',
            'normal',
            'high'
        );
    }

    public static function render_meta_box($post) {
        wp_nonce_field('save_automation_settings', 'automation_settings_nonce');

        // Get saved values
        $service = get_post_meta($post->ID, 'google_service', true);
        $action = get_post_meta($post->ID, 'google_sheets_action', true);
        $params = get_post_meta($post->ID, 'parameter_settings', true) ?: [['column_name' => '', 'wp_parameter' => '']];
        ?>
        <div class="automation-field">
            <label for="google_service">Google Service</label>
            <select name="google_service" id="google_service">
                <option value="">Select Service</option>
                <?php foreach (self::$services as $value => $label) : ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($service, $value); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Google Sheet Settings (always rendered) -->
        <div class="service-settings" id="google_sheet_settings">
            <div class="automation-field">
                <label for="google_api">Google API</label>
                <input type="text" name="google_api" value="<?php echo esc_attr(get_post_meta($post->ID, 'google_api', true)); ?>">
            </div>
            <div class="automation-field">
                <label for="oauth_json">OAuth 2.0 Client ID (JSON File Path)</label>
                <input type="text" name="oauth_json" value="<?php echo esc_attr(get_post_meta($post->ID, 'oauth_json', true)); ?>">
            </div>
            <div class="automation-field">
                <label for="sheet_id">Sheet ID</label>
                <input type="text" name="sheet_id" value="<?php echo esc_attr(get_post_meta($post->ID, 'sheet_id', true)); ?>">
            </div>
            <div class="automation-field">
                <label for="sheet_name">Sheet Name (tab)</label>
                <input type="text" name="sheet_name" value="<?php echo esc_attr(get_post_meta($post->ID, 'sheet_name', true)); ?>">
            </div>
            <div class="automation-field">
                <label for="file_path">File Path</label>
                <input type="text" name="file_path" value="<?php echo esc_attr(get_post_meta($post->ID, 'file_path', true)); ?>">
            </div>
            <div class="automation-field">
                <label for="wp_hook">WordPress Hook</label>
                <input type="text" name="wp_hook" value="<?php echo esc_attr(get_post_meta($post->ID, 'wp_hook', true)); ?>">
            </div>
        </div>

        <!-- Google Sheets Actions (only shown when Google Sheet is selected) -->
        <div id="service_actions_container">
            <div class="automation-field">
                <label for="google_sheets_action">Google Sheets Action</label>
                <select name="google_sheets_action" id="google_sheets_action">
                    <option value="">Select Action</option>
                    <?php foreach (self::$sheet_actions as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($action, $value); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Parameter Settings (always rendered) -->
            <div class="action-settings" id="send_data_to_google_sheets_settings">
                <h4>Parameter Settings</h4>
                <div class="repeater-header">
                    <span>Column Name</span>
                    <span>WP Parameter</span>
                    <span>Action</span>
                </div>
                <div class="repeater-rows">
                    <?php foreach ($params as $index => $param) : ?>
                        <div class="repeater-row">
                            <input type="text" name="parameter_settings[<?php echo $index; ?>][column_name]" 
                                   value="<?php echo esc_attr($param['column_name']); ?>" placeholder="Column header in sheet">
                            <input type="text" name="parameter_settings[<?php echo $index; ?>][wp_parameter]" 
                                   value="<?php echo esc_attr($param['wp_parameter']); ?>" placeholder="Parameter from WP">
                            <button type="button" class="remove-row">Remove</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="add-row">Add Row</button>
            </div>
        </div>
        <?php
    }

    public static function save_meta_boxes($post_id, $post) {
        if ($post->post_type !== 'google-automation' || 
            !isset($_POST['automation_settings_nonce']) || 
            !wp_verify_nonce($_POST['automation_settings_nonce'], 'save_automation_settings') || 
            defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Save service type
        $service = sanitize_text_field($_POST['google_service'] ?? '');
        update_post_meta($post_id, 'google_service', $service);

        // Save sheet-specific settings
        $fields = ['google_api', 'oauth_json', 'sheet_id', 'sheet_name', 'file_path', 'wp_hook'];
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }

        // Save action type
        $action = sanitize_text_field($_POST['google_sheets_action'] ?? '');
        update_post_meta($post_id, 'google_sheets_action', $action);

        // Save parameter settings
        if (isset($_POST['parameter_settings'])) {
            $params = [];
            foreach ($_POST['parameter_settings'] as $param) {
                if (!empty($param['column_name']) || !empty($param['wp_parameter'])) {
                    $params[] = [
                        'column_name' => sanitize_text_field($param['column_name']),
                        'wp_parameter' => sanitize_text_field($param['wp_parameter'])
                    ];
                }
            }
            update_post_meta($post_id, 'parameter_settings', $params);
        }
    }

    public static function enqueue_assets($hook) {
        if ('post.php' !== $hook && 'post-new.php' !== $hook) return;
        if (get_post_type() !== 'google-automation') return;

        wp_enqueue_style(
            'google-automation-admin',
            GOOGLE_AUTOMATION_URL . 'assets/css/admin.css',
            [],
            GOOGLE_AUTOMATION_VERSION
        );

        wp_enqueue_script(
            'google-automation-admin',
            GOOGLE_AUTOMATION_URL . 'assets/js/admin.js',
            ['jquery'],
            GOOGLE_AUTOMATION_VERSION,
            true
        );
    }
}