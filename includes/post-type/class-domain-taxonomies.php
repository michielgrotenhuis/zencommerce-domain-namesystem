<?php
/**
 * Domain Taxonomies Component - Simple Version
 * 
 * Handles the registration of domain taxonomies
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainTaxonomies {
    
    /**
     * Register all domain taxonomies
     */
    public function register() {
        $this->register_categories();
        
        // Create default terms after registration
        add_action('init', [$this, 'create_default_terms'], 12);
    }
    
    /**
     * Register domain categories taxonomy
     */
    private function register_categories() {
        $labels = [
            'name' => __('Domain Categories', 'domain-system'),
            'singular_name' => __('Domain Category', 'domain-system'),
            'search_items' => __('Search Categories', 'domain-system'),
            'popular_items' => __('Popular Categories', 'domain-system'),
            'all_items' => __('All Categories', 'domain-system'),
            'parent_item' => __('Parent Category', 'domain-system'),
            'parent_item_colon' => __('Parent Category:', 'domain-system'),
            'edit_item' => __('Edit Category', 'domain-system'),
            'update_item' => __('Update Category', 'domain-system'),
            'add_new_item' => __('Add New Category', 'domain-system'),
            'new_item_name' => __('New Category Name', 'domain-system'),
            'separate_items_with_commas' => __('Separate categories with commas', 'domain-system'),
            'add_or_remove_items' => __('Add or remove categories', 'domain-system'),
            'choose_from_most_used' => __('Choose from the most used', 'domain-system'),
            'not_found' => __('No categories found', 'domain-system'),
            'no_terms' => __('No categories', 'domain-system'),
            'items_list_navigation' => __('Categories list navigation', 'domain-system'),
            'items_list' => __('Categories list', 'domain-system'),
            'menu_name' => __('Categories', 'domain-system')
        ];
        
        register_taxonomy('domain_category', ['domain'], [
            'labels' => $labels,
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'domain-category',
                'with_front' => false,
                'hierarchical' => true
            ]
        ]);
    }
    
    /**
     * Create default categories
     */
    public function create_default_terms() {
        if (get_option('domain_categories_created')) {
            return;
        }
        
        $default_categories = [
            'Popular',
            'International', 
            'Academic & Education',
            'Finance',
            'Professional Businesses',
            'Technology',
            'Shopping & Sales',
            'Health & Fitness',
            'Travel',
            'Food & Drink'
        ];
        
        foreach ($default_categories as $category) {
            if (!term_exists($category, 'domain_category')) {
                wp_insert_term($category, 'domain_category');
            }
        }
        
        update_option('domain_categories_created', true);
    }
}