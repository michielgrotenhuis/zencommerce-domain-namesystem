<?php
/**
 * Domain Meta Boxes Component - Updated for Real Categories
 * 
 * File: includes/post-type/class-domain-meta-boxes.php
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
        
        // Registry Information (Updated to use taxonomy)
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
        
        // Remove default taxonomy boxes since we're using custom ones
        remove_meta_box('domain_categorydiv', 'domain', 'side');
        remove_meta_box('tagsdiv-domain_tag', 'domain', 'side');
        remove_meta_box('tagsdiv-domain_registry', 'domain', 'side');
    }
    
    /**
     * Render core information meta box
     */
    public function render_core_info($post) {
        wp_nonce_field('domain_meta_save', 'domain_meta_nonce');
        
        $tld = get_post_meta($post->ID, '_domain_tld', true);
        $product_id = get_post_meta($post->ID, '_domain_product_id', true);
        $existing_domains = function_exists('get_all_domain_tlds') ? get_all_domain_tlds(false) : [];
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
        // Load FAQ renderer if it exists
        $faq_renderer_file = DOMAIN_SYSTEM_INCLUDES_DIR . 'post-type/class-domain-faq-renderer.php';
        if (file_exists($faq_renderer_file)) {
            require_once $faq_renderer_file;
            $faq_renderer = new DomainFaqRenderer();
            $faq_renderer->render($post);
        } else {
            // Fallback FAQ rendering
            $faq_data = get_post_meta($post->ID, '_domain_faq', true) ?: [];
            ?>
            <div class="domain-faq">
                <div id="faq-items">
                    <?php if (!empty($faq_data)): ?>
                        <?php foreach ($faq_data as $index => $faq): ?>
                        <div class="faq-item" data-index="<?php echo $index; ?>">
                            <div class="faq-header">
                                <span class="faq-number"><?php echo $index + 1; ?></span>
                                <input type="text" 
                                       name="domain_faq[<?php echo $index; ?>][question]" 
                                       value="<?php echo esc_attr($faq['question']); ?>" 
                                       placeholder="<?php _e('FAQ Question', 'domain-system'); ?>" 
                                       class="faq-question" />
                                <button type="button" class="remove-faq button-link-delete"><?php _e('Remove', 'domain-system'); ?></button>
                            </div>
                            <textarea name="domain_faq[<?php echo $index; ?>][answer]" 
                                      rows="3" 
                                      placeholder="<?php _e('FAQ Answer', 'domain-system'); ?>" 
                                      class="faq-answer"><?php echo esc_textarea($faq['answer']); ?></textarea>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <p>
                    <button type="button" id="add-faq-item" class="button">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Add FAQ Item', 'domain-system'); ?>
                    </button>
                </p>
            </div>
            
            <style>
            .faq-item {
                border: 1px solid #ddd;
                margin-bottom: 10px;
                padding: 15px;
                background: #f9f9f9;
            }
            
            .faq-header {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }
            
            .faq-number {
                background: #0073aa;
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: bold;
                margin-right: 10px;
                flex-shrink: 0;
            }
            
            .faq-question {
                flex: 1;
                margin-right: 10px;
            }
            
            .faq-answer {
                width: 100%;
            }
            
            .remove-faq {
                color: #a00;
                text-decoration: none;
                flex-shrink: 0;
            }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                var faqIndex = <?php echo count($faq_data); ?>;
                
                $('#add-faq-item').on('click', function() {
                    var html = '<div class="faq-item" data-index="' + faqIndex + '">' +
                               '<div class="faq-header">' +
                               '<span class="faq-number">' + (faqIndex + 1) + '</span>' +
                               '<input type="text" name="domain_faq[' + faqIndex + '][question]" placeholder="<?php _e('FAQ Question', 'domain-system'); ?>" class="faq-question" />' +
                               '<button type="button" class="remove-faq button-link-delete"><?php _e('Remove', 'domain-system'); ?></button>' +
                               '</div>' +
                               '<textarea name="domain_faq[' + faqIndex + '][answer]" rows="3" placeholder="<?php _e('FAQ Answer', 'domain-system'); ?>" class="faq-answer"></textarea>' +
                               '</div>';
                    
                    $('#faq-items').append(html);
                    faqIndex++;
                    updateFaqNumbers();
                });
                
                $(document).on('click', '.remove-faq', function() {
                    $(this).closest('.faq-item').remove();
                    updateFaqNumbers();
                });
                
                function updateFaqNumbers() {
                    $('#faq-items .faq-item').each(function(index) {
                        $(this).find('.faq-number').text(index + 1);
                        $(this).attr('data-index', index);
                    });
                }
            });
            </script>
            <?php
        }
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
 * Render registry meta box - Updated to use taxonomy
 */
public function render_registry($post) {
    // Get selected registries for the post
    $selected_registries = wp_get_post_terms($post->ID, 'domain_registry', ['fields' => 'ids']);
    if (is_wp_error($selected_registries)) {
        $selected_registries = [];
    }

    // Get all available registries
    $all_registries = get_terms([
        'taxonomy'   => 'domain_registry',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC'
    ]);

    // Legacy support
    $legacy_registry = get_post_meta($post->ID, '_domain_registry', true);
    ?>
    <div class="domain-registry">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="domain_registry_select"><?php esc_html_e('Domain Registry', 'domain-system'); ?></label>
                </th>
                <td>
                    <?php if (!empty($all_registries) && !is_wp_error($all_registries)) : ?>
                        <select id="domain_registry_select" name="tax_input[domain_registry][]" class="widefat">
                            <option value=""><?php esc_html_e('Select Registry', 'domain-system'); ?></option>
                            <?php foreach ($all_registries as $registry) : ?>
                                <option value="<?php echo esc_attr($registry->term_id); ?>"
                                    <?php selected(in_array($registry->term_id, $selected_registries), true); ?>>
                                    <?php echo esc_html($registry->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <p class="description">
                            <?php esc_html_e('Select the registry that manages this TLD.', 'domain-system'); ?>
                            <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=domain_registry&post_type=domain')); ?>" target="_blank">
                                <?php esc_html_e('Manage Registries', 'domain-system'); ?>
                            </a>
                        </p>
                    <?php else : ?>
                        <p>
                            <em><?php esc_html_e('No registries available.', 'domain-system'); ?></em>
                            <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=domain_registry&post_type=domain')); ?>" class="button button-secondary">
                                <?php esc_html_e('Add Registry', 'domain-system'); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($legacy_registry)) : ?>
                        <div class="notice notice-info inline">
                            <p>
                                <strong><?php esc_html_e('Legacy Registry:', 'domain-system'); ?></strong>
                                <?php echo esc_html($legacy_registry); ?><br>
                                <small>
                                    <?php esc_html_e('This domain has a legacy registry value. Please select the appropriate registry above.', 'domain-system'); ?>
                                </small>
                            </p>
                        </div>
                    <?php endif; ?>
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
                <h4><?php _e('Categories', 'domain-system'); ?></h4>
                <p>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=domain_category&post_type=domain'); ?>" class="button button-secondary" style="width: 100%;">
                        <span class="dashicons dashicons-category"></span>
                        <?php _e('Manage Categories', 'domain-system'); ?>
                    </a>
                </p>
                <p>
                    <a href="<?php echo admin_url('edit-tags.php?taxonomy=domain_registry&post_type=domain'); ?>" class="button button-secondary" style="width: 100%;">
                        <span class="dashicons dashicons-admin-site"></span>
                        <?php _e('Manage Registries', 'domain-system'); ?>
                    </a>
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
}