<?php
/**
 * Domain AJAX Handlers Component - Improved Version
 * 
 * Handles all AJAX requests for domain post type functionality
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainAjaxHandlers {
    
    /**
     * Validation component instance
     * 
     * @var DomainValidation
     */
    private $validator;
    
    /**
     * Rate limiting storage
     * 
     * @var array
     */
    private static $rate_limits = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->validator = new DomainValidation();
        $this->init_hooks();
    }
    
    /**
     * Initialize AJAX hooks
     */
    private function init_hooks() {
        // Field validation
        add_action('wp_ajax_validate_domain_field', [$this, 'ajax_validate_field']);
        
        // TLD specific validation
        add_action('wp_ajax_validate_domain_tld', [$this, 'ajax_validate_tld']);
        
        // Domain suggestions
        add_action('wp_ajax_get_domain_suggestions', [$this, 'ajax_get_suggestions']);
        
        // Domain duplication
        add_action('wp_ajax_duplicate_domain', [$this, 'ajax_duplicate_domain']);
        
        // Auto-generate content
        add_action('wp_ajax_generate_domain_content', [$this, 'ajax_generate_content']);
        
        // FAQ operations
        add_action('wp_ajax_load_default_faqs', [$this, 'ajax_load_default_faqs']);
        
        // Registry suggestions
        add_action('wp_ajax_get_registry_suggestions', [$this, 'ajax_get_registry_suggestions']);
        
        // Price calculations
        add_action('wp_ajax_calculate_domain_pricing', [$this, 'ajax_calculate_pricing']);
        
        // Policy preview
        add_action('wp_ajax_generate_policy_preview', [$this, 'ajax_generate_policy_preview']);
    }
    
    /**
     * Check rate limits for expensive operations
     */
    private function check_rate_limit($action, $limit_per_hour = 60) {
        $user_id = get_current_user_id();
        $key = $action . '_' . $user_id;
        $current_time = time();
        $hour_ago = $current_time - 3600;
        
        if (!isset(self::$rate_limits[$key])) {
            self::$rate_limits[$key] = [];
        }
        
        // Clean old entries
        self::$rate_limits[$key] = array_filter(
            self::$rate_limits[$key], 
            function($timestamp) use ($hour_ago) {
                return $timestamp > $hour_ago;
            }
        );
        
        if (count(self::$rate_limits[$key]) >= $limit_per_hour) {
            wp_send_json_error(__('Rate limit exceeded. Please try again later.', 'domain-system'));
        }
        
        self::$rate_limits[$key][] = $current_time;
    }
    
    /**
     * Validate common AJAX prerequisites
     */
    private function validate_ajax_request($require_edit_capability = true) {
        if (!check_ajax_referer('domain_admin_nonce', 'nonce', false)) {
            wp_send_json_error(__('Invalid security token', 'domain-system'));
        }
        
        if ($require_edit_capability && !current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied', 'domain-system'));
        }
    }
    
    /**
     * Sanitize and validate numeric input
     */
    private function sanitize_numeric_input($value, $min = 0, $max = PHP_INT_MAX) {
        $value = is_numeric($value) ? floatval($value) : 0;
        return max($min, min($max, $value));
    }
    
    /**
     * AJAX handler for field validation - FIXED
     */
    public function ajax_validate_field() {
        $this->validate_ajax_request();
        
        $field = sanitize_text_field($_POST['field'] ?? '');
        $value = $_POST['value'] ?? '';
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (empty($field)) {
            wp_send_json_error(__('Field name is required', 'domain-system'));
        }
        
        // Get field configuration
        $config = $this->get_field_config($field);
        
        try {
            // Validate the field
            $validation_result = $this->validator->validate_field($value, $field, $post_id, $config);
            
            if ($validation_result === true) {
                $response_data = [
                    'valid' => true,
                    'message' => $this->get_success_message($field, $value)
                ];
                
                // Add extra data for specific fields
                if ($field === 'tld') {
                    $response_data['slug'] = tld_to_slug($value);
                    $response_data['url'] = home_url('/domains/' . tld_to_slug($value) . '/');
                }
                
                wp_send_json_success($response_data);
            } else {
                wp_send_json_error([
                    'valid' => false,
                    'message' => $validation_result
                ]);
            }
        } catch (Exception $e) {
            error_log('Domain validation error: ' . $e->getMessage());
            wp_send_json_error(__('Validation failed due to server error', 'domain-system'));
        }
    }
    
    /**
     * AJAX handler for TLD validation (enhanced)
     */
    public function ajax_validate_tld() {
        $this->validate_ajax_request();
        
        $tld = sanitize_text_field($_POST['tld'] ?? '');
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (empty($tld)) {
            wp_send_json_error(__('TLD is required', 'domain-system'));
        }
        
        // Add length validation
        if (strlen($tld) > 63) {
            wp_send_json_error(__('TLD too long (max 63 characters)', 'domain-system'));
        }
        
        try {
            // Validate TLD
            $validation_result = $this->validator->validate_field($tld, 'tld', $post_id);
            
            if ($validation_result === true) {
                wp_send_json_success([
                    'valid' => true,
                    'message' => __('TLD is available', 'domain-system'),
                    'slug' => tld_to_slug($tld),
                    'url' => home_url('/domains/' . tld_to_slug($tld) . '/'),
                    'suggestions' => $this->get_similar_tlds($tld)
                ]);
            } else {
                wp_send_json_error([
                    'valid' => false,
                    'message' => $validation_result,
                    'suggestions' => $this->get_alternative_tlds($tld)
                ]);
            }
        } catch (Exception $e) {
            error_log('TLD validation error: ' . $e->getMessage());
            wp_send_json_error(__('TLD validation failed', 'domain-system'));
        }
    }
    
    /**
     * AJAX handler for domain suggestions
     */
    public function ajax_get_suggestions() {
        $this->validate_ajax_request(false);
        $this->check_rate_limit('suggestions', 100);
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        $limit = intval($_POST['limit'] ?? 5);
        
        if (empty($query)) {
            wp_send_json_error(__('Search query is required', 'domain-system'));
        }
        
        if (strlen($query) > 100) {
            wp_send_json_error(__('Search query too long', 'domain-system'));
        }
        
        $limit = max(1, min(20, $limit)); // Limit between 1-20
        
        try {
            $suggestions = get_domain_search_suggestions($query, $limit);
            
            wp_send_json_success([
                'suggestions' => $suggestions,
                'query' => $query
            ]);
        } catch (Exception $e) {
            error_log('Domain suggestions error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to get suggestions', 'domain-system'));
        }
    }
    
    /**
     * AJAX handler for domain duplication
     */
    public function ajax_duplicate_domain() {
        $this->validate_ajax_request();
        $this->check_rate_limit('duplicate', 10);
        
        $post_id = intval($_POST['post_id'] ?? 0);
        
        if (!$post_id) {
            wp_send_json_error(__('Invalid post ID', 'domain-system'));
        }
        
        $original_post = get_post($post_id);
        
        if (!$original_post || $original_post->post_type !== 'domain') {
            wp_send_json_error(__('Invalid domain', 'domain-system'));
        }
        
        try {
            $new_post_id = $this->duplicate_domain_post($original_post);
            
            if (is_wp_error($new_post_id)) {
                wp_send_json_error($new_post_id->get_error_message());
            }
            
            // Log activity
            log_domain_activity('duplicated', $new_post_id, ['original_id' => $post_id]);
            
            wp_send_json_success([
                'message' => __('Domain duplicated successfully', 'domain-system'),
                'edit_url' => admin_url("post.php?post={$new_post_id}&action=edit"),
                'new_id' => $new_post_id
            ]);
        } catch (Exception $e) {
            error_log('Domain duplication error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to duplicate domain', 'domain-system'));
        }
    }
    
    /**
     * Handle the actual domain duplication logic
     */
    private function duplicate_domain_post($original_post) {
        // Create duplicate post
        $new_post_data = [
            'post_title' => $original_post->post_title . ' (Copy)',
            'post_content' => $original_post->post_content,
            'post_excerpt' => $original_post->post_excerpt,
            'post_type' => 'domain',
            'post_status' => 'draft',
            'post_author' => get_current_user_id()
        ];
        
        $new_post_id = wp_insert_post($new_post_data);
        
        if (is_wp_error($new_post_id)) {
            return $new_post_id;
        }
        
        // Copy meta data
        $this->copy_post_meta($original_post->ID, $new_post_id);
        
        // Copy taxonomy terms
        $this->copy_post_taxonomies($original_post->ID, $new_post_id);
        
        return $new_post_id;
    }
    
    /**
     * Copy post meta data
     */
    private function copy_post_meta($source_id, $target_id) {
        $meta_keys = get_post_meta($source_id);
        foreach ($meta_keys as $key => $values) {
            if (strpos($key, '_domain_') === 0 && $key !== '_domain_tld') {
                update_post_meta($target_id, $key, $values[0]);
            }
        }
    }
    
    /**
     * Copy post taxonomies
     */
    private function copy_post_taxonomies($source_id, $target_id) {
        $taxonomies = get_post_taxonomies($source_id);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_post_terms($source_id, $taxonomy, ['fields' => 'ids']);
            if (!empty($terms) && !is_wp_error($terms)) {
                wp_set_post_terms($target_id, $terms, $taxonomy);
            }
        }
    }
    
    /**
     * AJAX handler for auto-generating content
     */
    public function ajax_generate_content() {
        $this->validate_ajax_request();
        $this->check_rate_limit('generate_content', 30);
        
        $tld = sanitize_text_field($_POST['tld'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        
        if (empty($tld)) {
            wp_send_json_error(__('TLD is required for content generation', 'domain-system'));
        }
        
        try {
            $generated_content = $this->generate_content_for_tld($tld, $category);
            
            wp_send_json_success([
                'content' => $generated_content,
                'message' => __('Content generated successfully', 'domain-system')
            ]);
        } catch (Exception $e) {
            error_log('Content generation error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to generate content', 'domain-system'));
        }
    }
    
    /**
     * AJAX handler for loading default FAQs
     */
    public function ajax_load_default_faqs() {
        $this->validate_ajax_request();
        
        $tld = sanitize_text_field($_POST['tld'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        
        if (empty($tld)) {
            wp_send_json_error(__('TLD is required', 'domain-system'));
        }
        
        try {
            $default_faqs = $this->get_default_faqs_for_tld($tld, $category);
            
            wp_send_json_success([
                'faqs' => $default_faqs,
                'message' => sprintf(__('Loaded %d default FAQs', 'domain-system'), count($default_faqs))
            ]);
        } catch (Exception $e) {
            error_log('FAQ loading error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to load FAQs', 'domain-system'));
        }
    }
    
    /**
     * AJAX handler for registry suggestions
     */
    public function ajax_get_registry_suggestions() {
        $this->validate_ajax_request(false);
        
        $query = sanitize_text_field($_POST['query'] ?? '');
        
        if (strlen($query) < 2) {
            wp_send_json_success(['suggestions' => []]);
        }
        
        if (strlen($query) > 50) {
            wp_send_json_error(__('Query too long', 'domain-system'));
        }
        
        try {
            $suggestions = $this->get_registry_suggestions($query);
            
            wp_send_json_success([
                'suggestions' => array_slice($suggestions, 0, 10)
            ]);
        } catch (Exception $e) {
            error_log('Registry suggestions error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to get registry suggestions', 'domain-system'));
        }
    }
    
    /**
     * Get registry suggestions with improved logic
     */
    private function get_registry_suggestions($query) {
        $registries = get_domain_registries();
        $suggestions = [];
        
        foreach ($registries as $registry) {
            if (stripos($registry, $query) !== false) {
                $suggestions[] = $registry;
            }
        }
        
        // Add common registries if no matches
        if (empty($suggestions)) {
            $common_registries = [
                'Verisign', 'Donuts Inc.', 'GMO Registry', 'Radix Registry',
                'Afilias', 'Neustar', 'CentralNic', 'Google Registry'
            ];
            
            foreach ($common_registries as $registry) {
                if (stripos($registry, $query) !== false) {
                    $suggestions[] = $registry;
                }
            }
        }
        
        return $suggestions;
    }
    
    /**
     * AJAX handler for price calculations - FIXED
     */
    public function ajax_calculate_pricing() {
        $this->validate_ajax_request(false);
        
        $reg_price = $this->sanitize_numeric_input($_POST['registration_price'] ?? 0, 0, 9999.99);
        $renewal_price = $this->sanitize_numeric_input($_POST['renewal_price'] ?? 0, 0, 9999.99);
        $transfer_price = $this->sanitize_numeric_input($_POST['transfer_price'] ?? 0, 0, 9999.99);
        $years = intval($_POST['years'] ?? 3);
        $years = max(1, min(10, $years)); // Limit between 1-10 years
        
        try {
            $calculations = $this->calculate_domain_pricing($reg_price, $renewal_price, $transfer_price, $years);
            
            wp_send_json_success([
                'calculations' => $calculations,
                'currency_symbol' => get_option('domain_currency_symbol', '$')
            ]);
        } catch (Exception $e) {
            error_log('Price calculation error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to calculate pricing', 'domain-system'));
        }
    }
    
    /**
     * Calculate domain pricing with improved logic
     */
    private function calculate_domain_pricing($reg_price, $renewal_price, $transfer_price, $years) {
        $calculations = [
            'year_1' => $reg_price,
            'yearly_costs' => [],
            'total_costs' => [],
            'savings' => []
        ];
        
        $running_total = $reg_price;
        $calculations['yearly_costs'][] = $reg_price;
        $calculations['total_costs'][] = $running_total;
        
        for ($year = 2; $year <= $years; $year++) {
            $yearly_cost = $renewal_price > 0 ? $renewal_price : $reg_price;
            $running_total += $yearly_cost;
            
            $calculations['yearly_costs'][] = $yearly_cost;
            $calculations['total_costs'][] = $running_total;
            
            // Calculate potential savings vs monthly payments
            $monthly_equivalent = $running_total / ($year * 12);
            $calculations['savings'][] = [
                'years' => $year,
                'total' => $running_total,
                'monthly_equivalent' => round($monthly_equivalent, 2)
            ];
        }
        
        return $calculations;
    }
    
    /**
     * AJAX handler for policy preview generation
     */
    public function ajax_generate_policy_preview() {
        $this->validate_ajax_request(false);
        
        $policy_data = [
            'min_length' => intval($_POST['min_length'] ?? 2),
            'max_length' => intval($_POST['max_length'] ?? 63),
            'numbers_allowed' => !empty($_POST['numbers_allowed']),
            'hyphens_allowed' => sanitize_text_field($_POST['hyphens_allowed'] ?? 'middle'),
            'idn_allowed' => !empty($_POST['idn_allowed'])
        ];
        
        // Validate policy data
        $policy_data['min_length'] = max(1, min(63, $policy_data['min_length']));
        $policy_data['max_length'] = max($policy_data['min_length'], min(63, $policy_data['max_length']));
        
        try {
            $preview_text = format_domain_policy($policy_data);
            $examples = $this->generate_policy_examples($policy_data);
            
            wp_send_json_success([
                'preview_text' => $preview_text,
                'examples' => $examples
            ]);
        } catch (Exception $e) {
            error_log('Policy preview error: ' . $e->getMessage());
            wp_send_json_error(__('Failed to generate policy preview', 'domain-system'));
        }
    }
    
    // ... (rest of the methods remain the same but with improved error handling)
    
    /**
     * Get field configuration for validation
     */
    private function get_field_config($field) {
        $configs = [
            'tld' => [
                'label' => __('TLD', 'domain-system'),
                'required' => true,
                'max_length' => 63
            ],
            'product_id' => [
                'label' => __('Product ID', 'domain-system'),
                'max_length' => 100
            ],
            'registration_price' => [
                'label' => __('Registration Price', 'domain-system'),
                'required' => true,
                'type' => 'numeric'
            ],
            'renewal_price' => [
                'label' => __('Renewal Price', 'domain-system'),
                'type' => 'numeric'
            ],
            'transfer_price' => [
                'label' => __('Transfer Price', 'domain-system'),
                'type' => 'numeric'
            ],
            'restoration_price' => [
                'label' => __('Restoration Price', 'domain-system'),
                'type' => 'numeric'
            ],
            'hero_h1' => [
                'label' => __('Hero Title', 'domain-system'),
                'max_length' => 255
            ],
            'hero_subtitle' => [
                'label' => __('Hero Subtitle', 'domain-system'),
                'max_length' => 500
            ],
            'min_length' => [
                'label' => __('Minimum Length', 'domain-system'),
                'type' => 'numeric'
            ],
            'max_length' => [
                'label' => __('Maximum Length', 'domain-system'),
                'type' => 'numeric'
            ],
            'registry' => [
                'label' => __('Registry', 'domain-system'),
                'max_length' => 255
            ]
        ];
        
        return $configs[$field] ?? ['label' => ucfirst(str_replace('_', ' ', $field))];
    }
    
    /**
     * Get success message for validated field
     */
    private function get_success_message($field, $value) {
        switch ($field) {
            case 'tld':
                return __('TLD is available', 'domain-system');
            case 'product_id':
                return __('Product ID is available', 'domain-system');
            case 'registration_price':
            case 'renewal_price':
            case 'transfer_price':
            case 'restoration_price':
                return __('Valid price', 'domain-system');
            case 'min_length':
            case 'max_length':
                return __('Valid length', 'domain-system');
            case 'registry':
                return __('Valid registry name', 'domain-system');
            default:
                return __('Valid', 'domain-system');
        }
    }
    
    /**
     * Generate content for TLD
     */
    private function generate_content_for_tld($tld, $category = '') {
        $tld_clean = ltrim($tld, '.');
        
        $templates = [
            'hero_h1' => "Get Your Perfect {tld} Domain",
            'hero_subtitle' => "Register your {tld} domain today and establish your online presence with a memorable web address that reflects your brand.",
            'overview' => "The {tld} domain extension is perfect for businesses and individuals looking to create a distinctive online presence. With {tld} domains, you can build trust with your audience and improve your brand recognition.",
            'benefits' => "Choosing a {tld} domain offers several advantages: enhanced brand recognition, improved SEO potential, increased trust from visitors, and better memorability for your target audience.",
            'ideas' => "Great uses for {tld} domains include: business websites, online portfolios, e-commerce stores, blogs and personal sites, professional services, and creative projects."
        ];
        
        $generated = [];
        foreach ($templates as $field => $template) {
            $generated[$field] = str_replace('{tld}', '.' . $tld_clean, $template);
        }
        
        // Customize based on category
        if ($category) {
            $generated = $this->customize_content_for_category($generated, $category, $tld_clean);
        }
        
        return $generated;
    }
    
    /**
     * Customize content based on category
     */
    private function customize_content_for_category($content, $category, $tld) {
        $category_customizations = [
            'e-commerce-retail' => [
                'hero_h1' => "Perfect {tld} Domain for Your Online Store",
                'benefits' => "Ideal for e-commerce: builds customer trust, improves conversion rates, enhances brand credibility, and provides memorable shopping destination."
            ],
            'business-professional' => [
                'hero_h1' => "Professional {tld} Domain for Your Business",
                'benefits' => "Professional advantages: establishes credibility, improves client trust, enhances networking opportunities, and strengthens business communications."
            ],
            'creative-arts' => [
                'hero_h1' => "Creative {tld} Domain for Artists & Designers",
                'benefits' => "Creative benefits: showcases artistic identity, attracts target audience, builds portfolio presence, and establishes creative brand recognition."
            ]
        ];
        
        $category_key = strtolower(str_replace([' ', '&'], ['-', ''], $category));
        
        if (isset($category_customizations[$category_key])) {
            $customization = $category_customizations[$category_key];
            foreach ($customization as $field => $template) {
                $content[$field] = str_replace('{tld}', '.' . $tld, $template);
            }
        }
        
        return $content;
    }
    
    /**
     * Get default FAQs for TLD
     */
    private function get_default_faqs_for_tld($tld, $category = '') {
        $tld_clean = ltrim($tld, '.');
        
        $default_faqs = [
            [
                'question' => "What is a {tld} domain?",
                'answer' => "A {tld} domain is a top-level domain extension that provides a unique and memorable web address for your website."
            ],
            [
                'question' => "How much does a {tld} domain cost?",
                'answer' => "The cost varies depending on the registrar and any current promotions. Check our pricing above for current rates."
            ],
            [
                'question' => "Can I transfer my {tld} domain?",
                'answer' => "Yes, you can transfer your {tld} domain to another registrar after 60 days from the initial registration date."
            ],
            [
                'question' => "How long can I register a {tld} domain for?",
                'answer' => "You can register a {tld} domain for 1-10 years, depending on your needs and budget."
            ],
            [
                'question' => "What happens if I don't renew my {tld} domain?",
                'answer' => "If you don't renew your domain before expiration, it will go through a grace period and may eventually be released for public registration."
            ]
        ];
        
        // Replace placeholders
        foreach ($default_faqs as &$faq) {
            $faq['question'] = str_replace('{tld}', '.' . $tld_clean, $faq['question']);
            $faq['answer'] = str_replace('{tld}', '.' . $tld_clean, $faq['answer']);
        }
        
        return $default_faqs;
    }
    
    /**
     * Get similar TLDs for suggestions
     */
    private function get_similar_tlds($tld) {
        $all_tlds = get_all_domain_tlds();
        $similar = [];
        
        $tld_clean = ltrim($tld, '.');
        
        foreach ($all_tlds as $existing_tld) {
            $existing_clean = ltrim($existing_tld, '.');
            
            // Skip exact match
            if ($existing_clean === $tld_clean) {
                continue;
            }
            
            // Find similar TLDs
            similar_text(strtolower($tld_clean), strtolower($existing_clean), $percent);
            
            if ($percent > 60) {
                $similar[] = [
                    'tld' => $existing_tld,
                    'similarity' => round($percent, 1)
                ];
            }
        }
        
        // Sort by similarity
        usort($similar, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });
        
        return array_slice($similar, 0, 5);
    }
    
    /**
     * Get alternative TLDs when validation fails
     */
    private function get_alternative_tlds($tld) {
        $tld_clean = ltrim($tld, '.');
        $alternatives = [];
        
        // Suggest variations
        $variations = [
            $tld_clean . 's',     // shops -> shop
            rtrim($tld_clean, 's'), // shop -> shops
            $tld_clean . 'ing',   // shopping
            'e' . $tld_clean      // eshop
        ];
        
        foreach ($variations as $variation) {
            if (!domain_exists($variation)) {
                $alternatives[] = $variation;
            }
        }
        
        return array_slice($alternatives, 0, 3);
    }
    
    /**
     * Generate policy examples with improved validation
     */
    private function generate_policy_examples($policy) {
        $examples = [
            'valid' => [],
            'invalid' => []
        ];

        // Generate valid examples
        $valid_base = 'example';
        
        // Basic valid example
        $examples['valid'][] = $valid_base;
        
        if (!empty($policy['numbers_allowed'])) {
            $examples['valid'][] = 'shop123';
        }
        
        if (!empty($policy['hyphens_allowed']) && $policy['hyphens_allowed'] !== 'none') {
            if ($policy['hyphens_allowed'] === 'middle') {
                $examples['valid'][] = 'my-shop';
            } elseif ($policy['hyphens_allowed'] === 'anywhere') {
                $examples['valid'][] = '-myshop-';
            }
        }
        
        if (!empty($policy['idn_allowed'])) {
            $examples['valid'][] = 'münchen';
        }

        // Length-based examples
        if (!empty($policy['min_length']) && $policy['min_length'] > 1) {
            $examples['valid'][] = str_repeat('a', max($policy['min_length'], 2));
        }
        
        if (!empty($policy['max_length']) && $policy['max_length'] > $policy['min_length']) {
            $max_example_length = min($policy['max_length'], 15); // Keep examples readable
            $examples['valid'][] = str_repeat('b', $max_example_length);
        }

        // Generate invalid examples
        if (empty($policy['numbers_allowed'])) {
            $examples['invalid'][] = 'brand123';
        }
        
        if (empty($policy['idn_allowed'])) {
            $examples['invalid'][] = 'münchen';
        }
        
        if (empty($policy['hyphens_allowed']) || $policy['hyphens_allowed'] === 'none') {
            $examples['invalid'][] = 'my-shop';
        } elseif ($policy['hyphens_allowed'] === 'middle') {
            $examples['invalid'][] = '-shop';
            $examples['invalid'][] = 'shop-';
        }
        
        // Length violations
        if (!empty($policy['min_length']) && $policy['min_length'] > 1) {
            $examples['invalid'][] = str_repeat('x', max(1, $policy['min_length'] - 1));
        }
        
        if (!empty($policy['max_length'])) {
            $examples['invalid'][] = str_repeat('z', min($policy['max_length'] + 5, 70));
        }

        // Remove duplicates and limit examples
        $examples['valid'] = array_unique(array_slice($examples['valid'], 0, 5));
        $examples['invalid'] = array_unique(array_slice($examples['invalid'], 0, 5));

        return $examples;
    }
    
    /**
     * Enhanced logging for debugging
     */
    private function log_ajax_action($action, $data = []) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $log_data = [
                'action' => $action,
                'user_id' => get_current_user_id(),
                'timestamp' => current_time('mysql'),
                'data' => $data
            ];
            
            error_log('Domain AJAX: ' . json_encode($log_data));
        }
    }
    
    /**
     * Sanitize TLD input
     */
    private function sanitize_tld($tld) {
        // Remove leading dots and convert to lowercase
        $tld = ltrim(strtolower(trim($tld)), '.');
        
        // Remove invalid characters
        $tld = preg_replace('/[^a-z0-9\-]/', '', $tld);
        
        // Ensure it doesn't start or end with hyphen
        $tld = trim($tld, '-');
        
        return $tld;
    }
    
    /**
     * Validate price input with better error messages
     */
    private function validate_price_input($price, $field_name) {
        if (!is_numeric($price)) {
            return sprintf(__('%s must be a valid number', 'domain-system'), $field_name);
        }
        
        $price = floatval($price);
        
        if ($price < 0) {
            return sprintf(__('%s cannot be negative', 'domain-system'), $field_name);
        }
        
        if ($price > 9999.99) {
            return sprintf(__('%s cannot exceed $9,999.99', 'domain-system'), $field_name);
        }
        
        return true;
    }
    
    /**
     * Enhanced content generation with templates
     */
    private function get_content_templates() {
        return [
            'generic' => [
                'hero_h1' => "Get Your Perfect {tld} Domain",
                'hero_subtitle' => "Register your {tld} domain today and establish your online presence with a memorable web address that reflects your brand.",
                'overview' => "The {tld} domain extension is perfect for businesses and individuals looking to create a distinctive online presence.",
                'benefits' => "Enhanced brand recognition, improved SEO potential, increased trust from visitors, and better memorability.",
                'ideas' => "Business websites, online portfolios, e-commerce stores, blogs and personal sites, professional services."
            ],
            'business' => [
                'hero_h1' => "Professional {tld} Domain for Your Business",
                'hero_subtitle' => "Build credibility and trust with a professional {tld} domain that represents your business values.",
                'overview' => "The {tld} domain is specifically designed for professional and business applications.",
                'benefits' => "Professional credibility, enhanced business communications, improved client trust, networking advantages.",
                'ideas' => "Corporate websites, professional services, B2B platforms, business directories, industry portals."
            ],
            'creative' => [
                'hero_h1' => "Creative {tld} Domain for Artists & Designers",
                'hero_subtitle' => "Showcase your creativity with a unique {tld} domain that reflects your artistic vision.",
                'overview' => "The {tld} domain extension is perfect for creative professionals and artistic projects.",
                'benefits' => "Artistic identity showcase, creative brand recognition, portfolio presence, audience attraction.",
                'ideas' => "Art portfolios, design studios, creative agencies, photography sites, artistic blogs."
            ],
            'ecommerce' => [
                'hero_h1' => "Perfect {tld} Domain for Your Online Store",
                'hero_subtitle' => "Start selling online with a trusted {tld} domain that customers will remember and trust.",
                'overview' => "The {tld} domain extension is ideal for e-commerce and online retail businesses.",
                'benefits' => "Customer trust building, conversion rate improvement, brand credibility, memorable shopping destination.",
                'ideas' => "Online stores, marketplaces, product catalogs, retail websites, shopping platforms."
            ]
        ];
    }
    
    /**
     * Get appropriate template based on category
     */
    private function get_template_for_category($category) {
        $category_mapping = [
            'business-professional' => 'business',
            'creative-arts' => 'creative',
            'e-commerce-retail' => 'ecommerce'
        ];
        
        $template_key = $category_mapping[$category] ?? 'generic';
        $templates = $this->get_content_templates();
        
        return $templates[$template_key] ?? $templates['generic'];
    }
    
    /**
     * Improved content generation using templates
     */
    private function generate_enhanced_content($tld, $category = '') {
        $tld_clean = ltrim($tld, '.');
        $template = $this->get_template_for_category($category);
        
        $generated = [];
        foreach ($template as $field => $content) {
            $generated[$field] = str_replace('{tld}', '.' . $tld_clean, $content);
        }
        
        return $generated;
    }
    
    /**
     * Validate domain name according to RFC standards
     */
    private function validate_domain_name($domain) {
        // Basic length check
        if (strlen($domain) > 253) {
            return __('Domain name too long (max 253 characters)', 'domain-system');
        }
        
        // Check for valid characters
        if (!preg_match('/^[a-zA-Z0-9\-\.]+$/', $domain)) {
            return __('Domain contains invalid characters', 'domain-system');
        }
        
        // Check each label
        $labels = explode('.', $domain);
        foreach ($labels as $label) {
            if (strlen($label) > 63) {
                return __('Domain label too long (max 63 characters)', 'domain-system');
            }
            
            if (empty($label)) {
                return __('Empty domain label not allowed', 'domain-system');
            }
            
            if (substr($label, 0, 1) === '-' || substr($label, -1) === '-') {
                return __('Domain label cannot start or end with hyphen', 'domain-system');
            }
        }
        
        return true;
    }
    
    /**
     * Cache frequently accessed data
     */
    private function get_cached_data($key, $callback, $expiration = 3600) {
        $cache_key = 'domain_ajax_' . $key;
        $cached = get_transient($cache_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $data = call_user_func($callback);
        set_transient($cache_key, $data, $expiration);
        
        return $data;
    }
    
    /**
     * Clear cached data
     */
    public function clear_cache($key = null) {
        if ($key) {
            delete_transient('domain_ajax_' . $key);
        } else {
            // Clear all domain-related transients
            global $wpdb;
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_domain_ajax_%'");
        }
    }
}