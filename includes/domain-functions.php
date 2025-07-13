<?php
/**
 * Domain Helper Functions
 * 
 * Core utility functions for the Domain System plugin.
 * This file contains all the helper functions used throughout
 * the plugin for domain operations, data manipulation, and utilities.
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// DOMAIN DATA FUNCTIONS
// =============================================================================

/**
 * Get domain data with caching
 * 
 * @param int|null $post_id Domain post ID
 * @return array Domain data array
 */
if (!function_exists('get_domain_data')) {
    function get_domain_data($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID ?? 0;
        }
        
        if (!$post_id) {
            return [];
        }
        
        // Check cache first
        $cache_key = "domain_data_{$post_id}";
        $data = wp_cache_get($cache_key, 'domain_system');
        
        if ($data === false) {
            $data = [
                'id' => $post_id,
                'tld' => get_post_meta($post_id, '_domain_tld', true),
                'product_id' => get_post_meta($post_id, '_domain_product_id', true),
                'registration_price' => get_post_meta($post_id, '_domain_registration_price', true),
                'renewal_price' => get_post_meta($post_id, '_domain_renewal_price', true),
                'transfer_price' => get_post_meta($post_id, '_domain_transfer_price', true),
                'restoration_price' => get_post_meta($post_id, '_domain_restoration_price', true),
                'hero_h1' => get_post_meta($post_id, '_domain_hero_h1', true),
                'hero_subtitle' => get_post_meta($post_id, '_domain_hero_subtitle', true),
                'overview' => get_post_meta($post_id, '_domain_overview', true),
                'stats' => get_post_meta($post_id, '_domain_stats', true),
                'benefits' => get_post_meta($post_id, '_domain_benefits', true),
                'ideas' => get_post_meta($post_id, '_domain_ideas', true),
                'faq' => get_post_meta($post_id, '_domain_faq', true) ?: [],
                'policy' => [
                    'min_length' => get_post_meta($post_id, '_domain_min_length', true) ?: 2,
                    'max_length' => get_post_meta($post_id, '_domain_max_length', true) ?: 63,
                    'numbers_allowed' => get_post_meta($post_id, '_domain_numbers_allowed', true),
                    'hyphens_allowed' => get_post_meta($post_id, '_domain_hyphens_allowed', true) ?: 'middle',
                    'idn_allowed' => get_post_meta($post_id, '_domain_idn_allowed', true)
                ],
                'registry' => get_post_meta($post_id, '_domain_registry', true),
                'currency' => [
                    'code' => get_option('domain_currency', 'USD'),
                    'symbol' => get_option('domain_currency_symbol', '$'),
                    'name' => get_domain_currency_name()
                ],
                'last_updated' => get_post_modified_time('U', false, $post_id)
            ];
            
            // Add calculated fields
            $data['display_tld'] = $data['tld'] ? '.' . ltrim($data['tld'], '.') : '';
            $data['slug'] = tld_to_slug($data['tld']);
            $data['url'] = get_permalink($post_id);
            $data['is_complete'] = !empty($data['tld']) && !empty($data['registration_price']);
            
            // Cache for 1 hour if caching is enabled
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $data, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return apply_filters('domain_data', $data, $post_id);
    }
}

/**
 * Get domain by TLD
 * 
 * @param string $tld The TLD to search for
 * @return WP_Post|null Domain post object or null if not found
 */
