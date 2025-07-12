<?php
/**
 * Domain Meta Boxes Component - Updated for Structured Fields
 * 
 * Handles all meta box registration and rendering for domain posts
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainMetaBoxes {
    
    /**
     * Add all meta boxes
     */
    public function add_boxes() {
        // Core Information
        add_meta_box(
            'domain-core-info',
            __('Core Information', 'domain-system'),
            [$this, 'render_core_info'],
            'domain',
            'normal',
            'high'
        );
        
        // Pricing Information (Updated with Roundup fields)
        add_meta_box(
            'domain-pricing',
            __('Pricing Information', 'domain-system'),
            [$this, 'render_pricing'],
            'domain',
            'normal',
            'high'
        );
        
        // Categories
        add_meta_box(
            'domain-categories',
            __('Domain Categories', 'domain-system'),
            [$this, 'render_categories'],
            'domain',
            'side',
            'high'
        );
        
        // Hero Section
        add_meta_box(
            'domain-hero',
            __('Hero Section', 'domain-system'),
            [$this, 'render_hero'],
            'domain',
            'normal',
            'default'
        );
        
        // Domain Content Sections
        add_meta_box(
            'domain-content-sections',
            __('Domain Content Sections', 'domain-system'),
            [$this, 'render_content_sections'],
            'domain',
            'normal',
            'default'
        );
        
        // FAQ Management (Enhanced)
        add_meta_box(
            'domain-faq',
            __('FAQ Section', 'domain-system'),
            [$this, 'render_faq'],
            'domain',
            'normal',
            'default'
        );
        
        // Domain Policy
        add_meta_box(
            'domain-policy',
            __('Domain Policy & Rules', 'domain-system'),
            [$this, 'render_policy'],
            'domain',
            'side',
            'default'
        );
        
        // Registry Information
        add_meta_box(
            'domain-registry',
            __('Registry Information', 'domain-system'),
            [$this, 'render_registry'],
            'domain',
            'side',
            'default'
        );
        
        // Tools & Actions
        add_meta_box(
            'domain-tools',
            __('Tools & Actions', 'domain-system'),
            [$this, 'render_tools'],
            'domain',
            'side',
            'high'
        );
        
        // SEO Information
        add_meta_box(
            'domain-seo',
            __('SEO Information', 'domain-system'),
            [$this, 'render_seo'],
            'domain',
            'side',
            'low'
        );
    }
    
    /**
     * Render core information meta box
     */
    public function render_core_info($post) {
        wp_nonce_field('domain_meta_save', 'domain_meta_nonce');
        
        $tld = get_post_meta($post->ID, '_domain_tld', true);
        $product_id = get_post_meta($post->ID, '_domain_product_id', true);
        $existing_domains = get_all_domain_tlds(false);
        ?>
        <div class="domain-core-info">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="domain_tld"><?php _e('TLD (Top Level Domain)', 'domain-system'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="domain_tld" 
                               name="domain_tld" 
                               value="<?php echo esc_attr($tld); ?>" 
                               placeholder=".shop" 
                               class="regular-text domain-tld-input" 
                               required 
                               data-existing="<?php echo esc_attr(json_encode($existing_domains)); ?>" />
                        <div id="tld-validation-message" class="validation-message"></div>
                        <p class="description"><?php _e('Domain extension (e.g., .shop, .com, .co.uk)', 'domain-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_product_id"><?php _e('Product ID', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="domain_product_id" 
                               name="domain_product_id" 
                               value="<?php echo esc_attr($product_id); ?>" 
                               placeholder="PRD-123456" 
                               class="regular-text" />
                        <p class="description"><?php _e('External system product identifier (optional - for Upmind)', 'domain-system'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div class="domain-preview" id="domain-preview" style="display: none;">
                <h4><?php _e('Domain Preview', 'domain-system'); ?></h4>
                <div class="preview-content">
                    <p><strong><?php _e('URL:', 'domain-system'); ?></strong> <span id="preview-url"></span></p>
                    <p><strong><?php _e('Slug:', 'domain-system'); ?></strong> <span id="preview-slug"></span></p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render pricing meta box with roundup fields
     */
    public function render_pricing($post) {
        $price_fields = [
            'registration_roundup' => __('Registration Roundup', 'domain-system'),
            'renewal_roundup' => __('Renewal Roundup', 'domain-system'),
            'transfer_roundup' => __('Transfer Roundup', 'domain-system'),
            'restoration_roundup' => __('Restoration Roundup', 'domain-system')
        ];
        $currency_symbol = get_option('domain_currency_symbol', '$');
        ?>
        <div class="domain-pricing">
            <table class="form-table">
                <?php foreach ($price_fields as $field => $label): 
                    $value = get_post_meta($post->ID, "_domain_{$field}", true);
                    $required = ($field === 'registration_roundup') ? 'required' : '';
                ?>
                <tr>
                    <th scope="row">
                        <label for="domain_<?php echo $field; ?>">
                            <?php echo $label; ?>
                            <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                        </label>
                    </th>
                    <td>
                        <div class="price-input-group">
                            <span class="currency-symbol"><?php echo esc_html($currency_symbol); ?></span>
                            <input type="number" 
                                   step="0.01" 
                                   min="0" 
                                   id="domain_<?php echo $field; ?>" 
                                   name="domain_<?php echo $field; ?>" 
                                   value="<?php echo esc_attr($value); ?>" 
                                   class="small-text price-input" 
                                   <?php echo $required; ?> />
                        </div>
                        <?php if ($field === 'registration_roundup'): ?>
                        <p class="description"><?php _e('Primary registration price (e.g. $4.00)', 'domain-system'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="pricing-summary" id="pricing-summary">
                <h4><?php _e('Pricing Summary', 'domain-system'); ?></h4>
                <div class="summary-content"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render categories meta box
     */
    public function render_categories($post) {
        $primary_category = get_post_meta($post->ID, '_domain_primary_category', true);
        $secondary_categories = get_post_meta($post->ID, '_domain_secondary_categories', true) ?: [];
        $all_categories = $this->get_domain_categories();
        ?>
        <div class="domain-categories">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="domain_primary_category"><?php _e('Primary Category', 'domain-system'); ?> <span class="required">*</span></label>
                    </th>
                    <td>
                        <select id="domain_primary_category" name="domain_primary_category" class="widefat" required>
                            <option value=""><?php _e('Select Primary Category', 'domain-system'); ?></option>
                            <?php foreach ($all_categories as $slug => $name): ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php selected($primary_category, $slug); ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Main category for this domain extension', 'domain-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_secondary_categories"><?php _e('Secondary Categories', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <select id="domain_secondary_categories" name="domain_secondary_categories[]" class="widefat" multiple size="6">
                            <?php foreach ($all_categories as $slug => $name): ?>
                            <option value="<?php echo esc_attr($slug); ?>" <?php echo in_array($slug, $secondary_categories) ? 'selected' : ''; ?>>
                                <?php echo esc_html($name); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Additional categories (hold Ctrl/Cmd to select multiple)', 'domain-system'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render hero section meta box
     */
    public function render_hero($post) {
        $hero_h1 = get_post_meta($post->ID, '_domain_hero_h1', true);
        $hero_subtitle = get_post_meta($post->ID, '_domain_hero_subtitle', true);
        ?>
        <div class="domain-hero">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="domain_hero_h1"><?php _e('Hero H1', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="domain_hero_h1" 
                               name="domain_hero_h1" 
                               value="<?php echo esc_attr($hero_h1); ?>" 
                               class="large-text"
                               placeholder="<?php _e('e.g. Find your perfect .shop domain', 'domain-system'); ?>" />
                        <p class="description"><?php _e('Main headline for the hero section', 'domain-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_hero_subtitle"><?php _e('Hero Subtitle', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <textarea id="domain_hero_subtitle" 
                                  name="domain_hero_subtitle" 
                                  rows="3" 
                                  class="large-text"
                                  placeholder="<?php _e('Supporting text for the hero section', 'domain-system'); ?>"><?php echo esc_textarea($hero_subtitle); ?></textarea>
                        <p class="description"><?php _e('Subtitle text below the main headline', 'domain-system'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render content sections meta box
     */
    public function render_content_sections($post) {
        $tld = get_post_meta($post->ID, '_domain_tld', true);
        $tld_display = $tld ? ltrim($tld, '.') : 'TLD';
        
        $sections = [
            'overview' => [
                'title' => sprintf(__('Why Choose a .%s Domain?', 'domain-system'), $tld_display),
                'label' => __('Domain Overview', 'domain-system'),
                'description' => __('General overview and benefits of this domain extension', 'domain-system')
            ],
            'stats' => [
                'title' => sprintf(__('.%s Domain Stats & History', 'domain-system'), $tld_display),
                'label' => __('Domain Stats / History', 'domain-system'),
                'description' => __('Statistics, facts, and history about the domain', 'domain-system')
            ],
            'benefits' => [
                'title' => sprintf(__('.%s Domain Benefits', 'domain-system'), $tld_display),
                'label' => __('Domain Benefits', 'domain-system'),
                'description' => __('Specific benefits of choosing this domain extension', 'domain-system')
            ],
            'ideas' => [
                'title' => sprintf(__('.%s Domain Ideas', 'domain-system'), $tld_display),
                'label' => __('Domain Ideas', 'domain-system'),
                'description' => __('Ideas and suggestions for using this domain extension', 'domain-system')
            ]
        ];
        ?>
        <div class="domain-content-sections">
            <div class="content-tabs">
                <ul class="tab-nav">
                    <?php $first = true; foreach ($sections as $key => $section): ?>
                    <li><a href="#<?php echo $key; ?>-tab" class="<?php echo $first ? 'active' : ''; ?>"><?php echo esc_html($section['label']); ?></a></li>
                    <?php $first = false; endforeach; ?>
                </ul>
                
                <?php $first = true; foreach ($sections as $key => $section): 
                    $value = get_post_meta($post->ID, "_domain_{$key}", true);
                ?>
                <div id="<?php echo $key; ?>-tab" class="tab-content <?php echo $first ? 'active' : ''; ?>">
                    <h4><?php echo esc_html($section['title']); ?></h4>
                    <p class="description"><?php echo esc_html($section['description']); ?></p>
                    <?php 
                    wp_editor($value, "domain_{$key}", [
                        'textarea_name' => "domain_{$key}",
                        'textarea_rows' => 8,
                        'media_buttons' => true,
                        'teeny' => false,
                        'tinymce' => [
                            'toolbar1' => 'bold,italic,underline,link,unlink,bullist,numlist,blockquote,alignleft,aligncenter,alignright,undo,redo',
                            'toolbar2' => '',
                            'resize' => true
                        ]
                    ]);
                    ?>
                </div>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
        
        <style>
        .content-tabs .tab-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            border-bottom: 1px solid #ddd;
        }
        
        .content-tabs .tab-nav li {
            margin: 0;
        }
        
        .content-tabs .tab-nav a {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            border: 1px solid #ddd;
            border-bottom: none;
            background: #f9f9f9;
            color: #666;
        }
        
        .content-tabs .tab-nav a.active {
            background: #fff;
            color: #333;
            border-bottom: 1px solid #fff;
            margin-bottom: -1px;
        }
        
        .content-tabs .tab-content {
            display: none;
            padding: 20px 0;
        }
        
        .content-tabs .tab-content.active {
            display: block;
        }
        </style>
        <?php
    }
    
    /**
     * Render FAQ meta box
     */
    public function render_faq($post) {
        // Load FAQ renderer
        require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-faq-renderer.php';
        $faq_renderer = new DomainFaqRenderer();
        $faq_renderer->render($post);
    }
    
    /**
     * Render policy meta box with enhanced fields
     */
    public function render_policy($post) {
        $min_length = get_post_meta($post->ID, '_domain_min_length', true) ?: 2;
        $max_length = get_post_meta($post->ID, '_domain_max_length', true) ?: 63;
        $numbers_allowed = get_post_meta($post->ID, '_domain_numbers_allowed', true);
        $hyphens_allowed = get_post_meta($post->ID, '_domain_hyphens_allowed', true) ?: 'middle';
        $idn_allowed = get_post_meta($post->ID, '_domain_idn_allowed', true);
        ?>
        <div class="domain-policy">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="domain_min_length"><?php _e('Min Length', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               id="domain_min_length"
                               name="domain_min_length" 
                               value="<?php echo esc_attr($min_length); ?>" 
                               min="1" 
                               max="63" 
                               class="small-text" />
                        <span class="description"><?php _e('characters', 'domain-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_max_length"><?php _e('Max Length', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="number" 
                               id="domain_max_length"
                               name="domain_max_length" 
                               value="<?php echo esc_attr($max_length); ?>" 
                               min="1" 
                               max="63" 
                               class="small-text" />
                        <span class="description"><?php _e('characters', 'domain-system'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_numbers_allowed"><?php _e('Numbers Allowed', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   id="domain_numbers_allowed"
                                   name="domain_numbers_allowed" 
                                   value="1" 
                                   <?php checked($numbers_allowed, 1); ?> />
                            <?php _e('Yes', 'domain-system'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_hyphens_allowed"><?php _e('Hyphens Allowed', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <select id="domain_hyphens_allowed" name="domain_hyphens_allowed">
                            <option value="none" <?php selected($hyphens_allowed, 'none'); ?>><?php _e('Not allowed', 'domain-system'); ?></option>
                            <option value="middle" <?php selected($hyphens_allowed, 'middle'); ?>><?php _e('Middle only', 'domain-system'); ?></option>
                            <option value="start" <?php selected($hyphens_allowed, 'start'); ?>><?php _e('Start only', 'domain-system'); ?></option>
                            <option value="end" <?php selected($hyphens_allowed, 'end'); ?>><?php _e('End only', 'domain-system'); ?></option>
                            <option value="all" <?php selected($hyphens_allowed, 'all'); ?>><?php _e('Anywhere', 'domain-system'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_idn_allowed"><?php _e('IDN Allowed', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" 
                                   id="domain_idn_allowed"
                                   name="domain_idn_allowed" 
                                   value="1" 
                                   <?php checked($idn_allowed, 1); ?> />
                            <?php _e('Yes', 'domain-system'); ?>
                        </label>
                        <p class="description"><?php _e('Internationalized Domain Names', 'domain-system'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div class="policy-preview">
                <h4><?php _e('Policy Preview', 'domain-system'); ?></h4>
                <div id="policy-preview-text" class="policy-text"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render registry meta box
     */
    public function render_registry($post) {
        $registry = get_post_meta($post->ID, '_domain_registry', true);
        $registries = $this->get_popular_registries();
        ?>
        <div class="domain-registry">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="domain_registry"><?php _e('Domain Registry', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="domain_registry" 
                               name="domain_registry" 
                               value="<?php echo esc_attr($registry); ?>" 
                               class="widefat" 
                               list="registry-suggestions"
                               placeholder="<?php _e('e.g., Verisign, Donuts Inc.', 'domain-system'); ?>" />
                        
                        <datalist id="registry-suggestions">
                            <?php foreach ($registries as $reg): ?>
                            <option value="<?php echo esc_attr($reg); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        
                        <p class="description"><?php _e('The registry company that manages this TLD', 'domain-system'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Render tools meta box with bulk operations
     */
    public function render_tools($post) {
        $tld = get_post_meta($post->ID, '_domain_tld', true);
        ?>
        <div class="domain-tools">
            <?php if ($post->ID && !empty($tld)): ?>
                <div class="tool-section">
                    <h4><?php _e('Domain Information', 'domain-system'); ?></h4>
                    <p><strong><?php _e('TLD:', 'domain-system'); ?></strong> <?php echo esc_html($tld); ?></p>
                    <p><strong><?php _e('URL:', 'domain-system'); ?></strong></p>
                    <p><a href="<?php echo esc_url(get_permalink($post->ID)); ?>" target="_blank" class="button button-secondary">
                        <span class="dashicons dashicons-external"></span>
                        <?php _e('View Page', 'domain-system'); ?>
                    </a></p>
                </div>
                <hr>
            <?php endif; ?>
            
            <div class="tool-section">
                <h4><?php _e('Quick Actions', 'domain-system'); ?></h4>
                <p>
                    <button type="button" id="duplicate-domain" class="button button-secondary" <?php echo !$post->ID ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-admin-page"></span>
                        <?php _e('Duplicate Domain', 'domain-system'); ?>
                    </button>
                </p>
                <p>
                    <button type="button" id="generate-content" class="button button-secondary">
                        <span class="dashicons dashicons-superhero"></span>
                        <?php _e('Auto-Generate Content', 'domain-system'); ?>
                    </button>
                </p>
            </div>
            <hr>
            
            <div class="tool-section">
                <h4><?php _e('Bulk Operations', 'domain-system'); ?></h4>
                <p>
                    <a href="<?php echo admin_url('edit.php?post_type=domain&page=domain-bulk-operations'); ?>" class="button button-secondary" style="width: 100%;">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Bulk Upload/Update', 'domain-system'); ?>
                    </a>
                </p>
                <p>
                    <button type="button" id="export-domains" class="button button-secondary" style="width: 100%;">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export Domains', 'domain-system'); ?>
                    </button>
                </p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render SEO meta box
     */
    public function render_seo($post) {
        $seo_title = get_post_meta($post->ID, '_domain_seo_title', true);
        $seo_description = get_post_meta($post->ID, '_domain_seo_description', true);
        $seo_keywords = get_post_meta($post->ID, '_domain_seo_keywords', true);
        $og_image = get_post_meta($post->ID, '_domain_og_image', true);
        ?>
        <div class="domain-seo">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="domain_seo_title"><?php _e('SEO Title', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="domain_seo_title" 
                               name="domain_seo_title" 
                               value="<?php echo esc_attr($seo_title); ?>" 
                               class="widefat"
                               maxlength="60" />
                        <p class="description">
                            <?php _e('Recommended: 50-60 characters', 'domain-system'); ?>
                            <span class="char-count">0/60</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_seo_description"><?php _e('Meta Description', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <textarea id="domain_seo_description" 
                                  name="domain_seo_description" 
                                  rows="3" 
                                  class="widefat"
                                  maxlength="160"><?php echo esc_textarea($seo_description); ?></textarea>
                        <p class="description">
                            <?php _e('Recommended: 150-160 characters', 'domain-system'); ?>
                            <span class="char-count">0/160</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_seo_keywords"><?php _e('Focus Keywords', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <input type="text" 
                               id="domain_seo_keywords" 
                               name="domain_seo_keywords" 
                               value="<?php echo esc_attr($seo_keywords); ?>" 
                               class="widefat" />
                        <p class="description"><?php _e('Comma-separated keywords', 'domain-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="domain_og_image"><?php _e('Social Media Image', 'domain-system'); ?></label>
                    </th>
                    <td>
                        <div class="og-image-upload">
                            <input type="hidden" id="domain_og_image" name="domain_og_image" value="<?php echo esc_attr($og_image); ?>" />
                            <div class="og-image-preview" <?php echo empty($og_image) ? 'style="display:none;"' : ''; ?>>
                                <img src="<?php echo esc_url($og_image); ?>" style="max-width: 200px; height: auto;" />
                                <br>
                                <button type="button" class="button remove-og-image"><?php _e('Remove', 'domain-system'); ?></button>
                            </div>
                            <button type="button" class="button upload-og-image" <?php echo !empty($og_image) ? 'style="display:none;"' : ''; ?>>
                                <?php _e('Upload Image', 'domain-system'); ?>
                            </button>
                        </div>
                        <p class="description"><?php _e('Recommended: 1200x630px', 'domain-system'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
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
     * Get popular registries
     */
    private function get_popular_registries() {
        $registries = get_domain_registries();
        
        // Add some common ones if list is empty
        if (empty($registries)) {
            $registries = [
                'Verisign',
                'Donuts Inc.',
                'GMO Registry',
                'Radix Registry',
                'Afilias',
                'Neustar',
                'CentralNic',
                'Google Registry',
                'Amazon Registry Services'
            ];
        }
        
        return $registries;
    }
}