<?php
/**
 * Domain Template Functions
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display domain hero section
 */
function display_domain_hero($post_id = null) {
    $data = get_domain_data($post_id);
    
    $title = $data['hero_h1'] ?: sprintf(__('Register Your %s Domain', 'domain-system'), $data['tld']);
    $subtitle = $data['hero_subtitle'] ?: sprintf(__('Get your perfect %s domain name today', 'domain-system'), $data['tld']);
    $price = $data['registration_price'] ?: get_option('domain_default_registration_price', '12.99');
    ?>
    <section class="domain-hero">
        <div class="hero-content">
            <h1><?php echo esc_html($title); ?></h1>
            <p class="hero-subtitle"><?php echo esc_html($subtitle); ?></p>
            <div class="hero-price">
                <span class="price"><?php echo format_domain_price($price); ?></span>
                <span class="period">/<?php _e('year', 'domain-system'); ?></span>
            </div>
            <div class="domain-search">
                <form class="search-form" method="get" action="<?php echo esc_url(home_url('/domain-search/')); ?>">
                    <div class="search-input-group">
                        <input type="text" name="domain" placeholder="<?php _e('Enter your domain name', 'domain-system'); ?>" required />
                        <input type="hidden" name="tld" value="<?php echo esc_attr($data['tld']); ?>" />
                        <button type="submit"><?php _e('Search', 'domain-system'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain features
 */
function display_domain_features($post_id = null) {
    $features = apply_filters('domain_features_list', [
        'ssl' => [
            'title' => __('Free SSL Certificate', 'domain-system'),
            'icon' => 'lock'
        ],
        'privacy' => [
            'title' => __('WHOIS Privacy Protection', 'domain-system'),
            'icon' => 'hidden'
        ],
        'dns' => [
            'title' => __('Advanced DNS Management', 'domain-system'),
            'icon' => 'networking'
        ],
        'support' => [
            'title' => __('24/7 Expert Support', 'domain-system'),
            'icon' => 'sos'
        ],
        'transfer' => [
            'title' => __('Easy Domain Transfer', 'domain-system'),
            'icon' => 'migrate'
        ],
        'management' => [
            'title' => __('User-Friendly Control Panel', 'domain-system'),
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
                    <div class="feature-text">
                        <h3><?php echo esc_html($feature['title']); ?></h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain content sections
 */
function display_domain_content($post_id = null) {
    $data = get_domain_data($post_id);
    
    $sections = [
        'overview' => [
            'title' => __('Overview', 'domain-system'),
            'content' => $data['overview']
        ],
        'benefits' => [
            'title' => __('Benefits', 'domain-system'),
            'content' => $data['benefits']
        ],
        'ideas' => [
            'title' => __('Usage Ideas', 'domain-system'),
            'content' => $data['ideas']
        ]
    ];
    
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
                <h2><?php echo esc_html($section['title']); ?></h2>
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
 * Display domain FAQ
 */
function display_domain_faq($post_id = null) {
    $data = get_domain_data($post_id);
    $faqs = $data['faq'];
    
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
                        <?php echo esc_html($faq['question']); ?>
                        <span class="faq-toggle"></span>
                    </h3>
                    <div class="faq-answer">
                        <?php echo wp_kses_post($faq['answer']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain alternatives
 */
function display_domain_alternatives($post_id = null) {
    $alternatives = get_domain_alternatives($post_id ?: get_the_ID());
    
    if (empty($alternatives)) {
        return;
    }
    ?>
    <section class="domain-alternatives">
        <div class="container">
            <h2><?php _e('Alternative Domain Extensions', 'domain-system'); ?></h2>
            <div class="alternatives-grid">
                <?php foreach ($alternatives as $alt): ?>
                <div class="alternative-item">
                    <div class="alt-tld"><?php echo esc_html($alt['tld']); ?></div>
                    <div class="alt-price"><?php echo format_domain_price($alt['price']); ?></div>
                    <div class="alt-title"><?php echo esc_html($alt['title']); ?></div>
                    <a href="<?php echo esc_url($alt['url']); ?>" class="alt-link button">
                        <?php _e('Learn More', 'domain-system'); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display pricing table
 */
function display_domain_pricing($post_id = null) {
    $data = get_domain_data($post_id);
    
    $prices = [
        'registration' => [
            'label' => __('Registration', 'domain-system'),
            'price' => $data['registration_price']
        ],
        'renewal' => [
            'label' => __('Renewal', 'domain-system'),
            'price' => $data['renewal_price']
        ],
        'transfer' => [
            'label' => __('Transfer', 'domain-system'),
            'price' => $data['transfer_price']
        ],
        'restoration' => [
            'label' => __('Restoration', 'domain-system'),
            'price' => $data['restoration_price']
        ]
    ];
    
    // Remove empty prices
    $prices = array_filter($prices, function($price) {
        return !empty($price['price']);
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
 * Display domain policy information
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
            <div class="policy-content">
                <p><?php echo esc_html(format_domain_policy($policy)); ?></p>
                
                <?php if (!empty($data['registry'])): ?>
                <p class="registry-info">
                    <strong><?php _e('Registry:', 'domain-system'); ?></strong>
                    <?php echo esc_html($data['registry']); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain registration form
 */
function display_domain_registration_form($post_id = null) {
    $data = get_domain_data($post_id);
    $tld = $data['tld'];
    $price = $data['registration_price'];
    ?>
    <section class="domain-registration">
        <div class="container">
            <div class="registration-form">
                <h3><?php _e('Register Your Domain', 'domain-system'); ?></h3>
                <form method="post" action="<?php echo esc_url(apply_filters('domain_registration_url', '#')); ?>" class="domain-reg-form">
                    <div class="form-group">
                        <label for="domain-name"><?php _e('Domain Name:', 'domain-system'); ?></label>
                        <div class="domain-input-group">
                            <input type="text" id="domain-name" name="domain_name" required 
                                   placeholder="<?php _e('yourdomain', 'domain-system'); ?>" />
                            <span class="domain-tld">.<?php echo esc_html(ltrim($tld, '.')); ?></span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="registration-years"><?php _e('Registration Period:', 'domain-system'); ?></label>
                        <select id="registration-years" name="registration_years">
                            <option value="1"><?php _e('1 Year', 'domain-system'); ?> - <?php echo format_domain_price($price); ?></option>
                            <?php if (!empty($data['renewal_price'])): ?>
                            <option value="2"><?php _e('2 Years', 'domain-system'); ?> - <?php echo format_domain_price($price + $data['renewal_price']); ?></option>
                            <option value="3"><?php _e('3 Years', 'domain-system'); ?> - <?php echo format_domain_price($price + ($data['renewal_price'] * 2)); ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Add to Cart', 'domain-system'); ?>
                        </button>
                    </div>
                    
                    <input type="hidden" name="tld" value="<?php echo esc_attr($tld); ?>" />
                    <input type="hidden" name="price" value="<?php echo esc_attr($price); ?>" />
                    <?php wp_nonce_field('domain_registration', 'domain_registration_nonce'); ?>
                </form>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Display domain search widget
 */
function display_domain_search_widget($args = []) {
    $defaults = [
        'title' => __('Search Domains', 'domain-system'),
        'placeholder' => __('Enter domain name', 'domain-system'),
        'button_text' => __('Search', 'domain-system'),
        'show_suggestions' => true
    ];
    
    $args = wp_parse_args($args, $defaults);
    ?>
    <div class="domain-search-widget">
        <?php if (!empty($args['title'])): ?>
            <h3><?php echo esc_html($args['title']); ?></h3>
        <?php endif; ?>
        
        <form method="get" action="<?php echo esc_url(home_url('/domain-search/')); ?>" class="widget-search-form">
            <div class="search-input-group">
                <input type="text" name="domain" 
                       placeholder="<?php echo esc_attr($args['placeholder']); ?>" 
                       class="domain-search-input" required />
                <button type="submit" class="search-button">
                    <?php echo esc_html($args['button_text']); ?>
                </button>
            </div>
        </form>
        
        <?php if ($args['show_suggestions']): ?>
        <div class="search-suggestions" style="display: none;">
            <ul class="suggestion-list"></ul>
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Display domain breadcrumbs
 */
function display_domain_breadcrumbs($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $data = get_domain_data($post_id);
    $categories = wp_get_post_terms($post_id, 'domain_category');
    ?>
    <nav class="domain-breadcrumbs" aria-label="<?php _e('Breadcrumb Navigation', 'domain-system'); ?>">
        <ol class="breadcrumb-list">
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(home_url('/')); ?>"><?php _e('Home', 'domain-system'); ?></a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(get_post_type_archive_link('domain')); ?>"><?php _e('Domains', 'domain-system'); ?></a>
            </li>
            <?php if (!empty($categories)): ?>
            <li class="breadcrumb-item">
                <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item current" aria-current="page">
                <?php echo esc_html($data['tld']); ?>
            </li>
        </ol>
    </nav>
    <?php
}

/**
 * Display domain social sharing
 */
function display_domain_social_sharing($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $url = get_permalink($post_id);
    $title = get_the_title($post_id);
    $data = get_domain_data($post_id);
    
    $share_text = sprintf(__('Check out %s domains!', 'domain-system'), $data['tld']);
    ?>
    <div class="domain-social-sharing">
        <h4><?php _e('Share this domain:', 'domain-system'); ?></h4>
        <div class="social-buttons">
            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($url); ?>&text=<?php echo urlencode($share_text); ?>" 
               target="_blank" class="social-button twitter">
                <span class="dashicons dashicons-twitter"></span>
                <?php _e('Twitter', 'domain-system'); ?>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($url); ?>" 
               target="_blank" class="social-button facebook">
                <span class="dashicons dashicons-facebook"></span>
                <?php _e('Facebook', 'domain-system'); ?>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($url); ?>" 
               target="_blank" class="social-button linkedin">
                <span class="dashicons dashicons-linkedin"></span>
                <?php _e('LinkedIn', 'domain-system'); ?>
            </a>
            <a href="mailto:?subject=<?php echo urlencode($title); ?>&body=<?php echo urlencode($share_text . ' ' . $url); ?>" 
               class="social-button email">
                <span class="dashicons dashicons-email"></span>
                <?php _e('Email', 'domain-system'); ?>
            </a>
        </div>
    </div>
    <?php
}

/**
 * Display related domains
 */
function display_related_domains($post_id = null, $limit = 4) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $categories = wp_get_post_terms($post_id, 'domain_category', ['fields' => 'ids']);
    
    $args = [
        'post_type' => 'domain',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'post__not_in' => [$post_id],
        'orderby' => 'rand'
    ];
    
    if (!empty($categories)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'domain_category',
                'field' => 'term_id',
                'terms' => $categories[0]
            ]
        ];
    }
    
    $query = new WP_Query($args);
    
    if (!$query->have_posts()) {
        return;
    }
    ?>
    <section class="related-domains">
        <div class="container">
            <h2><?php _e('Related Domains', 'domain-system'); ?></h2>
            <div class="related-domains-grid">
                <?php while ($query->have_posts()): $query->the_post(); ?>
                    <?php $data = get_domain_data(get_the_ID()); ?>
                    <div class="related-domain-item">
                        <h3><a href="<?php the_permalink(); ?>"><?php echo esc_html($data['tld']); ?></a></h3>
                        <div class="domain-price"><?php echo format_domain_price($data['registration_price']); ?></div>
                        <p><?php the_excerpt(); ?></p>
                        <a href="<?php the_permalink(); ?>" class="button">
                            <?php _e('Learn More', 'domain-system'); ?>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
}