if (!function_exists('get_domain_by_tld')) {
    function get_domain_by_tld($tld) {
        $tld = sanitize_domain_tld($tld);
        
        if (empty($tld)) {
            return null;
        }
        
        // Check cache first
        $cache_key = "domain_by_tld_{$tld}";
        $domain = wp_cache_get($cache_key, 'domain_system');
        
        if ($domain === false) {
            $query = new WP_Query([
                'post_type' => 'domain',
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'meta_query' => [
                    [
                        'key' => '_domain_tld',
                        'value' => $tld,
                        'compare' => '='
                    ]
                ],
                'fields' => 'all'
            ]);
            
            $domain = $query->have_posts() ? $query->posts[0] : null;
            
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $domain, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return $domain;
    }
}

/**
 * Get domain by slug (URL-friendly version)
 * 
 * @param string $slug URL slug
 * @return WP_Post|null Domain post object or null if not found
 */
if (!function_exists('get_domain_by_slug')) {
    function get_domain_by_slug($slug) {
        $tld = slug_to_tld($slug);
        return get_domain_by_tld($tld);
    }
}

/**
 * Check if domain exists
 * 
 * @param string $tld The TLD to check
 * @return bool True if domain exists, false otherwise
 */
if (!function_exists('domain_exists')) {
    function domain_exists($tld) {
        return get_domain_by_tld($tld) !== null;
    }
}

/**
 * Get domain URL by TLD
 * 
 * @param string $tld The TLD
 * @return string|null Domain URL or null if not found
 */
if (!function_exists('get_domain_url_by_tld')) {
    function get_domain_url_by_tld($tld) {
        $domain = get_domain_by_tld($tld);
        return $domain ? get_permalink($domain->ID) : null;
    }
}

// =============================================================================
// DOMAIN LISTING AND SEARCH FUNCTIONS
// =============================================================================

/**
 * Get all domain TLDs
 * 
 * @param bool $published_only Whether to get only published domains
 * @return array Array of TLD strings
 */
if (!function_exists('get_all_domain_tlds')) {
    function get_all_domain_tlds($published_only = true) {
        static $tlds = [];
        $cache_key = $published_only ? 'published' : 'all';
        
        if (!isset($tlds[$cache_key])) {
            global $wpdb;
            
            $cache_name = "all_domain_tlds_{$cache_key}";
            $results = wp_cache_get($cache_name, 'domain_system');
            
            if ($results === false) {
                $status_condition = $published_only ? "AND p.post_status = 'publish'" : "";
                
                $results = $wpdb->get_col($wpdb->prepare("
                    SELECT DISTINCT pm.meta_value 
                    FROM {$wpdb->postmeta} pm
                    INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                    WHERE pm.meta_key = %s 
                    AND p.post_type = %s
                    {$status_condition}
                    AND pm.meta_value != ''
                    ORDER BY pm.meta_value ASC
                ", '_domain_tld', 'domain'));
                
                $results = array_filter($results);
                
                if (get_option('domain_enable_cache', true)) {
                    wp_cache_set($cache_name, $results, 'domain_system', HOUR_IN_SECONDS);
                }
            }
            
            $tlds[$cache_key] = $results;
        }
        
        return $tlds[$cache_key];
    }
}

/**
 * Get domain alternatives
 * 
 * @param int $post_id Current domain post ID
 * @param mixed $limit_or_tld Number of alternatives to return OR TLD string for backward compatibility
 * @return array Array of alternative domains
 */
if (!function_exists('get_domain_alternatives')) {
    function get_domain_alternatives($post_id, $limit_or_tld = 3) {
        // Handle backward compatibility - if second parameter is string, it's a TLD
        if (is_string($limit_or_tld)) {
            $current_tld = $limit_or_tld;
            $limit = 3; // Default limit
        } else {
            $limit = intval($limit_or_tld);
            $current_tld = get_post_meta($post_id, '_domain_tld', true);
        }
        
        // Validate inputs
        if (!get_option('domain_show_alternatives', true) || !$post_id || $limit < 1) {
            return [];
        }
        
        // Clean the current TLD
        $current_tld = ltrim($current_tld, '.');
        
        $categories = wp_get_post_terms($post_id, 'domain_category', ['fields' => 'ids']);
        
        $args = [
            'post_type' => 'domain',
            'posts_per_page' => $limit + 5, // Get extra in case some are filtered out
            'post_status' => 'publish',
            'post__not_in' => [$post_id],
            'meta_query' => [
                [
                    'key' => '_domain_tld',
                    'value' => $current_tld,
                    'compare' => '!='
                ],
                [
                    'key' => '_domain_registration_price',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => '_domain_registration_price',
                    'value' => '',
                    'compare' => '!='
                ]
            ],
            'orderby' => 'rand'
        ];
        
        // Include same category if available
        if (!empty($categories) && !is_wp_error($categories)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'domain_category',
                    'field' => 'term_id',
                    'terms' => $categories[0]
                ]
            ];
        }
        
        $query = new WP_Query($args);
        $alternatives = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts() && count($alternatives) < $limit) {
                $query->the_post();
                $alt_tld = get_post_meta(get_the_ID(), '_domain_tld', true);
                $alt_price = get_post_meta(get_the_ID(), '_domain_registration_price', true);
                
                // Validate and clean the data
                if (!empty($alt_tld) && !empty($alt_price) && is_numeric($alt_price)) {
                    $alt_tld_clean = ltrim($alt_tld, '.');
                    
                    // Skip if it's the same as current TLD
                    if ($alt_tld_clean === $current_tld) {
                        continue;
                    }
                    
                    $alternatives[] = [
                        'id' => get_the_ID(),
                        'tld' => $alt_tld_clean,
                        'display_tld' => '.' . $alt_tld_clean,
                        'price' => floatval($alt_price),
                        'formatted_price' => format_domain_price($alt_price),
                        'url' => get_permalink(),
                        'title' => get_the_title(),
                        'excerpt' => get_the_excerpt()
                    ];
                }
            }
            wp_reset_postdata();
        }
        
        // If we still don't have enough alternatives, add some popular defaults
        if (count($alternatives) < $limit) {
            $default_alternatives = [
                'com' => ['name' => '.com', 'price' => 13.99],
                'net' => ['name' => '.net', 'price' => 14.99],
                'org' => ['name' => '.org', 'price' => 14.99],
                'shop' => ['name' => '.shop', 'price' => 9.99],
                'online' => ['name' => '.online', 'price' => 8.99],
                'store' => ['name' => '.store', 'price' => 12.99],
                'tech' => ['name' => '.tech', 'price' => 15.99],
                'info' => ['name' => '.info', 'price' => 11.99]
            ];
            
            foreach ($default_alternatives as $tld => $data) {
                if (count($alternatives) >= $limit) {
                    break;
                }
                
                // Skip if it's the current TLD or already in alternatives
                if ($tld === $current_tld) {
                    continue;
                }
                
                $already_exists = false;
                foreach ($alternatives as $existing) {
                    if ($existing['tld'] === $tld) {
                        $already_exists = true;
                        break;
                    }
                }
                
                if (!$already_exists) {
                    $alternatives[] = [
                        'id' => 0,
                        'tld' => $tld,
                        'display_tld' => $data['name'],
                        'price' => $data['price'],
                        'formatted_price' => format_domain_price($data['price']),
                        'url' => '#',
                        'title' => $data['name'] . ' Domain',
                        'excerpt' => 'Popular domain extension'
                    ];
                }
            }
        }
        
        return array_slice($alternatives, 0, $limit);
    }
}

