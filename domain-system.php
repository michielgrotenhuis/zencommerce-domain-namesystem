<?php
/**
 * Plugin Name: Domain System
 * Plugin URI: https://yourwebsite.com/domain-system
 * Description: A comprehensive domain management system for WordPress with TLD information, pricing, and analytics.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: domain-system
 *
 * @package DomainSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOMAIN_SYSTEM_VERSION', '1.0.0');
define('DOMAIN_SYSTEM_PLUGIN_FILE', __FILE__);
define('DOMAIN_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOMAIN_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DOMAIN_SYSTEM_INCLUDES_DIR', DOMAIN_SYSTEM_PLUGIN_DIR . 'includes/');
define('DOMAIN_SYSTEM_ASSETS_URL', DOMAIN_SYSTEM_PLUGIN_URL . 'assets/');
define('DOMAIN_SYSTEM_TEMPLATES_DIR', DOMAIN_SYSTEM_PLUGIN_DIR . 'templates/');

/**
 * Debug function to check plugin status
 */
function domain_system_debug() {
    error_log('=== DOMAIN SYSTEM DEBUG START ===');
    error_log('Plugin activated: ' . (is_plugin_active(plugin_basename(__FILE__)) ? 'YES' : 'NO'));
    error_log('Constants defined: ' . (defined('DOMAIN_SYSTEM_VERSION') ? 'YES' : 'NO'));
    error_log('=== DOMAIN SYSTEM DEBUG END ===');
}

/**
 * Register domain post type directly
 */
function domain_system_register_post_type() {
    error_log('Registering domain post type...');
    
    $labels = [
        'name' => __('Domains', 'domain-system'),
        'singular_name' => __('Domain', 'domain-system'),
        'menu_name' => __('Domains', 'domain-system'),
        'name_admin_bar' => __('Domain', 'domain-system'),
        'add_new' => __('Add New', 'domain-system'),
        'add_new_item' => __('Add New Domain', 'domain-system'),
        'new_item' => __('New Domain', 'domain-system'),
        'edit_item' => __('Edit Domain', 'domain-system'),
        'view_item' => __('View Domain', 'domain-system'),
        'all_items' => __('All Domains', 'domain-system'),
        'search_items' => __('Search Domains', 'domain-system'),
        'not_found' => __('No domains found.', 'domain-system'),
        'not_found_in_trash' => __('No domains found in Trash.', 'domain-system'),
    ];

    $args = [
        'labels' => $labels,
        'description' => __('Domain management system', 'domain-system'),
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'rewrite' => ['slug' => 'domains'],
        'capability_type' => 'post',
        'has_archive' => true,
        'hierarchical' => false,
        'menu_position' => 25,
        'menu_icon' => 'dashicons-admin-site-alt3',
        'supports' => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true,
    ];

    $result = register_post_type('domain', $args);
    
    if (is_wp_error($result)) {
        error_log('Domain post type registration failed: ' . $result->get_error_message());
    } else {
        error_log('Domain post type registered successfully');
    }
    
    // Verify registration
    if (post_type_exists('domain')) {
        error_log('Domain post type EXISTS after registration');
    } else {
        error_log('Domain post type DOES NOT EXIST after registration');
    }
}

/**
 * Create basic database options
 */
function domain_system_create_options() {
    $defaults = [
        'domain_currency_symbol' => '$',
        'domain_posts_per_page' => 10,
        'domain_enable_analytics' => '1',
    ];
    
    foreach ($defaults as $option => $value) {
        if (get_option($option) === false) {
            add_option($option, $value);
        }
    }
}

/**
 * Load includes safely
 */
function domain_system_load_includes() {
    // Load functions file
    $functions_file = DOMAIN_SYSTEM_INCLUDES_DIR . 'functions.php';
    if (file_exists($functions_file)) {
        require_once $functions_file;
        error_log('Functions file loaded successfully');
    } else {
        error_log('Functions file NOT found: ' . $functions_file);
    }
    
    // Load other includes
    $includes = [
        'domain-functions.php',
        'domain-template-functions.php'
    ];
    
    foreach ($includes as $file) {
        $file_path = DOMAIN_SYSTEM_INCLUDES_DIR . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
            error_log("Loaded: {$file}");
        } else {
            error_log("NOT found: {$file}");
        }
    }
    
    // Load post type classes if they exist
    $post_type_files = [
        'post-type/class-domain-registration.php',
        'post-type/class-domain-taxonomies.php',
        'post-type/class-domain-meta-boxes.php',
        'post-type/class-domain-admin-interface.php',
        'post-type/class-domain-post-type.php',
    ];
    
    foreach ($post_type_files as $file) {
        $file_path = DOMAIN_SYSTEM_INCLUDES_DIR . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
            error_log("Loaded post-type file: {$file}");
        } else {
            error_log("NOT found post-type file: {$file}");
        }
    }
    
    // Load the assets handler
    $assets_file = DOMAIN_SYSTEM_INCLUDES_DIR . 'class-domain-system-assets.php';
    if (file_exists($assets_file)) {
        require_once $assets_file;
        new DomainSystemAssets();
        error_log('Assets handler loaded');
    } else {
        error_log('Assets handler NOT found: ' . $assets_file);
    }
}

