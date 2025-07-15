<?php
/**
 * Domain Taxonomies Component - Updated with Real Categories
 * 
 * File: includes/post-type/class-domain-taxonomies.php
 * 
 * Handles registration and management of domain taxonomies
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainTaxonomies {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'register'], 5);
        add_action('admin_init', [$this, 'init_admin_hooks']);
        add_action('init', [$this, 'create_default_categories'], 20);
    }
    
    /**
     * Register taxonomies
     */
    public function register() {
        // Domain Category taxonomy
        register_taxonomy('domain_category', 'domain', [
            'hierarchical' => true,
            'labels' => [
                'name' => __('Domain Categories', 'domain-system'),
                'singular_name' => __('Domain Category', 'domain-system'),
                'search_items' => __('Search Categories', 'domain-system'),
                'all_items' => __('All Categories', 'domain-system'),
                'parent_item' => __('Parent Category', 'domain-system'),
                'parent_item_colon' => __('Parent Category:', 'domain-system'),
                'edit_item' => __('Edit Category', 'domain-system'),
                'update_item' => __('Update Category', 'domain-system'),
                'add_new_item' => __('Add New Category', 'domain-system'),
                'new_item_name' => __('New Category Name', 'domain-system'),
                'menu_name' => __('Categories', 'domain-system'),
                'not_found' => __('No categories found.', 'domain-system'),
                'back_to_items' => __('← Back to Categories', 'domain-system'),
                'item_link' => __('Category Link', 'domain-system'),
                'item_link_description' => __('A link to a category.', 'domain-system'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rest_base' => 'domain-categories',
            'query_var' => true,
            'rewrite' => [
                'slug' => 'domain-category',
                'with_front' => false,
                'hierarchical' => true,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ],
            'meta_box_cb' => [$this, 'category_meta_box'],
        ]);
        
        // Domain Tags taxonomy (optional)
        register_taxonomy('domain_tag', 'domain', [
            'hierarchical' => false,
            'labels' => [
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
                'choose_from_most_used' => __('Choose from the most used tags', 'domain-system'),
                'not_found' => __('No tags found.', 'domain-system'),
                'menu_name' => __('Tags', 'domain-system'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rest_base' => 'domain-tags',
            'query_var' => true,
            'rewrite' => [
                'slug' => 'domain-tag',
                'with_front' => false,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ],
        ]);
        
        // Domain Registry taxonomy
        register_taxonomy('domain_registry', 'domain', [
            'hierarchical' => false,
            'labels' => [
                'name' => __('Domain Registries', 'domain-system'),
                'singular_name' => __('Domain Registry', 'domain-system'),
                'search_items' => __('Search Registries', 'domain-system'),
                'popular_items' => __('Popular Registries', 'domain-system'),
                'all_items' => __('All Registries', 'domain-system'),
                'edit_item' => __('Edit Registry', 'domain-system'),
                'update_item' => __('Update Registry', 'domain-system'),
                'add_new_item' => __('Add New Registry', 'domain-system'),
                'new_item_name' => __('New Registry Name', 'domain-system'),
                'separate_items_with_commas' => __('Separate registries with commas', 'domain-system'),
                'add_or_remove_items' => __('Add or remove registries', 'domain-system'),
                'choose_from_most_used' => __('Choose from the most used registries', 'domain-system'),
                'not_found' => __('No registries found.', 'domain-system'),
                'menu_name' => __('Registries', 'domain-system'),
            ],
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'show_in_quick_edit' => true,
            'show_admin_column' => false,
            'show_in_rest' => true,
            'rest_base' => 'domain-registries',
            'query_var' => true,
            'rewrite' => [
                'slug' => 'domain-registry',
                'with_front' => false,
            ],
            'capabilities' => [
                'manage_terms' => 'manage_categories',
                'edit_terms' => 'manage_categories',
                'delete_terms' => 'manage_categories',
                'assign_terms' => 'edit_posts',
            ],
            'meta_box_cb' => false, // We'll handle this in meta boxes
        ]);
    }
    
    /**
     * Initialize admin hooks
     */
    public function init_admin_hooks() {
        // Add custom fields to category forms
        add_action('domain_category_add_form_fields', [$this, 'add_category_fields']);
        add_action('domain_category_edit_form_fields', [$this, 'edit_category_fields']);
        add_action('created_domain_category', [$this, 'save_category_fields']);
        add_action('edited_domain_category', [$this, 'save_category_fields']);
        
        // Add custom columns to category list
        add_filter('manage_edit-domain_category_columns', [$this, 'add_category_columns']);
        add_filter('manage_domain_category_custom_column', [$this, 'populate_category_columns'], 10, 3);
        
        // Add admin styles
        add_action('admin_head', [$this, 'add_taxonomy_admin_styles']);
        
        // Clear cache when terms are modified
        add_action('created_domain_category', [$this, 'clear_taxonomy_cache']);
        add_action('edited_domain_category', [$this, 'clear_taxonomy_cache']);
        add_action('deleted_domain_category', [$this, 'clear_taxonomy_cache']);
    }
    
    /**
     * Custom category meta box with primary/secondary selection
     */
    public function category_meta_box($post) {
        $primary_category = get_post_meta($post->ID, '_domain_primary_category', true);
        $secondary_categories = wp_get_post_terms($post->ID, 'domain_category', ['fields' => 'ids']);
        
        // Remove primary category from secondary list
        if ($primary_category) {
            $secondary_categories = array_diff($secondary_categories, [$primary_category]);
        }
        
        $categories = get_terms([
            'taxonomy' => 'domain_category',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC'
        ]);
        ?>
        <div id="domain-category-meta-box">
            <div class="categorydiv">
                <h4><?php _e('Primary Category', 'domain-system'); ?> <span class="required">*</span></h4>
                <p class="description"><?php _e('Select the main category for this domain extension.', 'domain-system'); ?></p>
                
                <select name="domain_primary_category" id="domain_primary_category" class="widefat" required>
                    <option value=""><?php _e('Select Primary Category', 'domain-system'); ?></option>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($primary_category, $category->term_id); ?>>
                        <?php echo esc_html($category->name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <h4 style="margin-top: 20px;"><?php _e('Additional Categories', 'domain-system'); ?></h4>
                <p class="description"><?php _e('Select additional categories that apply to this domain.', 'domain-system'); ?></p>
                
                <div class="category-checklist">
                    <?php foreach ($categories as $category): ?>
                    <label>
                        <input type="checkbox" 
                               name="tax_input[domain_category][]" 
                               value="<?php echo esc_attr($category->term_id); ?>"
                               <?php checked(in_array($category->term_id, $secondary_categories)); ?>
                               data-primary-check="<?php echo esc_attr($category->term_id); ?>" />
                        <?php echo esc_html($category->name); ?>
                    </label><br>
                    <?php endforeach; ?>
                </div>
                
                <p style="margin-top: 15px;">
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=domain_category&post_type=domain'); ?>" target="_blank">
                        <?php _e('Manage Categories', 'domain-system'); ?>
                    </a>
                </p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Update checkboxes when primary category changes
            $('#domain_primary_category').on('change', function() {
                var primaryId = $(this).val();
                
                // Uncheck all first
                $('.category-checklist input[type="checkbox"]').prop('checked', false);
                
                // Check the primary category if selected
                if (primaryId) {
                    $('.category-checklist input[data-primary-check="' + primaryId + '"]').prop('checked', true);
                }
            });
            
            // Prevent unchecking primary category
            $('.category-checklist input[type="checkbox"]').on('change', function() {
                var primaryId = $('#domain_primary_category').val();
                var thisId = $(this).data('primary-check');
                
                if (primaryId == thisId && !$(this).is(':checked')) {
                    $(this).prop('checked', true);
                    alert('<?php echo esc_js(__('You cannot uncheck the primary category. Please select a different primary category first.', 'domain-system')); ?>');
                }
            });
        });
        </script>
        <?php
    }
    
    /**
     * Add fields to category add form
     */
    public function add_category_fields() {
        ?>
        <div class="form-field term-icon-wrap">
            <label for="category-icon"><?php _e('Category Icon', 'domain-system'); ?></label>
            <input type="text" id="category-icon" name="category_icon" placeholder="fas fa-globe" />
            <p><?php _e('Font Awesome icon class (e.g., fas fa-globe, fas fa-shopping-cart)', 'domain-system'); ?></p>
        </div>
        
        <div class="form-field term-color-wrap">
            <label for="category-color"><?php _e('Category Color', 'domain-system'); ?></label>
            <input type="color" id="category-color" name="category_color" value="#0073aa" />
            <p><?php _e('Color used for category badges and highlights', 'domain-system'); ?></p>
        </div>
        
        <div class="form-field term-order-wrap">
            <label for="category-order"><?php _e('Display Order', 'domain-system'); ?></label>
            <input type="number" id="category-order" name="category_order" value="0" min="0" />
            <p><?php _e('Order in which this category appears (0 = first)', 'domain-system'); ?></p>
        </div>
        
        <div class="form-field term-featured-wrap">
            <label>
                <input type="checkbox" id="category-featured" name="category_featured" value="1" />
                <?php _e('Featured Category', 'domain-system'); ?>
            </label>
            <p><?php _e('Featured categories appear prominently on the frontend', 'domain-system'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Add fields to category edit form
     */
    public function edit_category_fields($term) {
        $icon = get_term_meta($term->term_id, 'category_icon', true);
        $color = get_term_meta($term->term_id, 'category_color', true) ?: '#0073aa';
        $order = get_term_meta($term->term_id, 'category_order', true) ?: 0;
        $featured = get_term_meta($term->term_id, 'category_featured', true);
        ?>
        <tr class="form-field term-icon-wrap">
            <th scope="row"><label for="category-icon"><?php _e('Category Icon', 'domain-system'); ?></label></th>
            <td>
                <input type="text" id="category-icon" name="category_icon" value="<?php echo esc_attr($icon); ?>" placeholder="fas fa-globe" />
                <p class="description"><?php _e('Font Awesome icon class (e.g., fas fa-globe, fas fa-shopping-cart)', 'domain-system'); ?></p>
                <?php if ($icon): ?>
                <p><strong><?php _e('Preview:', 'domain-system'); ?></strong> <i class="<?php echo esc_attr($icon); ?>"></i></p>
                <?php endif; ?>
            </td>
        </tr>
        
        <tr class="form-field term-color-wrap">
            <th scope="row"><label for="category-color"><?php _e('Category Color', 'domain-system'); ?></label></th>
            <td>
                <input type="color" id="category-color" name="category_color" value="<?php echo esc_attr($color); ?>" />
                <p class="description"><?php _e('Color used for category badges and highlights', 'domain-system'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field term-order-wrap">
            <th scope="row"><label for="category-order"><?php _e('Display Order', 'domain-system'); ?></label></th>
            <td>
                <input type="number" id="category-order" name="category_order" value="<?php echo esc_attr($order); ?>" min="0" />
                <p class="description"><?php _e('Order in which this category appears (0 = first)', 'domain-system'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field term-featured-wrap">
            <th scope="row"><?php _e('Featured Category', 'domain-system'); ?></th>
            <td>
                <label>
                    <input type="checkbox" id="category-featured" name="category_featured" value="1" <?php checked($featured, 1); ?> />
                    <?php _e('Featured categories appear prominently on the frontend', 'domain-system'); ?>
                </label>
            </td>
        </tr>
        <?php
    }
    
    /**
     * Save category custom fields
     */
    public function save_category_fields($term_id) {
        if (isset($_POST['category_icon'])) {
            update_term_meta($term_id, 'category_icon', sanitize_text_field($_POST['category_icon']));
        }
        
        if (isset($_POST['category_color'])) {
            update_term_meta($term_id, 'category_color', sanitize_hex_color($_POST['category_color']));
        }
        
        if (isset($_POST['category_order'])) {
            update_term_meta($term_id, 'category_order', intval($_POST['category_order']));
        }
        
        $featured = isset($_POST['category_featured']) ? 1 : 0;
        update_term_meta($term_id, 'category_featured', $featured);
        
        // Clear cache
        $this->clear_taxonomy_cache($term_id);
    }
    
    /**
     * Add custom columns to category list
     */
    public function add_category_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['name'] = $columns['name'];
        $new_columns['icon'] = __('Icon', 'domain-system');
        $new_columns['color'] = __('Color', 'domain-system');
        $new_columns['order'] = __('Order', 'domain-system');
        $new_columns['featured'] = __('Featured', 'domain-system');
        $new_columns['count'] = $columns['posts'];
        
        return $new_columns;
    }
    
    /**
     * Populate custom columns in category list
     */
    public function populate_category_columns($content, $column_name, $term_id) {
        switch ($column_name) {
            case 'icon':
                $icon = get_term_meta($term_id, 'category_icon', true);
                if ($icon) {
                    $content = '<i class="' . esc_attr($icon) . '" style="font-size: 18px;"></i>';
                } else {
                    $content = '<span style="color: #ccc;">—</span>';
                }
                break;
                
            case 'color':
                $color = get_term_meta($term_id, 'category_color', true) ?: '#0073aa';
                $content = '<div style="width: 20px; height: 20px; background-color: ' . esc_attr($color) . '; border-radius: 3px; border: 1px solid #ddd;"></div>';
                break;
                
            case 'order':
                $order = get_term_meta($term_id, 'category_order', true) ?: 0;
                $content = '<span class="order-badge">' . esc_html($order) . '</span>';
                break;
                
            case 'featured':
                $featured = get_term_meta($term_id, 'category_featured', true);
                if ($featured) {
                    $content = '<span style="color: #46b450;">★ ' . __('Featured', 'domain-system') . '</span>';
                } else {
                    $content = '<span style="color: #ccc;">—</span>';
                }
                break;
        }
        
        return $content;
    }
    
    /**
     * Add admin styles for taxonomies
     */
    public function add_taxonomy_admin_styles() {
        $screen = get_current_screen();
        if ($screen && (strpos($screen->id, 'domain_category') !== false || $screen->post_type === 'domain')) {
            ?>
            <style>
            .category-checklist label {
                display: block;
                margin: 5px 0;
                padding: 3px 0;
            }
            
            .order-badge {
                display: inline-block;
                background: #0073aa;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
                font-weight: bold;
            }
            
            .column-icon, .column-color {
                width: 60px;
            }
            
            .column-order {
                width: 80px;
            }
            
            .column-featured {
                width: 100px;
            }
            
            #domain-category-meta-box .categorydiv {
                margin-bottom: 0;
            }
            
            #domain-category-meta-box h4 {
                margin: 15px 0 8px 0;
                font-weight: 600;
            }
            
            #domain-category-meta-box h4:first-child {
                margin-top: 0;
            }
            
            .required {
                color: #dc3232;
            }
            </style>
            <?php
        }
    }
    
    /**
     * Create default categories
     */
    public function create_default_categories() {
        // Only run once
        if (get_option('domain_default_categories_created')) {
            return;
        }
        
        $default_categories = [
            [
                'name' => __('Popular', 'domain-system'),
                'slug' => 'popular',
                'description' => __('Most popular domain extensions', 'domain-system'),
                'icon' => 'fas fa-star',
                'color' => '#f39c12',
                'order' => 1,
                'featured' => 1
            ],
            [
                'name' => __('International', 'domain-system'),
                'slug' => 'international',
                'description' => __('Country-specific and international domains', 'domain-system'),
                'icon' => 'fas fa-globe',
                'color' => '#3498db',
                'order' => 2,
                'featured' => 1
            ],
            [
                'name' => __('Business', 'domain-system'),
                'slug' => 'business',
                'description' => __('Professional business domains', 'domain-system'),
                'icon' => 'fas fa-briefcase',
                'color' => '#2c3e50',
                'order' => 3,
                'featured' => 1
            ],
            [
                'name' => __('Technology', 'domain-system'),
                'slug' => 'technology',
                'description' => __('Tech-focused domain extensions', 'domain-system'),
                'icon' => 'fas fa-laptop-code',
                'color' => '#9b59b6',
                'order' => 4,
                'featured' => 1
            ],
            [
                'name' => __('Shopping & Sales', 'domain-system'),
                'slug' => 'shopping-sales',
                'description' => __('E-commerce and retail domains', 'domain-system'),
                'icon' => 'fas fa-shopping-cart',
                'color' => '#e74c3c',
                'order' => 5,
                'featured' => 0
            ],
            [
                'name' => __('Creative & Arts', 'domain-system'),
                'slug' => 'creative-arts',
                'description' => __('Creative industry and arts domains', 'domain-system'),
                'icon' => 'fas fa-palette',
                'color' => '#e67e22',
                'order' => 6,
                'featured' => 0
            ],
            [
                'name' => __('Education', 'domain-system'),
                'slug' => 'education',
                'description' => __('Educational and academic domains', 'domain-system'),
                'icon' => 'fas fa-graduation-cap',
                'color' => '#27ae60',
                'order' => 7,
                'featured' => 0
            ],
            [
                'name' => __('Health & Fitness', 'domain-system'),
                'slug' => 'health-fitness',
                'description' => __('Health, medical, and fitness domains', 'domain-system'),
                'icon' => 'fas fa-heartbeat',
                'color' => '#16a085',
                'order' => 8,
                'featured' => 0
            ],
            [
                'name' => __('Finance', 'domain-system'),
                'slug' => 'finance',
                'description' => __('Financial services and banking domains', 'domain-system'),
                'icon' => 'fas fa-dollar-sign',
                'color' => '#f39c12',
                'order' => 9,
                'featured' => 0
            ],
            [
                'name' => __('Real Estate', 'domain-system'),
                'slug' => 'real-estate',
                'description' => __('Property and real estate domains', 'domain-system'),
                'icon' => 'fas fa-home',
                'color' => '#34495e',
                'order' => 10,
                'featured' => 0
            ],
            [
                'name' => __('Travel', 'domain-system'),
                'slug' => 'travel',
                'description' => __('Travel and tourism domains', 'domain-system'),
                'icon' => 'fas fa-plane',
                'color' => '#3498db',
                'order' => 11,
                'featured' => 0
            ],
            [
                'name' => __('Food & Drink', 'domain-system'),
                'slug' => 'food-drink',
                'description' => __('Food, beverage, and restaurant domains', 'domain-system'),
                'icon' => 'fas fa-utensils',
                'color' => '#e74c3c',
                'order' => 12,
                'featured' => 0
            ],
            [
                'name' => __('Sports & Hobbies', 'domain-system'),
                'slug' => 'sports-hobbies',
                'description' => __('Sports, recreation, and hobby domains', 'domain-system'),
                'icon' => 'fas fa-football-ball',
                'color' => '#27ae60',
                'order' => 13,
                'featured' => 0
            ],
            [
                'name' => __('Media & Entertainment', 'domain-system'),
                'slug' => 'media-entertainment',
                'description' => __('Media, entertainment, and music domains', 'domain-system'),
                'icon' => 'fas fa-film',
                'color' => '#9b59b6',
                'order' => 14,
                'featured' => 0
            ],
            [
                'name' => __('Personal', 'domain-system'),
                'slug' => 'personal',
                'description' => __('Personal websites and blogs', 'domain-system'),
                'icon' => 'fas fa-user',
                'color' => '#95a5a6',
                'order' => 15,
                'featured' => 0
            ],
            [
                'name' => __('Organizations', 'domain-system'),
                'slug' => 'organizations',
                'description' => __('Non-profit and organization domains', 'domain-system'),
                'icon' => 'fas fa-users',
                'color' => '#7f8c8d',
                'order' => 16,
                'featured' => 0
            ],
            [
                'name' => __('Budget Friendly', 'domain-system'),
                'slug' => 'budget',
                'description' => __('Affordable domain options under $5', 'domain-system'),
                'icon' => 'fas fa-tag',
                'color' => '#2ecc71',
                'order' => 17,
                'featured' => 0
            ],
            [
                'name' => __('Premium', 'domain-system'),
                'slug' => 'premium',
                'description' => __('Premium and exclusive domain extensions', 'domain-system'),
                'icon' => 'fas fa-crown',
                'color' => '#f1c40f',
                'order' => 18,
                'featured' => 0
            ],
            [
                'name' => __('New Extensions', 'domain-system'),
                'slug' => 'new',
                'description' => __('Recently launched domain extensions', 'domain-system'),
                'icon' => 'fas fa-rocket',
                'color' => '#e67e22',
                'order' => 19,
                'featured' => 0
            ],
            [
                'name' => __('Short Domains', 'domain-system'),
                'slug' => 'short',
                'description' => __('Short and memorable domain extensions', 'domain-system'),
                'icon' => 'fas fa-compress-alt',
                'color' => '#8e44ad',
                'order' => 20,
                'featured' => 0
            ]
        ];
        
        foreach ($default_categories as $category_data) {
            // Check if category already exists
            $existing = term_exists($category_data['slug'], 'domain_category');
            if (!$existing) {
                $result = wp_insert_term(
                    $category_data['name'],
                    'domain_category',
                    [
                        'slug' => $category_data['slug'],
                        'description' => $category_data['description']
                    ]
                );
                
                if (!is_wp_error($result)) {
                    $term_id = $result['term_id'];
                    
                    // Add custom meta fields
                    update_term_meta($term_id, 'category_icon', $category_data['icon']);
                    update_term_meta($term_id, 'category_color', $category_data['color']);
                    update_term_meta($term_id, 'category_order', $category_data['order']);
                    update_term_meta($term_id, 'category_featured', $category_data['featured']);
                }
            }
        }
        
        // Mark as created
        update_option('domain_default_categories_created', true);
    }
    
    /**
     * Clear taxonomy cache
     */
    public function clear_taxonomy_cache($term_id = null) {
        wp_cache_delete('domain_categories_list', 'domain_system');
        wp_cache_delete('domain_featured_categories', 'domain_system');
        wp_cache_delete('domain_category_colors', 'domain_system');
        
        // Clear domain system cache as well
        if (function_exists('clear_domain_cache')) {
            clear_domain_cache();
        }
        
        do_action('domain_taxonomy_cache_cleared', $term_id);
    }
    
    /**
     * Get formatted categories list for use in meta boxes and forms
     */
    public function get_categories_for_select() {
        $cache_key = 'domain_categories_list';
        $categories = wp_cache_get($cache_key, 'domain_system');
        
        if ($categories === false) {
            $terms = get_terms([
                'taxonomy' => 'domain_category',
                'hide_empty' => false,
                'orderby' => 'meta_value_num name',
                'order' => 'ASC',
                'meta_key' => 'category_order'
            ]);
            
            $categories = [];
            foreach ($terms as $term) {
                $categories[$term->term_id] = $term->name;
            }
            
            wp_cache_set($cache_key, $categories, 'domain_system', HOUR_IN_SECONDS);
        }
        
        return $categories;
    }
    
    /**
     * Get featured categories
     */
    public function get_featured_categories() {
        $cache_key = 'domain_featured_categories';
        $categories = wp_cache_get($cache_key, 'domain_system');
        
        if ($categories === false) {
            $terms = get_terms([
                'taxonomy' => 'domain_category',
                'hide_empty' => false,
                'meta_query' => [
                    [
                        'key' => 'category_featured',
                        'value' => '1',
                        'compare' => '='
                    ]
                ],
                'orderby' => 'meta_value_num name',
                'order' => 'ASC',
                'meta_key' => 'category_order'
            ]);
            
            $categories = [];
            foreach ($terms as $term) {
                $categories[] = [
                    'term_id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'description' => $term->description,
                    'icon' => get_term_meta($term->term_id, 'category_icon', true),
                    'color' => get_term_meta($term->term_id, 'category_color', true),
                    'order' => get_term_meta($term->term_id, 'category_order', true),
                    'count' => $term->count
                ];
            }
            
            wp_cache_set($cache_key, $categories, 'domain_system', HOUR_IN_SECONDS);
        }
        
        return $categories;
    }
    
    /**
     * Get category colors for CSS
     */
    public function get_category_colors() {
        $cache_key = 'domain_category_colors';
        $colors = wp_cache_get($cache_key, 'domain_system');
        
        if ($colors === false) {
            $terms = get_terms([
                'taxonomy' => 'domain_category',
                'hide_empty' => false
            ]);
            
            $colors = [];
            foreach ($terms as $term) {
                $color = get_term_meta($term->term_id, 'category_color', true);
                if ($color) {
                    $colors[$term->term_id] = $color;
                }
            }
            
            wp_cache_set($cache_key, $colors, 'domain_system', HOUR_IN_SECONDS);
        }
        
        return $colors;
    }
    
    /**
     * Get domains by category with enhanced data
     */
    public function get_domains_by_category($category_id, $limit = -1) {
        $args = [
            'post_type' => 'domain',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'tax_query' => [
                [
                    'taxonomy' => 'domain_category',
                    'field' => 'term_id',
                    'terms' => $category_id
                ]
            ],
            'meta_query' => [
                [
                    'key' => '_domain_tld',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => '_domain_registration_roundup',
                    'compare' => 'EXISTS'
                ]
            ],
            'orderby' => 'menu_order title',
            'order' => 'ASC'
        ];
        
        $query = new WP_Query($args);
        $domains = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                
                if (function_exists('get_domain_data')) {
                    $domains[] = get_domain_data(get_the_ID());
                } else {
                    // Fallback if domain functions not available
                    $domains[] = [
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'tld' => get_post_meta(get_the_ID(), '_domain_tld', true),
                        'price' => get_post_meta(get_the_ID(), '_domain_registration_roundup', true),
                        'url' => get_permalink()
                    ];
                }
            }
            wp_reset_postdata();
        }
        
        return $domains;
    }
}