/**
 * Get domain search suggestions
 * 
 * @param string $query Search query
 * @param int $limit Number of suggestions to return
 * @return array Array of domain suggestions
 */
if (!function_exists('get_domain_search_suggestions')) {
    function get_domain_search_suggestions($query, $limit = 5) {
        $query = sanitize_text_field($query);
        
        if (strlen($query) < 2) {
            return [];
        }
        
        $args = [
            'post_type' => 'domain',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            's' => $query,
            'meta_query' => [
                [
                    'key' => '_domain_tld',
                    'compare' => 'EXISTS'
                ]
            ]
        ];
        
        $search_query = new WP_Query($args);
        $suggestions = [];
        
        if ($search_query->have_posts()) {
            while ($search_query->have_posts()) {
                $search_query->the_post();
                $data = get_domain_data(get_the_ID());
                
                $suggestions[] = [
                    'id' => get_the_ID(),
                    'tld' => $data['tld'],
                    'display_tld' => $data['display_tld'],
                    'title' => get_the_title(),
                    'price' => $data['registration_price'],
                    'formatted_price' => format_domain_price($data['registration_price']),
                    'url' => get_permalink()
                ];
            }
            wp_reset_postdata();
        }
        
        return $suggestions;
    }
}

/**
 * Get popular domains
 * 
 * @param int $limit Number of domains to return
 * @return array Array of popular domains
 */
if (!function_exists('get_popular_domains')) {
    function get_popular_domains($limit = 10) {
        $cache_key = "popular_domains_{$limit}";
        $domains = wp_cache_get($cache_key, 'domain_system');
        
        if ($domains === false) {
            $args = [
                'post_type' => 'domain',
                'post_status' => 'publish',
                'posts_per_page' => $limit,
                'meta_query' => [
                    [
                        'key' => '_domain_registration_price',
                        'compare' => 'EXISTS'
                    ]
                ],
                'orderby' => 'menu_order date',
                'order' => 'ASC'
            ];
            
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
                        'display_tld' => $data['display_tld'],
                        'price' => $data['registration_price'],
                        'formatted_price' => format_domain_price($data['registration_price']),
                        'url' => get_permalink(),
                        'excerpt' => get_the_excerpt()
                    ];
                }
                wp_reset_postdata();
            }
            
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $domains, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return $domains;
    }
}

/**
 * Generate domain suggestions based on keyword
 * 
 * @param string $keyword Base keyword
 * @param int $limit Number of suggestions to generate
 * @return array Array of domain suggestions
 */
if (!function_exists('generate_domain_suggestions')) {
    function generate_domain_suggestions($keyword, $limit = 10) {
        $keyword = sanitize_text_field($keyword);
        $suggestions = [];
        
        if (empty($keyword)) {
            return $suggestions;
        }
        
        // Get available TLDs
        $tlds = get_all_domain_tlds();
        
        foreach ($tlds as $tld) {
            if (count($suggestions) >= $limit) {
                break;
            }
            
            $domain_post = get_domain_by_tld($tld);
            if ($domain_post) {
                $data = get_domain_data($domain_post->ID);
                
                $suggestions[] = [
                    'domain' => $keyword . '.' . ltrim($tld, '.'),
                    'keyword' => $keyword,
                    'tld' => $tld,
                    'display_tld' => '.' . ltrim($tld, '.'),
                    'price' => $data['registration_price'],
                    'formatted_price' => format_domain_price($data['registration_price']),
                    'available' => true, // This would need real availability checking
                    'url' => get_permalink($domain_post->ID),
                    'registry' => $data['registry']
                ];
            }
        }
        
        return $suggestions;
    }
}

// =============================================================================
// FORMATTING AND DISPLAY FUNCTIONS
// =============================================================================

/**
 * Format price with currency - Protected against redeclaration
 */
if (!function_exists('format_domain_price')) {
    function format_domain_price($price, $show_currency = true) {
        if (empty($price) || !is_numeric($price)) {
            return '';
        }
        
        $currency_symbol = get_option('domain_currency_symbol', '$');
        $formatted_price = number_format((float)$price, 2);
        
        return $show_currency ? $currency_symbol . $formatted_price : $formatted_price;
    }
}

/**
 * Format domain policy text - Protected against redeclaration
 */
