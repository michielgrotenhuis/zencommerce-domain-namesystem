<?php
/**
 * Domain Validation Component
 * 
 * Handles all validation logic for domain post type fields
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainValidation {
    
    /**
     * Validation rules for different field types
     * 
     * @var array
     */
    private $validation_rules = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_validation_rules();
    }
    
    /**
     * Initialize validation rules
     */
    private function init_validation_rules() {
        $this->validation_rules = [
            'tld' => [
                'required' => true,
                'min_length' => 1,
                'max_length' => 64,
                'pattern' => '/^[a-z0-9.-]+$/i',
                'custom' => [$this, 'validate_tld_uniqueness']
            ],
            'product_id' => [
                'max_length' => 50,
                'pattern' => '/^[a-zA-Z0-9_-]*$/',
                'custom' => [$this, 'validate_product_id_uniqueness']
            ],
            'registration_price' => [
                'required' => true,
                'type' => 'number',
                'min' => 0,
                'max' => 99999.99
            ],
            'renewal_price' => [
                'type' => 'number',
                'min' => 0,
                'max' => 99999.99
            ],
            'transfer_price' => [
                'type' => 'number',
                'min' => 0,
                'max' => 99999.99
            ],
            'restoration_price' => [
                'type' => 'number',
                'min' => 0,
                'max' => 99999.99
            ],
            'hero_h1' => [
                'max_length' => 100,
                'custom' => [$this, 'validate_text_content']
            ],
            'hero_subtitle' => [
                'max_length' => 300,
                'custom' => [$this, 'validate_text_content']
            ],
            'overview' => [
                'max_length' => 5000,
                'custom' => [$this, 'validate_html_content']
            ],
            'stats' => [
                'max_length' => 5000,
                'custom' => [$this, 'validate_html_content']
            ],
            'benefits' => [
                'max_length' => 5000,
                'custom' => [$this, 'validate_html_content']
            ],
            'ideas' => [
                'max_length' => 5000,
                'custom' => [$this, 'validate_html_content']
            ],
            'min_length' => [
                'type' => 'integer',
                'min' => 1,
                'max' => 63,
                'custom' => [$this, 'validate_min_length']
            ],
            'max_length' => [
                'type' => 'integer',
                'min' => 1,
                'max' => 63,
                'custom' => [$this, 'validate_max_length']
            ],
            'registry' => [
                'max_length' => 100,
                'custom' => [$this, 'validate_registry_name']
            ]
        ];
        
        // Allow filtering of validation rules
        $this->validation_rules = apply_filters('domain_validation_rules', $this->validation_rules);
    }
    
    /**
     * Validate a field value
     * 
     * @param mixed $value Field value
     * @param string $field Field name
     * @param int $post_id Post ID being validated
     * @param array $config Field configuration
     * @return true|string True if valid, error message if invalid
     */
    public function validate_field($value, $field, $post_id, $config = []) {
        // Get validation rules for this field
        $rules = $this->validation_rules[$field] ?? [];
        
        // Merge with config rules
        $rules = array_merge($rules, $config);
        
        // Check required fields
        if (!empty($rules['required']) && $this->is_empty_value($value)) {
            return sprintf(__('%s is required.', 'domain-system'), $config['label'] ?? ucfirst(str_replace('_', ' ', $field)));
        }
        
        // Skip further validation if value is empty and not required
        if ($this->is_empty_value($value)) {
            return true;
        }
        
        // Type validation
        if (isset($rules['type'])) {
            $type_validation = $this->validate_type($value, $rules['type'], $field);
            if ($type_validation !== true) {
                return $type_validation;
            }
        }
        
        // Length validation
        $length_validation = $this->validate_length($value, $rules, $field);
        if ($length_validation !== true) {
            return $length_validation;
        }
        
        // Range validation (for numbers)
        if (isset($rules['min']) || isset($rules['max'])) {
            $range_validation = $this->validate_range($value, $rules, $field);
            if ($range_validation !== true) {
                return $range_validation;
            }
        }
        
        // Pattern validation
        if (isset($rules['pattern'])) {
            $pattern_validation = $this->validate_pattern($value, $rules['pattern'], $field);
            if ($pattern_validation !== true) {
                return $pattern_validation;
            }
        }
        
        // Custom validation
        if (isset($rules['custom']) && is_callable($rules['custom'])) {
            $custom_validation = call_user_func($rules['custom'], $value, $field, $post_id, $rules);
            if ($custom_validation !== true) {
                return $custom_validation;
            }
        }
        
        return true;
    }
    
    /**
     * Validate multiple fields at once
     * 
     * @param array $field_values Associative array of field => value
     * @param int $post_id Post ID being validated
     * @param array $configs Field configurations
     * @return array Array of validation errors (empty if all valid)
     */
    public function validate_fields($field_values, $post_id, $configs = []) {
        $errors = [];
        
        foreach ($field_values as $field => $value) {
            $config = $configs[$field] ?? [];
            $validation_result = $this->validate_field($value, $field, $post_id, $config);
            
            if ($validation_result !== true) {
                $errors[$field] = $validation_result;
            }
        }
        
        // Cross-field validation
        $cross_validation_errors = $this->validate_cross_field_dependencies($field_values, $post_id);
        $errors = array_merge($errors, $cross_validation_errors);
        
        return $errors;
    }
    
    /**
     * Check if value is considered empty
     * 
     * @param mixed $value Value to check
     * @return bool True if empty
     */
    private function is_empty_value($value) {
        if ($value === null || $value === '') {
            return true;
        }
        
        if (is_array($value) && empty($value)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate field type
     * 
     * @param mixed $value Value to validate
     * @param string $type Expected type
     * @param string $field Field name
     * @return true|string Validation result
     */
    private function validate_type($value, $type, $field) {
        switch ($type) {
            case 'number':
            case 'float':
                if (!is_numeric($value)) {
                    return sprintf(__('%s must be a valid number.', 'domain-system'), ucfirst(str_replace('_', ' ', $field)));
                }
                break;
                
            case 'integer':
            case 'int':
                if (!is_numeric($value) || (int)$value != $value) {
                    return sprintf(__('%s must be a valid integer.', 'domain-system'), ucfirst(str_replace('_', ' ', $field)));
                }
                break;
                
            case 'email':
                if (!is_email($value)) {
                    return sprintf(__('%s must be a valid email address.', 'domain-system'), ucfirst(str_replace('_', ' ', $field)));
                }
                break;
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return sprintf(__('%s must be a valid URL.', 'domain-system'), ucfirst(str_replace('_', ' ', $field)));
                }
                break;
                
            case 'boolean':
            case 'bool':
                if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'true', 'false'], true)) {
                    return sprintf(__('%s must be a valid boolean value.', 'domain-system'), ucfirst(str_replace('_', ' ', $field)));
                }
                break;
        }
        
        return true;
    }
    
    /**
     * Validate field length
     * 
     * @param mixed $value Value to validate
     * @param array $rules Validation rules
     * @param string $field Field name
     * @return true|string Validation result
     */
    private function validate_length($value, $rules, $field) {
        $length = is_string($value) ? strlen($value) : 0;
        
        if (isset($rules['min_length']) && $length < $rules['min_length']) {
            return sprintf(__('%s must be at least %d characters long.', 'domain-system'), 
                         ucfirst(str_replace('_', ' ', $field)), $rules['min_length']);
        }
        
        if (isset($rules['max_length']) && $length > $rules['max_length']) {
            return sprintf(__('%s cannot exceed %d characters.', 'domain-system'), 
                         ucfirst(str_replace('_', ' ', $field)), $rules['max_length']);
        }
        
        return true;
    }
    
    /**
     * Validate numeric range
     * 
     * @param mixed $value Value to validate
     * @param array $rules Validation rules
     * @param string $field Field name
     * @return true|string Validation result
     */
    private function validate_range($value, $rules, $field) {
        $numeric_value = floatval($value);
        
        if (isset($rules['min']) && $numeric_value < $rules['min']) {
            return sprintf(__('%s must be at least %s.', 'domain-system'), 
                         ucfirst(str_replace('_', ' ', $field)), $rules['min']);
        }
        
        if (isset($rules['max']) && $numeric_value > $rules['max']) {
            return sprintf(__('%s cannot exceed %s.', 'domain-system'), 
                         ucfirst(str_replace('_', ' ', $field)), $rules['max']);
        }
        
        return true;
    }
    
    /**
     * Validate against pattern
     * 
     * @param mixed $value Value to validate
     * @param string $pattern Regular expression pattern
     * @param string $field Field name
     * @return true|string Validation result
     */
    private function validate_pattern($value, $pattern, $field) {
        if (!preg_match($pattern, $value)) {
            return sprintf(__('%s contains invalid characters.', 'domain-system'), 
                         ucfirst(str_replace('_', ' ', $field)));
        }
        
        return true;
    }
    
    /**
     * Validate cross-field dependencies
     * 
     * @param array $field_values Field values
     * @param int $post_id Post ID
     * @return array Validation errors
     */
    private function validate_cross_field_dependencies($field_values, $post_id) {
        $errors = [];
        
        // Validate min_length vs max_length
        if (isset($field_values['min_length']) && isset($field_values['max_length'])) {
            $min = intval($field_values['min_length']);
            $max = intval($field_values['max_length']);
            
            if ($min > $max) {
                $errors['min_length'] = __('Minimum length cannot be greater than maximum length.', 'domain-system');
            }
        }
        
        // Validate pricing relationships
        if (isset($field_values['registration_price']) && isset($field_values['renewal_price'])) {
            $reg_price = floatval($field_values['registration_price']);
            $renewal_price = floatval($field_values['renewal_price']);
            
            // Warn if renewal price is significantly different from registration price
            if ($renewal_price > 0 && abs($reg_price - $renewal_price) > ($reg_price * 3)) {
                $errors['renewal_price'] = __('Renewal price seems unusually different from registration price. Please verify.', 'domain-system');
            }
        }
        
        return $errors;
    }
    
    /**
     * Custom validation: TLD uniqueness
     * 
     * @param string $value TLD value
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_tld_uniqueness($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        $tld = sanitize_domain_tld($value);
        
        // Check for basic TLD format
        if (empty($tld) || $tld !== $value) {
            return __('TLD format is invalid. Please use format like ".shop" or "shop".', 'domain-system');
        }
        
        // Check for reserved or problematic TLDs
        $reserved_tlds = ['localhost', 'local', 'test', 'invalid', 'example'];
        if (in_array(ltrim($tld, '.'), $reserved_tlds)) {
            return __('This TLD is reserved and cannot be used.', 'domain-system');
        }
        
        // Check if TLD already exists (excluding current post)
        $existing = get_domain_by_tld($tld);
        if ($existing && $existing->ID !== $post_id) {
            return sprintf(__('TLD "%s" already exists in another domain.', 'domain-system'), $tld);
        }
        
        return true;
    }
    
    /**
     * Custom validation: Product ID uniqueness
     * 
     * @param string $value Product ID value
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_product_id_uniqueness($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        // Check if product ID already exists (excluding current post)
        $existing = get_domain_by_product_id($value);
        if ($existing && $existing->ID !== $post_id) {
            return sprintf(__('Product ID "%s" is already in use by another domain.', 'domain-system'), $value);
        }
        
        return true;
    }
    
    /**
     * Custom validation: Text content
     * 
     * @param string $value Text value
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_text_content($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        // Check for suspicious content
        $suspicious_patterns = [
            '/\b(viagra|cialis|pharmacy)\b/i',
            '/\b(casino|gambling|poker)\b/i',
            '/\b(click here|free money)\b/i'
        ];
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return __('Content appears to contain suspicious or promotional text.', 'domain-system');
            }
        }
        
        // Check for excessive capitalization
        if (strlen($value) > 20) {
            $uppercase_ratio = strlen(preg_replace('/[^A-Z]/', '', $value)) / strlen($value);
            if ($uppercase_ratio > 0.5) {
                return __('Content contains too much uppercase text.', 'domain-system');
            }
        }
        
        return true;
    }
    
    /**
     * Custom validation: HTML content
     * 
     * @param string $value HTML value
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_html_content($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        // First validate as text content
        $text_validation = $this->validate_text_content(strip_tags($value), $field, $post_id, $rules);
        if ($text_validation !== true) {
            return $text_validation;
        }
        
        // Check for malicious scripts
        $dangerous_tags = ['script', 'iframe', 'object', 'embed', 'form'];
        foreach ($dangerous_tags as $tag) {
            if (stripos($value, "<$tag") !== false) {
                return sprintf(__('Content contains disallowed HTML tag: %s', 'domain-system'), $tag);
            }
        }
        
        // Check for excessive HTML
        $text_length = strlen(strip_tags($value));
        $html_length = strlen($value);
        
        if ($text_length > 0 && ($html_length / $text_length) > 3) {
            return __('Content contains too much HTML markup relative to text.', 'domain-system');
        }
        
        return true;
    }
    
    /**
     * Custom validation: Min length field
     * 
     * @param int $value Min length value
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_min_length($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        $min = intval($value);
        
        // Get max length from POST data or database
        $max_length = null;
        if (isset($_POST['domain_max_length'])) {
            $max_length = intval($_POST['domain_max_length']);
        } else {
            $max_length = intval(get_post_meta($post_id, '_domain_max_length', true));
        }
        
        if ($max_length && $min > $max_length) {
            return __('Minimum length cannot be greater than maximum length.', 'domain-system');
        }
        
        return true;
    }
    
    /**
     * Custom validation: Max length field
     * 
     * @param int $value Max length value
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_max_length($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        $max = intval($value);
        
        // Get min length from POST data or database
        $min_length = null;
        if (isset($_POST['domain_min_length'])) {
            $min_length = intval($_POST['domain_min_length']);
        } else {
            $min_length = intval(get_post_meta($post_id, '_domain_min_length', true));
        }
        
        if ($min_length && $max < $min_length) {
            return __('Maximum length cannot be less than minimum length.', 'domain-system');
        }
        
        return true;
    }
    
    /**
     * Custom validation: Registry name
     * 
     * @param string $value Registry name
     * @param string $field Field name
     * @param int $post_id Current post ID
     * @param array $rules Validation rules
     * @return true|string Validation result
     */
    private function validate_registry_name($value, $field, $post_id, $rules) {
        if (empty($value)) {
            return true;
        }
        
        // Check for valid registry name format
        if (!preg_match('/^[a-zA-Z0-9\s\-&.,()]+$/', $value)) {
            return __('Registry name contains invalid characters.', 'domain-system');
        }
        
        // Check against known registries for suggestions
        $known_registries = [
            'Verisign', 'Donuts Inc.', 'GMO Registry', 'Radix Registry',
            'Afilias', 'Neustar', 'CentralNic', 'Google Registry',
            'Amazon Registry Services', 'Identity Digital'
        ];
        
        // Suggest similar registry if close match found
        foreach ($known_registries as $known) {
            $similarity = 0;
            similar_text(strtolower($value), strtolower($known), $similarity);
            
            if ($similarity > 70 && strcasecmp($value, $known) !== 0) {
                return sprintf(__('Did you mean "%s"? If not, please verify the registry name.', 'domain-system'), $known);
            }
        }
        
        return true;
    }
    
    /**
     * Validate FAQ data
     * 
     * @param array $faq_data FAQ data array
     * @param int $post_id Post ID
     * @return array Validation errors
     */
    public function validate_faq_data($faq_data, $post_id) {
        $errors = [];
        
        if (!is_array($faq_data)) {
            return ['faq' => __('FAQ data must be an array.', 'domain-system')];
        }
        
        foreach ($faq_data as $index => $faq) {
            $faq_errors = [];
            
            // Validate question
            if (empty($faq['question'])) {
                $faq_errors[] = sprintf(__('FAQ %d: Question is required.', 'domain-system'), $index + 1);
            } elseif (strlen($faq['question']) > 200) {
                $faq_errors[] = sprintf(__('FAQ %d: Question cannot exceed 200 characters.', 'domain-system'), $index + 1);
            }
            
            // Validate answer
            if (empty($faq['answer'])) {
                $faq_errors[] = sprintf(__('FAQ %d: Answer is required.', 'domain-system'), $index + 1);
            } elseif (strlen($faq['answer']) > 1000) {
                $faq_errors[] = sprintf(__('FAQ %d: Answer cannot exceed 1000 characters.', 'domain-system'), $index + 1);
            }
            
            // Check for duplicate questions
            $question_lower = strtolower(trim($faq['question']));
            foreach ($faq_data as $other_index => $other_faq) {
                if ($index !== $other_index && 
                    strtolower(trim($other_faq['question'])) === $question_lower) {
                    $faq_errors[] = sprintf(__('FAQ %d: Duplicate question found.', 'domain-system'), $index + 1);
                    break;
                }
            }
            
            if (!empty($faq_errors)) {
                $errors["faq_{$index}"] = implode(' ', $faq_errors);
            }
        }
        
        return $errors;
    }
    
    /**
     * Validate SEO data
     * 
     * @param array $seo_data SEO data array
     * @param int $post_id Post ID
     * @return array Validation errors
     */
    public function validate_seo_data($seo_data, $post_id) {
        $errors = [];
        
        // Validate SEO title
        if (!empty($seo_data['seo_title'])) {
            if (strlen($seo_data['seo_title']) > 60) {
                $errors['seo_title'] = __('SEO title should not exceed 60 characters for optimal display.', 'domain-system');
            }
        }
        
        // Validate meta description
        if (!empty($seo_data['seo_description'])) {
            if (strlen($seo_data['seo_description']) > 160) {
                $errors['seo_description'] = __('Meta description should not exceed 160 characters for optimal display.', 'domain-system');
            }
        }
        
        // Validate keywords
        if (!empty($seo_data['seo_keywords'])) {
            $keywords = array_map('trim', explode(',', $seo_data['seo_keywords']));
            if (count($keywords) > 10) {
                $errors['seo_keywords'] = __('Too many keywords. Focus on 5-10 relevant keywords for better SEO.', 'domain-system');
            }
        }
        
        // Validate OG image
        if (!empty($seo_data['og_image'])) {
            if (!filter_var($seo_data['og_image'], FILTER_VALIDATE_URL)) {
                $errors['og_image'] = __('Social media image must be a valid URL.', 'domain-system');
            }
        }
        
        return $errors;
    }
    
    /**
     * Get validation rules for a specific field
     * 
     * @param string $field Field name
     * @return array Validation rules
     */
    public function get_field_rules($field) {
        return $this->validation_rules[$field] ?? [];
    }
    
    /**
     * Get all validation rules
     * 
     * @return array All validation rules
     */
    public function get_all_rules() {
        return $this->validation_rules;
    }
    
    /**
     * Add custom validation rule
     * 
     * @param string $field Field name
     * @param array $rules Validation rules
     */
    public function add_field_rules($field, $rules) {
        $this->validation_rules[$field] = array_merge(
            $this->validation_rules[$field] ?? [],
            $rules
        );
    }
    
    /**
     * Remove validation rule
     * 
     * @param string $field Field name
     * @param string $rule_key Rule key to remove
     */
    public function remove_field_rule($field, $rule_key) {
        if (isset($this->validation_rules[$field][$rule_key])) {
            unset($this->validation_rules[$field][$rule_key]);
        }
    }
}