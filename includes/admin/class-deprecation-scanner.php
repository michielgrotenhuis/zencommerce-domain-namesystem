<?php
/**
 * Domain System Deprecation Scanner
 * 
 * Scans plugin files for deprecated WordPress APIs
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class DomainDeprecationScanner {
    
    public function __construct() {
        // Only run in admin for administrators
        if (is_admin() && current_user_can('manage_options')) {
            add_action('admin_menu', [$this, 'add_scanner_page']);
        }
    }
    
    /**
     * Add scanner page to admin menu
     */
    public function add_scanner_page() {
        add_submenu_page(
            'edit.php?post_type=domain',
            __('Deprecation Scanner', 'domain-system'),
            __('Deprecation Scanner', 'domain-system'),
            'manage_options',
            'domain-deprecation-scanner',
            [$this, 'render_scanner_page']
        );
    }
    
    /**
     * Render scanner page
     */
    public function render_scanner_page() {
        if (isset($_POST['scan_files']) && wp_verify_nonce($_POST['scan_nonce'], 'scan_deprecation')) {
            $this->scan_plugin_files();
        }
        
        ?>
        <div class="wrap">
            <h1><?php _e('Domain System - Deprecation Scanner', 'domain-system'); ?></h1>
            <p><?php _e('This tool will scan your plugin files for deprecated WordPress APIs that might be causing console warnings.', 'domain-system'); ?></p>
            
            <div class="notice notice-info">
                <p><strong><?php _e('What this scanner looks for:', 'domain-system'); ?></strong></p>
                <ul>
                    <li><?php _e('Deprecated wp.editPost JavaScript APIs', 'domain-system'); ?></li>
                    <li><?php _e('Deprecated WordPress PHP filters', 'domain-system'); ?></li>
                    <li><?php _e('Components missing new required props', 'domain-system'); ?></li>
                </ul>
            </div>
            
            <form method="post">
                <?php wp_nonce_field('scan_deprecation', 'scan_nonce'); ?>
                <p>
                    <input type="submit" name="scan_files" class="button-primary" value="<?php _e('Scan Plugin Files', 'domain-system'); ?>">
                </p>
            </form>
        </div>
        <?php
    }
    
    /**
     * Scan plugin files for deprecated code
     */
    private function scan_plugin_files() {
        // Get the plugin directory (go up from includes/admin/ to plugin root)
        $plugin_dir = dirname(dirname(dirname(__FILE__))) . '/';
        
        $deprecated_patterns = [
            // JavaScript deprecations (WordPress 6.6+)
            'wp.editPost.PluginPostStatusInfo' => 'Use wp.editor.PluginPostStatusInfo instead (since WP 6.6)',
            'wp.editPost.PluginSidebarMoreMenuItem' => 'Use wp.editor.PluginSidebarMoreMenuItem instead (since WP 6.6)',
            'wp.editPost.PluginSidebar' => 'Use wp.editor.PluginSidebar instead (since WP 6.6)',
            'wp.editPost.PluginPostPublishPanel' => 'Use wp.editor.PluginPostPublishPanel instead (since WP 6.6)',
            
            // PHP filter deprecations (WordPress 5.8+)
            "add_filter('allowed_block_types'" => 'Use allowed_block_types_all filter instead (since WP 5.8)',
            "add_filter('block_categories'" => 'Use block_categories_all filter instead (since WP 5.8)',
            "add_filter('block_editor_settings'" => 'Use block_editor_settings_all filter instead (since WP 5.8)',
            "add_filter('block_editor_preload_paths'" => 'Use block_editor_rest_api_preload_paths filter instead (since WP 5.8)',
            
            // Component deprecations
            'wp.components.ToggleControl' => 'Add __nextHasNoMarginBottom: true prop to avoid margin deprecation (since WP 6.7)',
            
            // Other common deprecations
            "select('core/edit-post')" => 'Many core/edit-post selectors are deprecated. Check WordPress docs for alternatives',
        ];
        
        $results = [];
        $this->scan_directory($plugin_dir, $deprecated_patterns, $results);
        
        $this->display_results($results, $plugin_dir);
    }
    
    /**
     * Recursively scan directory for files
     */
    private function scan_directory($dir, $patterns, &$results) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = glob($dir . '*');
        
        foreach ($files as $file) {
            if (is_dir($file)) {
                // Skip certain directories
                $dirname = basename($file);
                if (in_array($dirname, ['node_modules', '.git', 'vendor', 'tests'])) {
                    continue;
                }
                $this->scan_directory($file . '/', $patterns, $results);
            } elseif (in_array(pathinfo($file, PATHINFO_EXTENSION), ['php', 'js', 'jsx', 'ts', 'tsx'])) {
                $this->scan_file($file, $patterns, $results);
            }
        }
    }
    
    /**
     * Scan individual file for deprecated patterns
     */
    private function scan_file($file, $patterns, &$results) {
        $content = file_get_contents($file);
        if ($content === false) {
            return;
        }
        
        $lines = explode("\n", $content);
        
        foreach ($lines as $line_num => $line) {
            foreach ($patterns as $pattern => $suggestion) {
                if (stripos($line, $pattern) !== false) {
                    $results[] = [
                        'file' => $file,
                        'line' => $line_num + 1,
                        'pattern' => $pattern,
                        'suggestion' => $suggestion,
                        'code' => trim($line),
                        'severity' => $this->get_severity($pattern)
                    ];
                }
            }
        }
    }
    
    /**
     * Get severity level for pattern
     */
    private function get_severity($pattern) {
        if (strpos($pattern, 'wp.editPost') !== false) {
            return 'high'; // These cause console errors
        } elseif (strpos($pattern, 'add_filter') !== false) {
            return 'medium'; // These might cause issues
        }
        return 'low';
    }
    
    /**
     * Display scan results
     */
    private function display_results($results, $plugin_dir) {
        echo '<div class="notice notice-info"><p>' . sprintf(__('Scan completed. Found %d potential issues.', 'domain-system'), count($results)) . '</p></div>';
        
        if (empty($results)) {
            echo '<div class="notice notice-success"><p><strong>' . __('Great!', 'domain-system') . '</strong> ' . __('No deprecated code patterns found.', 'domain-system') . '</p></div>';
            return;
        }
        
        // Group by severity
        $high = array_filter($results, function($r) { return $r['severity'] === 'high'; });
        $medium = array_filter($results, function($r) { return $r['severity'] === 'medium'; });
        $low = array_filter($results, function($r) { return $r['severity'] === 'low'; });
        
        if (!empty($high)) {
            echo '<div class="notice notice-error"><p><strong>' . __('High Priority Issues Found!', 'domain-system') . '</strong> ' . __('These are likely causing the console errors you see.', 'domain-system') . '</p></div>';
            $this->display_results_table($high, $plugin_dir, 'high');
        }
        
        if (!empty($medium)) {
            echo '<h3>' . __('Medium Priority Issues', 'domain-system') . '</h3>';
            $this->display_results_table($medium, $plugin_dir, 'medium');
        }
        
        if (!empty($low)) {
            echo '<h3>' . __('Low Priority Issues', 'domain-system') . '</h3>';
            $this->display_results_table($low, $plugin_dir, 'low');
        }
        
        $this->display_fix_guide();
    }
    
    /**
     * Display results table
     */
    private function display_results_table($results, $plugin_dir, $severity) {
        $color = $severity === 'high' ? '#d63638' : ($severity === 'medium' ? '#dba617' : '#135e96');
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . __('File', 'domain-system') . '</th>';
        echo '<th>' . __('Line', 'domain-system') . '</th>';
        echo '<th>' . __('Deprecated Pattern', 'domain-system') . '</th>';
        echo '<th>' . __('Suggestion', 'domain-system') . '</th>';
        echo '<th>' . __('Code', 'domain-system') . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        foreach ($results as $result) {
            $relative_file = str_replace($plugin_dir, '', $result['file']);
            echo '<tr>';
            echo '<td><code>' . esc_html($relative_file) . '</code></td>';
            echo '<td>' . esc_html($result['line']) . '</td>';
            echo '<td><code style="color: ' . $color . ';">' . esc_html($result['pattern']) . '</code></td>';
            echo '<td><small>' . esc_html($result['suggestion']) . '</small></td>';
            echo '<td><code style="background: #f6f7f7; font-size: 11px;">' . esc_html(substr($result['code'], 0, 80)) . (strlen($result['code']) > 80 ? '...' : '') . '</code></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
    }
    
    /**
     * Display fix guide
     */
    private function display_fix_guide() {
        ?>
        <div class="notice notice-warning">
            <h3><?php _e('Quick Fix Guide', 'domain-system'); ?></h3>
            
            <h4><?php _e('JavaScript Fixes (for .js files):', 'domain-system'); ?></h4>
            <pre style="background: #f6f7f7; padding: 10px; border-left: 4px solid #0073aa; overflow-x: auto;">
// ❌ Replace this:
const { PluginSidebar } = wp.editPost;

// ✅ With this:
const { PluginSidebar } = wp.editor;

// ❌ Replace this:
wp.element.createElement(wp.components.ToggleControl, {
    label: 'Enable Feature',
    checked: isEnabled,
    onChange: setIsEnabled
});

// ✅ With this:
wp.element.createElement(wp.components.ToggleControl, {
    label: 'Enable Feature',
    checked: isEnabled,
    onChange: setIsEnabled,
    __nextHasNoMarginBottom: true
});
            </pre>
            
            <h4><?php _e('PHP Filter Fixes (for .php files):', 'domain-system'); ?></h4>
            <pre style="background: #f6f7f7; padding: 10px; border-left: 4px solid #0073aa; overflow-x: auto;">
// ❌ Replace this:
add_filter('allowed_block_types', 'my_function');

// ✅ With this:
add_filter('allowed_block_types_all', 'my_function', 10, 2);

// And update your function to accept the new parameter:
function my_function($allowed_blocks, $editor_context) {
    // Your code here - now with context awareness
    return $allowed_blocks;
}
            </pre>
            
            <p><strong><?php _e('Need help?', 'domain-system'); ?></strong> <?php _e('Copy the problematic code and ask for specific fix instructions.', 'domain-system'); ?></p>
        </div>
        <?php
    }
}