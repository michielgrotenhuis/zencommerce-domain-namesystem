<?php
/**
 * Domain Meta Boxes Component
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
        
        // Pricing Information
        add_meta_box(
            'domain-pricing',
            __('Pricing Information', 'domain-system'),
            [$this, 'render_pricing'],
            'domain',
            'normal',
            'high'
        );
        
        // Content Sections
        add_meta_box(
            'domain-content',
            __('Content Sections', 'domain-system'),
            [$this, 'render_content'],
            'domain',
            'normal',
            'default'
        );
        
        // FAQ Management
        add_meta_box(
            'domain-faq',
            __('FAQ Management', 'domain-system'),
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
                        <p class="description"><?php _e('External system product identifier (optional)', 'domain-system'); ?></p>
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
     * Render pricing meta box
     */
    public function render_pricing($post) {
        $price_fields = ['registration_price', 'renewal_price', 'transfer_price', 'restoration_price'];
        $field_labels = [
            'registration_price' => __('Registration Price', 'domain-system'),
            'renewal_price' => __('Renewal Price', 'domain-system'),
            'transfer_price' => __('Transfer Price', 'domain-system'),
            'restoration_price' => __('Restoration Price', 'domain-system')
        ];
        $currency_symbol = get_option('domain_currency_symbol', '$');
        ?>
        <div class="domain-pricing">
            <table class="form-table">
                <?php foreach ($price_fields as $field): 
                    $value = get_post_meta($post->ID, "_domain_{$field}", true);
                    $required = ($field === 'registration_price') ? 'required' : '';
                ?>
                <tr>
                    <th scope="row">
                        <label for="domain_<?php echo $field; ?>">
                            <?php echo $field_labels[$field]; ?>
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
     * Render content meta box
     */
    public function render_content($post) {
        $content_fields = ['hero_h1', 'hero_subtitle', 'overview', 'stats', 'benefits', 'ideas'];
        $field_labels = [
            'hero_h1' => __('Hero Title', 'domain-system'),
            'hero_subtitle' => __('Hero Subtitle', 'domain-system'),
            'overview' => __('Domain Overview', 'domain-system'),
            'stats' => __('Domain Stats & History', 'domain-system'),
            'benefits' => __('Domain Benefits', 'domain-system'),
            'ideas' => __('Usage Ideas', 'domain-system')
        ];
        ?>
        <div class="domain-content">
            <div class="content-tabs">
                <ul class="tab-nav">
                    <li><a href="#hero-tab" class="active"><?php _e('Hero Section', 'domain-system'); ?></a></li>
                    <li><a href="#content-tab"><?php _e('Content', 'domain-system'); ?></a></li>
                </ul>
                
                <div id="hero-tab" class="tab-content active">
                    <table class="form-table">
                        <?php foreach (['hero_h1', 'hero_subtitle'] as $field):
                            $value = get_post_meta($post->ID, "_domain_{$field}", true);
                        ?>
                        <tr>
                            <th scope="row">
                                <label for="domain_<?php echo $field; ?>"><?php echo $field_labels[$field]; ?></label>
                            </th>
                            <td>
                                <?php if ($field === 'hero_subtitle'): ?>
                                    <textarea id="domain_<?php echo $field; ?>" 
                                              name="domain_<?php echo $field; ?>" 
                                              rows="3" 
                                              class="large-text"><?php echo esc_textarea($value); ?></textarea>
                                <?php else: ?>
                                    <input type="text" 
                                           id="domain_<?php echo $field; ?>" 
                                           name="domain_<?php echo $field; ?>" 
                                           value="<?php echo esc_attr($value); ?>" 
                                           class="large-text" />
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <div id="content-tab" class="tab-content">
                    <?php foreach (['overview', 'stats', 'benefits', 'ideas'] as $field):
                        $value = get_post_meta($post->ID, "_domain_{$field}", true);
                    ?>
                    <div class="content-section">
                        <h4><?php echo $field_labels[$field]; ?></h4>
                        <?php 
                        wp_editor($value, "domain_{$field}", [
                            'textarea_name' => "domain_{$field}",
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
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
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
     * Render policy meta box
     */
    public function render_policy($post) {
        $policy_fields = [
            'min_length' => __('Minimum Length', 'domain-system'),
            'max_length' => __('Maximum Length', 'domain-system'),
            'numbers_allowed' => __('Numbers Allowed', 'domain-system'),
            'hyphens_allowed' => __('Hyphens Allowed', 'domain-system'),
            'idn_allowed' => __('IDN Allowed', 'domain-system')
        ];
        ?>
        <div class="domain-policy">
            <table class="form-table">
                <?php foreach ($policy_fields as $field => $label):
                    $value = get_post_meta($post->ID, "_domain_{$field}", true);
                    
                    // Set default values
                    if ($value === '') {
                        if ($field === 'min_length') $value = 2;
                        if ($field === 'max_length') $value = 63;
                        if ($field === 'hyphens_allowed') $value = 'middle';
                    }
                ?>
                <tr>
                    <th scope="row">
                        <label for="domain_<?php echo $field; ?>"><?php echo $label; ?></label>
                    </th>
                    <td>
                        <?php if ($field === 'numbers_allowed' || $field === 'idn_allowed'): ?>
                            <label>
                                <input type="checkbox" 
                                       id="domain_<?php echo $field; ?>"
                                       name="domain_<?php echo $field; ?>" 
                                       value="1" 
                                       <?php checked($value, 1); ?> />
                                <?php _e('Allowed', 'domain-system'); ?>
                            </label>
                        <?php elseif ($field === 'hyphens_allowed'): ?>
                            <select id="domain_<?php echo $field; ?>" name="domain_<?php echo $field; ?>">
                                <option value="none" <?php selected($value, 'none'); ?>><?php _e('Not allowed', 'domain-system'); ?></option>
                                <option value="middle" <?php selected($value, 'middle'); ?>><?php _e('Middle only', 'domain-system'); ?></option>
                                <option value="all" <?php selected($value, 'all'); ?>><?php _e('Anywhere', 'domain-system'); ?></option>
                            </select>
                        <?php else: ?>
                            <input type="number" 
                                   id="domain_<?php echo $field; ?>"
                                   name="domain_<?php echo $field; ?>" 
                                   value="<?php echo esc_attr($value); ?>" 
                                   min="1" 
                                   max="63" 
                                   class="small-text" />
                            <?php if ($field === 'min_length'): ?>
                                <span class="description"> - </span>
                            <?php else: ?>
                                <span class="description"><?php _e('characters', 'domain-system'); ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
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
                        <label for="domain_registry"><?php _e('Registry', 'domain-system'); ?></label>
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
     * Render tools meta box
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
                    <label for="bulk-import-file"><?php _e('Import CSV:', 'domain-system'); ?></label>
                    <input type="file" id="bulk-import-file" accept=".csv" style="width: 100%; margin: 5px 0;" />
                </p>
                <p>
                    <button type="button" id="process-bulk-import" class="button button-secondary" style="width: 100%;">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import CSV', 'domain-system'); ?>
                    </button>
                </p>
                
                <div id="import-progress" class="import-progress" style="display: none;">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p class="import-status"></p>
                </div>
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