if (!function_exists('format_domain_policy')) {
    function format_domain_policy($policy) {
        if (empty($policy) || !is_array($policy)) {
            return '';
        }
        
        $text = [];
        
        // Length requirements
        if (!empty($policy['min_length']) || !empty($policy['max_length'])) {
            $min = $policy['min_length'] ?: 1;
            $max = $policy['max_length'] ?: 63;
            $text[] = sprintf(__('Length: %d-%d characters', 'domain-system'), $min, $max);
        }
        
        // Character restrictions
        $allowed = [];
        $allowed[] = __('letters', 'domain-system');
        
        if (!empty($policy['numbers_allowed'])) {
            $allowed[] = __('numbers', 'domain-system');
        }
        
        if (!empty($policy['hyphens_allowed']) && $policy['hyphens_allowed'] !== 'none') {
            switch ($policy['hyphens_allowed']) {
                case 'middle':
                    $allowed[] = __('hyphens (middle only)', 'domain-system');
                    break;
                case 'all':
                    $allowed[] = __('hyphens', 'domain-system');
                    break;
            }
        }
        
        if (!empty($policy['idn_allowed'])) {
            $allowed[] = __('international characters', 'domain-system');
        }
        
        if (!empty($allowed)) {
            $text[] = __('Allowed:', 'domain-system') . ' ' . implode(', ', $allowed);
        }
        
        return implode('. ', $text);
    }
}

/**
 * Get domain currency name - Protected against redeclaration
 */
if (!function_exists('get_domain_currency_name')) {
    function get_domain_currency_name($code = null) {
        if (!$code) {
            $code = get_option('domain_currency', 'USD');
        }
        
        $currencies = [
            'USD' => __('US Dollar', 'domain-system'),
            'EUR' => __('Euro', 'domain-system'),
            'GBP' => __('British Pound', 'domain-system'),
            'CAD' => __('Canadian Dollar', 'domain-system'),
            'AUD' => __('Australian Dollar', 'domain-system'),
            'JPY' => __('Japanese Yen', 'domain-system')
        ];
        
        return $currencies[$code] ?? $code;
    }
}

// =============================================================================
// VALIDATION FUNCTIONS
// =============================================================================

/**
 * Validate domain name - Protected against redeclaration
 */
if (!function_exists('validate_domain_name')) {
    function validate_domain_name($domain, $tld_rules = []) {
        $errors = [];
        
        // Basic validation
        if (empty($domain)) {
            $errors[] = __('Domain name cannot be empty', 'domain-system');
            return $errors;
        }
        
        $domain = strtolower(trim($domain));
        $length = strlen($domain);
        $min_length = $tld_rules['min_length'] ?? 2;
        $max_length = $tld_rules['max_length'] ?? 63;
        
        // Length validation
        if ($length < $min_length) {
            $errors[] = sprintf(__('Domain must be at least %d characters', 'domain-system'), $min_length);
        }
        
        if ($length > $max_length) {
            $errors[] = sprintf(__('Domain cannot exceed %d characters', 'domain-system'), $max_length);
        }
        
        // Character validation
        if (!preg_match('/^[a-z0-9-]+$/i', $domain)) {
            if (!($tld_rules['numbers_allowed'] ?? true) && preg_match('/[0-9]/', $domain)) {
                $errors[] = __('Numbers are not allowed in this domain extension', 'domain-system');
            }
            
            if (preg_match('/[^a-z0-9-]/i', $domain)) {
                $errors[] = __('Domain contains invalid characters. Only letters, numbers, and hyphens are allowed.', 'domain-system');
            }
        }
        
        // Hyphen validation
        $hyphens_allowed = $tld_rules['hyphens_allowed'] ?? 'middle';
        if (strpos($domain, '-') !== false) {
            switch ($hyphens_allowed) {
                case 'none':
                    $errors[] = __('Hyphens are not allowed in this domain extension', 'domain-system');
                    break;
                case 'middle':
                    if (substr($domain, 0, 1) === '-' || substr($domain, -1) === '-') {
                        $errors[] = __('Hyphens cannot be at the beginning or end of the domain', 'domain-system');
                    }
                    break;
            }
            
            // Check for consecutive hyphens
            if (strpos($domain, '--') !== false) {
                $errors[] = __('Consecutive hyphens are not allowed', 'domain-system');
            }
        }
        
        // Reserved words check
        $reserved_words = [
            'www', 'ftp', 'mail', 'email', 'smtp', 'pop', 'imap',
            'admin', 'administrator', 'root', 'test', 'blog'
        ];
        
        if (in_array($domain, $reserved_words)) {
            $errors[] = __('This domain name is reserved and cannot be registered', 'domain-system');
        }
        
        return apply_filters('domain_validation_errors', $errors, $domain, $tld_rules);
    }
}

/**
 * Sanitize domain TLD - Protected against redeclaration
 */
if (!function_exists('sanitize_domain_tld')) {
    function sanitize_domain_tld($tld) {
        if (empty($tld)) {
            return '';
        }
        
        $tld = sanitize_text_field($tld);
        $tld = ltrim($tld, '.');
        $tld = strtolower($tld);
        $tld = preg_replace('/[^a-z0-9.-]/', '', $tld);
        
        return $tld;
    }
}

// =============================================================================
// URL AND SLUG FUNCTIONS
// =============================================================================

/**
 * Convert TLD to URL slug - Protected against redeclaration
 */
