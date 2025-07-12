<?php
/**
 * Plugin Name: Domain System
 * Plugin URI: https://yourwebsite.com/domain-system
 * Description: A comprehensive domain management system for WordPress with TLD information, pricing, and analytics.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: domain-system
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package DomainSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOMAIN_SYSTEM_VERSION', '1.0.0');
define('DOMAIN_SYSTEM_PLUGIN_FILE', __FILE__);
define('DOMAIN_SYSTEM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOMAIN_SYSTEM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DOMAIN_SYSTEM_INCLUDES_DIR', DOMAIN_SYSTEM_PLUGIN_DIR . 'includes/');
define('DOMAIN_SYSTEM_ASSETS_URL', DOMAIN_SYSTEM_PLUGIN_URL . 'assets/');

/**
 * Main Domain System Class
 */
class DomainSystem {
    
    /**
     * Plugin instance
     * 
     * @var DomainSystem
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     * 
     * @return DomainSystem
     */
    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        
        // Initialize components
        add_action('init', [$this, 'init_components']);
        
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Plugin action links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'action_links']);
    }
    
    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'domain-system',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Initialize plugin components
     */
    public function init_components() {
        // Load required files
        $this->load_includes();
        
        // Initialize components
        $this->init_post_type();
        $this->init_admin();
        $this->init_frontend();
        $this->init_ajax();
    }
    
    /**
     * Load required include files
     */
    private function load_includes() {
        $includes = [
            'functions.php',
            'class-domain-validation.php',
            'class-domain-post-type.php',
        ];
        
        // Load admin files only in admin
        if (is_admin()) {
            $includes[] = 'class-domain-admin-interface.php';
        }
        
        // Load AJAX handlers for both admin and frontend
        if (is_admin() || wp_doing_ajax()) {
            $includes[] = 'class-domain-ajax-handlers.php';
        }
        
        // Load frontend files only on frontend
        if (!is_admin()) {
            $includes[] = 'class-domain-frontend.php';
        }
        
        foreach ($includes as $file) {
            $file_path = DOMAIN_SYSTEM_INCLUDES_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    /**
     * Initialize post type
     */
    private function init_post_type() {
        if (class_exists('DomainPostType')) {
            new DomainPostType();
        }
    }
    
    /**
     * Initialize admin interface
     */
    private function init_admin() {
        if (is_admin() && class_exists('DomainAdminInterface')) {
            new DomainAdminInterface();
        }
    }
    
    /**
     * Initialize frontend
     */
    private function init_frontend() {
        if (!is_admin() && class_exists('DomainFrontend')) {
            new DomainFrontend();
        }
    }
    
    /**
     * Initialize AJAX handlers
     */
    private function init_ajax() {
        if ((is_admin() || wp_doing_ajax()) && class_exists('DomainAjaxHandlers')) {
            new DomainAjaxHandlers();
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Create upload directories
        $this->create_directories();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear scheduled events
        wp_clear_scheduled_hook('domain_system_cleanup');
    }
    
    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Domain analytics table
        $table_name = $wpdb->prefix . 'domain_analytics';
        
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Update database version
        update_option('domain_system_db_version', '1.0');
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $defaults = [
            'domain_currency_symbol' => '$',
            'domain_posts_per_page' => 10,
            'domain_enable_analytics' => '1',
            'domain_auto_generate_content' => '1',
            'domain_seo_optimization' => '1',
            'domain_show_pricing' => '1',
            'domain_show_faqs' => '1',
            'domain_archive_layout' => 'grid'
        ];
        
        foreach ($defaults as $option => $value) {
            if (get_option($option) === false) {
                add_option($option, $value);
            }
        }
    }
    
    /**
     * Create required directories
     */
    private function create_directories() {
        $upload_dir = wp_upload_dir();
        $domain_dir = $upload_dir['basedir'] . '/domain-system';
        
        if (!file_exists($domain_dir)) {
            wp_mkdir_p($domain_dir);
        }
        
        // Create subdirectories
        $subdirs = ['exports', 'imports', 'cache'];
        foreach ($subdirs as $subdir) {
            $dir_path = $domain_dir . '/' . $subdir;
            if (!file_exists($dir_path)) {
                wp_mkdir_p($dir_path);
            }
        }
        
        // Create .htaccess file for security
        $htaccess_content = "deny from all\n";
        file_put_contents($domain_dir . '/.htaccess', $htaccess_content);
    }
    
    /**
     * Add plugin action links
     */
    public function action_links($links) {
        $plugin_links = [
            '<a href="' . admin_url('edit.php?post_type=domain&page=domain-settings') . '">' . __('Settings', 'domain-system') . '</a>',
            '<a href="' . admin_url('edit.php?post_type=domain') . '">' . __('Domains', 'domain-system') . '</a>',
            '<a href="https://yourwebsite.com/docs" target="_blank">' . __('Documentation', 'domain-system') . '</a>'
        ];
        
        return array_merge($plugin_links, $links);
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return DOMAIN_SYSTEM_VERSION;
    }
    
    /**
     * Get plugin directory path
     */
    public function get_plugin_dir() {
        return DOMAIN_SYSTEM_PLUGIN_DIR;
    }
    
    /**
     * Get plugin URL
     */
    public function get_plugin_url() {
        return DOMAIN_SYSTEM_PLUGIN_URL;
    }
}

/**
 * Initialize the plugin
 */
function domain_system() {
    return DomainSystem::instance();
}

// Start the plugin
domain_system();

/**
 * Helper functions for easier access
 */

/**
 * Get domain system instance
 */
function get_domain_system() {
    return DomainSystem::instance();
}

/**
 * Check if domain system is active
 */
function is_domain_system_active() {
    return class_exists('DomainSystem');
}

/**
 * Get domain by TLD
 */
function get_domain_by_tld($tld) {
    $args = [
        'post_type' => 'domain',
        'meta_query' => [
            [
                'key' => '_domain_tld',
                'value' => $tld,
                'compare' => '='
            ]
        ],
        'posts_per_page' => 1
    ];
    
    $domains = get_posts($args);
    return !empty($domains) ? $domains[0] : null;
}

/**
 * Get all domain TLDs
 */
function get_all_domain_tlds() {
    static $tlds = null;
    
    if ($tlds === null) {
        global $wpdb;
        
        $tlds = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_domain_tld'
            AND p.post_type = 'domain'
            AND p.post_status = 'publish'
            ORDER BY meta_value ASC
        ");
    }
    
    return $tlds ?: [];
}

/**
 * Check if domain exists
 */
function domain_exists($tld) {
    return get_domain_by_tld($tld) !== null;
}

/**
 * Get domain registries
 */
function get_domain_registries() {
    return [
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
        'XYZ.COM LLC'
    ];
}

/**
 * Convert TLD to slug
 */
function tld_to_slug($tld) {
    $tld = ltrim($tld, '.');
    return sanitize_title($tld . '-domain');
}

/**
 * Format domain policy
 */
function format_domain_policy($policy_data) {
    $policy_text = '';
    
    if (!empty($policy_data['min_length']) || !empty($policy_data['max_length'])) {
        $policy_text .= sprintf(
            __('Domain length: %d to %d characters. ', 'domain-system'),
            $policy_data['min_length'] ?: 1,
            $policy_data['max_length'] ?: 63
        );
    }
    
    if (!empty($policy_data['numbers_allowed'])) {
        $policy_text .= __('Numbers are allowed. ', 'domain-system');
    } else {
        $policy_text .= __('Numbers are not allowed. ', 'domain-system');
    }
    
    if (!empty($policy_data['hyphens_allowed'])) {
        switch ($policy_data['hyphens_allowed']) {
            case 'middle':
                $policy_text .= __('Hyphens allowed in middle positions only. ', 'domain-system');
                break;
            case 'anywhere':
                $policy_text .= __('Hyphens allowed anywhere except first and last position. ', 'domain-system');
                break;
            default:
                $policy_text .= __('Hyphens are not allowed. ', 'domain-system');
        }
    }
    
    if (!empty($policy_data['idn_allowed'])) {
        $policy_text .= __('Internationalized domain names (IDN) are supported.', 'domain-system');
    }
    
    return trim($policy_text);
}

/**
 * Get domain search suggestions
 */
function get_domain_search_suggestions($query, $limit = 5) {
    global $wpdb;
    
    $suggestions = $wpdb->get_results($wpdb->prepare("
        SELECT p.ID, p.post_title, pm.meta_value as tld
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'domain'
        AND p.post_status = 'publish'
        AND pm.meta_key = '_domain_tld'
        AND (p.post_title LIKE %s OR pm.meta_value LIKE %s)
        ORDER BY p.post_title ASC
        LIMIT %d
    ", '%' . $wpdb->esc_like($query) . '%', '%' . $wpdb->esc_like($query) . '%', $limit));
    
    return $suggestions;
}

/**
 * Log domain activity
 */
function log_domain_activity($event_type, $post_id, $data = []) {
    if (!get_option('domain_enable_analytics')) {
        return;
    }
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'domain_analytics';
    
    $wpdb->insert(
        $table_name,
        [
            'post_id' => $post_id,
            'event_type' => $event_type,
            'event_data' => json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ],
        ['%d', '%s', '%s', '%s', '%s']
    );
}