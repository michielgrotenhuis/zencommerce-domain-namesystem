<?php
/**
 * Domain Bulk Operations Component
 * 
 * Handles bulk upload, update, and export operations for domains
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainBulkOperations {
    
    /**
     * Initialize hooks
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('wp_ajax_process_bulk_import', [$this, 'process_bulk_import']);
        add_action('wp_ajax_export_domains', [$this, 'export_domains']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=domain',
            __('Bulk Operations', 'domain-system'),
            __('Bulk Operations', 'domain-system'),
            'edit_posts',
            'domain-bulk-operations',
            [$this, 'render_bulk_operations_page']
        );
    }
    
    /**
     * Enqueue scripts
     */
    public function enqueue_scripts($hook) {
        if (strpos($hook, 'domain-bulk-operations') === false) {
            return;
        }
        
        wp_enqueue_script(
            'domain-bulk-operations',
            DOMAIN_SYSTEM_ASSETS_URL . 'js/bulk-operations.js',
            ['jquery'],
            DOMAIN_SYSTEM_VERSION,
            true
        );
        
        wp_localize_script('domain-bulk-operations', 'domainBulk', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('domain_bulk_nonce'),
            'strings' => [
                'processing' => __('Processing...', 'domain-system'),
                'complete' => __('Complete!', 'domain-system'),
                'error' => __('Error occurred', 'domain-system'),
                'confirmDelete' => __('Are you sure you want to delete selected domains?', 'domain-system')
            ]
        ]);
    }
    
    /**
     * Render bulk operations page
     */
    public function render_bulk_operations_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Domain Bulk Operations', 'domain-system'); ?></h1>
            
            <div class="bulk-operations-container">
                <div class="bulk-tabs">
                    <nav class="nav-tab-wrapper">
                        <a href="#import-tab" class="nav-tab nav-tab-active"><?php _e('Import', 'domain-system'); ?></a>
                        <a href="#export-tab" class="nav-tab"><?php _e('Export', 'domain-system'); ?></a>
                        <a href="#update-tab" class="nav-tab"><?php _e('Bulk Update', 'domain-system'); ?></a>
                    </nav>
                    
                    <div id="import-tab" class="tab-content active">
                        <?php $this->render_import_section(); ?>
                    </div>
                    
                    <div id="export-tab" class="tab-content">
                        <?php $this->render_export_section(); ?>
                    </div>
                    
                    <div id="update-tab" class="tab-content">
                        <?php $this->render_update_section(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .bulk-operations-container {
            max-width: 1200px;
            margin: 20px 0;
        }
        
        .bulk-tabs .tab-content {
            display: none;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-top: none;
            padding: 20px;
        }
        
        .bulk-tabs .tab-content.active {
            display: block;
        }
        
        .import-section, .export-section, .update-section {
            max-width: 800px;
        }
        
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            transition: border-color 0.3s ease;
        }
        
        .file-upload-area.dragover {
            border-color: #0073aa;
            background-color: #f0f8ff;
        }
        
        .progress-container {
            display: none;
            margin: 20px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background-color: #0073aa;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .results-container {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f9f9f9;
        }
        
        .error-list, .success-list {
            max-height: 300px;
            overflow-y: auto;
            margin: 10px 0;
        }
        
        .error-item {
            color: #d63384;
            margin: 5px 0;
        }
        
        .success-item {
            color: #198754;
            margin: 5px 0;
        }
        </style>
        <?php
    }
    
    /**
     * Render import section
     */
    private function render_import_section() {
        ?>
        <div class="import-section">
            <h2><?php _e('Import Domains from CSV', 'domain-system'); ?></h2>
            <p><?php _e('Upload a CSV file to import multiple domains at once.', 'domain-system'); ?></p>
            
            <div class="file-upload-area" id="file-upload-area">
                <div class="upload-icon">
                    <span class="dashicons dashicons-upload" style="font-size: 48px; color: #ddd;"></span>
                </div>
                <h3><?php _e('Drop CSV file here or click to browse', 'domain-system'); ?></h3>
                <p><?php _e('Maximum file size: 10MB', 'domain-system'); ?></p>
                <input type="file" id="csv-file-input" accept=".csv" style="display: none;" />
                <button type="button" class="button button-secondary" onclick="document.getElementById('csv-file-input').click();">
                    <?php _e('Choose File', 'domain-system'); ?>
                </button>
            </div>
            
            <div class="import-options">
                <h3><?php _e('Import Options', 'domain-system'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Update Existing', 'domain-system'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="update-existing" checked />
                                <?php _e('Update existing domains if TLD matches', 'domain-system'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Default Category', 'domain-system'); ?></th>
                        <td>
                            <select id="default-category">
                                <option value=""><?php _e('No default category', 'domain-system'); ?></option>
                                <?php foreach ($this->get_domain_categories() as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Batch Size', 'domain-system'); ?></th>
                        <td>
                            <select id="batch-size">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <p class="description"><?php _e('Number of domains to process per batch', 'domain-system'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="import-actions">
                <button type="button" id="start-import" class="button button-primary" disabled>
                    <?php _e('Start Import', 'domain-system'); ?>
                </button>
                <button type="button" id="download-template" class="button button-secondary">
                    <?php _e('Download CSV Template', 'domain-system'); ?>
                </button>
            </div>
            
            <div class="progress-container" id="import-progress">
                <h3><?php _e('Import Progress', 'domain-system'); ?></h3>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <p class="progress-text">0%</p>
            </div>
            
            <div class="results-container" id="import-results" style="display: none;">
                <h3><?php _e('Import Results', 'domain-system'); ?></h3>
                <div class="results-summary"></div>
                <div class="error-list"></div>
                <div class="success-list"></div>
            </div>
            
            <div class="csv-format-info">
                <h3><?php _e('CSV Format', 'domain-system'); ?></h3>
                <p><?php _e('Your CSV file should include the following columns:', 'domain-system'); ?></p>
                <ul>
                    <li><strong>tld</strong> - <?php _e('Required. Domain extension (e.g., .shop, .com)', 'domain-system'); ?></li>
                    <li><strong>title</strong> - <?php _e('Domain title (auto-generated if empty)', 'domain-system'); ?></li>
                    <li><strong>product_id</strong> - <?php _e('External product ID (optional)', 'domain-system'); ?></li>
                    <li><strong>registration_roundup</strong> - <?php _e('Registration price', 'domain-system'); ?></li>
                    <li><strong>renewal_roundup</strong> - <?php _e('Renewal price', 'domain-system'); ?></li>
                    <li><strong>transfer_roundup</strong> - <?php _e('Transfer price', 'domain-system'); ?></li>
                    <li><strong>restoration_roundup</strong> - <?php _e('Restoration price', 'domain-system'); ?></li>
                    <li><strong>primary_category</strong> - <?php _e('Primary category slug', 'domain-system'); ?></li>
                    <li><strong>secondary_categories</strong> - <?php _e('Secondary categories (semicolon separated)', 'domain-system'); ?></li>
                    <li><strong>registry</strong> - <?php _e('Registry name', 'domain-system'); ?></li>
                    <li><strong>hero_h1</strong> - <?php _e('Hero headline', 'domain-system'); ?></li>
                    <li><strong>hero_subtitle</strong> - <?php _e('Hero subtitle', 'domain-system'); ?></li>
                    <li><strong>overview</strong> - <?php _e('Domain overview content', 'domain-system'); ?></li>
                    <li><strong>benefits</strong> - <?php _e('Domain benefits content', 'domain-system'); ?></li>
                    <li><strong>ideas</strong> - <?php _e('Domain ideas content', 'domain-system'); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render export section
     */
    private function render_export_section() {
        $domain_count = wp_count_posts('domain');
        ?>
        <div class="export-section">
            <h2><?php _e('Export Domains', 'domain-system'); ?></h2>
            <p><?php printf(__('Export your domain data. Currently %d domains available.', 'domain-system'), $domain_count->publish + $domain_count->draft); ?></p>
            
            <div class="export-options">
                <h3><?php _e('Export Options', 'domain-system'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Export Format', 'domain-system'); ?></th>
                        <td>
                            <label>
                                <input type="radio" name="export-format" value="csv" checked />
                                <?php _e('CSV', 'domain-system'); ?>
                            </label><br>
                            <label>
                                <input type="radio" name="export-format" value="json" />
                                <?php _e('JSON', 'domain-system'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Status Filter', 'domain-system'); ?></th>
                        <td>
                            <select id="export-status">
                                <option value="all"><?php _e('All Statuses', 'domain-system'); ?></option>
                                <option value="publish"><?php _e('Published Only', 'domain-system'); ?></option>
                                <option value="draft"><?php _e('Drafts Only', 'domain-system'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Category Filter', 'domain-system'); ?></th>
                        <td>
                            <select id="export-category">
                                <option value=""><?php _e('All Categories', 'domain-system'); ?></option>
                                <?php foreach ($this->get_domain_categories() as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Include Content', 'domain-system'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" id="include-content" checked />
                                <?php _e('Include content sections (overview, benefits, etc.)', 'domain-system'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" id="include-faq" checked />
                                <?php _e('Include FAQ data', 'domain-system'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" id="include-seo" />
                                <?php _e('Include SEO metadata', 'domain-system'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="export-actions">
                <button type="button" id="start-export" class="button button-primary">
                    <?php _e('Export Domains', 'domain-system'); ?>
                </button>
                <button type="button" id="preview-export" class="button button-secondary">
                    <?php _e('Preview Export', 'domain-system'); ?>
                </button>
            </div>
            
            <div class="progress-container" id="export-progress">
                <h3><?php _e('Export Progress', 'domain-system'); ?></h3>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <p class="progress-text">0%</p>
            </div>
            
            <div class="results-container" id="export-results" style="display: none;">
                <h3><?php _e('Export Complete', 'domain-system'); ?></h3>
                <div class="export-download"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render update section
     */
    private function render_update_section() {
        ?>
        <div class="update-section">
            <h2><?php _e('Bulk Update Domains', 'domain-system'); ?></h2>
            <p><?php _e('Update multiple domains at once with new values.', 'domain-system'); ?></p>
            
            <div class="update-filters">
                <h3><?php _e('Select Domains', 'domain-system'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Filter by Category', 'domain-system'); ?></th>
                        <td>
                            <select id="update-category-filter">
                                <option value=""><?php _e('All Categories', 'domain-system'); ?></option>
                                <?php foreach ($this->get_domain_categories() as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Filter by Price Range', 'domain-system'); ?></th>
                        <td>
                            <input type="number" id="price-min" placeholder="Min" step="0.01" style="width: 80px;" />
                            -
                            <input type="number" id="price-max" placeholder="Max" step="0.01" style="width: 80px;" />
                        </td>
                    </tr>
                </table>
                <button type="button" id="load-domains" class="button button-secondary">
                    <?php _e('Load Matching Domains', 'domain-system'); ?>
                </button>
            </div>
            
            <div class="domain-list-container" id="domain-list" style="display: none;">
                <h3><?php _e('Domains to Update', 'domain-system'); ?></h3>
                <div class="domains-table-wrapper">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <td class="check-column">
                                    <input type="checkbox" id="select-all-domains" />
                                </td>
                                <th><?php _e('TLD', 'domain-system'); ?></th>
                                <th><?php _e('Title', 'domain-system'); ?></th>
                                <th><?php _e('Price', 'domain-system'); ?></th>
                                <th><?php _e('Category', 'domain-system'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="domains-tbody">
                            <!-- Populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="update-fields" id="update-fields" style="display: none;">
                <h3><?php _e('Update Fields', 'domain-system'); ?></h3>
                <p><?php _e('Select which fields to update and their new values:', 'domain-system'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label>
                                <input type="checkbox" name="update_field" value="primary_category" />
                                <?php _e('Primary Category', 'domain-system'); ?>
                            </label>
                        </th>
                        <td>
                            <select name="new_primary_category" disabled>
                                <option value=""><?php _e('Select Category', 'domain-system'); ?></option>
                                <?php foreach ($this->get_domain_categories() as $slug => $name): ?>
                                <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>
                                <input type="checkbox" name="update_field" value="registration_roundup" />
                                <?php _e('Registration Price', 'domain-system'); ?>
                            </label>
                        </th>
                        <td>
                            <div class="price-update-options">
                                <label>
                                    <input type="radio" name="price_update_type" value="set" checked />
                                    <?php _e('Set to:', 'domain-system'); ?>
                                </label>
                                <input type="number" name="new_registration_price" step="0.01" disabled />
                                <br>
                                <label>
                                    <input type="radio" name="price_update_type" value="increase" />
                                    <?php _e('Increase by:', 'domain-system'); ?>
                                </label>
                                <input type="number" name="price_increase" step="0.01" disabled />
                                <br>
                                <label>
                                    <input type="radio" name="price_update_type" value="decrease" />
                                    <?php _e('Decrease by:', 'domain-system'); ?>
                                </label>
                                <input type="number" name="price_decrease" step="0.01" disabled />
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>
                                <input type="checkbox" name="update_field" value="registry" />
                                <?php _e('Registry', 'domain-system'); ?>
                            </label>
                        </th>
                        <td>
                            <input type="text" name="new_registry" disabled placeholder="<?php _e('Registry name', 'domain-system'); ?>" />
                        </td>
                    </tr>
                </table>
                
                <div class="update-actions">
                    <button type="button" id="preview-update" class="button button-secondary">
                        <?php _e('Preview Changes', 'domain-system'); ?>
                    </button>
                    <button type="button" id="apply-update" class="button button-primary">
                        <?php _e('Apply Changes', 'domain-system'); ?>
                    </button>
                </div>
            </div>
            
            <div class="progress-container" id="update-progress">
                <h3><?php _e('Update Progress', 'domain-system'); ?></h3>
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <p class="progress-text">0%</p>
            </div>
            
            <div class="results-container" id="update-results" style="display: none;">
                <h3><?php _e('Update Results', 'domain-system'); ?></h3>
                <div class="results-summary"></div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Process bulk import via AJAX
     */
    public function process_bulk_import() {
        check_ajax_referer('domain_bulk_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied', 'domain-system'));
        }
        
        $batch_data = $_POST['batch_data'] ?? [];
        $options = $_POST['options'] ?? [];
        
        if (empty($batch_data)) {
            wp_send_json_error(__('No data to import', 'domain-system'));
        }
        
        $results = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => [],
            'success' => []
        ];
        
        foreach ($batch_data as $index => $row) {
            try {
                $result = $this->import_single_domain($row, $options);
                $results['processed']++;
                
                if ($result['success']) {
                    if ($result['action'] === 'created') {
                        $results['created']++;
                        $results['success'][] = sprintf(
                            __('Row %d: Created domain %s', 'domain-system'),
                            $index + 1,
                            $result['tld']
                        );
                    } else {
                        $results['updated']++;
                        $results['success'][] = sprintf(
                            __('Row %d: Updated domain %s', 'domain-system'),
                            $index + 1,
                            $result['tld']
                        );
                    }
                } else {
                    $results['errors'][] = sprintf(
                        __('Row %d: %s', 'domain-system'),
                        $index + 1,
                        $result['error']
                    );
                }
            } catch (Exception $e) {
                $results['errors'][] = sprintf(
                    __('Row %d: Exception - %s', 'domain-system'),
                    $index + 1,
                    $e->getMessage()
                );
            }
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * Import single domain
     */
    private function import_single_domain($data, $options) {
        // Validate required fields
        if (empty($data['tld'])) {
            return ['success' => false, 'error' => __('TLD is required', 'domain-system')];
        }
        
        $tld = sanitize_domain_tld($data['tld']);
        
        // Check if domain exists
        $existing_domain = get_domain_by_tld($tld);
        
        if ($existing_domain && empty($options['update_existing'])) {
            return ['success' => false, 'error' => __('Domain already exists', 'domain-system')];
        }
        
        $post_data = [
            'post_type' => 'domain',
            'post_status' => 'draft',
            'post_title' => !empty($data['title']) ? sanitize_text_field($data['title']) : sprintf(__('%s Domain', 'domain-system'), $tld),
            'post_content' => !empty($data['content']) ? wp_kses_post($data['content']) : '',
            'post_excerpt' => !empty($data['excerpt']) ? sanitize_textarea_field($data['excerpt']) : '',
        ];
        
        if ($existing_domain) {
            $post_data['ID'] = $existing_domain->ID;
            $post_id = wp_update_post($post_data);
            $action = 'updated';
        } else {
            $post_id = wp_insert_post($post_data);
            $action = 'created';
        }
        
        if (is_wp_error($post_id)) {
            return ['success' => false, 'error' => $post_id->get_error_message()];
        }
        
        // Update meta fields
        $meta_fields = [
            'tld', 'product_id', 'registration_roundup', 'renewal_roundup',
            'transfer_roundup', 'restoration_roundup', 'primary_category',
            'secondary_categories', 'registry', 'hero_h1', 'hero_subtitle',
            'overview', 'benefits', 'ideas', 'stats'
        ];
        
        foreach ($meta_fields as $field) {
            if (isset($data[$field])) {
                $value = $data[$field];
                
                // Handle secondary categories
                if ($field === 'secondary_categories' && is_string($value)) {
                    $value = array_map('trim', explode(';', $value));
                }
                
                update_post_meta($post_id, "_domain_{$field}", $value);
            }
        }
        
        // Set default category if specified
        if (!empty($options['default_category']) && empty($data['primary_category'])) {
            update_post_meta($post_id, '_domain_primary_category', $options['default_category']);
        }
        
        // Auto-generate slug
        $slug = tld_to_slug($tld);
        wp_update_post([
            'ID' => $post_id,
            'post_name' => $slug
        ]);
        
        return [
            'success' => true,
            'action' => $action,
            'tld' => $tld,
            'post_id' => $post_id
        ];
    }
    
    /**
     * Export domains via AJAX
     */
    public function export_domains() {
        check_ajax_referer('domain_bulk_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Permission denied', 'domain-system'));
        }
        
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        $filters = $_POST['filters'] ?? [];
        $options = $_POST['options'] ?? [];
        
        // Build query args
        $args = [
            'post_type' => 'domain',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_domain_tld',
                    'compare' => 'EXISTS'
                ]
            ]
        ];
        
        // Apply filters
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $args['post_status'] = sanitize_text_field($filters['status']);
        } else {
            $args['post_status'] = ['publish', 'draft'];
        }
        
        if (!empty($filters['category'])) {
            $args['meta_query'][] = [
                'key' => '_domain_primary_category',
                'value' => sanitize_text_field($filters['category']),
                'compare' => '='
            ];
        }
        
        $domains = get_posts($args);
        
        if (empty($domains)) {
            wp_send_json_error(__('No domains found matching criteria', 'domain-system'));
        }
        
        $export_data = [];
        
        foreach ($domains as $domain) {
            $domain_data = [
                'id' => $domain->ID,
                'title' => $domain->post_title,
                'tld' => get_post_meta($domain->ID, '_domain_tld', true),
                'product_id' => get_post_meta($domain->ID, '_domain_product_id', true),
                'registration_roundup' => get_post_meta($domain->ID, '_domain_registration_roundup', true),
                'renewal_roundup' => get_post_meta($domain->ID, '_domain_renewal_roundup', true),
                'transfer_roundup' => get_post_meta($domain->ID, '_domain_transfer_roundup', true),
                'restoration_roundup' => get_post_meta($domain->ID, '_domain_restoration_roundup', true),
                'primary_category' => get_post_meta($domain->ID, '_domain_primary_category', true),
                'secondary_categories' => implode(';', get_post_meta($domain->ID, '_domain_secondary_categories', true) ?: []),
                'registry' => get_post_meta($domain->ID, '_domain_registry', true),
                'hero_h1' => get_post_meta($domain->ID, '_domain_hero_h1', true),
                'hero_subtitle' => get_post_meta($domain->ID, '_domain_hero_subtitle', true),
                'status' => $domain->post_status,
                'created' => $domain->post_date,
                'modified' => $domain->post_modified
            ];
            
            // Include content if requested
            if (!empty($options['include_content'])) {
                $domain_data['overview'] = get_post_meta($domain->ID, '_domain_overview', true);
                $domain_data['benefits'] = get_post_meta($domain->ID, '_domain_benefits', true);
                $domain_data['ideas'] = get_post_meta($domain->ID, '_domain_ideas', true);
                $domain_data['stats'] = get_post_meta($domain->ID, '_domain_stats', true);
            }
            
            // Include FAQ if requested
            if (!empty($options['include_faq'])) {
                $faq = get_post_meta($domain->ID, '_domain_faq', true);
                $domain_data['faq'] = json_encode($faq);
            }
            
            // Include SEO if requested
            if (!empty($options['include_seo'])) {
                $domain_data['seo_title'] = get_post_meta($domain->ID, '_domain_seo_title', true);
                $domain_data['seo_description'] = get_post_meta($domain->ID, '_domain_seo_description', true);
                $domain_data['seo_keywords'] = get_post_meta($domain->ID, '_domain_seo_keywords', true);
            }
            
            $export_data[] = $domain_data;
        }
        
        // Generate export file
        if ($format === 'csv') {
            $filename = $this->generate_csv_export($export_data);
        } else {
            $filename = $this->generate_json_export($export_data);
        }
        
        wp_send_json_success([
            'filename' => $filename,
            'count' => count($export_data),
            'download_url' => wp_upload_dir()['url'] . '/domain-system/exports/' . $filename
        ]);
    }
    
    /**
     * Generate CSV export
     */
    private function generate_csv_export($data) {
        if (empty($data)) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/domain-system/exports/';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        $filename = 'domains-export-' . date('Y-m-d-H-i-s') . '.csv';
        $filepath = $export_dir . $filename;
        
        $file = fopen($filepath, 'w');
        
        // Write headers
        $headers = array_keys($data[0]);
        fputcsv($file, $headers);
        
        // Write data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }
        
        fclose($file);
        
        return $filename;
    }
    
    /**
     * Generate JSON export
     */
    private function generate_json_export($data) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/domain-system/exports/';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        $filename = 'domains-export-' . date('Y-m-d-H-i-s') . '.json';
        $filepath = $export_dir . $filename;
        
        $json_data = [
            'export_date' => current_time('mysql'),
            'total_domains' => count($data),
            'domains' => $data
        ];
        
        file_put_contents($filepath, json_encode($json_data, JSON_PRETTY_PRINT));
        
        return $filename;
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
     * Download CSV template
     */
    public function download_csv_template() {
        $headers = [
            'tld',
            'title',
            'product_id',
            'registration_roundup',
            'renewal_roundup',
            'transfer_roundup',
            'restoration_roundup',
            'primary_category',
            'secondary_categories',
            'registry',
            'hero_h1',
            'hero_subtitle',
            'overview',
            'benefits',
            'ideas',
            'stats'
        ];
        
        $sample_data = [
            [
                '.example',
                'Example Domain Registration',
                'PRD-123456',
                '9.99',
                '12.99',
                '9.99',
                '79.99',
                'technology',
                'new;short',
                'Example Registry Inc.',
                'Get Your Perfect .example Domain',
                'Perfect for examples and demonstrations',
                'The .example domain is ideal for...',
                'Benefits include enhanced credibility...',
                'Great for tech startups, demos...',
                'Released in 2023, over 1000 registrations...'
            ]
        ];
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="domain-import-template.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, $headers);
        
        foreach ($sample_data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}