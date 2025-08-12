<?php
/*
Plugin Name: CHT EDM
Description: Google Automation for CHT/GTO.
Version: 1.0.0
Author: Kael
*/

// Prevent direct access to the file
defined('ABSPATH') || exit;

// Define constants
define('GOOGLE_AUTOMATION_VERSION', '1.0.0');
define('GOOGLE_AUTOMATION_PATH', plugin_dir_path(__FILE__));
define('GOOGLE_AUTOMATION_URL', plugin_dir_url(__FILE__));

// Include required files
require_once GOOGLE_AUTOMATION_PATH . 'includes/class-cpt-automation.php';
require_once GOOGLE_AUTOMATION_PATH . 'includes/class-meta-boxes.php';
require_once GOOGLE_AUTOMATION_PATH . 'includes/class-automation-registry.php';
require_once GOOGLE_AUTOMATION_PATH . 'includes/class-automation-handler.php';
require_once GOOGLE_AUTOMATION_PATH . 'includes/class-google-sheets-service.php';

// Initialize plugin components
add_action('init', 'google_automation_init');

function google_automation_init() {
    CPT_Automation::register_post_type();
    Automation_Meta_Boxes::init();
    Automation_Registry::init();
}

// Flush rewrite rules on activation
register_activation_hook(__FILE__, 'google_automation_activate');
function google_automation_activate() {
    google_automation_init();
    flush_rewrite_rules();
}

// Flush rewrite rules on deactivation
register_deactivation_hook(__FILE__, 'google_automation_deactivate');
function google_automation_deactivate() {
    flush_rewrite_rules();
}