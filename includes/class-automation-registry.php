<?php
class Automation_Registry {
    private static $registered_hooks = [];

    public static function init() {
        add_action('wp_loaded', [__CLASS__, 'register_automations']);
    }

    public static function register_automations() {
        $automations = get_posts([
            'post_type' => 'google-automation',
            'post_status' => 'publish',
            'numberposts' => -1,
        ]);

        foreach ($automations as $automation) {
            $hook_name = get_post_meta($automation->ID, 'wp_hook', true);
            $service = get_post_meta($automation->ID, 'google_service', true);
            $action = get_post_meta($automation->ID, 'google_sheets_action', true);
            
            if ($hook_name && $service === 'google_sheet' && $action === 'send_data_to_google_sheets') {
                self::register_automation_hook($automation->ID, $hook_name);
            }
        }
    }

    private static function register_automation_hook($automation_id, $hook_name) {
        // Avoid duplicate registrations
        if (isset(self::$registered_hooks[$automation_id])) return;
        
        // Get hook priority and accepted args
        $priority = 10;
        $accepted_args = 1;
        
        // Handle specific hooks differently
        $hook_class = self::get_hook_class($hook_name);
        
        if ($hook_class) {
            $handler = new $hook_class($automation_id);
            add_action($hook_name, [$handler, 'handle'], $priority, $accepted_args);
            self::$registered_hooks[$automation_id] = true;
        }
    }

    private static function get_hook_class($hook_name) {
        $hook_map = [
            'woocommerce_new_order' => 'WooCommerce_Order_Hook',
            'woocommerce_checkout_order_processed' => 'WooCommerce_Order_Hook',
            // Add more hooks here as needed
        ];
        
        if (isset($hook_map[$hook_name])) {
            $class_name = $hook_map[$hook_name];
            $file_path = GOOGLE_AUTOMATION_PATH . "includes/hooks/class-{$hook_name}-hook.php";
            
            if (!class_exists($class_name) && file_exists($file_path)) {
                require_once $file_path;
            }
            
            if (class_exists($class_name)) {
                return $class_name;
            }
        }
        
        return false;
    }
}