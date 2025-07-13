<?php
/**
 * Domain System Assets Handler
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainSystemAssets {
    
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('wp_ajax_duplicate_domain', [$this, 'handle_duplicate_domain']);
        add_action('wp_ajax_generate_domain_content', [$this, 'handle_generate_content']);
        add_action('wp_ajax_load_default_faqs', [$this, 'handle_load_default_faqs']);
        add_action('admin_footer', [$this, 'add_admin_footer_scripts']);
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        // Only load on domain edit pages
        if ($post_type !== 'domain' || !in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }
        
        // Enqueue WordPress media uploader
        wp_enqueue_media();
        
        // Enqueue jQuery UI for sortable functionality
        wp_enqueue_script('jquery-ui-sortable');
        
        // Enqueue your existing admin.js
        wp_enqueue_script(
            'domain-admin',
            DOMAIN_SYSTEM_ASSETS_URL . 'js/admin.js',
            ['jquery', 'jquery-ui-sortable'],
            DOMAIN_SYSTEM_VERSION,
            true
        );
    // Enqueue our custom scripts
    wp_enqueue_script(
        'domain-metabox-admin',
        DOMAIN_SYSTEM_ASSETS_URL . 'js/domain-metabox-admin.js',
        ['jquery', 'jquery-ui-sortable', 'wp-util'],
        '1.0.0',
        true
    );
        
        // Enqueue admin CSS
        wp_enqueue_style(
            'domain-admin',
            DOMAIN_SYSTEM_ASSETS_URL . 'css/admin.css',
            [],
            DOMAIN_SYSTEM_VERSION
        );
        
        // Localize script with necessary data
        wp_localize_script('domain-admin', 'domainAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('domain_admin_nonce'),
            'siteUrl' => home_url(),
            'adminUrl' => admin_url(),
            'strings' => [
                'generating' => __('Generating...', 'domain-system'),
                'error' => __('An error occurred. Please try again.', 'domain-system'),
                'confirm_delete' => __('Are you sure you want to delete this FAQ?', 'domain-system'),
                'confirm_duplicate' => __('Are you sure you want to duplicate this domain?', 'domain-system'),
                'content_generated' => __('Content generated successfully!', 'domain-system'),
                'enter_tld_first' => __('Please enter a TLD first.', 'domain-system')
            ]
        ]);
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on domain-related pages
        if (is_post_type_archive('domain') || is_singular('domain')) {
            
            wp_enqueue_script(
                'domain-frontend',
                DOMAIN_SYSTEM_ASSETS_URL . 'js/frontend.js',
                ['jquery'],
                DOMAIN_SYSTEM_VERSION,
                true
            );
            
            wp_enqueue_style(
                'domain-frontend',
                DOMAIN_SYSTEM_ASSETS_URL . 'css/frontend.css',
                [],
                DOMAIN_SYSTEM_VERSION
            );
            
            // Localize frontend script
            wp_localize_script('domain-frontend', 'domainFrontend', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('domain_frontend_nonce'),
                'trackingEnabled' => get_option('domain_enable_analytics', true)
            ]);
        }
    }
    
    /**
     * Handle domain duplication
     */
    public function handle_duplicate_domain() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'domain_admin_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $original_post_id = intval($_POST['post_id']);
        $original_post = get_post($original_post_id);
        
        if (!$original_post || $original_post->post_type !== 'domain') {
            wp_send_json_error(['message' => 'Invalid domain post']);
            return;
        }
        
        // Create new post
        $new_post_data = [
            'post_title' => $original_post->post_title . ' (Copy)',
            'post_content' => $original_post->post_content,
            'post_status' => 'draft',
            'post_type' => 'domain',
            'post_author' => get_current_user_id()
        ];
        
        $new_post_id = wp_insert_post($new_post_data);
        
        if (is_wp_error($new_post_id)) {
            wp_send_json_error(['message' => 'Failed to create duplicate post']);
            return;
        }
        
        // Copy all meta fields
        $meta_keys = [
            '_domain_tld', '_domain_registration_price', '_domain_renewal_price',
            '_domain_transfer_price', '_domain_restoration_price', '_domain_category',
            '_domain_registry', '_domain_min_length', '_domain_max_length'
        ];
        
        foreach ($meta_keys as $meta_key) {
            $meta_value = get_post_meta($original_post_id, $meta_key, true);
            if ($meta_value) {
                update_post_meta($new_post_id, $meta_key, $meta_value);
            }
        }
        
        // Clear the TLD to force user to enter new one
        update_post_meta($new_post_id, '_domain_tld', '');
        
        wp_send_json_success([
            'message' => 'Domain duplicated successfully',
            'edit_url' => get_edit_post_link($new_post_id, 'raw')
        ]);
    }
    
    /**
     * Handle content generation
     */
    public function handle_generate_content() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'domain_admin_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }
        
        $tld = sanitize_text_field($_POST['tld']);
        $category = sanitize_text_field($_POST['category'] ?? '');
        
        if (empty($tld)) {
            wp_send_json_error(['message' => 'TLD is required']);
            return;
        }
        
        // Generate content
        $generated_content = $this->generate_content_for_tld($tld, $category);
        
        wp_send_json_success([
            'message' => 'Content generated successfully',
            'content' => $generated_content
        ]);
    }
    
    /**
     * Handle loading default FAQs
     */
    public function handle_load_default_faqs() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'domain_admin_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
            return;
        }
        
        $tld = sanitize_text_field($_POST['tld']);
        
        if (empty($tld)) {
            wp_send_json_error(['message' => 'TLD is required']);
            return;
        }
        
        // Generate default FAQs
        $default_faqs = $this->get_default_faqs($tld);
        
        wp_send_json_success([
            'message' => 'Default FAQs loaded',
            'faqs' => $default_faqs
        ]);
    }
    
    /**
     * Generate content for TLD
     */
    private function generate_content_for_tld($tld, $category = '') {
        $tld_clean = ltrim($tld, '.');
        $tld_display = '.' . $tld_clean;
        
        return [
            'hero_title' => sprintf(__('Register Your %s Domain Today', 'domain-system'), $tld_display),
            'hero_subtitle' => sprintf(__('Get your %s domain and establish your online presence with a professional web address.', 'domain-system'), $tld_display),
            'description' => sprintf(__('%s domains are perfect for businesses and individuals looking to create a memorable online presence. With competitive pricing and reliable service, your %s domain is just a click away.', 'domain-system'), $tld_display, $tld_display),
            'benefits' => __('✓ Professional appearance\n✓ Memorable web address\n✓ Global recognition\n✓ SEO benefits\n✓ Brand protection', 'domain-system'),
            'policy_info' => sprintf(__('Standard registration policies apply for %s domains. Most %s domains support 2-63 characters and allow letters, numbers, and hyphens.', 'domain-system'), $tld_display, $tld_display)
        ];
    }
    
    /**
     * Get default FAQs for TLD
     */
    private function get_default_faqs($tld) {
        $tld_clean = ltrim($tld, '.');
        $tld_display = '.' . $tld_clean;
        
        return [
            [
                'question' => sprintf(__('What is a %s domain?', 'domain-system'), $tld_display),
                'answer' => sprintf(__('A %s domain is a top-level domain that provides a unique web address for your website.', 'domain-system'), $tld_display)
            ],
            [
                'question' => sprintf(__('How much does a %s domain cost?', 'domain-system'), $tld_display),
                'answer' => sprintf(__('%s domain pricing varies. Check our current rates above for the most up-to-date pricing.', 'domain-system'), $tld_display)
            ],
            [
                'question' => sprintf(__('Who can register a %s domain?', 'domain-system'), $tld_display),
                'answer' => sprintf(__('Anyone can register a %s domain. There are typically no restrictions on registration.', 'domain-system'), $tld_display)
            ]
        ];
    }
    
    /**
     * Add admin footer scripts for enhanced functionality
     */
    public function add_admin_footer_scripts() {
        global $post_type;
        
        if ($post_type !== 'domain') {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Add FAQ template if it doesn't exist
            if (!$('.domain-faq-template').length) {
                const faqTemplate = `
                    <div class="domain-faq-template" style="display: none;">
                        <div class="domain-faq-item" data-index="__INDEX__">
                            <div class="faq-handle">
                                <span class="dashicons dashicons-move"></span>
                            </div>
                            <div class="faq-content">
                                <div class="faq-question">
                                    <label for="faq_question___INDEX__">Question:</label>
                                    <input type="text" id="faq_question___INDEX__" name="domain_faq[__INDEX__][question]" value="" class="large-text" />
                                </div>
                                <div class="faq-answer">
                                    <label for="faq_answer___INDEX__">Answer:</label>
                                    <textarea id="faq_answer___INDEX__" name="domain_faq[__INDEX__][answer]" rows="4" class="large-text"></textarea>
                                </div>
                            </div>
                            <div class="faq-actions">
                                <a href="#" class="remove-faq dashicons dashicons-trash" title="Remove FAQ"></a>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(faqTemplate);
            }
            
            // Add FAQ container if it doesn't exist
            if (!$('#domain-faqs-container').length && $('.domain-faqs-header').length) {
                $('.domain-faqs-header').after('<div id="domain-faqs-container" class="domain-faqs-container"></div>');
            }
        });
        </script>
        <?php
    }
}