<?php
/**
 * Domain System Helper Functions
 * 
 * Contains utility functions used throughout the Domain System plugin
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get domain by TLD
 * 
 * @param string $tld The TLD to search for
 * @return WP_Post|null Domain post object or null if not found
 */
function get_domain_by_tld($tld) {
    $args = [
        'post_type' => 'domain',
        'meta_query' => [
            [
                'key' => '_domain_tld',
                'value' => sanitize_text_field($tld),
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1,
        'post_status' => 'publish'
    ];
    
    $domains = get_posts($args);
    return !empty($domains) ? $domains[0] : null;
}

/**
 * Get all domain TLDs
 * 
 * @param bool $published_only Whether to get only published domains
 * @return array Array of TLD strings
 */
function get_all_domain_tlds($published_only = true) {
    static $cache = [];
    $cache_key = $published_only ? 'published' : 'all';
    
    if (!isset($cache[$cache_key])) {
        global $wpdb;
        
        $status_condition = $published_only ? "AND p.post_status = 'publish'" : '';
        
        $tlds = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = %s
            AND p.post_type = %s
            {$status_condition}
            AND pm.meta_value != ''
            ORDER BY pm.meta_value ASC
        ", '_domain_tld', 'domain'));
        
        $cache[$cache_key] = $tlds ?: [];
    }
    
    return $cache[$cache_key];
}

/**
 * Check if domain exists
 * 
 * @param string $tld The TLD to check
 * @return bool True if domain exists, false otherwise
 */
function domain_exists($tld) {
    return get_domain_by_tld($tld) !== null;
}

/**
 * Get domain registries
 * 
 * @return array Array of registry names
 */
function get_domain_registries() {
    static $registries = null;
    
    if ($registries === null) {
        $registries = apply_filters('domain_system_registries', [
            'Verisign',
            'Donuts Inc.',
            'GMO Registry',
            'Radix Registry',
            'Afilias',
            'Neustar',
            'CentralNic',
            'Google Registry',
            'Amazon Registry Services',
            'Charleston Road Registry',
            'Nominet',
            'Public Interest Registry',
            'Internet Corporation for Assigned Names and Numbers',
            'Minds + Machines',
            'Rightside Registry',
            'Famous Four Media',
            'Registry Services, LLC',
            'dot Luxury LLC',
            'Identity Digital',
            'XYZ.COM LLC',
            'Tucows Domains Inc.',
            'eNom, LLC',
            'GoDaddy Registry',
            'Namecheap, Inc.',
            'PDR Ltd.',
            'Gandi SAS',
            'Name.com, Inc.',
            'MarkMonitor Inc.',
            'Network Solutions, LLC',
            'Wild West Domains, LLC'
        ]);
    }
    
    return $registries;
}

/**
 * Convert TLD to slug
 * 
 * @param string $tld The TLD to convert
 * @return string Sanitized slug
 */
function tld_to_slug($tld) {
    $tld = ltrim($tld, '.');
    $tld = strtolower($tld);
    $slug = sanitize_title($tld . '-domain');
    
    return apply_filters('domain_system_tld_slug', $slug, $tld);
}

/**
 * Format domain policy text
 * 
 * @param array $policy_data Policy configuration array
 * @return string Formatted policy text
 */
function format_domain_policy($policy_data) {
    $policy_text = '';
    
    // Length requirements
    if (!empty($policy_data['min_length']) || !empty($policy_data['max_length'])) {
        $min = intval($policy_data['min_length']) ?: 1;
        $max = intval($policy_data['max_length']) ?: 63;
        
        $policy_text .= sprintf(
            __('Domain length: %d to %d characters. ', 'domain-system'),
            $min,
            $max
        );
    }
    
    // Numbers policy
    if (!empty($policy_data['numbers_allowed'])) {
        $policy_text .= __('Numbers are allowed. ', 'domain-system');
    } else {
        $policy_text .= __('Numbers are not allowed. ', 'domain-system');
    }
    
    // Hyphens policy
    if (!empty($policy_data['hyphens_allowed'])) {
        switch ($policy_data['hyphens_allowed']) {
            case 'middle':
                $policy_text .= __('Hyphens allowed in middle positions only. ', 'domain-system');
                break;
            case 'anywhere':
                $policy_text .= __('Hyphens allowed anywhere except first and last position. ', 'domain-system');
                break;
            case 'none':
            default:
                $policy_text .= __('Hyphens are not allowed. ', 'domain-system');
        }
    } else {
        $policy_text .= __('Hyphens are not allowed. ', 'domain-system');
    }
    
    // IDN support
    if (!empty($policy_data['idn_allowed'])) {
        $policy_text .= __('Internationalized domain names (IDN) are supported.', 'domain-system');
    } else {
        $policy_text .= __('Internationalized domain names (IDN) are not supported.', 'domain-system');
    }
    
    return trim(apply_filters('domain_system_policy_text', $policy_text, $policy_data));
}

/**
 * Get domain search suggestions
 * 
 * @param string $query Search query
 * @param int $limit Maximum number of suggestions
 * @return array Array of suggestion objects
 */
function get_domain_search_suggestions($query, $limit = 5) {
    global $wpdb;
    
    $query = sanitize_text_field($query);
    $limit = intval($limit);
    
    if (empty($query) || $limit <= 0) {
        return [];
    }
    
    $suggestions = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value as tld, p.post_excerpt
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = %s
        AND p.post_status = 'publish'
        AND pm.meta_key = '_domain_tld'
        AND (p.post_title LIKE %s OR pm.meta_value LIKE %s OR p.post_excerpt LIKE %s)
        ORDER BY 
            CASE 
                WHEN pm.meta_value LIKE %s THEN 1
                WHEN p.post_title LIKE %s THEN 2
                ELSE 3
            END,
            p.post_title ASC
        LIMIT %d
    ", 
        'domain',
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%',
        '%' . $wpdb->esc_like($query) . '%',
        $wpdb->esc_like($query) . '%',
        $wpdb->esc_like($query) . '%',
        $limit
    ));
    
    return apply_filters('domain_system_search_suggestions', $suggestions, $query, $limit);
}