/**
 * Initialize meta boxes
 */
function domain_system_add_meta_boxes() {
    if (class_exists('DomainMetaBoxes')) {
        $meta_boxes = new DomainMetaBoxes();
        $meta_boxes->add_boxes();
        error_log('Meta boxes added via class');
    } else {
        // Enhanced fallback meta boxes
        add_meta_box(
            'domain-enhanced-info',
            __('Domain Information', 'domain-system'),
            'domain_system_enhanced_meta_box',
            'domain',
            'normal',
            'high'
        );
        
        add_meta_box(
            'domain-faqs',
            __('Domain FAQs', 'domain-system'),
            'domain_system_faqs_meta_box',
            'domain',
            'normal',
            'default'
        );
        
        add_meta_box(
            'domain-tools',
            __('Domain Tools', 'domain-system'),
            'domain_system_tools_meta_box',
            'domain',
            'side',
            'high'
        );
        
        error_log('Enhanced fallback meta boxes added');
    }
}

/**
 * Enhanced meta box callback
 */
function domain_system_enhanced_meta_box($post) {
    wp_nonce_field('domain_meta_save', 'domain_meta_nonce');
    
    $tld = get_post_meta($post->ID, '_domain_tld', true);
    $reg_price = get_post_meta($post->ID, '_domain_registration_price', true);
    $renewal_price = get_post_meta($post->ID, '_domain_renewal_price', true);
    $category = get_post_meta($post->ID, '_domain_category', true);
    ?>
    <table class="form-table domain-form-table">
        <tr>
            <th><label for="domain_tld"><?php _e('TLD (Required)', 'domain-system'); ?></label></th>
            <td>
                <div class="domain-field-wrapper">
                    <input type="text" id="domain_tld" name="domain_tld" value="<?php echo esc_attr($tld); ?>" placeholder=".shop" class="regular-text" required />
                    <div class="domain-field-status"></div>
                </div>
                <p class="description"><?php _e('Domain extension (e.g., .shop, .com, .co.uk)', 'domain-system'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="domain_registration_price"><?php _e('Registration Price', 'domain-system'); ?></label></th>
            <td>
                <div class="domain-price-input">
                    <span class="currency-symbol">$</span>
                    <input type="number" step="0.01" min="0" id="domain_registration_price" name="domain_registration_price" value="<?php echo esc_attr($reg_price); ?>" class="small-text" />
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="domain_renewal_price"><?php _e('Renewal Price', 'domain-system'); ?></label></th>
            <td>
                <div class="domain-price-input">
                    <span class="currency-symbol">$</span>
                    <input type="number" step="0.01" min="0" id="domain_renewal_price" name="domain_renewal_price" value="<?php echo esc_attr($renewal_price); ?>" class="small-text" />
                </div>
            </td>
        </tr>
        <tr>
            <th><label for="domain_category"><?php _e('Category', 'domain-system'); ?></label></th>
            <td>
                <select id="domain_category" name="domain_category" class="regular-text">
                    <option value=""><?php _e('Select Category', 'domain-system'); ?></option>
                    <option value="popular" <?php selected($category, 'popular'); ?>><?php _e('Popular', 'domain-system'); ?></option>
                    <option value="business" <?php selected($category, 'business'); ?>><?php _e('Business', 'domain-system'); ?></option>
                    <option value="technology" <?php selected($category, 'technology'); ?>><?php _e('Technology', 'domain-system'); ?></option>
                    <option value="shopping" <?php selected($category, 'shopping'); ?>><?php _e('Shopping', 'domain-system'); ?></option>
                </select>
            </td>
        </tr>
    </table>
    
    <div class="domain-content-generator">
        <h4><?php _e('Content Generation', 'domain-system'); ?></h4>
        <p><?php _e('Generate content automatically based on the TLD and category.', 'domain-system'); ?></p>
        <button type="button" class="button button-secondary generate-content">
            <span class="dashicons dashicons-superhero"></span>
            <span><?php _e('Generate Content', 'domain-system'); ?></span>
        </button>
    </div>
    <?php
}

/**
 * FAQs meta box callback
 */
function domain_system_faqs_meta_box($post) {
    $faqs = get_post_meta($post->ID, '_domain_faq', true) ?: [];
    ?>
    <div class="domain-faqs-header">
        <button type="button" class="button button-primary add-faq">
            <span class="dashicons dashicons-plus-alt"></span>
            <?php _e('Add FAQ', 'domain-system'); ?>
        </button>
        <button type="button" class="button button-secondary load-default-faqs">
            <span class="dashicons dashicons-download"></span>
            <?php _e('Load Defaults', 'domain-system'); ?>
        </button>
    </div>
    
    <div id="domain-faqs-container" class="domain-faqs-container">
        <?php if (empty($faqs)): ?>
            <p><?php _e('No FAQs added yet. Click "Add FAQ" to get started.', 'domain-system'); ?></p>
        <?php else: ?>
            <?php foreach ($faqs as $index => $faq): ?>
                <div class="domain-faq-item" data-index="<?php echo $index; ?>">
                    <div class="faq-handle">
                        <span class="dashicons dashicons-move"></span>
                    </div>
                    <div class="faq-content">
                        <div class="faq-question">
                            <label for="faq_question_<?php echo $index; ?>"><?php _e('Question:', 'domain-system'); ?></label>
                            <input type="text" id="faq_question_<?php echo $index; ?>" name="domain_faq[<?php echo $index; ?>][question]" value="<?php echo esc_attr($faq['question'] ?? ''); ?>" class="large-text" />
                        </div>
                        <div class="faq-answer">
                            <label for="faq_answer_<?php echo $index; ?>"><?php _e('Answer:', 'domain-system'); ?></label>
                            <textarea id="faq_answer_<?php echo $index; ?>" name="domain_faq[<?php echo $index; ?>][answer]" rows="4" class="large-text"><?php echo esc_textarea($faq['answer'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    <div class="faq-actions">
                        <a href="#" class="remove-faq dashicons dashicons-trash" title="<?php _e('Remove FAQ', 'domain-system'); ?>"></a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Tools meta box callback
 */
function domain_system_tools_meta_box($post) {
    ?>
    <div class="domain-quick-actions">
        <?php if ($post->ID): ?>
            <button type="button" class="button button-secondary duplicate-domain" data-post-id="<?php echo $post->ID; ?>">
                <span class="dashicons dashicons-admin-page"></span>
                <?php _e('Duplicate Domain', 'domain-system'); ?>
            </button>
        <?php endif; ?>
        
        <button type="button" class="button button-secondary calculate-pricing">
            <span class="dashicons dashicons-calculator"></span>
            <?php _e('Calculate Pricing', 'domain-system'); ?>
        </button>
        
        <div id="pricing-calculator-results" style="display: none; margin-top: 15px; padding: 10px; background: #f9f9f9; border-radius: 4px;"></div>
    </div>
    <?php
}

/**
 * Enhanced save meta data
 */
function domain_system_enhanced_save_meta($post_id) {
    if (!isset($_POST['domain_meta_nonce']) || 
        !wp_verify_nonce($_POST['domain_meta_nonce'], 'domain_meta_save') ||
        !current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = [
        'domain_tld' => 'sanitize_text_field',
        'domain_registration_price' => 'floatval',
        'domain_renewal_price' => 'floatval',
        'domain_category' => 'sanitize_text_field',
    ];
    
    foreach ($fields as $field => $sanitize_func) {
        if (isset($_POST[$field])) {
            $value = $sanitize_func($_POST[$field]);
            update_post_meta($post_id, "_{$field}", $value);
        }
    }
    
    // Handle FAQ data
    if (isset($_POST['domain_faq']) && is_array($_POST['domain_faq'])) {
        $faq_data = [];
        foreach ($_POST['domain_faq'] as $faq) {
            if (!empty($faq['question']) && !empty($faq['answer'])) {
                $faq_data[] = [
                    'question' => sanitize_text_field($faq['question']),
                    'answer' => sanitize_textarea_field($faq['answer'])
                ];
            }
        }
        update_post_meta($post_id, '_domain_faq', $faq_data);
    }
}

/**
 * Plugin activation
 */
function domain_system_activate() {
    error_log('Domain System: Activating plugin');
    domain_system_create_options();
    domain_system_register_post_type();
    flush_rewrite_rules();
    error_log('Domain System: Plugin activated successfully');
}

/**
 * Plugin deactivation
 */
function domain_system_deactivate() {
    flush_rewrite_rules();
}

// Hook everything up
add_action('plugins_loaded', 'domain_system_debug');
add_action('init', 'domain_system_register_post_type', 10);
add_action('init', 'domain_system_load_includes', 11);
add_action('add_meta_boxes', 'domain_system_add_meta_boxes');
add_action('save_post_domain', 'domain_system_enhanced_save_meta');

// Activation/Deactivation hooks
register_activation_hook(__FILE__, 'domain_system_activate');
register_deactivation_hook(__FILE__, 'domain_system_deactivate');

// Add admin notice for debugging
add_action('admin_notices', function() {
    if (current_user_can('manage_options')) {
        $post_type_exists = post_type_exists('domain') ? 'YES' : 'NO';
        echo '<div class="notice notice-info"><p><strong>Domain System Debug:</strong> Post type exists: ' . $post_type_exists . '</p></div>';
    }
});

error_log('Domain System: Plugin file loaded');