if (!function_exists('tld_to_slug')) {
    function tld_to_slug($tld) {
        if (empty($tld)) {
            return '';
        }
        
        $tld = sanitize_domain_tld($tld);
        return str_replace('.', '-', $tld);
    }
}

/**
 * Convert URL slug back to TLD - Protected against redeclaration
 */
if (!function_exists('slug_to_tld')) {
    function slug_to_tld($slug) {
        if (empty($slug)) {
            return '';
        }
        
        return str_replace('-', '.', sanitize_text_field($slug));
    }
}

// =============================================================================
// STATISTICS AND ANALYTICS FUNCTIONS
// =============================================================================

/**
 * Get domain statistics
 * 
 * @return array Domain statistics
 */
if (!function_exists('get_domain_statistics')) {
    function get_domain_statistics() {
        $cache_key = 'domain_statistics';
        $stats = wp_cache_get($cache_key, 'domain_system');
        
        if ($stats === false) {
            $domain_count = wp_count_posts('domain');
            $category_count = wp_count_terms(['taxonomy' => 'domain_category']);
            
            // Get price statistics
            global $wpdb;
            $price_stats = $wpdb->get_row("
                SELECT 
                    MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                    MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price,
                    AVG(CAST(meta_value AS DECIMAL(10,2))) as avg_price,
                    COUNT(*) as price_count
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_domain_registration_price' 
                AND p.post_status = 'publish'
                AND p.post_type = 'domain'
                AND pm.meta_value != ''
                AND pm.meta_value > 0
            ");
            
            $stats = [
                'total_domains' => intval($domain_count->publish + $domain_count->draft),
                'published_domains' => intval($domain_count->publish),
                'draft_domains' => intval($domain_count->draft),
                'private_domains' => intval($domain_count->private ?? 0),
                'total_categories' => intval($category_count),
                'min_price' => floatval($price_stats->min_price ?? 0),
                'max_price' => floatval($price_stats->max_price ?? 0),
                'avg_price' => round(floatval($price_stats->avg_price ?? 0), 2),
                'domains_with_prices' => intval($price_stats->price_count ?? 0),
                'last_updated' => current_time('timestamp')
            ];
            
            // Add percentage calculations
            if ($stats['total_domains'] > 0) {
                $stats['completion_rate'] = round(($stats['domains_with_prices'] / $stats['total_domains']) * 100, 1);
                $stats['publish_rate'] = round(($stats['published_domains'] / $stats['total_domains']) * 100, 1);
            } else {
                $stats['completion_rate'] = 0;
                $stats['publish_rate'] = 0;
            }
            
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $stats, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return $stats;
    }
}

// =============================================================================
// CACHE MANAGEMENT FUNCTIONS
// =============================================================================

/**
 * Clear domain cache
 * 
 * @param int|null $post_id Specific post ID to clear cache for
 */
if (!function_exists('clear_domain_cache')) {
    function clear_domain_cache($post_id = null) {
        if ($post_id) {
            // Clear specific domain cache
            wp_cache_delete("domain_data_{$post_id}", 'domain_system');
            
            // Also clear TLD cache if we have the TLD
            $tld = get_post_meta($post_id, '_domain_tld', true);
            if ($tld) {
                wp_cache_delete("domain_by_tld_{$tld}", 'domain_system');
                wp_cache_delete("domain_by_slug_" . tld_to_slug($tld), 'domain_system');
            }
        } else {
            // Clear all domain-related cache
            wp_cache_flush();
        }
        
        // Clear other cached data
        wp_cache_delete('all_domain_tlds_published', 'domain_system');
        wp_cache_delete('all_domain_tlds_all', 'domain_system');
        wp_cache_delete('domain_statistics', 'domain_system');
        
        // Clear popular domains cache
        for ($i = 5; $i <= 20; $i += 5) {
            wp_cache_delete("popular_domains_{$i}", 'domain_system');
        }
        
        do_action('domain_cache_cleared', $post_id);
    }
}

// =============================================================================
// PERMISSION AND CAPABILITY FUNCTIONS
// =============================================================================

/**
 * Check if user can manage domains
 * 
 * @param int|null $user_id User ID to check
 * @return bool True if user can manage domains
 */
if (!function_exists('can_manage_domains')) {
    function can_manage_domains($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'edit_posts') || user_can($user_id, 'manage_options');
    }
}

/**
 * Check if user can edit specific domain
 * 
 * @param int $post_id Domain post ID
 * @param int|null $user_id User ID to check
 * @return bool True if user can edit domain
 */
if (!function_exists('can_edit_domain')) {
    function can_edit_domain($post_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'edit_post', $post_id);
    }
}

// =============================================================================
// LOGGING AND ACTIVITY FUNCTIONS
// =============================================================================

/**
 * Log domain activity
 * 
 * @param string $action Action performed
 * @param int $domain_id Domain post ID
 * @param array $details Additional details
 */
