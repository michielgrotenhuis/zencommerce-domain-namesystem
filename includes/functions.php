<?php
/**
 * Domain System Helper Functions - Clean Version
 * 
 * Contains utility functions used throughout the Domain System plugin
 * This version avoids function conflicts with the main plugin file
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Only declare functions if they don't already exist

/**
 * Get domain registries
 * 
 * @return array Array of registry names
 */
if (!function_exists('get_domain_registries')) {
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
}

/**
 * Convert TLD to slug
 * 
 * @param string $tld The TLD to convert
 * @return string Sanitized slug
 */
if (!function_exists('tld_to_slug')) {
    function tld_to_slug($tld) {
        $tld = ltrim($tld, '.');
        $tld = strtolower($tld);
        $slug = sanitize_title($tld . '-domain');
        
        return apply_filters('domain_system_tld_slug', $slug, $tld);
    }
}

/**
 * Format domain policy text
 * 
 * @param array $policy_data Policy configuration array
 * @return string Formatted policy text
 */
if (!function_exists('format_domain_policy')) {
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
}

/**
 * Get user IP address
 * 
 * @return string IP address
 */
if (!function_exists('domain_get_user_ip')) {
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
}

/**
 * Format price for display
 * 
 * @param float $price Price value
 * @param string $currency Currency symbol
 * @return string Formatted price
 */
if (!function_exists('format_domain_price')) {
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
}

/**
 * Get domain meta value
 * 
 * @param int $post_id Domain post ID
 * @param string $key Meta key (without _domain_ prefix)
 * @param mixed $default Default value if not found
 * @return mixed Meta value
 */
if (!function_exists('get_domain_meta')) {
    function get_domain_meta($post_id, $key, $default = '') {
        $meta_key = '_domain_' . $key;
        $value = get_post_meta($post_id, $meta_key, true);
        
        return !empty($value) ? $value : $default;
    }
}

/**
 * Update domain meta value
 * 
 * @param int $post_id Domain post ID
 * @param string $key Meta key (without _domain_ prefix)
 * @param mixed $value Meta value
 * @return bool Success status
 */
if (!function_exists('update_domain_meta')) {
    function update_domain_meta($post_id, $key, $value) {
        $meta_key = '_domain_' . $key;
        return update_post_meta($post_id, $meta_key, $value);
    }
}

/**
 * Validate domain name format
 * 
 * @param string $domain Domain name to validate
 * @return bool|string True if valid, error message if invalid
 */
if (!function_exists('validate_domain_name')) {
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
}

/**
 * Validate TLD format
 * 
 * @param string $tld TLD to validate
 * @return bool|string True if valid, error message if invalid
 */
if (!function_exists('validate_tld_format')) {
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
}

/**
 * Get domain statistics
 * 
 * @param int $post_id Domain post ID
 * @return array Statistics array
 */
if (!function_exists('get_domain_statistics')) {
    function get_domain_statistics($post_id) {
        $stats = [
            'views' => intval(get_domain_meta($post_id, 'page_views', 0)),
            'searches' => intval(get_domain_meta($post_id, 'search_count', 0)),
            'clicks' => intval(get_domain_meta($post_id, 'click_count', 0)),
            'registrations' => intval(get_domain_meta($post_id, 'registration_count', 0))
        ];
        
        return apply_filters('domain_system_statistics', $stats, $post_id);
    }
}

/**
 * Increment domain statistic
 * 
 * @param int $post_id Domain post ID
 * @param string $stat_type Type of statistic to increment
 * @param int $amount Amount to increment by
 * @return bool Success status
 */
if (!function_exists('increment_domain_stat')) {
    function increment_domain_stat($post_id, $stat_type, $amount = 1) {
        $current = intval(get_domain_meta($post_id, $stat_type, 0));
        $new_value = $current + intval($amount);
        
        return update_domain_meta($post_id, $stat_type, $new_value);
    }
}

/**
 * Sanitize domain input
 * 
 * @param string $domain Domain to sanitize
 * @return string Sanitized domain
 */
if (!function_exists('sanitize_domain_input')) {
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
}

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
        wp_cache_delete('domain_price_range', 'domain_system');
        wp_cache_delete('domain_registries_list', 'domain_system');
        
        // Clear popular domains cache
        for ($i = 5; $i <= 20; $i += 5) {
            wp_cache_delete("popular_domains_{$i}", 'domain_system');
        }
        
        do_action('domain_cache_cleared', $post_id);
    }
}

/**
 * Get domain currency name
 * 
 * @param string $code Currency code
 * @return string Currency name
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

/**
 * Sanitize domain TLD
 * 
 * @param string $tld TLD to sanitize
 * @return string Sanitized TLD
 */
if (!function_exists('sanitize_domain_tld')) {
    function sanitize_domain_tld($tld) {
        if (empty($tld)) {
            return '';
        }
        
        $tld = sanitize_text_field($tld);
        $tld = strtolower($tld);
        
        // Ensure it starts with a dot
        if (substr($tld, 0, 1) !== '.') {
            $tld = '.' . $tld;
        }
        
        // Remove invalid characters (keep dots, letters, numbers, hyphens)
        $tld = preg_replace('/[^a-z0-9.-]/', '', $tld);
        
        // Remove consecutive dots
        $tld = preg_replace('/\.+/', '.', $tld);
        
        return $tld;
    }
}

/**
 * Setup domain function hooks
 */
if (!function_exists('setup_domain_function_hooks')) {
    function setup_domain_function_hooks() {
        // Clear cache when domain is saved
        add_action('save_post_domain', function($post_id) {
            clear_domain_cache($post_id);
            if (function_exists('log_domain_activity')) {
                log_domain_activity('updated', $post_id);
            }
        });
        
        // Clear cache when domain is deleted
        add_action('before_delete_post', function($post_id) {
            $post = get_post($post_id);
            if ($post && $post->post_type === 'domain') {
                clear_domain_cache($post_id);
                if (function_exists('log_domain_activity')) {
                    log_domain_activity('deleted', $post_id);
                }
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
    
    // Initialize hooks
    add_action('init', 'setup_domain_function_hooks', 20);
}