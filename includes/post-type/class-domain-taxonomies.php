<?php
/**
 * Domain Taxonomies Component
 * 
 * Handles the registration of domain taxonomies and default terms
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
        $this->register_tags();
        
        // Create default terms
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
            ],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts'
            ]
        ]);
    }
    
    /**
     * Register domain tags taxonomy
     */
    private function register_tags() {
        $labels = [
            'name' => __('Domain Tags', 'domain-system'),
            'singular_name' => __('Domain Tag', 'domain-system'),
            'search_items' => __('Search Tags', 'domain-system'),
            'popular_items' => __('Popular Tags', 'domain-system'),
            'all_items' => __('All Tags', 'domain-system'),
            'edit_item' => __('Edit Tag', 'domain-system'),
            'update_item' => __('Update Tag', 'domain-system'),
            'add_new_item' => __('Add New Tag', 'domain-system'),
            'new_item_name' => __('New Tag Name', 'domain-system'),
            'separate_items_with_commas' => __('Separate tags with commas', 'domain-system'),
            'add_or_remove_items' => __('Add or remove tags', 'domain-system'),
            'choose_from_most_used' => __('Choose from the most used', 'domain-system'),
            'not_found' => __('No tags found', 'domain-system'),
            'menu_name' => __('Tags', 'domain-system')
        ];
        
        register_taxonomy('domain_tag', ['domain'], [
            'labels' => $labels,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_rest' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'domain-tag',
                'with_front' => false
            ]
        ]);
    }
    
    /**
     * Create default categories and tags
     */
    public function create_default_terms() {
        if (get_option('domain_categories_created')) {
            return;
        }
        
        $this->create_default_categories();
        $this->create_default_tags();
        
        update_option('domain_categories_created', true);
    }
    
    /**
     * Create default domain categories
     */
    private function create_default_categories() {
        $default_categories = [
            'E-commerce & Retail' => [
                'description' => 'Perfect for online stores and retail businesses',
                'children' => ['Fashion', 'Electronics', 'Home & Garden', 'Sports']
            ],
            'Business & Professional' => [
                'description' => 'Ideal for corporate and professional services',
                'children' => ['Consulting', 'Legal', 'Accounting', 'Marketing']
            ],
            'Creative & Arts' => [
                'description' => 'Great for artists, designers, and creative professionals',
                'children' => ['Photography', 'Design', 'Music', 'Writing']
            ],
            'Technology & Development' => [
                'description' => 'Perfect for tech companies and developers',
                'children' => ['Software', 'Apps', 'Gaming', 'AI & ML']
            ],
            'Health & Wellness' => [
                'description' => 'Ideal for healthcare and wellness businesses',
                'children' => ['Medical', 'Fitness', 'Mental Health', 'Nutrition']
            ],
            'Education & Training' => [
                'description' => 'Great for educational institutions and training',
                'children' => ['Online Courses', 'Universities', 'Certification', 'Tutorials']
            ],
            'Food & Restaurants' => [
                'description' => 'Perfect for restaurants and food businesses',
                'children' => ['Restaurants', 'Recipes', 'Catering', 'Food Delivery']
            ],
            'Travel & Tourism' => [
                'description' => 'Ideal for travel and tourism services',
                'children' => ['Hotels', 'Tours', 'Transportation', 'Destinations']
            ],
            'Real Estate' => [
                'description' => 'Great for real estate professionals',
                'children' => ['Residential', 'Commercial', 'Rental', 'Investment']
            ],
            'Finance & Insurance' => [
                'description' => 'Perfect for financial services',
                'children' => ['Banking', 'Investment', 'Insurance', 'Cryptocurrency']
            ]
        ];
        
        foreach ($default_categories as $name => $data) {
            if (!term_exists($name, 'domain_category')) {
                $parent_term = wp_insert_term($name, 'domain_category', [
                    'description' => $data['description']
                ]);
                
                if (!is_wp_error($parent_term) && isset($data['children'])) {
                    foreach ($data['children'] as $child_name) {
                        if (!term_exists($child_name, 'domain_category')) {
                            wp_insert_term($child_name, 'domain_category', [
                                'parent' => $parent_term['term_id']
                            ]);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Create default domain tags
     */
    private function create_default_tags() {
        $default_tags = [
            'Popular', 'New', 'Premium', 'Business', 'Personal',
            'Professional', 'Creative', 'International', 'Short',
            'Brandable', 'Memorable', 'SEO-Friendly', 'Trending',
            'Industry-Specific', 'Generic', 'Geographic'
        ];
        
        foreach ($default_tags as $tag) {
            if (!term_exists($tag, 'domain_tag')) {
                wp_insert_term($tag, 'domain_tag');
            }
        }
    }
    
    /**
     * Get category hierarchy for display
     */
    public function get_category_hierarchy() {
        $categories = get_terms([
            'taxonomy' => 'domain_category',
            'hide_empty' => false,
            'parent' => 0
        ]);
        
        $hierarchy = [];
        
        foreach ($categories as $category) {
            $children = get_terms([
                'taxonomy' => 'domain_category',
                'hide_empty' => false,
                'parent' => $category->term_id
            ]);
            
            $hierarchy[$category->term_id] = [
                'term' => $category,
                'children' => $children
            ];
        }
        
        return $hierarchy;
    }
    
    /**
     * Get popular tags
     */
    public function get_popular_tags($limit = 10) {
        return get_terms([
            'taxonomy' => 'domain_tag',
            'orderby' => 'count',
            'order' => 'DESC',
            'number' => $limit,
            'hide_empty' => false
        ]);
    }
}