if (!function_exists('log_domain_activity')) {
    function log_domain_activity($action, $domain_id, $details = []) {
        if (!get_option('domain_enable_logging', false)) {
            return;
        }
        
        $log_entry = [
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'user_name' => wp_get_current_user()->display_name,
            'action' => sanitize_text_field($action),
            'domain_id' => intval($domain_id),
            'domain_tld' => get_post_meta($domain_id, '_domain_tld', true),
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        $logs = get_option('domain_activity_logs', []);
        $logs[] = $log_entry;
        
        // Keep only last 1000 entries
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        update_option('domain_activity_logs', $logs);
        
        do_action('domain_activity_logged', $log_entry);
    }
}

// =============================================================================
// SYSTEM CONFIGURATION FUNCTIONS
// =============================================================================

/**
 * Check if domain system is properly configured
 * 
 * @return array Configuration status with details
 */
if (!function_exists('is_domain_system_configured')) {
    function is_domain_system_configured() {
        $status = [
            'configured' => true,
            'issues' => [],
            'warnings' => []
        ];
        
        // Check required pages
        $required_pages = ['domain-search'];
        foreach ($required_pages as $page_slug) {
            if (!get_page_by_path($page_slug)) {
                $status['issues'][] = sprintf(__('Missing required page: %s', 'domain-system'), $page_slug);
                $status['configured'] = false;
            }
        }
        
        // Check if any domains exist
        $domain_count = wp_count_posts('domain');
        if ($domain_count->publish + $domain_count->draft == 0) {
            $status['warnings'][] = __('No domains have been created yet', 'domain-system');
        }
        
        // Check if categories exist
        $category_count = wp_count_terms(['taxonomy' => 'domain_category']);
        if ($category_count == 0) {
            $status['warnings'][] = __('No domain categories have been created', 'domain-system');
        }
        
        // Check currency settings
        $currency = get_option('domain_currency');
        $currency_symbol = get_option('domain_currency_symbol');
        if (empty($currency) || empty($currency_symbol)) {
            $status['warnings'][] = __('Currency settings are not configured', 'domain-system');
        }
        
       // Check rewrite rules
$rules = get_option('rewrite_rules');
if (!isset($rules['^domains/([^/]+)/?$'])) {
    $status['issues'][] = __('URL rewrite rules are not properly configured', 'domain-system');
    $status['configured'] = false;
}

        
        return $status;
    }
}

/**
 * Get domain system requirements
 * 
 * @return array System requirements status
 */
if (!function_exists('get_domain_system_requirements')) {
    function get_domain_system_requirements() {
        return [
            'php_version' => [
                'required' => '7.4',
                'current' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'pass' : 'fail'
            ],
            'wordpress_version' => [
                'required' => '5.0',
                'current' => get_bloginfo('version'),
                'status' => version_compare(get_bloginfo('version'), '5.0', '>=') ? 'pass' : 'fail'
            ],
            'memory_limit' => [
                'required' => '128M',
                'current' => ini_get('memory_limit'),
                'status' => wp_convert_hr_to_bytes(ini_get('memory_limit')) >= wp_convert_hr_to_bytes('128M') ? 'pass' : 'warning'
            ],
            'max_execution_time' => [
                'required' => '30',
                'current' => ini_get('max_execution_time'),
                'status' => intval(ini_get('max_execution_time')) >= 30 ? 'pass' : 'warning'
            ]
        ];
    }
}

// =============================================================================
// IMPORT/EXPORT FUNCTIONS
// =============================================================================

/**
 * Get domain export data
 * 
 * @param array $post_ids Array of post IDs to export (empty for all)
 * @return array Export data array
 */
if (!function_exists('get_domain_export_data')) {
    function get_domain_export_data($post_ids = []) {
        if (empty($post_ids)) {
            // Get all domains
            $domains = get_posts([
                'post_type' => 'domain',
                'post_status' => 'any',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            $post_ids = $domains;
        }
        
        $export_data = [];
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post || $post->post_type !== 'domain') {
                continue;
            }
            
            $data = get_domain_data($post_id);
            $categories = wp_get_post_terms($post_id, 'domain_category', ['fields' => 'names']);
            
            $export_data[] = [
                'id' => $post_id,
                'title' => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'status' => $post->post_status,
                'slug' => $post->post_name,
                'tld' => $data['tld'],
                'product_id' => $data['product_id'],
                'registration_price' => $data['registration_price'],
                'renewal_price' => $data['renewal_price'],
                'transfer_price' => $data['transfer_price'],
                'restoration_price' => $data['restoration_price'],
                'registry' => $data['registry'],
                'hero_h1' => $data['hero_h1'],
                'hero_subtitle' => $data['hero_subtitle'],
                'overview' => $data['overview'],
                'stats' => $data['stats'],
                'benefits' => $data['benefits'],
                'ideas' => $data['ideas'],
                'faq' => json_encode($data['faq']),
                'policy' => json_encode($data['policy']),
                'categories' => implode(';', $categories),
                'created' => $post->post_date,
                'modified' => $post->post_modified,
                'author' => get_userdata($post->post_author)->display_name ?? ''
            ];
        }
        
        return $export_data;
    }
}

/**
 * Prepare domain data for CSV export
 * 
 * @param array $export_data Export data from get_domain_export_data()
 * @return array CSV-ready data with headers
 */
if (!function_exists('prepare_domain_csv_export')) {
    function prepare_domain_csv_export($export_data) {
        $csv_data = [];
        
        // CSV Headers
        $headers = [
            'ID',
            'Title',
            'TLD',
            'Registration Price',
            'Renewal Price',
            'Transfer Price',
            'Restoration Price',
            'Registry',
            'Product ID',
            'Status',
            'Categories',
            'Hero Title',
            'Hero Subtitle',
            'Overview',
            'Benefits',
            'Ideas',
            'Created Date',
            'Author'
        ];
        
        $csv_data[] = $headers;
        
        // Data rows
        foreach ($export_data as $domain) {
            $csv_data[] = [
                $domain['id'],
                $domain['title'],
                $domain['tld'],
                $domain['registration_price'],
                $domain['renewal_price'],
                $domain['transfer_price'],
                $domain['restoration_price'],
                $domain['registry'],
                $domain['product_id'],
                $domain['status'],
                $domain['categories'],
                $domain['hero_h1'],
                $domain['hero_subtitle'],
                wp_strip_all_tags($domain['overview']),
                wp_strip_all_tags($domain['benefits']),
                wp_strip_all_tags($domain['ideas']),
                $domain['created'],
                $domain['author']
            ];
        }
        
        return $csv_data;
    }
}

/**
 * Validate import data
 * 
 * @param array $import_data Raw import data
 * @return array Validation results
 */
if (!function_exists('validate_domain_import_data')) {
    function validate_domain_import_data($import_data) {
        $results = [
            'valid' => [],
            'invalid' => [],
            'duplicates' => [],
            'warnings' => []
        ];
        
        $existing_tlds = get_all_domain_tlds(false); // Get all TLDs including drafts
        
        foreach ($import_data as $index => $domain_data) {
            $errors = [];
            $warnings = [];
            
            // Check required fields
            if (empty($domain_data['tld'])) {
                $errors[] = __('TLD is required', 'domain-system');
            }
            
            if (empty($domain_data['title']) && !empty($domain_data['tld'])) {
                $domain_data['title'] = $domain_data['tld'] . ' ' . __('Domain', 'domain-system');
                $warnings[] = __('Title was auto-generated', 'domain-system');
            }
            
            // Check for duplicates
            if (!empty($domain_data['tld']) && in_array($domain_data['tld'], $existing_tlds)) {
                $errors[] = __('TLD already exists', 'domain-system');
                $results['duplicates'][] = $domain_data['tld'];
            }
            
            // Validate prices
            if (!empty($domain_data['registration_price']) && !is_numeric($domain_data['registration_price'])) {
                $errors[] = __('Invalid registration price', 'domain-system');
            }
            
            if (!empty($domain_data['renewal_price']) && !is_numeric($domain_data['renewal_price'])) {
                $errors[] = __('Invalid renewal price', 'domain-system');
            }
            
            // Validate TLD format
            if (!empty($domain_data['tld'])) {
                $sanitized_tld = sanitize_domain_tld($domain_data['tld']);
                if ($sanitized_tld !== $domain_data['tld']) {
                    $warnings[] = __('TLD was sanitized', 'domain-system');
                    $domain_data['tld'] = $sanitized_tld;
                }
            }
            
            $domain_data['row_index'] = $index;
            $domain_data['errors'] = $errors;
            $domain_data['warnings'] = $warnings;
            
            if (empty($errors)) {
                $results['valid'][] = $domain_data;
            } else {
                $results['invalid'][] = $domain_data;
            }
            
            if (!empty($warnings)) {
                $results['warnings'][] = $domain_data;
            }
        }
        
        return $results;
    }
}

// =============================================================================
// UTILITY AND MISCELLANEOUS FUNCTIONS
// =============================================================================

/**
 * Get domain by product ID
 * 
 * @param string $product_id External product ID
 * @return WP_Post|null Domain post or null if not found
 */
if (!function_exists('get_domain_by_product_id')) {
    function get_domain_by_product_id($product_id) {
        if (empty($product_id)) {
            return null;
        }
        
        $query = new WP_Query([
            'post_type' => 'domain',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_domain_product_id',
                    'value' => sanitize_text_field($product_id),
                    'compare' => '='
                ]
            ]
        ]);
        
        return $query->have_posts() ? $query->posts[0] : null;
    }
}

