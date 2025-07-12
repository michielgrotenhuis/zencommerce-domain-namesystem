<?php
/**
 * Domain Registration Component - Simple Version
 * 
 * Handles the registration of the domain custom post type
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainRegistration {
    
    /**
     * Register the domain post type
     */
    public function register() {
        $labels = [
            'name' => __('Domains', 'domain-system'),
            'singular_name' => __('Domain', 'domain-system'),
            'menu_name' => __('Domains', 'domain-system'),
            'name_admin_bar' => __('Domain', 'domain-system'),
            'archives' => __('Domain Archives', 'domain-system'),
            'attributes' => __('Domain Attributes', 'domain-system'),
            'parent_item_colon' => __('Parent Domain:', 'domain-system'),
            'all_items' => __('All Domains', 'domain-system'),
            'add_new_item' => __('Add New Domain', 'domain-system'),
            'add_new' => __('Add New', 'domain-system'),
            'new_item' => __('New Domain', 'domain-system'),
            'edit_item' => __('Edit Domain', 'domain-system'),
            'update_item' => __('Update Domain', 'domain-system'),
            'view_item' => __('View Domain', 'domain-system'),
            'view_items' => __('View Domains', 'domain-system'),
            'search_items' => __('Search Domains', 'domain-system'),
            'not_found' => __('No domains found', 'domain-system'),
            'not_found_in_trash' => __('No domains found in trash', 'domain-system'),
            'featured_image' => __('Featured Image', 'domain-system'),
            'set_featured_image' => __('Set featured image', 'domain-system'),
            'remove_featured_image' => __('Remove featured image', 'domain-system'),
            'use_featured_image' => __('Use as featured image', 'domain-system'),
            'insert_into_item' => __('Insert into domain', 'domain-system'),
            'uploaded_to_this_item' => __('Uploaded to this domain', 'domain-system'),
            'items_list' => __('Domains list', 'domain-system'),
            'items_list_navigation' => __('Domains list navigation', 'domain-system'),
            'filter_items_list' => __('Filter domains list', 'domain-system')
        ];
        
        $args = [
            'label' => __('Domain', 'domain-system'),
            'description' => __('Domain landing pages and TLD information', 'domain-system'),
            'labels' => $labels,
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'custom-fields', 'author'],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 25,
            'menu_icon' => 'dashicons-admin-site-alt3',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => 'domains',
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'rewrite' => [
                'slug' => 'domains',
                'with_front' => false,
                'feeds' => true,
                'pages' => true
            ],
            'capability_type' => 'post',
            'capabilities' => [
                'edit_post' => 'edit_posts',
                'read_post' => 'read',
                'delete_post' => 'delete_posts',
                'edit_posts' => 'edit_posts',
                'edit_others_posts' => 'edit_others_posts',
                'publish_posts' => 'publish_posts',
                'read_private_posts' => 'read_private_posts'
            ],
            'show_in_rest' => true,
            'rest_base' => 'domains',
            'rest_controller_class' => 'WP_REST_Posts_Controller'
        ];
        
        register_post_type('domain', apply_filters('domain_post_type_args', $args));
    }
}