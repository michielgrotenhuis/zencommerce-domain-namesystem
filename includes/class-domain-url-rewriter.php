<?php
/**
 * Domain URL Rewriter
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainURLRewriter {
    
    public function __construct() {
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('template_redirect', [$this, 'handle_template_redirect']);
        add_filter('post_type_link', [$this, 'filter_post_link'], 10, 2);
        add_filter('manage_domain_posts_columns', [$this, 'add_admin_columns']);
        add_action('manage_domain_posts_custom_column', [$this, 'populate_admin_columns'], 10, 2);
        add_filter('manage_edit-domain_sortable_columns', [$this, 'add_sortable_columns']);
        add_action('pre_get_posts', [$this, 'handle_column_sorting']);
    }
    
    /**
     * Add rewrite rules
     */
    public function add_rewrite_rules() {
        // Main domain page: /domains/shop/
        add_rewrite_rule(
            '^domains/([^/]+)/?$',
            'index.php?post_type=domain&domain_slug=$matches[1]',
            'top'
        );
        
        // Domain search: /domain-search/
        add_rewrite_rule(
            '^domain-search/?$',
            'index.php?domain_search=1',
            'top'
        );
        
        // Domain category: /domain-category/business/
        add_rewrite_rule(
            '^domain-category/([^/]+)/?$',
            'index.php?domain_category=$matches[1]',
            'top'
        );
        
        // API endpoints
        add_rewrite_rule(
            '^api/domains/?$',
            'index.php?domain_api=list',
            'top'
        );
        
        add_rewrite_rule(
            '^api/domains/([^/]+)/?$',
            'index.php?domain_api=single&domain_slug=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'domain_slug';
        $vars[] = 'domain_search';
        $vars[] = 'domain_api';
        return $vars;
    }
    
    /**
     * Handle template redirect
     */
    public function handle_template_redirect() {
        global $wp_query;
        
        // Handle domain pages
        $domain_slug = get_query_var('domain_slug');
        if (!empty($domain_slug)) {
            $this->handle_domain_page($domain_slug);
            return;
        }
        
        // Handle domain search
        if (get_query_var('domain_search')) {
            $this->handle_domain_search();
            return;
        }
        
        // Handle API requests
        $domain_api = get_query_var('domain_api');
        if (!empty($domain_api)) {
            $this->handle_api_request($domain_api);
            return;
        }
    }
    
    /**
     * Handle domain page display
     */
    private function handle_domain_page($slug) {
        $domain_post = $this->get_domain_by_slug($slug);
        
        if (!$domain_post) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }
        
        global $post, $wp_query;
        $post = $domain_post;
        setup_postdata($post);
        
        $wp_query->is_single = true;
        $wp_query->is_singular = true;
        $wp_query->is_404 = false;
        $wp_query->queried_object = $post;
        $wp_query->queried_object_id = $post->ID;
        
        // Load template
        $template = $this->locate_domain_template();
        if ($template) {
            include $template;
            exit;
        }
    }
    
    /**
     * Handle domain search page
     */
    private function handle_domain_search() {
        global $wp_query;
        
        $wp_query->is_home = false;
        $wp_query->is_page = true;
        $wp_query->is_404 = false;
        
        // Set page title
        add_filter('wp_title', function($title) {
            return __('Domain Search', 'domain-system') . ' | ' . get_bloginfo('name');
        });
        
        add_filter('document_title_parts', function($parts) {
            $parts['title'] = __('Domain Search', 'domain-system');
            return $parts;
        });
        
        // Load search template
        $template = $this->locate_search_template();
        if ($template) {
            include $template;
            exit;
        }
    }
    
    /**
     * Handle API requests
     */
    private function handle_api_request($api_type) {
        header('Content-Type: application/json');
        
        switch ($api_type) {
            case 'list':
                $this->api_list_domains();
                break;
            case 'single':
                $this->api_single_domain();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
        }
        
        exit;
    }
    
    /**
     * API: List domains
     */
    private function api_list_domains() {
        $page = max(1, intval($_GET['page'] ?? 1));
        $per_page = min(50, max(1, intval($_GET['per_page'] ?? 10)));
        $category = sanitize_text_field($_GET['category'] ?? '');
        
        $args = [
            'post_type' => 'domain',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $page,
            'meta_query' => [
                [
                    'key' => '_domain_tld',
                    'compare' => 'EXISTS'
                ]
            ]
        ];
        
        if ($category) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'domain_category',
                    'field' => 'slug',
                    'terms' => $category
                ]
            ];
        }
        
        $query = new WP_Query($args);
        $domains = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $data = get_domain_data(get_the_ID());
                
                $domains[] = [
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'tld' => $data['tld'],
                    'price' => $data['registration_price'],
                    'url' => get_permalink(),
                    'excerpt' => get_the_excerpt()
                ];
            }
            wp_reset_postdata();
        }
        
        echo json_encode([
            'domains' => $domains,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $page
        ]);
    }
    
    /**
     * API: Single domain
     */
    private function api_single_domain() {
        $slug = get_query_var('domain_slug');
        $domain_post = $this->get_domain_by_slug($slug);
        
        if (!$domain_post) {
            http_response_code(404);
            echo json_encode(['error' => 'Domain not found']);
            return;
        }
        
        $data = get_domain_data($domain_post->ID);
        $categories = wp_get_post_terms($domain_post->ID, 'domain_category', ['fields' => 'names']);
        
        echo json_encode([
            'id' => $domain_post->ID,
            'title' => $domain_post->post_title,
            'content' => apply_filters('the_content', $domain_post->post_content),
            'tld' => $data['tld'],
            'prices' => [
                'registration' => $data['registration_price'],
                'renewal' => $data['renewal_price'],
                'transfer' => $data['transfer_price'],
                'restoration' => $data['restoration_price']
            ],
            'policy' => $data['policy'],
            'registry' => $data['registry'],
            'categories' => $categories,
            'url' => get_permalink($domain_post->ID),
            'faq' => $data['faq']
        ]);
    }
    
    /**
     * Get domain by slug
     */
    private function get_domain_by_slug($slug) {
        // Try cache first
        $cache_key = "domain_by_slug_{$slug}";
        $domain = wp_cache_get($cache_key, 'domain_system');
        
        if ($domain === false) {
            $tld = str_replace('-', '.', $slug);
            
            $query = new WP_Query([
                'post_type' => 'domain',
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => '_domain_tld',
                        'value' => [ltrim($tld, '.'), $tld],
                        'compare' => 'IN'
                    ]
                ]
            ]);
            
            $domain = $query->have_posts() ? $query->posts[0] : null;
            
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $domain, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return $domain;
    }
    
    /**
     * Locate domain template
     */
    private function locate_domain_template() {
        $templates = [
            'single-domain.php',
            'domain/single.php',
            'page-domain.php'
        ];
        
        // Check theme first
        foreach ($templates as $template) {
            $theme_template = locate_template([$template]);
            if ($theme_template) {
                return $theme_template;
            }
        }
        
        // Fallback to plugin template
        $plugin_template = DOMAIN_SYSTEM_TEMPLATES_DIR . 'single-domain.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
        
        return null;
    }
    
    /**
     * Locate search template
     */
    private function locate_search_template() {
        $templates = [
            'page-domain-search.php',
            'domain/search.php',
            'domain-search.php'
        ];
        
        // Check theme first
        foreach ($templates as $template) {
            $theme_template = locate_template([$template]);
            if ($theme_template) {
                return $theme_template;
            }
        }
        
        // Fallback to plugin template
        $plugin_template = DOMAIN_SYSTEM_TEMPLATES_DIR . 'domain-search.php';
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }
        
        return null;
    }
    
    /**
     * Filter post permalink
     */
    public function filter_post_link($post_link, $post) {
        if ($post->post_type === 'domain') {
            $tld = get_post_meta($post->ID, '_domain_tld', true);
            if (!empty($tld)) {
                $slug = ltrim($tld, '.');
                $slug = str_replace('.', '-', $slug);
                return home_url("/domains/{$slug}/");
            }
        }
        return $post_link;
    }
    
    /**
     * Add admin columns
     */
    public function add_admin_columns($columns) {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['domain_tld'] = __('TLD', 'domain-system');
        $new_columns['domain_price'] = __('Price', 'domain-system');
        $new_columns['domain_status'] = __('Status', 'domain-system');
        $new_columns['taxonomy-domain_category'] = __('Category', 'domain-system');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Populate admin columns
     */
    public function populate_admin_columns($column, $post_id) {
        switch ($column) {
            case 'domain_tld':
                $tld = get_post_meta($post_id, '_domain_tld', true);
                if (!empty($tld)) {
                    $display_tld = ltrim($tld, '.');
                    echo '<strong>.' . esc_html($display_tld) . '</strong>';
                    echo '<div class="row-actions">';
                    echo '<span class="view"><a href="' . esc_url(get_permalink($post_id)) . '" target="_blank">' . __('View', 'domain-system') . '</a> | </span>';
                    echo '<span class="copy"><a href="#" onclick="navigator.clipboard.writeText(\'' . esc_js(get_permalink($post_id)) . '\'); return false;">' . __('Copy URL', 'domain-system') . '</a></span>';
                    echo '</div>';
                } else {
                    echo '<span style="color: #dc3232;">' . __('Not set', 'domain-system') . '</span>';
                }
                break;
                
            case 'domain_price':
                $price = get_post_meta($post_id, '_domain_registration_price', true);
                $renewal = get_post_meta($post_id, '_domain_renewal_price', true);
                
                if (!empty($price)) {
                    echo '<strong>' . format_domain_price($price) . '</strong>';
                    if (!empty($renewal)) {
                        echo '<br><small>' . __('Renewal:', 'domain-system') . ' ' . format_domain_price($renewal) . '</small>';
                    }
                } else {
                    echo '<span style="color: #dc3232;">' . __('Not set', 'domain-system') . '</span>';
                }
                break;
                
            case 'domain_status':
                $tld = get_post_meta($post_id, '_domain_tld', true);
                $price = get_post_meta($post_id, '_domain_registration_price', true);
                
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
                
                if ($status === 'complete') {
                    echo '<span style="color: #46b450;" title="' . __('Complete', 'domain-system') . '">●</span>';
                } else {
                    echo '<span style="color: #dc3232;" title="' . implode(', ', $issues) . '">●</span>';
                }
                break;
        }
    }
    
    /**
     * Add sortable columns
     */
    public function add_sortable_columns($columns) {
        $columns['domain_tld'] = 'domain_tld';
        $columns['domain_price'] = 'domain_price';
        return $columns;
    }
    
    /**
     * Handle column sorting
     */
    public function handle_column_sorting($query) {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'domain') {
            return;
        }
        
        $orderby = $query->get('orderby');
        
        if ($orderby === 'domain_tld') {
            $query->set('meta_key', '_domain_tld');
            $query->set('orderby', 'meta_value');
        } elseif ($orderby === 'domain_price') {
            $query->set('meta_key', '_domain_registration_price');
            $query->set('orderby', 'meta_value_num');
        }
    }
    
    /**
     * Get domain archive URL
     */
    public function get_domain_archive_url() {
        return home_url('/domains/');
    }
    
    /**
     * Get domain category URL
     */
    public function get_domain_category_url($category_slug) {
        return home_url("/domain-category/{$category_slug}/");
    }
    
    /**
     * Get domain search URL
     */
    public function get_domain_search_url($query = '') {
        $url = home_url('/domain-search/');
        if ($query) {
            $url = add_query_arg('q', urlencode($query), $url);
        }
        return $url;
    }
}