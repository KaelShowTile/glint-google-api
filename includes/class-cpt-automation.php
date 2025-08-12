<?php
class CPT_Automation {
    public static function register_post_type() {
        $args = [
            'label' => 'Google Automation',
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-google',
            'labels' => [
                'name' => 'Google Automations',
                'singular_name' => 'Google Automation',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Automation',
                'edit_item' => 'Edit Automation',
                'new_item' => 'New Automation',
                'view_item' => 'View Automation',
                'search_items' => 'Search Automations',
                'not_found' => 'No automations found',
                'not_found_in_trash' => 'No automations found in Trash',
                'menu_name' => 'Google Automations'
            ],
        ];

        register_post_type('google-automation', $args);
    }
}