/**
 * Log domain activity
 * 
 * @param string $event_type Type of event
 * @param int $post_id Domain post ID
 * @param array $data Additional event data
 * @return bool Success status
 */
function log_domain_activity($event_type, $post_id, $data = []) {
    if (!get_option('domain_enable_analytics')) {
        return false;
    }
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'domain_analytics';
    
    // Check if table exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") !== $table_name) {
        return false;
    }
    
    $result = $wpdb->insert(
        $table_name,
        [
            'post_id' => intval($post_id),
            'event_type' => sanitize_text_field($event_type),
            'event_data' => wp_json_encode($data),
            'ip_address' => domain_get_user_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'created_at' => current_time('mysql')
        ],
        ['%d', '%s', '%s', '%s', '%s', '%s']
    );
    
    return $result !== false;
}

/**
 * Get user IP address
 * 
 * @return string IP address
 */
function domain_get_user_ip() {
    $ip_fields = [
        'HTTP_CF_CONNECTING_IP',     // Cloudflare
        'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
        'HTTP_X_FORWARDED',          // Proxy
        'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
        'HTTP_FORWARDED_FOR',        // Proxy
        'HTTP_FORWARDED',            // Proxy
        'REMOTE_ADDR'                // Standard
    ];
    
    foreach ($ip_fields as $field) {
        if (!empty($_SERVER[$field])) {
            $ip = $_SERVER[$field];
            
            // Handle comma-separated IPs
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            
            // Validate IP
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
}

/**
 * Get domain categories
 * 
 * @return array Array of category key => label pairs
 */
function get_domain_categories() {
    static $categories = null;
    
    if ($categories === null) {
        $categories = apply_filters('domain_system_categories', [
            'generic' => __('Generic', 'domain-system'),
            'business-professional' => __('Business & Professional', 'domain-system'),
            'e-commerce-retail' => __('E-commerce & Retail', 'domain-system'),
            'creative-arts' => __('Creative & Arts', 'domain-system'),
            'technology' => __('Technology', 'domain-system'),
            'education' => __('Education', 'domain-system'),
            'health-medical' => __('Health & Medical', 'domain-system'),
            'finance' => __('Finance', 'domain-system'),
            'real-estate' => __('Real Estate', 'domain-system'),
            'travel-tourism' => __('Travel & Tourism', 'domain-system'),
            'food-beverage' => __('Food & Beverage', 'domain-system'),
            'sports-recreation' => __('Sports & Recreation', 'domain-system'),
            'non-profit' => __('Non-profit', 'domain-system'),
            'geographic' => __('Geographic', 'domain-system'),
            'entertainment' => __('Entertainment', 'domain-system'),
            'news-media' => __('News & Media', 'domain-system'),
            'automotive' => __('Automotive', 'domain-system'),
            'fashion-beauty' => __('Fashion & Beauty', 'domain-system'),
            'gaming' => __('Gaming', 'domain-system'),
            'other' => __('Other', 'domain-system')
        ]);
    }
    
    return $categories;
}

/**
 * Get domain types
 * 
 * @return array Array of type key => label pairs
 */
function get_domain_types() {
    static $types = null;
    
    if ($types === null) {
        $types = apply_filters('domain_system_types', [
            'gtld' => __('Generic TLD (gTLD)', 'domain-system'),
            'cctld' => __('Country Code TLD (ccTLD)', 'domain-system'),
            'sponsored' => __('Sponsored TLD', 'domain-system'),
            'infrastructure' => __('Infrastructure TLD', 'domain-system'),
            'new-gtld' => __('New Generic TLD', 'domain-system'),
            'brand' => __('Brand TLD', 'domain-system'),
            'geographic' => __('Geographic TLD', 'domain-system')
        ]);
    }
    
    return $types;
}

/**
 * Format price for display
 * 
 * @param float $price Price value
 * @param string $currency Currency symbol
 * @return string Formatted price
 */
function format_domain_price($price, $currency = null) {
    if ($currency === null) {
        $currency = get_option('domain_currency_symbol', '$');
    }
    
    $price = floatval($price);
    
    if ($price <= 0) {
        return __('Contact for pricing', 'domain-system');
    }
    
    $formatted = $currency . number_format($price, 2);
    
    return apply_filters('domain_system_formatted_price', $formatted, $price, $currency);
}

/**
 * Get domain meta value
 * 
 * @param int $post_id Domain post ID
 * @param string $key Meta key (without _domain_ prefix)
 * @param mixed $default Default value if not found
 * @return mixed Meta value
 */
function get_domain_meta($post_id, $key, $default = '') {
    $meta_key = '_domain_' . $key;
    $value = get_post_meta($post_id, $meta_key, true);
    
    return !empty($value) ? $value : $default;
}

/**
 * Update domain meta value
 * 
 * @param int $post_id Domain post ID
 * @param string $key Meta key (without _domain_ prefix)
 * @param mixed $value Meta value
 * @return bool Success status
 */
function update_domain_meta($post_id, $key, $value) {
    $meta_key = '_domain_' . $key;
    return update_post_meta($post_id, $meta_key, $value);
}

/**
 * Validate domain name format
 * 
 * @param string $domain Domain name to validate
 * @return bool|string True if valid, error message if invalid
 */
function validate_domain_name($domain) {
    $domain = trim($domain);
    
    // Check length
    if (strlen($domain) > 253) {
        return __('Domain name too long (max 253 characters)', 'domain-system');
    }
    
    if (strlen($domain) < 1) {
        return __('Domain name cannot be empty', 'domain-system');
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
 * Validate TLD format
 * 
 * @param string $tld TLD to validate
 * @return bool|string True if valid, error message if invalid
 */
function validate_tld_format($tld) {
    $tld = ltrim($tld, '.');
    
    if (empty($tld)) {
        return __('TLD cannot be empty', 'domain-system');
    }
    
    if (strlen($tld) > 63) {
        return __('TLD too long (max 63 characters)', 'domain-system');
    }
    
    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $tld)) {
        return __('TLD contains invalid characters', 'domain-system');
    }
    
    if (substr($tld, 0, 1) === '-' || substr($tld, -1) === '-') {
        return __('TLD cannot start or end with hyphen', 'domain-system');
    }
    
    // Check for numeric-only TLD (not allowed)
    if (is_numeric($tld)) {
        return __('TLD cannot be numeric only', 'domain-system');
    }
    
    return true;
}

/**
 * Get domain statistics
 * 
 * @param int $post_id Domain post ID
 * @return array Statistics array
 */
function get_domain_statistics($post_id) {
    $stats = [
        'views' => intval(get_domain_meta($post_id, 'page_views', 0)),
        'searches' => intval(get_domain_meta($post_id, 'search_count', 0)),
        'clicks' => intval(get_domain_meta($post_id, 'click_count', 0)),
        'registrations' => intval(get_domain_meta($post_id, 'registration_count', 0))
    ];
    
    return apply_filters('domain_system_statistics', $stats, $post_id);
}

/**
 * Increment domain statistic
 * 
 * @param int $post_id Domain post ID
 * @param string $stat_type Type of statistic to increment
 * @param int $amount Amount to increment by
 * @return bool Success status
 */
function increment_domain_stat($post_id, $stat_type, $amount = 1) {
    $current = intval(get_domain_meta($post_id, $stat_type, 0));
    $new_value = $current + intval($amount);
    
    return update_domain_meta($post_id, $stat_type, $new_value);
}

/**
 * Get domain pricing data
 * 
 * @param int $post_id Domain post ID
 * @return array Pricing data array
 */
function get_domain_pricing($post_id) {
    $pricing = [
        'registration' => floatval(get_domain_meta($post_id, 'registration_price', 0)),
        'renewal' => floatval(get_domain_meta($post_id, 'renewal_price', 0)),
        'transfer' => floatval(get_domain_meta($post_id, 'transfer_price', 0)),
        'restoration' => floatval(get_domain_meta($post_id, 'restoration_price', 0))
    ];
    
    // Use registration price for renewal if not set
    if ($pricing['renewal'] <= 0) {
        $pricing['renewal'] = $pricing['registration'];
    }
    
    return apply_filters('domain_system_pricing', $pricing, $post_id);
}

/**
 * Calculate multi-year pricing
 * 
 * @param array $pricing Pricing data from get_domain_pricing()
 * @param int $years Number of years
 * @return array Calculation results
 */
function calculate_multiyear_pricing($pricing, $years = 3) {
    $years = max(1, min(10, intval($years)));
    $calculations = [
        'years' => [],
        'yearly_costs' => [],
        'total_costs' => [],
        'monthly_equivalent' => []
    ];
    
    $running_total = 0;
    
    for ($year = 1; $year <= $years; $year++) {
        $yearly_cost = ($year === 1) ? $pricing['registration'] : $pricing['renewal'];
        $running_total += $yearly_cost;
        
        $calculations['years'][] = $year;
        $calculations['yearly_costs'][] = $yearly_cost;
        $calculations['total_costs'][] = $running_total;
        $calculations['monthly_equivalent'][] = round($running_total / ($year * 12), 2);
    }
    
    return apply_filters('domain_system_multiyear_pricing', $calculations, $pricing, $years);
}

/**
 * Get domain FAQs
 * 
 * @param int $post_id Domain post ID
 * @return array FAQ data
 */
function get_domain_faqs($post_id) {
    $faqs = get_domain_meta($post_id, 'faqs', []);
    
    if (!is_array($faqs)) {
        $faqs = [];
    }
    
    return apply_filters('domain_system_faqs', $faqs, $post_id);
}

/**
 * Get domain policy data
 * 
 * @param int $post_id Domain post ID
 * @return array Policy data
 */
function get_domain_policy($post_id) {
    $policy = [
        'min_length' => intval(get_domain_meta($post_id, 'min_length', 2)),
        'max_length' => intval(get_domain_meta($post_id, 'max_length', 63)),
        'numbers_allowed' => get_domain_meta($post_id, 'numbers_allowed', false),
        'hyphens_allowed' => get_domain_meta($post_id, 'hyphens_allowed', 'middle'),
        'idn_allowed' => get_domain_meta($post_id, 'idn_allowed', false)
    ];
    
    return apply_filters('domain_system_policy', $policy, $post_id);
}

/**
 * Check if domain registration is allowed based on policy
 * 
 * @param string $domain_name Domain name to check
 * @param array $policy Policy data
 * @return bool|string True if allowed, error message if not
 */
function check_domain_policy($domain_name, $policy) {
    $domain_name = trim($domain_name);
    $length = strlen($domain_name);
    
    // Check length
    if ($length < $policy['min_length']) {
        return sprintf(__('Domain name too short (minimum %d characters)', 'domain-system'), $policy['min_length']);
    }
    
    if ($length > $policy['max_length']) {
        return sprintf(__('Domain name too long (maximum %d characters)', 'domain-system'), $policy['max_length']);
    }
    
    // Check numbers
    if (!$policy['numbers_allowed'] && preg_match('/\d/', $domain_name)) {
        return __('Numbers are not allowed in domain names', 'domain-system');
    }
    
    // Check hyphens
    if ($policy['hyphens_allowed'] === 'none' && strpos($domain_name, '-') !== false) {
        return __('Hyphens are not allowed in domain names', 'domain-system');
    }
    
    if ($policy['hyphens_allowed'] === 'middle') {
        if (substr($domain_name, 0, 1) === '-' || substr($domain_name, -1) === '-') {
            return __('Hyphens are not allowed at the beginning or end of domain names', 'domain-system');
        }
    }
    
    // Check IDN
    if (!$policy['idn_allowed'] && !preg_match('/^[a-zA-Z0-9\-]+$/', $domain_name)) {
        return __('International characters are not allowed in domain names', 'domain-system');
    }
    
    return true;
}

/**
 * Sanitize domain input
 * 
 * @param string $domain Domain to sanitize
 * @return string Sanitized domain
 */
function sanitize_domain_input($domain) {
    $domain = trim($domain);
    $domain = strtolower($domain);
    $domain = ltrim($domain, '.');
    
    // Remove invalid characters
    $domain = preg_replace('/[^a-z0-9\-]/', '', $domain);
    
    // Remove consecutive hyphens
    $domain = preg_replace('/\-+/', '-', $domain);
    
    // Remove leading/trailing hyphens
    $domain = trim($domain, '-');
    
    return $domain;
}

/**
 * Generate default content for TLD
 * 
 * @param string $tld TLD value
 * @param string $category Category key
 * @return array Generated content
 */
function generate_default_tld_content($tld, $category = '') {
    $tld_clean = ltrim($tld, '.');
    
    $content = [
        'title' => sprintf(__('%s Domain Registration', 'domain-system'), $tld),
        'hero_h1' => sprintf(__('Get Your Perfect %s Domain', 'domain-system'), $tld),
        'hero_subtitle' => sprintf(__('Register your %s domain today and establish your online presence with a memorable web address.', 'domain-system'), $tld),
        'overview' => sprintf(__('The %s domain extension is perfect for creating a distinctive online presence. Join thousands of websites using %s domains.', 'domain-system'), $tld, $tld),
        'benefits' => sprintf(__('Benefits of %s domains include enhanced brand recognition, improved SEO potential, increased trust from visitors, and better memorability.', 'domain-system'), $tld),
        'meta_title' => sprintf(__('%s Domain Registration - Register Your %s Domain', 'domain-system'), $tld, $tld),
        'meta_description' => sprintf(__('Register your %s domain today. Get the perfect %s domain name for your business or personal website.', 'domain-system'), $tld, $tld)
    ];
    
    // Customize based on category
    if (!empty($category) && function_exists('get_domain_categories')) {
        $categories = get_domain_categories();
        if (isset($categories[$category])) {
            $content = customize_content_for_category($content, $category, $tld_clean);
        }
    }
    
    return apply_filters('domain_system_default_content', $content, $tld, $category);
}

/**
 * Customize content based on category
 * 
 * @param array $content Base content array
 * @param string $category Category key
 * @param string $tld_clean Clean TLD string
 * @return array Customized content
 */
function customize_content_for_category($content, $category, $tld_clean) {
    $customizations = [
        'business-professional' => [
            'hero_h1' => sprintf(__('Professional %s Domain for Your Business', 'domain-system'), '.' . $tld_clean),
            'benefits' => sprintf(__('%s domains provide professional credibility, enhanced business communications, improved client trust, and networking advantages.', 'domain-system'), '.' . $tld_clean)
        ],
        'e-commerce-retail' => [
            'hero_h1' => sprintf(__('Perfect %s Domain for Your Online Store', 'domain-system'), '.' . $tld_clean),
            'benefits' => sprintf(__('%s domains are ideal for e-commerce: they build customer trust, improve conversion rates, enhance brand credibility, and provide a memorable shopping destination.', 'domain-system'), '.' . $tld_clean)
        ],
        'creative-arts' => [
            'hero_h1' => sprintf(__('Creative %s Domain for Artists & Designers', 'domain-system'), '.' . $tld_clean),
            'benefits' => sprintf(__('%s domains offer creative benefits: showcase artistic identity, attract target audience, build portfolio presence, and establish creative brand recognition.', 'domain-system'), '.' . $tld_clean)
        ],
        'technology' => [
            'hero_h1' => sprintf(__('Innovative %s Domain for Tech Companies', 'domain-system'), '.' . $tld_clean),
            'benefits' => sprintf(__('%s domains are perfect for technology: establish innovation leadership, attract tech-savvy customers, build developer community, and enhance technical credibility.', 'domain-system'), '.' . $tld_clean)
        ]
    ];
    
    if (isset($customizations[$category])) {
        $content = array_merge($content, $customizations[$category]);
    }
    
    return $content;
}

/**
 * Get default FAQs for TLD
 * 
 * @param string $tld TLD value
 * @param string $category Category key
 * @return array Default FAQ data
 */
function get_default_tld_faqs($tld, $category = '') {
    $tld_clean = ltrim($tld, '.');
    
    $faqs = [
        [
            'question' => sprintf(__('What is a %s domain?', 'domain-system'), $tld),
            'answer' => sprintf(__('A %s domain is a top-level domain extension that provides a unique and memorable web address for your website.', 'domain-system'), $tld)
        ],
        [
            'question' => sprintf(__('How much does a %s domain cost?', 'domain-system'), $tld),
            'answer' => __('The cost varies depending on the registrar and any current promotions. Check our pricing section above for current rates.', 'domain-system')
        ],
        [
            'question' => sprintf(__('Can I transfer my %s domain?', 'domain-system'), $tld),
            'answer' => sprintf(__('Yes, you can transfer your %s domain to another registrar after 60 days from the initial registration date.', 'domain-system'), $tld)
        ],
        [
            'question' => sprintf(__('How long can I register a %s domain for?', 'domain-system'), $tld),
            'answer' => sprintf(__('You can register a %s domain for 1-10 years, depending on your needs and budget.', 'domain-system'), $tld)
        ],
        [