<?php
/**
 * Domain Admin Interface Component
 * 
 * Handles admin interface enhancements for domain post type
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainAdminInterface {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_init', [$this, 'init']);
    }
    
    /**
     * Initialize admin interface
     */
    public function init() {
        // Add admin hooks
        add_filter('manage_domain_posts_columns', [$this, 'add_admin_columns']);
        add_action('manage_domain_posts_custom_column', [$this, 'populate_admin_columns'], 10, 2);
        add_filter('manage_edit-domain_sortable_columns', [$this, 'add_sortable_columns']);
        add_action('pre_get_posts', [$this, 'handle_column_sorting']);
        add_action('admin_head', [$this, 'add_admin_styles']);
    }
    
    /**
     * Add custom columns to domain list
     */
    public function add_admin_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['domain_tld'] = __('TLD', 'domain-system');
        $new_columns['domain_price'] = __('Price', 'domain-system');
        $new_columns['domain_category'] = __('Category', 'domain-system');
        $new_columns['domain_status'] = __('Status', 'domain-system');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Populate custom columns
     */
public function populate_admin_columns($column, $post_id) {
    switch ($column) {
        case 'domain_tld':
            $tld = get_post_meta($post_id, '_domain_tld', true);
            if (!empty($tld)) {
                $display_tld = ltrim($tld, '.');
                echo '<strong>.' . esc_html($display_tld) . '</strong>';
                echo '<div class="row-actions">';
                echo '<span class="view"><a href="' . esc_url(get_permalink($post_id)) . '" target="_blank">' . __('View', 'domain-system') . '</a></span>';
                echo '</div>';
            } else {
                echo '<span style="color: #dc3232;">' . __('Not set', 'domain-system') . '</span>';
            }
            break;
            
        case 'domain_price':
            // Check for roundup price first, then fallback to regular price
            $price = get_post_meta($post_id, '_domain_registration_roundup', true);
            if (empty($price)) {
                $price = get_post_meta($post_id, '_domain_registration_price', true);
            }
            
            $renewal = get_post_meta($post_id, '_domain_renewal_roundup', true);
            if (empty($renewal)) {
                $renewal = get_post_meta($post_id, '_domain_renewal_price', true);
            }
            
            if (!empty($price)) {
                echo '<strong>' . $this->format_price($price) . '</strong>';
                if (!empty($renewal)) {
                    echo '<br><small>' . __('Renewal:', 'domain-system') . ' ' . $this->format_price($renewal) . '</small>';
                }
            } else {
                echo '<span style="color: #dc3232;">' . __('Not set', 'domain-system') . '</span>';
            }
            break;
            
        case 'domain_category':
            // Updated to use taxonomy instead of meta fields
            $categories = wp_get_post_terms($post_id, 'domain_category');
            
            if (!empty($categories) && !is_wp_error($categories)) {
                $category_names = [];
                $primary_category = $categories[0]; // First category as primary
                
                foreach ($categories as $category) {
                    $color = get_term_meta($category->term_id, 'category_color', true) ?: '#0073aa';
                    $icon = get_term_meta($category->term_id, 'category_icon', true);
                    
                    $badge_html = '<span class="category-badge" style="background-color: ' . esc_attr($color) . ';">';
                    if ($icon) {
                        $badge_html .= '<span class="dashicons ' . esc_attr($icon) . '" style="font-size: 12px; line-height: 1;"></span> ';
                    }
                    $badge_html .= esc_html($category->name) . '</span>';
                    
                    $category_names[] = $badge_html;
                }
                
                echo implode(' ', $category_names);
                
                // Show features if any
                $features = wp_get_post_terms($post_id, 'domain_feature', ['number' => 3]);
                if (!empty($features) && !is_wp_error($features)) {
                    echo '<br><small>' . count($features) . ' ' . __('features', 'domain-system') . '</small>';
                }
            } else {
                echo '<span style="color: #666;">' . __('No category', 'domain-system') . '</span>';
            }
            break;
            
        case 'domain_status':
            $tld = get_post_meta($post_id, '_domain_tld', true);
            $price = get_post_meta($post_id, '_domain_registration_roundup', true) ?: get_post_meta($post_id, '_domain_registration_price', true);
            $categories = wp_get_post_terms($post_id, 'domain_category');
            
            $status = 'complete';
            $issues = [];
            
            if (empty($tld)) {
                $status = 'incomplete';
                $issues[] = __('No TLD', 'domain-system');
            }
            
            if (empty($price)) {
                $status = 'incomplete';
                $issues[] = __('No price', 'domain-system');
            }
            
            if (empty($categories) || is_wp_error($categories)) {
                $status = 'incomplete';
                $issues[] = __('No category', 'domain-system');
            }
            
            if ($status === 'complete') {
                echo '<span style="color: #46b450;" title="' . __('Complete', 'domain-system') . '">●</span>';
            } else {
                echo '<span style="color: #dc3232;" title="' . implode(', ', $issues) . '">●</span>';
            }
            break;
    }
}
    
   
    
   /**
 * Handle column sorting
 */