/**
 * Get domains by category
 * 
 * @param string $category_slug Category slug
 * @param int $limit Number of domains to return
 * @return array Array of domain data
 */
if (!function_exists('get_domains_by_category')) {
    function get_domains_by_category($category_slug, $limit = -1) {
        $args = [
            'post_type' => 'domain',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'tax_query' => [
                [
                    'taxonomy' => 'domain_category',
                    'field' => 'slug',
                    'terms' => $category_slug
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        $domains = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $domains[] = get_domain_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return $domains;
    }
}

/**
 * Get random domains
 * 
 * @param int $limit Number of domains to return
 * @return array Array of domain data
 */
if (!function_exists('get_random_domains')) {
    function get_random_domains($limit = 5) {
        $args = [
            'post_type' => 'domain',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'rand',
            'meta_query' => [
                [
                    'key' => '_domain_tld',
                    'compare' => 'EXISTS'
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        $domains = [];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $domains[] = get_domain_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return $domains;
    }
}

/**
 * Search domains with advanced filters
 * 
 * @param array $args Search arguments
 * @return array Search results
 */
if (!function_exists('search_domains')) {
    function search_domains($args = []) {
        $defaults = [
            'query' => '',
            'category' => '',
            'min_price' => '',
            'max_price' => '',
            'registry' => '',
            'limit' => 10,
            'orderby' => 'title',
            'order' => 'ASC'
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        $query_args = [
            'post_type' => 'domain',
            'post_status' => 'publish',
            'posts_per_page' => intval($args['limit']),
            'orderby' => $args['orderby'],
            'order' => $args['order']
        ];
        
        // Text search
        if (!empty($args['query'])) {
            $query_args['s'] = sanitize_text_field($args['query']);
        }
        
        // Category filter
        if (!empty($args['category'])) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'domain_category',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($args['category'])
                ]
            ];
        }
        
        // Price filters
        $meta_query = [];
        
        if (!empty($args['min_price']) || !empty($args['max_price'])) {
            $price_query = [
                'key' => '_domain_registration_price',
                'type' => 'DECIMAL(10,2)'
            ];
            
            if (!empty($args['min_price']) && !empty($args['max_price'])) {
                $price_query['value'] = [floatval($args['min_price']), floatval($args['max_price'])];
                $price_query['compare'] = 'BETWEEN';
            } elseif (!empty($args['min_price'])) {
                $price_query['value'] = floatval($args['min_price']);
                $price_query['compare'] = '>=';
            } elseif (!empty($args['max_price'])) {
                $price_query['value'] = floatval($args['max_price']);
                $price_query['compare'] = '<=';
            }
            
            $meta_query[] = $price_query;
        }
        
        // Registry filter
        if (!empty($args['registry'])) {
            $meta_query[] = [
                'key' => '_domain_registry',
                'value' => sanitize_text_field($args['registry']),
                'compare' => 'LIKE'
            ];
        }
        
        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }
        
        $query = new WP_Query($query_args);
        $results = [
            'domains' => [],
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages
        ];
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $results['domains'][] = get_domain_data(get_the_ID());
            }
            wp_reset_postdata();
        }
        
        return $results;
    }
}

/**
 * Get domain price range
 * 
 * @return array Min and max prices
 */
if (!function_exists('get_domain_price_range')) {
    function get_domain_price_range() {
        global $wpdb;
        
        $cache_key = 'domain_price_range';
        $range = wp_cache_get($cache_key, 'domain_system');
        
        if ($range === false) {
            $result = $wpdb->get_row("
                SELECT 
                    MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                    MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_domain_registration_price' 
                AND p.post_status = 'publish'
                AND p.post_type = 'domain'
                AND pm.meta_value != ''
                AND pm.meta_value > 0
            ");
            
            $range = [
                'min' => floatval($result->min_price ?? 0),
                'max' => floatval($result->max_price ?? 0)
            ];
            
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $range, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return $range;
    }
}

/**
 * Get domain registries list
 * 
 * @return array List of unique registries
 */
if (!function_exists('get_domain_registries')) {
    function get_domain_registries() {
        global $wpdb;
        
        $cache_key = 'domain_registries';
        $registries = wp_cache_get($cache_key, 'domain_system');
        
        if ($registries === false) {
            $results = $wpdb->get_col("
                SELECT DISTINCT meta_value
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_domain_registry' 
                AND p.post_status = 'publish'
                AND p.post_type = 'domain'
                AND pm.meta_value != ''
                ORDER BY meta_value ASC
            ");
            
            $registries = array_filter($results);
            
            if (get_option('domain_enable_cache', true)) {
                wp_cache_set($cache_key, $registries, 'domain_system', HOUR_IN_SECONDS);
            }
        }
        
        return $registries;
    }
}

// =============================================================================
// HOOKS AND FILTERS SETUP
// =============================================================================

/**
 * Setup domain function hooks
 */
if (!function_exists('setup_domain_function_hooks')) {
    function setup_domain_function_hooks() {
        // Clear cache when domain is saved
        add_action('save_post_domain', function($post_id) {
            clear_domain_cache($post_id);
            log_domain_activity('updated', $post_id);
        });
        
        // Clear cache when domain is deleted
        add_action('before_delete_post', function($post_id) {
            $post = get_post($post_id);
            if ($post && $post->post_type === 'domain') {
                clear_domain_cache($post_id);
                log_domain_activity('deleted', $post_id);
            }
        });
        
        // Clear relevant caches when terms are updated
        add_action('created_term', function($term_id, $tt_id, $taxonomy) {
            if ($taxonomy === 'domain_category') {
                clear_domain_cache();
            }
        }, 10, 3);
        
        add_action('edited_term', function($term_id, $tt_id, $taxonomy) {
            if ($taxonomy === 'domain_category') {
                clear_domain_cache();
            }
        }, 10, 3);
    }
}
if (is_admin() && defined('DOMAIN_SYSTEM_INCLUDES_DIR')) {
    require_once DOMAIN_SYSTEM_INCLUDES_DIR . 'admin/class-deprecation-scanner.php';
    new DomainDeprecationScanner();
}
// Initialize hooks
add_action('init', 'setup_domain_function_hooks', 20);