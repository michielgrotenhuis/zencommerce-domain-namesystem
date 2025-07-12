<?php
/**
 * Domain Template Functions - Updated for Structured Fields
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display domain hero section - Updated for new hero fields
 */
function display_domain_hero($post_id = null) {
    $data = get_domain_data($post_id);
    
    // Use custom hero fields or generate defaults
    $title = $data['hero_h1'] ?: sprintf(__('Register Your %s Domain', 'domain-system'), $data['display_tld']);
    $subtitle = $data['hero_subtitle'] ?: sprintf(__('Get your perfect %s domain name today and establish your online presence', 'domain-system'), $data['display_tld']);
    $price = $data['registration_roundup'] ?: $data['registration_price'] ?: get_option('domain_default_registration_price', '12.99');
    ?>
    <section class="domain-hero">
        <div class="hero-content">
            <h1><?php echo esc_html($title); ?></h1>
            <p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p>
            
            <?php if ($price): ?>
            <div class="hero-price">
                <span class="price"><?php echo format_domain_price($price); ?></span>
                <span class="period">/<?php _e('year', 'domain-system'); ?></span>
            </div>
            <?php endif; ?>
            
            <div class="domain-search">
                <form class="search-form" method="get" action="<?php echo esc_url(home_url('/domain-search/')); ?>">
                    <div class="search-input-group">
                        <input type="text" name="domain" placeholder="<?php _e('Enter your domain name', 'domain-system'); ?>" required />
                        <input type="hidden" name="tld" value="<?php echo esc_attr($data['tld']); ?>" />
                        <button type="submit"><?php _e('Search', 'domain-system'); ?></button>
                    </div>
                </form>
            </div>
            
            <?php if ($data['primary_category']): ?>
            <div class="hero-category">
                <span class="category-badge"><?php echo esc_html($data['category_info']['primary']['name']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Display domain content sections - Updated for new content structure
 */
function display_domain_content($post_id = null) {
    $data = get_domain_data($post_id);
    
    $sections = [
        'overview' => [
            'title' => sprintf(__('Why Choose a %s Domain?', 'domain-system'), $data['display_tld']),
            'content' => $data['overview'],
            'icon' => 'info'
        ],
        'benefits' => [
            'title' => sprintf(__('%s Domain Benefits', 'domain-system'), $data['display_tld']),
            'content' => $data['benefits'],
            'icon' => 'star-filled'
        ],
        'ideas' => [
            'title' => sprintf(__('%s Domain Ideas', 'domain-system'), $data['display_tld']),
            'content' => $data['ideas'],
            'icon' => 'lightbulb'
        ],
        'stats' => [
            'title' => sprintf(__('%s Domain Stats & History', 'domain-system'), $data['display_tld']),
            'content' => $data['stats'],
            'icon' => 'chart-bar'
        ]
    ];
    
    // Filter out empty sections
    $sections = array_filter($sections, function($section) {
        return !empty($section['content']);
    });
    
    if (empty($sections)) {
        return;
    }
    ?>
    <section class="domain-content">
        <div class="container">
            <?php foreach ($sections as $key => $section): ?>
            <div class="content-section content-<?php echo esc_attr($key); ?>">
                <div class="section-header">
                    <span class="section-icon dashicons dashicons-<?php echo esc_attr($section['icon']); ?>"></span>
                    <h2><?php echo esc_html($section['title']); ?></h2>
                </div>
                <div class="content-text">
                    <?php echo wp_kses_post($section['content']); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php
}

/**
 * Display domain pricing table - Updated for roundup fields
 */
function display_domain_pricing($post_id = null) {
    $data = get_domain_data($post_id);
    
    $prices = [
        'registration' => [
            'label' => __('Registration', 'domain-system'),
            'price' => $data['registration_roundup'] ?: $data['registration_price']
        ],
        'renewal' => [
            'label' => __('Renewal', 'domain-system'),
            'price' => $data['renewal_roundup'] ?: $data['renewal_price']
        ],
        'transfer' => [
            'label' => __('Transfer', 'domain-system'),
            'price' => $data['transfer_roundup'] ?: $data['transfer_price']
        ],
        'restoration' => [
            'label' => __('Restoration', 'domain-system'),
            'price' => $data['restoration_roundup'] ?: $data['restoration_price']
        ]
    ];
    
    // Remove empty prices
    $prices = array_filter($prices, function($price) {
        return !empty($price['price']) && $price['price'] > 0;
    });
    
    if (empty($prices)) {
        return;
    }
    ?>
    <section class="domain-pricing">
        <div class="container">
            <h2><?php _e('Pricing', 'domain-system'); ?></h2>
            <div class="pricing-table">
                <?php foreach ($prices as $type => $price_data): ?>
                <div class="pricing-row pricing-<?php echo esc_attr($type); ?>">
                    <div class="pricing-label">
                        <span class="pricing-icon dashicons dashicons-<?php echo $this->get_pricing_icon($type); ?>"></span>
                        <?php echo esc_html($price_data['label']); ?>
                    </div>
                    <div class="pricing-price">
                        <?php echo format_domain_price($price_data['price']); ?>
                        <span class="pricing-period">/<?php _e('year', 'domain-system'); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Get pricing icon for display
 */
function get_pricing_icon($type) {
    $icons = [
        'registration' => 'plus-alt',
        'renewal' => 'update',
        'transfer' => 'migrate',
        'restoration' => 'sos'
    ];
    return $icons[$type] ?? 'money';
}

/**
 * Display domain alternatives - Updated to use related domains
 */
function display_domain_alternatives($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $alternatives = get_domain_alternatives($post_id, 3);
    
    if (empty($alternatives)) {
        return;
    }
    ?>
    <section class="domain-alternatives">
        <div class="container">
            <h2><?php _e('Find a ', 'domain-system'); ?><?php echo esc_html(get_domain_data($post_id)['display_tld']); ?><?php _e(' domain alternative', 'domain-system'); ?></h2>
            <p class="alternatives-intro"><?php _e('Explore these related domain extensions that might be perfect for your project:', 'domain-system'); ?></p>
            
            <div class="alternatives-grid">
                <?php foreach ($alternatives as $alt): ?>
                <div class="alternative-item">
                    <div class="alt-header">
                        <div class="alt-tld"><?php echo esc_html($alt['display_tld']); ?></div>
                        <div class="alt-price"><?php echo esc_html($alt['formatted_price']); ?></div>
                    </div>
                    <div class="alt-content">
                        <h3 class="alt-title"><?php echo esc_html($alt['title']); ?></h3>
                        <?php if (!empty($alt['excerpt'])): ?>
                        <p class="alt-excerpt"><?php echo esc_html($alt['excerpt']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="alt-footer">
                        <a href="<?php echo esc_url($alt['url']); ?>" class="alt-link button">
                            <?php _e('Learn More', 'domain-system'); ?>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain categories and badges
 */
function display_domain_categories($post_id = null) {
    $data = get_domain_data($post_id);
    $category_info = $data['category_info'];
    
    if (empty($category_info['primary']['name'])) {
        return;
    }
    ?>
    <section class="domain-categories">
        <div class="container">
            <div class="category-badges">
                <div class="primary-category">
                    <span class="category-badge primary">
                        <span class="badge-icon dashicons dashicons-tag"></span>
                        <?php echo esc_html($category_info['primary']['name']); ?>
                    </span>
                </div>
                
                <?php if (!empty($category_info['secondary'])): ?>
                <div class="secondary-categories">
                    <?php foreach ($category_info['secondary'] as $secondary): ?>
                    <span class="category-badge secondary">
                        <?php echo esc_html($secondary['name']); ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain policy information - Enhanced
 */
function display_domain_policy($post_id = null) {
    $data = get_domain_data($post_id);
    $policy = $data['policy'];
    
    if (empty($policy) || empty(array_filter($policy))) {
        return;
    }
    ?>
    <section class="domain-policy">
        <div class="container">
            <h2><?php _e('Domain Requirements', 'domain-system'); ?></h2>
            
            <div class="policy-grid">
                <div class="policy-item">
                    <span class="policy-icon dashicons dashicons-editor-textcolor"></span>
                    <div class="policy-content">
                        <h4><?php _e('Length', 'domain-system'); ?></h4>
                        <p><?php printf(__('%d to %d characters', 'domain-system'), $policy['min_length'], $policy['max_length']); ?></p>
                    </div>
                </div>
                
                <div class="policy-item">
                    <span class="policy-icon dashicons dashicons-<?php echo $policy['numbers_allowed'] ? 'yes' : 'no'; ?>"></span>
                    <div class="policy-content">
                        <h4><?php _e('Numbers', 'domain-system'); ?></h4>
                        <p><?php echo $policy['numbers_allowed'] ? __('Allowed', 'domain-system') : __('Not allowed', 'domain-system'); ?></p>
                    </div>
                </div>
                
                <div class="policy-item">
                    <span class="policy-icon dashicons dashicons-minus"></span>
                    <div class="policy-content">
                        <h4><?php _e('Hyphens', 'domain-system'); ?></h4>
                        <p><?php echo $this->get_hyphens_policy_text($policy['hyphens_allowed']); ?></p>
                    </div>
                </div>
                
                <div class="policy-item">
                    <span class="policy-icon dashicons dashicons-translation"></span>
                    <div class="policy-content">
                        <h4><?php _e('International', 'domain-system'); ?></h4>
                        <p><?php echo $policy['idn_allowed'] ? __('IDN supported', 'domain-system') : __('ASCII only', 'domain-system'); ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (!empty($data['registry'])): ?>
            <div class="registry-info">
                <h3><?php _e('Registry Information', 'domain-system'); ?></h3>
                <p><strong><?php _e('Managed by:', 'domain-system'); ?></strong> <?php echo esc_html($data['registry']); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php
}

/**
 * Get human-readable hyphens policy text
 */
function get_hyphens_policy_text($policy) {
    switch ($policy) {
        case 'none':
            return __('Not allowed', 'domain-system');
        case 'middle':
            return __('Middle only', 'domain-system');
        case 'start':
            return __('Start only', 'domain-system');
        case 'end':
            return __('End only', 'domain-system');
        case 'all':
            return __('Anywhere', 'domain-system');
        default:
            return __('Middle only', 'domain-system');
    }
}

/**
 * Display domain FAQ - Enhanced
 */
function display_domain_faq($post_id = null) {
    $data = get_domain_data($post_id);
    $faqs = $data['faq'];
    
    // If no custom FAQs, generate defaults
    if (empty($faqs)) {
        $faqs = generate_default_domain_faq($data['tld'], $data['primary_category']);
    }
    
    if (empty($faqs)) {
        return;
    }
    ?>
    <section class="domain-faq">
        <div class="container">
            <h2><?php _e('Frequently Asked Questions', 'domain-system'); ?></h2>
            <div class="faq-list">
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="faq-item" data-index="<?php echo $index; ?>">
                    <h3 class="faq-question">
                        <button type="button" class="faq-toggle" aria-expanded="false">
                            <?php echo esc_html($faq['question']); ?>
                            <span class="faq-icon dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                    </h3>
                    <div class="faq-answer" aria-hidden="true">
                        <div class="faq-answer-content">
                            <?php echo wp_kses_post($faq['answer']); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain registration form - Enhanced
 */
function display_domain_registration_form($post_id = null) {
    $data = get_domain_data($post_id);
    $tld = $data['tld'];
    $price = $data['registration_roundup'] ?: $data['registration_price'];
    
    if (empty($tld) || empty($price)) {
        return;
    }
    ?>
    <section class="domain-registration">
        <div class="container">
            <div class="registration-form">
                <h3><?php _e('Register Your Domain', 'domain-system'); ?></h3>
                <form method="post" action="<?php echo esc_url(apply_filters('domain_registration_url', '#')); ?>" class="domain-reg-form">
                    <div class="form-row">
                        <div class="form-group domain-input-group">
                            <label for="domain-name" class="sr-only"><?php _e('Domain Name:', 'domain-system'); ?></label>
                            <div class="input-wrapper">
                                <input type="text" id="domain-name" name="domain_name" required 
                                       placeholder="<?php _e('yourdomain', 'domain-system'); ?>" 
                                       pattern="[a-zA-Z0-9-]+" 
                                       title="<?php _e('Only letters, numbers, and hyphens allowed', 'domain-system'); ?>" />
                                <span class="domain-tld"><?php echo esc_html($data['display_tld']); ?></span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="registration-years"><?php _e('Years:', 'domain-system'); ?></label>
                            <select id="registration-years" name="registration_years">
                                <option value="1"><?php _e('1 Year', 'domain-system'); ?> - <?php echo format_domain_price($price); ?></option>
                                <?php if (!empty($data['renewal_roundup']) || !empty($data['renewal_price'])): 
                                    $renewal_price = $data['renewal_roundup'] ?: $data['renewal_price'];
                                ?>
                                <option value="2"><?php _e('2 Years', 'domain-system'); ?> - <?php echo format_domain_price($price + $renewal_price); ?></option>
                                <option value="3"><?php _e('3 Years', 'domain-system'); ?> - <?php echo format_domain_price($price + ($renewal_price * 2)); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="button button-primary register-btn">
                                <?php _e('Add to Cart', 'domain-system'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <input type="hidden" name="tld" value="<?php echo esc_attr($tld); ?>" />
                    <input type="hidden" name="price" value="<?php echo esc_attr($price); ?>" />
                    <?php wp_nonce_field('domain_registration', 'domain_registration_nonce'); ?>
                </form>
            </div>
            
            <div class="registration-features">
                <h4><?php _e('Included with every domain:', 'domain-system'); ?></h4>
                <ul class="features-list">
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Free DNS management', 'domain-system'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Email forwarding', 'domain-system'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('24/7 support', 'domain-system'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Easy domain management', 'domain-system'); ?></li>
                </ul>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain breadcrumbs - Enhanced with categories
 */
function display_domain_breadcrumbs($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $data = get_domain_data($post_id);
    $category_info = $data['category_info'];
    ?>
    <nav class="domain-breadcrumbs" aria-label="<?php _e('Breadcrumb Navigation', 'domain-system'); ?>">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'domain-system'); ?></a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(get_post_type_archive_link('domain')); ?>"><?php _e('Domains', 'domain-system'); ?></a>
            </li>
            <?php if (!empty($category_info['primary']['name'])): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(home_url('/domain-category/' . $category_info['primary']['slug'] . '/')); ?>">
                    <?php echo esc_html($category_info['primary']['name']); ?>
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item current" aria-current="page">
                <?php echo esc_html($data['display_tld']); ?>
            </li>
        </ol>
    </nav>
    <?php
}

/**
 * Display domain features grid
 */
function display_domain_features($post_id = null) {
    $features = apply_filters('domain_features_list', [
        'ssl' => [
            'title' => __('Free SSL Certificate', 'domain-system'),
            'description' => __('Secure your website with a free SSL certificate', 'domain-system'),
            'icon' => 'lock'
        ],
        'privacy' => [
            'title' => __('WHOIS Privacy Protection', 'domain-system'),
            'description' => __('Keep your personal information private', 'domain-system'),
            'icon' => 'hidden'
        ],
        'dns' => [
            'title' => __('Advanced DNS Management', 'domain-system'),
            'description' => __('Complete control over your domain\'s DNS settings', 'domain-system'),
            'icon' => 'networking'
        ],
        'support' => [
            'title' => __('24/7 Expert Support', 'domain-system'),
            'description' => __('Get help whenever you need it from our experts', 'domain-system'),
            'icon' => 'sos'
        ],
        'transfer' => [
            'title' => __('Easy Domain Transfer', 'domain-system'),
            'description' => __('Transfer your domain easily if needed', 'domain-system'),
            'icon' => 'migrate'
        ],
        'management' => [
            'title' => __('User-Friendly Control Panel', 'domain-system'),
            'description' => __('Manage your domain with our intuitive interface', 'domain-system'),
            'icon' => 'dashboard'
        ]
    ]);
    ?>
    <section class="domain-features">
        <div class="container">
            <h2><?php _e('What You Get', 'domain-system'); ?></h2>
            <div class="features-grid">
                <?php foreach ($features as $key => $feature): ?>
                <div class="feature-item" data-feature="<?php echo esc_attr($key); ?>">
                    <div class="feature-icon">
                        <span class="dashicons dashicons-<?php echo esc_attr($feature['icon']); ?>"></span>
                    </div>
                    <div class="feature-content">
                        <h3><?php echo esc_html($feature['title']); ?></h3>
                        <p><?php echo esc_html($feature['description']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display complete domain page template
 */
function display_complete_domain_page($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    // Display all sections in order
    display_domain_breadcrumbs($post_id);
    display_domain_hero($post_id);
    display_domain_categories($post_id);
    display_domain_features($post_id);
    display_domain_pricing($post_id);
    display_domain_registration_form($post_id);
    display_domain_content($post_id);
    display_domain_policy($post_id);
    display_domain_faq($post_id);
    display_domain_alternatives($post_id);
}

/**
 * Get domain page schema markup for SEO
 */
function get_domain_schema_markup($post_id = null) {
    $data = get_domain_data($post_id);
    
    if (empty($data['tld'])) {
        return '';
    }
    
    $schema = [
        "@context" => "https://schema.org",
        "@type" => "Product",
        "name" => sprintf(__('%s Domain Registration', 'domain-system'), $data['display_tld']),
        "description" => get_the_excerpt($post_id) ?: sprintf(__('Register your %s domain today', 'domain-system'), $data['display_tld']),
        "url" => get_permalink($post_id),
        "category" => $data['category_info']['primary']['name'] ?? 'Domains'
    ];
    
    // Add pricing information
    if (!empty($data['registration_roundup']) || !empty($data['registration_price'])) {
        $price = $data['registration_roundup'] ?: $data['registration_price'];
        $schema["offers"] = [
            "@type" => "Offer",
            "price" => $price,
            "priceCurrency" => $data['currency']['code'],
            "availability" => "https://schema.org/InStock",
            "validFrom" => get_the_date('c', $post_id)
        ];
    }
    
    // Add organization/provider
    $schema["provider"] = [
        "@type" => "Organization",
        "name" => get_bloginfo('name'),
        "url" => home_url()
    ];
    
    return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
}