public function handle_column_sorting($query) {
    if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'domain') {
        return;
    }

    $orderby = sanitize_key($query->get('orderby'));

    switch ($orderby) {
        case 'domain_tld':
            $query->set('meta_key', '_domain_tld');
            $query->set('orderby', 'meta_value');
            break;

        case 'domain_price':
            // Consider using a normalized meta key for sorting if both keys are used
            $query->set('meta_key', '_domain_registration_roundup');
            $query->set('orderby', 'meta_value_num');
            break;

        case 'domain_category':
            // Only works if you're saving a meta key for primary category
            $query->set('meta_key', '_domain_primary_category');
            $query->set('orderby', 'meta_value');
            break;
    }
}

    
    /**
     * Add admin styles
     */
    public function add_admin_styles() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'domain') {
        ?>
        <style>
        .category-badge {
            display: inline-block;
            padding: 2px 8px;
            background: #0073aa;
            color: white;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            margin: 1px;
            white-space: nowrap;
        }
        
        .category-badge .dashicons {
            margin-right: 2px;
            width: 12px;
            height: 12px;
            font-size: 12px;
        }
        
        .column-domain_tld {
            width: 120px;
        }
        
        .column-domain_price {
            width: 100px;
        }
        
        .column-domain_category {
            width: 200px;
        }
        
        .column-domain_status {
            width: 60px;
            text-align: center;
        }
        
        .domain-status-complete {
            color: #46b450;
        }
        
        .domain-status-incomplete {
            color: #dc3232;
        }
        
        .wp-list-table .column-domain_status {
            text-align: center;
        }
        
        .wp-list-table .column-domain_status span {
            font-size: 16px;
            cursor: help;
        }
        
        /* Category filter dropdown styling */
        .tablenav .actions select {
            max-width: 200px;
        }
        </style>
        <?php
    }
}
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        $screen = get_current_screen();
        if ($screen && $screen->post_type === 'domain') {
            // Add any admin scripts here if needed
        }
    }
    
    /**
     * Render title notices
     */
    public function render_title_notices($post) {
        if ($post->post_type !== 'domain') {
            return;
        }
        
        $tld = get_post_meta($post->ID, '_domain_tld', true);
        if (!empty($tld) && empty($post->post_title)) {
            echo '<div class="notice notice-info inline">';
            echo '<p>' . __('Title will be auto-generated based on TLD if left empty.', 'domain-system') . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Show admin notices
     */
    public function show_notices() {
        // Check for validation errors
        if (isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            $errors = get_post_meta($post_id, '_domain_validation_errors', true);
            
            if (!empty($errors) && is_array($errors)) {
                echo '<div class="notice notice-error">';
                echo '<p><strong>' . __('Domain validation errors:', 'domain-system') . '</strong></p>';
                echo '<ul>';
                foreach ($errors as $error) {
                    echo '<li>' . esc_html($error) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        }
        
        // Show setup notices for new installations
        $domain_count = wp_count_posts('domain');
        if (($domain_count->publish + $domain_count->draft) === 0) {
            echo '<div class="notice notice-info">';
            echo '<p>' . sprintf(
                __('Welcome to Domain System! <a href="%s">Create your first domain</a> or <a href="%s">import domains via CSV</a>.', 'domain-system'),
                admin_url('post-new.php?post_type=domain'),
                admin_url('edit.php?post_type=domain&page=domain-bulk-operations')
            ) . '</p>';
            echo '</div>';
        }
    }
    
    /**
     * Format price for display
     */
    private function format_price($price) {
        if (empty($price) || !is_numeric($price)) {
            return '';
        }
        
        $currency_symbol = get_option('domain_currency_symbol', '$');
        return $currency_symbol . number_format((float)$price, 2);
    }
    
    /**
     * Get domain categories
     */
    private function get_domain_categories() {
        return [
            'popular' => __('Popular', 'domain-system'),
            'international' => __('International', 'domain-system'),
            'academic-education' => __('Academic & Education', 'domain-system'),
            'finance' => __('Finance', 'domain-system'),
            'professional-businesses' => __('Professional Businesses', 'domain-system'),
            'audio-video' => __('Audio & Video', 'domain-system'),
            'arts-culture' => __('Arts & Culture', 'domain-system'),
            'marketing' => __('Marketing', 'domain-system'),
            'products' => __('Products', 'domain-system'),
            'services' => __('Services', 'domain-system'),
            'short' => __('Short', 'domain-system'),
            'new' => __('New', 'domain-system'),
            'adult' => __('Adult', 'domain-system'),
            'technology' => __('Technology', 'domain-system'),
            'real-estate' => __('Real Estate', 'domain-system'),
            'politics' => __('Politics', 'domain-system'),
            'budget' => __('$3 or less', 'domain-system'),
            'organizations' => __('Organizations', 'domain-system'),
            'shopping-sales' => __('Shopping & Sales', 'domain-system'),
            'media-music' => __('Media & Music', 'domain-system'),
            'fun' => __('Fun', 'domain-system'),
            'sports-hobbies' => __('Sports & Hobbies', 'domain-system'),
            'transport' => __('Transport', 'domain-system'),
            'personal' => __('Personal', 'domain-system'),
            'social-lifestyle' => __('Social & Lifestyle', 'domain-system'),
            'food-drink' => __('Food & Drink', 'domain-system'),
            'beauty' => __('Beauty', 'domain-system'),
            'cities' => __('Cities', 'domain-system'),
            'travel' => __('Travel', 'domain-system'),
            'health-fitness' => __('Health & Fitness', 'domain-system'),
            'colors' => __('Colors', 'domain-system'),
            'trades-construction' => __('Trades & Construction', 'domain-system'),
            'non-english' => __('Non-English', 'domain-system'),
            'religion' => __('Religion', 'domain-system')
        ];
    }
    
    /**
     * Add bulk actions
     */
    public function add_bulk_actions($actions) {
        $actions['export_domains'] = __('Export Selected', 'domain-system');
        $actions['update_category'] = __('Update Category', 'domain-system');
        return $actions;
    }
    
    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions($redirect_to, $action, $post_ids) {
        if ($action === 'export_domains') {
            // Handle export
            $redirect_to = add_query_arg('bulk_exported', count($post_ids), $redirect_to);
        } elseif ($action === 'update_category') {
            // Handle category update
            $redirect_to = add_query_arg('bulk_updated', count($post_ids), $redirect_to);
        }
        
        return $redirect_to;
    }
    
    /**
     * Show bulk action notices
     */
    public function show_bulk_action_notices() {
        if (!empty($_REQUEST['bulk_exported'])) {
            $count = intval($_REQUEST['bulk_exported']);
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(_n('%d domain exported.', '%d domains exported.', $count, 'domain-system'), $count)
            );
        }
        
        if (!empty($_REQUEST['bulk_updated'])) {
            $count = intval($_REQUEST['bulk_updated']);
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(_n('%d domain updated.', '%d domains updated.', $count, 'domain-system'), $count)
            );
        }
    }
}