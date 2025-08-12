<?php
class Automation_Handler {
    private $automation_id;
    private $service;
    
    public function __construct($automation_id) {
        $this->automation_id = $automation_id;
        $this->service = new Google_Sheets_Service();
    }
    
    public function execute($hook_data) {
        $params = get_post_meta($this->automation_id, 'parameter_settings', true);
        if (empty($params)) return;
        
        $row_data = [];
        foreach ($params as $param) {
            $row_data[$param['column_name']] = $this->get_parameter_value(
                $param['wp_parameter'], 
                $hook_data
            );
        }
        
        $settings = $this->get_sheet_settings();
        $this->service->send_data($row_data, $settings);
    }
    
    private function get_parameter_value($expression, $hook_data) {
        // Security: Only allow specific patterns
        if (preg_match('/^\$order->(get_\w+\(\)|\w+)$/', $expression)) {
            try {
                // Extract variable name and method
                $parts = explode('->', substr($expression, 1));
                $object_name = $parts[0];
                $method_property = $parts[1];
                
                if (isset($hook_data[$object_name])) {
                    $object = $hook_data[$object_name];
                    
                    // Handle method calls
                    if (strpos($method_property, 'get_') === 0 && substr($method_property, -2) === '()') {
                        $method_name = substr($method_property, 0, -2);
                        if (method_exists($object, $method_name)) {
                            return $object->$method_name();
                        }
                    }
                    // Handle properties
                    elseif (property_exists($object, $method_property)) {
                        return $object->$method_property;
                    }
                }
            } catch (Exception $e) {
                error_log('Automation parameter error: ' . $e->getMessage());
            }
        }
        
        return '';
    }
    
    private function get_sheet_settings() {
        return [
            'spreadsheet_id' => get_post_meta($this->automation_id, 'sheet_id', true),
            'sheet_name' => get_post_meta($this->automation_id, 'sheet_name', true),
            'oauth_credentials_path' => get_post_meta($this->automation_id, 'oauth_json', true),
        ];
    }
}