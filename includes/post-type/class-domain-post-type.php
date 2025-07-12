<?php
/**
 * Domain Post Type Handler - Updated for Structured Fields
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainPostType {
    
    /**
     * Meta field definitions - Updated with new fields
     * 
     * @var array
     */
    private $meta_fields = [];
    
    /**
     * Component instances
     * 
     * @var array
     */
    private $components = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_components();
        $this->init_meta_fields();
        $this->init_hooks();
    }
    
    /**
     * Load component classes
     */
    private function load_components() {
        // Load component files
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-registration.php';
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-taxonomies.php';
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-meta-boxes.php';
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-validation.php';
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-ajax-handlers.php';
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-bulk-operations.php';
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-admin-interface.php';
        
        // Initialize components
        $this->components['registration'] = new DomainRegistration();
        $this->components['taxonomies'] = new DomainTaxonomies();
        $this->components['meta_boxes'] = new DomainMetaBoxes();
        $this->components['validation'] = new DomainValidation();
        $this->components['ajax'] = new DomainAjaxHandlers();
        $this->components['bulk_ops'] = new DomainBulkOperations();
        $this->components['admin'] = new DomainAdminInterface();
    }
    
    /**
     * Initialize meta field definitions - Updated with new structured fields
     */
    private function init_meta_fields() {
        $this->meta_fields = [
            // Core fields
            'tld' => [
                'type' => 'text',
                'required' => true,
                'sanitize' => 'sanitize_domain_tld',
                'validate' => 'validate_tld_field',
                'label' => __('TLD (Top Level Domain)', 'domain-system'),
                'description' => __('Domain extension (e.g., .shop, .com, .co.uk)', 'domain-system')
            ],
            'product_id' => [
                'type' => 'text',
                'sanitize' => 'sanitize_text_field',
                'label' => __('Product ID', 'domain-system'),
                'description' => __('External system product identifier (optional - for Upmind)', 'domain-system')
            ],
            
            // Pricing fields (updated to roundup fields)
            'registration_roundup' => [
                'type' => 'number',
                'required' => true,
                'sanitize' => 'floatval',
                'validate' => 'validate_price_field',
                'label' => __('Registration Roundup', 'domain-system'),
                'description' => __('Primary registration price (e.g. $4.00)', 'domain-system')
            ],
            'renewal_roundup' => [
                'type' => 'number',
                'sanitize' => 'floatval',
                'validate' => 'validate_price_field',
                'label' => __('Renewal Roundup', 'domain-system'),
                'description' => __('Annual renewal price', 'domain-system')
            ],
            'transfer_roundup' => [
                'type' => 'number',
                'sanitize' => 'floatval',
                'validate' => 'validate_price_field',
                'label' => __('Transfer Roundup', 'domain-system'),
                'description' => __('Price for domain transfer', 'domain-system')
            ],
            'restoration_roundup' => [
                'type' => 'number',
                'sanitize' => 'floatval',
                'validate' => 'validate_price_field',
                'label' => __('Restoration Roundup', 'domain-system'),
                'description' => __('Price for domain restoration after expiry', 'domain-system')
            ],
            
            // Category fields
            'primary_category' => [
                'type' => 'select',
                'required' => true,
                'sanitize' => 'sanitize_text_field',
                'label' => __('Primary Category', 'domain-system'),
                'description' => __('Main category for this domain extension', 'domain-system')
            ],
            'secondary_categories' => [
                'type' => 'multiselect',
                'sanitize' => 'sanitize_text_field',
                'label' => __('Secondary Categories', 'domain-system'),
                'description' => __('Additional categories for better targeting', 'domain-system')
            ],
            
            // Hero section fields
            'hero_h1' => [
                'type' => 'text',
                'sanitize' => 'sanitize_text_field',
                'label' => __('Hero H1', 'domain-system'),
                'description' => __('Main headline for the hero section (e.g. Find your perfect .shop domain)', 'domain-system')
            ],
            'hero_subtitle' => [
                'type' => 'textarea',
                'sanitize' => 'sanitize_textarea_field',
                'label' => __('Hero Subtitle', 'domain-system'),
                'description' => __('Subtitle text below the main headline', 'domain-system')
            ],
            
            // Content section fields
            'overview' => [
                'type' => 'editor',
                'sanitize' => 'wp_kses_post',
                'label' => __('Domain Overview', 'domain-system'),
                'description' => __('General overview and benefits of this domain extension', 'domain-system')
            ],
            'stats' => [
                'type' => 'editor',
                'sanitize' => 'wp_kses_post',
                'label' => __('Domain Stats & History', 'domain-system'),
                'description' => __('Statistics, facts, and history about the domain', 'domain-system')
            ],
            'benefits' => [
                'type' => 'editor',
                'sanitize' => 'wp_kses_post',
                'label' => __('Domain Benefits', 'domain-system'),
                'description' => __('Specific benefits of choosing this domain extension', 'domain-system')
            ],
            'ideas' => [
                'type' => 'editor',
                'sanitize' => 'wp_kses_post',
                'label' => __('Domain Ideas', 'domain-system'),
                'description' => __('Ideas and suggestions for using this domain extension', 'domain-system')
            ],
            
            // Policy fields (enhanced)
            'min_length' => [
                'type' => 'number',
                'default' => 2,
                'sanitize' => 'intval',
                'validate' => 'validate_length_field',
                'label' => __('Min Length', 'domain-system'),
                'description' => __('Minimum domain name length in characters', 'domain-system')
            ],
            'max_length' => [
                'type' => 'number',
                'default' => 63,
                'sanitize' => 'intval',
                'validate' => 'validate_length_field',
                'label' => __('Max Length', 'domain-system'),
                'description' => __('Maximum domain name length in characters', 'domain-system')
            ],
            'numbers_allowed' => [
                'type' => 'checkbox',
                'sanitize' => 'boolval',
                'label' => __('Numbers Allowed', 'domain-system'),
                'description' => __('Whether numbers are allowed in domain names', 'domain-system')
            ],
            'hyphens_allowed' => [
                'type' => 'select',
                'default' => 'middle',
                'sanitize' => 'sanitize_text_field',
                'options' => [
                    'none' => __('Not allowed', 'domain-system'),
                    'middle' => __('Middle only', 'domain-system'),
                    'start' => __('Start only', 'domain-system'),
                    'end' => __('End only', 'domain-system'),
                    'all' => __('Anywhere', 'domain-system')
                ],
                'label' => __('Hyphens Allowed', 'domain-system'),
                'description' => __('Where hyphens are allowed in domain names', 'domain-system')
            ],
            'idn_allowed' => [
                'type' => 'checkbox',
                'sanitize' => 'boolval',
                'label' => __('IDN Allowed', 'domain-system'),
                'description' => __('Whether Internationalized Domain Names are allowed', 'domain-system')
            ],
            
            // Registry field
            'registry' => [
                'type' => 'text',
                'sanitize' => 'sanitize_text_field',
                'label' => __('Domain Registry', 'domain-system'),
                'description' => __('The registry company that manages this TLD', 'domain-system')
            ]
        ];
        
        // Allow filtering of meta fields
        $this->meta_fields = apply_filters('domain_meta_fields', $this->meta_fields);
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Core functionality
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_domain', [$this, 'save_meta_data'], 10, 2);
        add_action('delete_post', [$this, 'handle_post_deletion']);
        
        // Template loading
        add_filter('template_include', [$this, 'load_template']);
        
        // Admin enhancements
        add_filter('post_updated_messages', [$this, 'update_messages']);
        add_filter('bulk_post_updated_messages', [$this, 'bulk_update_messages'], 10, 2);
        add_filter('enter_title_here', [$this, 'change_title_placeholder'], 10, 2);
        add_action('edit_form_after_title', [$this, 'add_after_title_content']);
        add_action('admin_notices', [$this, 'show_admin_notices']);
        
        // Scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);
        
        // URL rewriting
        add_action('init', [$this, 'setup_url_rewriting']);
    }
    
    /**
     * Setup URL rewriting for domains/tld/ structure
     */
    public function setup_url_rewriting() {
        // Load URL rewriter component
        if (!class_exists('DomainURLRewriter')) {
            require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'class-domain-url-rewriter.php';
            new DomainURLRewriter();
        }
    }
    
    /**
     * Register post type (delegated to component)
     */
    public function register_post_type() {
        $this->components['registration']->register();
    }
    
    /**
     * Register taxonomies (delegated to component)
     */
    public function register_taxonomies() {
        $this->components['taxonomies']->register();
    }
    
    /**
     * Add meta boxes (delegated to component)
     */
    public function add_meta_boxes() {
        $this->components['meta_boxes']->add_boxes();
    }
    
    /**
     * Save meta data - Updated for new fields
     */
    public function save_meta_data($post_id, $post) {
        // Security checks
        if (!isset($_POST['domain_meta_nonce']) || 
            !wp_verify_nonce($_POST['domain_meta_nonce'], 'domain_meta_save') ||
            !current_user_can('edit_post', $post_id) ||
            (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
            return;
        }
        
        $errors = [];
        $updated_fields = [];
        
        // Validate and save meta fields
        foreach ($this->meta_fields as $field => $config) {
            $post_field = "domain_{$field}";
            
            if (isset($_POST[$post_field])) {
                $value = $_POST[$post_field];
                $old_value = get_post_meta($post_id, "_domain_{$field}", true);
                
                // Handle arrays (for multiselect fields)
                if (is_array($value)) {
                    $value = array_map('sanitize_text_field', $value);
                } else {
                    // Apply sanitization
                    if (isset($config['sanitize'])) {
                        $sanitize_func = $config['sanitize'];
                        if (function_exists($sanitize_func)) {
                            $value = $sanitize_func($value);
                        }
                    }
                }
                
                // Apply validation
                if (isset($config['validate'])) {
                    $validation_result = $this->components['validation']->validate_field($value, $field, $post_id, $config);
                    if ($validation_result !== true) {
                        $errors[] = $validation_result;
                        continue;
                    }
                }
                
                // Check for required fields
                if (!empty($config['required']) && empty($value)) {
                    $errors[] = sprintf(__('%s is required.', 'domain-system'), $config['label']);
                    continue;
                }
                
                // Update meta field
                update_post_meta($post_id, "_domain_{$field}", $value);
                
                if ($old_value !== $value) {
                    $updated_fields[] = $field;
                }
            }
        }
        
        // Save FAQ data
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
            $updated_fields[] = 'faq';
        }
        
        // Save SEO fields
        $seo_fields = ['seo_title', 'seo_description', 'seo_keywords', 'og_image'];
        foreach ($seo_fields as $seo_field) {
            if (isset($_POST["domain_{$seo_field}"])) {
                $value = sanitize_text_field($_POST["domain_{$seo_field}"]);
                update_post_meta($post_id, "_domain_{$seo_field}", $value);
            }
        }
        
        // Auto-update post slug based on TLD
        if (isset($_POST['domain_tld'])) {
            $tld = sanitize_domain_tld($_POST['domain_tld']);
            $slug = tld_to_slug($tld);
            
            wp_update_post([
                'ID' => $post_id,
                'post_name' => $slug
            ]);
        }
        
        // Auto-generate title if empty and TLD is set
        if (empty($post->post_title) && isset($_POST['domain_tld'])) {
            $tld = sanitize_domain_tld($_POST['domain_tld']);
            $title = sprintf(__('%s Domain Registration', 'domain-system'), $tld);
            
            wp_update_post([
                'ID' => $post_id,
                'post_title' => $title
            ]);
        }
        
        // Store validation errors
        if (!empty($errors)) {
            update_post_meta($post_id, '_domain_validation_errors', $errors);
        } else {
            delete_post_meta($post_id, '_domain_validation_errors');
        }
        
        // Log activity
        if (!empty($updated_fields)) {
            log_domain_activity('updated', $post_id, [
                'fields' => $updated_fields,
                'errors' => $errors
            ]);
        }
        
        // Clear cache
        clear_domain_cache($post_id);
        
        do_action('domain_post_saved', $post_id, $updated_fields, $errors);
    }
    
    /**
     * Handle post deletion
     */
    public function handle_post_deletion($post_id) {
        $post = get_post($post_id);
        if ($post && $post->post_type === 'domain') {
            $tld = get_post_meta($post_id, '_domain_tld', true);
            
            // Log deletion
            log_domain_activity('deleted', $post_id, ['tld' => $tld]);
            
            // Clear cache
            clear_domain_cache($post_id);
            
            do_action('domain_post_deleted', $post_id, $tld);
        }
    }
    
    /**
     * Load custom template
     */
    public function load_template($template) {
        if (is_singular('domain')) {
            $custom_template = locate_template(['single-domain.php']);
            if ($custom_template) {
                return $custom_template;
            }
            
            // Fallback to plugin template
            $plugin_template = DOMAIN_SYSTEM_PLUGIN_DIR . 'templates/single-domain.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        } elseif (is_post_type_archive('domain')) {
            $custom_template = locate_template(['archive-domain.php']);
            if ($custom_template) {
                return $custom_template;
            }
            
            $plugin_template = DOMAIN_SYSTEM_PLUGIN_DIR . 'templates/archive-domain.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (!in_array($hook, ['post.php', 'post-new.php']) || get_current_screen()->post_type !== 'domain') {
            return;
        }
        
        // Enqueue validation script
        wp_enqueue_script(
            'domain-admin-validation',
            DOMAIN_SYSTEM_ASSETS_URL . 'js/domain-admin-validation.js',
            ['jquery'],
            DOMAIN_SYSTEM_VERSION,
            true
        );
        
        // Enqueue tab functionality
        wp_enqueue_script(
            'domain-admin-tabs',
            DOMAIN_SYSTEM_ASSETS_URL . 'js/domain-admin-tabs.js',
            ['jquery'],
            DOMAIN_SYSTEM_VERSION,
            true
        );
        
        // Localize script with validation errors and settings
        $validation_errors = [];
        if (isset($_GET['post'])) {
            $validation_errors = get_post_meta(intval($_GET['post']), '_domain_validation_errors', true) ?: [];
        }
        
        wp_localize_script('domain-admin-validation', 'domainAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('domain_admin_nonce'),
            'validationErrors' => $validation_errors,
            'categories' => $this->get_domain_categories()
        ]);
        
        $this->components['admin']->enqueue_scripts();
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (is_singular('domain')) {
            wp_enqueue_script('domain-frontend', 
                DOMAIN_SYSTEM_ASSETS_URL . 'js/frontend.js', 
                ['jquery'], 
                DOMAIN_SYSTEM_VERSION, 
                true);
                
            // Localize for related domains functionality
            global $post;
            $primary_category = get_post_meta($post->ID, '_domain_primary_category', true);
            
            wp_localize_script('domain-frontend', 'domainFrontend', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'postId' => $post->ID,
                'primaryCategory' => $primary_category
            ]);
        }
    }
    
    /**
     * Update post messages
     */
    public function update_messages($messages) {
        global $post;
        
        $messages['domain'] = [
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf(__('Domain updated. <a href="%s">View domain</a>', 'domain-system'), esc_url(get_permalink($post->ID))),
            2  => __('Custom field updated.', 'domain-system'),
            3  => __('Custom field deleted.', 'domain-system'),
            4  => __('Domain updated.', 'domain-system'),
            5  => isset($_GET['revision']) ? sprintf(__('Domain restored to revision from %s', 'domain-system'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => sprintf(__('Domain published. <a href="%s">View domain</a>', 'domain-system'), esc_url(get_permalink($post->ID))),
            7  => __('Domain saved.', 'domain-system'),
            8  => sprintf(__('Domain submitted. <a target="_blank" href="%s">Preview domain</a>', 'domain-system'), esc_url(add_query_arg('preview', 'true', get_permalink($post->ID)))),
            9  => sprintf(__('Domain scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview domain</a>', 'domain-system'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post->ID))),
            10 => sprintf(__('Domain draft updated. <a target="_blank" href="%s">Preview domain</a>', 'domain-system'), esc_url(add_query_arg('preview', 'true', get_permalink($post->ID))))
        ];
        
        return $messages;
    }
    
    /**
     * Bulk update messages
     */
    public function bulk_update_messages($bulk_messages, $bulk_counts) {
        $bulk_messages['domain'] = [
            'updated'   => _n('%s domain updated.', '%s domains updated.', $bulk_counts['updated'], 'domain-system'),
            'locked'    => _n('%s domain not updated, somebody is editing it.', '%s domains not updated, somebody is editing them.', $bulk_counts['locked'], 'domain-system'),
            'deleted'   => _n('%s domain permanently deleted.', '%s domains permanently deleted.', $bulk_counts['deleted'], 'domain-system'),
            'trashed'   => _n('%s domain moved to the Trash.', '%s domains moved to the Trash.', $bulk_counts['trashed'], 'domain-system'),
            'untrashed' => _n('%s domain restored from the Trash.', '%s domains restored from the Trash.', $bulk_counts['untrashed'], 'domain-system'),
        ];
        
        return $bulk_messages;
    }
    
    /**
     * Change title placeholder
     */
    public function change_title_placeholder($title, $post) {
        if ($post->post_type === 'domain') {
            return __('Enter domain title (auto-generated if left empty)', 'domain-system');
        }
        return $title;
    }
    
    /**
     * Add content after title
     */
    public function add_after_title_content($post) {
        if ($post->post_type === 'domain') {
            $this->components['admin']->render_title_notices($post);
        }
    }
    
    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        $screen = get_current_screen();
        
        if ($screen->post_type === 'domain') {
            $this->components['admin']->show_notices();
        }
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
     * Get meta field definition
     */
    public function get_meta_field($field) {
        return $this->meta_fields[$field] ?? null;
    }
    
    /**
     * Get all meta fields
     */
    public function get_meta_fields() {
        return $this->meta_fields;
    }
    
    /**
     * Get component instance
     */
    public function get_component($name) {
        return $this->components[$name] ?? null;
    }
}