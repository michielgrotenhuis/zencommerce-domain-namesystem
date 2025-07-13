<?php
/**
 * Domain Meta Box Enhancements
 * 
 * Adds proper JavaScript/CSS enqueuing and AJAX handlers
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainMetaBoxEnhancements {
    
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_duplicate_domain', [$this, 'handle_duplicate_domain']);
        add_action('wp_ajax_generate_domain_content', [$this, 'handle_generate_content']);
        add_action('admin_footer', [$this, 'add_admin_footer_scripts']);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post_type;
        
        // Only load on domain edit pages
        if ($post_type !== 'domain' || !in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }
        
        // Enqueue WordPress media uploader
        wp_enqueue_media();
        
        // Enqueue jQuery UI for sortable functionality
        wp_enqueue_script('jquery-ui-sortable');
        
        // Enqueue our custom scripts
        wp_enqueue_script(
            'domain-metabox-admin',
            plugin_dir_url(__FILE__) . 'assets/js/domain-metabox-admin.js',
            ['jquery', 'jquery-ui-sortable', 'wp-util'],
            '1.0.0',
            true
        );
        
        // Enqueue our custom styles
        wp_enqueue_style(
            'domain-metabox-admin',
            plugin_dir_url(__FILE__) . 'assets/css/domain-metabox-admin.css',
            [],
            '1.0.0'
        );
        
        // Localize script with necessary data
        wp_localize_script('domain-metabox-admin', 'domain_admin_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('domain_admin_nonce'),
            'site_url' => home_url(),
            'admin_url' => admin_url(),
            'messages' => [
                'duplicate_confirm' => __('Are you sure you want to duplicate this domain?', 'domain-system'),
                'generate_confirm' => __('This will generate content for all sections. Continue?', 'domain-system'),
                'remove_faq_confirm' => __('Are you sure you want to remove this FAQ item?', 'domain-system'),
                'error_occurred' => __('An error occurred. Please try again.', 'domain-system'),
                'content_generated' => __('Content generated successfully!', 'domain-system'),
                'enter_tld_first' => __('Please enter a TLD first.', 'domain-system')
            ]
        ]);
    }
    
    /**
     * Handle domain duplication via AJAX
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
            '_domain_tld', '_domain_product_id', '_domain_registration_roundup',
            '_domain_renewal_roundup', '_domain_transfer_roundup', '_domain_restoration_roundup',
            '_domain_primary_category', '_domain_secondary_categories', '_domain_hero_h1',
            '_domain_hero_subtitle', '_domain_overview', '_domain_stats', '_domain_benefits',
            '_domain_ideas', '_domain_faq', '_domain_min_length', '_domain_max_length',
            '_domain_numbers_allowed', '_domain_hyphens_allowed', '_domain_idn_allowed',
            '_domain_registry', '_domain_seo_title', '_domain_seo_description',
            '_domain_seo_keywords', '_domain_og_image'
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
     * Handle content generation via AJAX
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
        
        if (empty($tld)) {
            wp_send_json_error(['message' => 'TLD is required']);
            return;
        }
        
        // Clean TLD format
        $tld = ltrim($tld, '.');
        $tld_display = '.' . $tld;
        
        // Generate content based on TLD
        $generated_content = $this->generate_content_for_tld($tld);
        
        wp_send_json_success($generated_content);
    }
    
    /**
     * Generate content for a specific TLD
     */
    private function generate_content_for_tld($tld) {
        $tld_display = '.' . $tld;
        $tld_upper = strtoupper($tld);
        
        // Generate hero content
        $hero_h1 = sprintf(__('Find Your Perfect %s Domain', 'domain-system'), $tld_display);
        $hero_subtitle = sprintf(
            __('Secure your %s domain today and establish your online presence with a memorable, professional web address.', 'domain-system'),
            $tld_display
        );
        
        // Generate overview content
        $overview = sprintf(
            __('<p>%s domains offer a unique opportunity to create a memorable online presence. Whether you\'re starting a new business, launching a personal project, or expanding your digital footprint, a %s domain provides the perfect foundation for your website.</p>

<p>With %s domains, you get:</p>
<ul>
<li>Enhanced brand recognition and memorability</li>
<li>Professional appearance that builds trust</li>
<li>SEO benefits from relevant domain extensions</li>
<li>Global accessibility and recognition</li>
</ul>

<p>Join thousands of satisfied customers who have chosen %s domains for their online ventures.</p>', 'domain-system'),
            $tld_display, $tld_display, $tld_display, $tld_display
        );
        
        // Generate stats content
        $stats = sprintf(
            __('<p>%s domains have gained significant traction since their introduction:</p>

<ul>
<li><strong>Registration Growth:</strong> Steady increase in registrations year over year</li>
<li><strong>Global Reach:</strong> Available worldwide with international support</li>
<li><strong>Industry Adoption:</strong> Trusted by businesses and individuals alike</li>
<li><strong>Search Engine Recognition:</strong> Fully supported by major search engines</li>
</ul>

<p>The %s extension represents innovation in the domain name space, offering users more choice and flexibility in their online branding.</p>', 'domain-system'),
            $tld_display, $tld_display
        );
        
        // Generate benefits content
        $benefits = sprintf(
            __('<h3>Key Benefits of %s Domains</h3>

<h4>🎯 Brand Differentiation</h4>
<p>Stand out from the crowd with a unique domain extension that reflects your brand\'s personality and purpose.</p>

<h4>🌍 Global Recognition</h4>
<p>%s domains are recognized and trusted worldwide, ensuring your website is accessible to international audiences.</p>

<h4>🔒 Security & Trust</h4>
<p>Built with modern security standards and backed by reliable registry infrastructure.</p>

<h4>📈 SEO Advantages</h4>
<p>Search engines treat %s domains equally to traditional extensions, with potential benefits for relevant searches.</p>

<h4>💰 Value for Money</h4>
<p>Competitive pricing with excellent value compared to premium traditional domains.</p>', 'domain-system'),
            $tld_display, $tld_display, $tld_display
        );
        
        // Generate ideas content based on TLD type
        $ideas = $this->generate_domain_ideas($tld);
        
        return [
            'hero_h1' => $hero_h1,
            'hero_subtitle' => $hero_subtitle,
            'overview' => $overview,
            'stats' => $stats,
            'benefits' => $benefits,
            'ideas' => $ideas
        ];
    }
    
    /**
     * Generate domain ideas based on TLD
     */
    private function generate_domain_ideas($tld) {
        $tld_display = '.' . $tld;
        
        // Common domain ideas that work for most TLDs
        $common_ideas = [
            'Business Names' => ['yourbusiness', 'companyname', 'brandname', 'startup'],
            'Personal Projects' => ['yourname', 'portfolio', 'blog', 'personal'],
            'Creative Ventures' => ['creative', 'design', 'art', 'studio'],
            'Professional Services' => ['consulting', 'services', 'expert', 'pro']
        ];
        
        // TLD-specific ideas
        $specific_ideas = [];
        
        switch (strtolower($tld)) {
            case 'shop':
                $specific_ideas = [
                    'Online Stores' => ['mystore', 'boutique', 'market', 'goods'],
                    'Product Brands' => ['products', 'brand', 'collection', 'line'],
                    'Retail Concepts' => ['retail', 'sale', 'discount', 'premium']
                ];
                break;
            case 'tech':
                $specific_ideas = [
                    'Technology' => ['innovation', 'digital', 'code', 'dev'],
                    'Startups' => ['startup', 'venture', 'labs', 'hub'],
                    'Services' => ['solutions', 'systems', 'platform', 'cloud']
                ];
                break;
            case 'blog':
                $specific_ideas = [
                    'Content' => ['stories', 'news', 'updates', 'journal'],
                    'Topics' => ['lifestyle', 'travel', 'food', 'fashion'],
                    'Personal' => ['thoughts', 'diary', 'life', 'journey']
                ];
                break;
            default:
                $specific_ideas = [
                    'Industry-Specific' => ['industry', 'niche', 'specialty', 'focus'],
                    'Geographic' => ['local', 'regional', 'city', 'area'],
                    'Community' => ['group', 'community', 'network', 'connect']
                ];
        }
        
        $all_ideas = array_merge($specific_ideas, $common_ideas);
        
        $ideas_html = sprintf(
            __('<p>Looking for inspiration for your %s domain? Here are some creative ideas to get you started:</p>', 'domain-system'),
            $tld_display
        );
        
        foreach ($all_ideas as $category => $examples) {
            $ideas_html .= "<h4>{$category}</h4><ul>";
            foreach ($examples as $example) {
                $ideas_html .= "<li><strong>{$example}{$tld_display}</strong></li>";
            }
            $ideas_html .= "</ul>";
        }
        
        $ideas_html .= sprintf(
            __('<p><strong>Pro Tip:</strong> Keep your %s domain short, memorable, and relevant to your brand or purpose. Avoid hyphens and numbers when possible for the best user experience.</p>', 'domain-system'),
            $tld_display
        );
        
        return $ideas_html;
    }
    
    /**
     * Add admin footer scripts for inline functionality
     */
    public function add_admin_footer_scripts() {
        global $post_type;
        
        if ($post_type !== 'domain') {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Initialize FAQ functionality if FAQ items exist
            if ($('.faq-item').length > 0) {
                $('.faq-item').each(function() {
                    $(this).find('.faq-item-content').show();
                });
            }
            
            // Auto-update pricing summary
            $('.price-input').on('input', function() {
                updatePricingSummary();
            });
            
            function updatePricingSummary() {
                var $summary = $('.pricing-summary .summary-content');
                var registration = parseFloat($('#domain_registration_roundup').val()) || 0;
                var renewal = parseFloat($('#domain_renewal_roundup').val()) || 0;
                var transfer = parseFloat($('#domain_transfer_roundup').val()) || 0;
                var restoration = parseFloat($('#domain_restoration_roundup').val()) || 0;
                
                var currency = '<?php echo esc_js(get_option("domain_currency_symbol", "")); ?>';

                var summaryHtml = '';
                if (registration > 0) {
                    summaryHtml += '<div class="summary-item"><strong>' + currency + registration.toFixed(2) + '</strong><span>Registration</span></div>';
                }
                if (renewal > 0) {
                    summaryHtml += '<div class="summary-item"><strong>' + currency + renewal.toFixed(2) + '</strong><span>Renewal</span></div>';
                }
                if (transfer > 0) {
                    summaryHtml += '<div class="summary-item"><strong>' + currency + transfer.toFixed(2) + '</strong><span>Transfer</span></div>';
                }
                if (restoration > 0) {
                    summaryHtml += '<div class="summary-item"><strong>' + currency + restoration.toFixed(2) + '</strong><span>Restoration</span></div>';
                }
                
                $summary.html(summaryHtml);
                
                if (summaryHtml) {
                    $('.pricing-summary').show();
                } else {
                    $('.pricing-summary').hide();
                }
            }
            
            // Update policy preview
            function updatePolicyPreview() {
                var minLength = $('#domain_min_length').val() || 2;
                var maxLength = $('#domain_max_length').val() || 63;
                var numbersAllowed = $('#domain_numbers_allowed').is(':checked');
                var hyphensAllowed = $('#domain_hyphens_allowed').val();
                var idnAllowed = $('#domain_idn_allowed').is(':checked');
                
                var policyText = 'Domain Policy:\n';
                policyText += '• Length: ' + minLength + '-' + maxLength + ' characters\n';
                policyText += '• Numbers: ' + (numbersAllowed ? 'Allowed' : 'Not allowed') + '\n';
                policyText += '• Hyphens: ' + hyphensAllowed.charAt(0).toUpperCase() + hyphensAllowed.slice(1) + '\n';
                policyText += '• Internationalized domains: ' + (idnAllowed ? 'Supported' : 'Not supported');
                
                $('#policy-preview-text').text(policyText);
            }
            
            $('.domain-policy input, .domain-policy select').on('change input', updatePolicyPreview);
            
            // Initialize on page load
            updatePricingSummary();
            updatePolicyPreview();
        });
        </script>
        <?php
    }
}

// Initialize the enhancements
new DomainMetaBoxEnhancements();