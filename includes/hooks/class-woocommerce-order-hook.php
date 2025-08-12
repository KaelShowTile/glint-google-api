<?php
class WooCommerce_Order_Hook extends Abstract_Hook {
    public function handle($order_id) {
        if (!function_exists('wc_get_order')) return;
        
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $hook_data = [
            'order_id' => $order_id,
            'order' => $order
        ];
        
        $this->process_automation($hook_data);
    }
}