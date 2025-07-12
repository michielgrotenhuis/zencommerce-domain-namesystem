<?php
/**
 * Template Name: Single Domain Page
 * 
 * Single domain template for domain landing pages
 * 
 * @package YourSite
 * @since 1.0.0
 */

get_header(); 

// Get domain data
$domain_data = get_domain_page_data();

?>

<main id="main" class="site-main domain-single-page">
    <?php while (have_posts()) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('domain-landing-page'); ?>>
        
        <?php 
        // Hero Section
        get_template_part('template-parts/domains-single/hero', null, $domain_data);
        
        // Page Content Section (if there's content in the editor)
        if (get_the_content()) {
            get_template_part('template-parts/domains-single/page-content', null, $domain_data);
        }
        
        // Why Choose Section
        get_template_part('template-parts/domains-single/why-choose', null, $domain_data);
        
        // Registration Steps Section
        get_template_part('template-parts/domains-single/registration-steps', null, $domain_data);
        
        // Benefits Section
        get_template_part('template-parts/domains-single/benefits', null, $domain_data);
        
        // FAQ Section
        if (!empty($domain_data['domain_faq'])) {
            get_template_part('template-parts/domains-single/faq', null, $domain_data);
        }
        
        // Domain Alternatives Section
        if (!empty($domain_data['domain_alternatives'])) {
            get_template_part('template-parts/domains-single/alternatives', null, $domain_data);
        }
        
        // CTA Section
        get_template_part('template-parts/domains-single/cta', null, $domain_data);
        ?>

    </article>
    <?php endwhile; ?>
</main>

<?php
// Include Upmind scripts and styles
get_template_part('template-parts/domains-single/upmind-integration', null, $domain_data);

get_footer();

/**
 * Helper function to get all domain page data
 * Centralizes data retrieval for better maintainability
 */
function get_domain_page_data() {
    global $post;
    
    // Get domain TLD from meta or slug
    $domain_tld = get_post_meta($post->ID, '_domain_tld', true);
    if (empty($domain_tld)) {
        // Extract from page slug as fallback
        $slug = get_post_field('post_name', $post->ID);
        $domain_tld = str_replace('-', '.', $slug);
        
        if (empty($domain_tld)) {
            $domain_tld = 'store';
        }
    }
    
    // Remove leading dot if present
    $domain_tld = ltrim($domain_tld, '.');
    
    // Get pricing information
    $registration_price = get_post_meta($post->ID, '_domain_registration_price', true) ?: '12.99';
    $renewal_price = get_post_meta($post->ID, '_domain_renewal_price', true) ?: '14.99';
    $transfer_price = get_post_meta($post->ID, '_domain_transfer_price', true) ?: '12.99';
    $restoration_price = get_post_meta($post->ID, '_domain_restoration_price', true) ?: '99.99';
    
    // Get current user currency
    $current_currency = array('code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar');
    if (function_exists('yoursite_get_user_currency_safe')) {
        $user_currency = yoursite_get_user_currency_safe();
        if (!empty($user_currency) && is_array($user_currency)) {
            $current_currency = $user_currency;
        }
    }
    
    // Get hero section data
    $hero_h1 = get_post_meta($post->ID, '_domain_hero_h1', true);
    $hero_subtitle = get_post_meta($post->ID, '_domain_hero_subtitle', true);
    
    // Get content sections
    $domain_overview = get_post_meta($post->ID, '_domain_overview', true);
    $domain_stats = get_post_meta($post->ID, '_domain_stats', true);
    $domain_benefits = get_post_meta($post->ID, '_domain_benefits', true);
    $domain_ideas = get_post_meta($post->ID, '_domain_ideas', true);
    
    // Get FAQ data
    $domain_faq = get_post_meta($post->ID, '_domain_faq', true);
    if (!is_array($domain_faq)) {
        $domain_faq = array();
    }
    
    // Get domain policy information
    $domain_policy = array(
        'min_length' => get_post_meta($post->ID, '_domain_min_length', true) ?: '2',
        'max_length' => get_post_meta($post->ID, '_domain_max_length', true) ?: '63',
        'numbers_allowed' => get_post_meta($post->ID, '_domain_numbers_allowed', true) === '1',
        'hyphens_allowed' => get_post_meta($post->ID, '_domain_hyphens_allowed', true) ?: 'middle',
        'idn_allowed' => get_post_meta($post->ID, '_domain_idn_allowed', true) === '1'
    );
    
    // Get registry information
    $domain_registry = get_post_meta($post->ID, '_domain_registry', true);
    
    // Get domain features (legacy support)
    $domain_features = get_post_meta($post->ID, '_domain_features', true);
    if (empty($domain_features)) {
        $domain_features = array(
            'SSL certificates included',
            'WHOIS privacy protection',
            'Free DNS management',
            'Easy domain forwarding',
            '24/7 expert support'
        );
    } else {
        if (!is_array($domain_features)) {
            $domain_features = explode("\n", $domain_features);
        }
    }
    
    // Get domain alternatives based on primary category
    $domain_alternatives = get_domain_alternatives($post->ID, $domain_tld);
    
    return array(
        'domain_ext' => $domain_tld,
        'domain_price' => $registration_price,
        'domain_renewal_price' => $renewal_price,
        'domain_transfer_price' => $transfer_price,
        'domain_restoration_price' => $restoration_price,
        'current_currency' => $current_currency,
        'hero_h1' => $hero_h1,
        'hero_subtitle' => $hero_subtitle,
        'domain_overview' => $domain_overview,
        'domain_stats' => $domain_stats,
        'domain_benefits' => $domain_benefits,
        'domain_ideas' => $domain_ideas,
        'domain_faq' => $domain_faq,
        'domain_policy' => $domain_policy,
        'domain_registry' => $domain_registry,
        'domain_features' => $domain_features,
        'domain_alternatives' => $domain_alternatives,
        'product_id' => get_post_meta($post->ID, '_domain_product_id', true)
    );
}

/**
 * Get domain alternatives based on primary category (Theme version)
 * Renamed to avoid conflict with plugin function
 */
function get_theme_domain_alternatives($post_id, $current_tld) {
    $alternatives = array();
    
    // Get the primary category of the current domain
    $terms = wp_get_post_terms($post_id, 'domain_category', array('number' => 1));
    
    if (!empty($terms) && !is_wp_error($terms)) {
        $primary_category = $terms[0];
        
        // Get other domains in the same category
        $related_domains = new WP_Query(array(
            'post_type' => 'domain',
            'posts_per_page' => 4,
            'post__not_in' => array($post_id),
            'tax_query' => array(
                array(
                    'taxonomy' => 'domain_category',
                    'field' => 'term_id',
                    'terms' => $primary_category->term_id
                )
            ),
            'meta_query' => array(
                array(
                    'key' => '_domain_tld',
                    'compare' => 'EXISTS'
                ),
                array(
                    'key' => '_domain_registration_price',
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        if ($related_domains->have_posts()) {
            while ($related_domains->have_posts()) {
                $related_domains->the_post();
                $alt_tld = get_post_meta(get_the_ID(), '_domain_tld', true);
                $alt_price = get_post_meta(get_the_ID(), '_domain_registration_price', true);
                
                if (!empty($alt_tld) && !empty($alt_price)) {
                    $alt_tld = ltrim($alt_tld, '.');
                    $alternatives[$alt_tld] = array(
                        'name' => '.' . $alt_tld,
                        'price' => $alt_price,
                        'url' => get_permalink()
                    );
                }
            }
            wp_reset_postdata();
        }
    }
    
    // If we don't have enough alternatives, add some defaults
    if (count($alternatives) < 3) {
        $default_alternatives = array(
            'shop' => array('name' => '.shop', 'price' => '9.99'),
            'online' => array('name' => '.online', 'price' => '8.99'),
            'store' => array('name' => '.store', 'price' => '12.99'),
            'com' => array('name' => '.com', 'price' => '13.99'),
            'net' => array('name' => '.net', 'price' => '14.99'),
            'org' => array('name' => '.org', 'price' => '14.99')
        );
        
        foreach ($default_alternatives as $key => $alt) {
            if ($key !== $current_tld && count($alternatives) < 3) {
                if (!isset($alternatives[$key])) {
                    $alternatives[$key] = $alt;
                }
            }
        }
    }
    
    return array_slice($alternatives, 0, 3);
}

/**
 * Get domain by TLD
 */
function get_domain_by_tld($tld) {
    $tld = ltrim($tld, '.');
    
    $query = new WP_Query(array(
        'post_type' => 'domain',
        'posts_per_page' => 1,
        'meta_query' => array(
            array(
                'key' => '_domain_tld',
                'value' => $tld,
                'compare' => '='
            )
        )
    ));
    
    if ($query->have_posts()) {
        return $query->posts[0];
    }
    
    return null;
}

/**
 * Get domain URL by TLD
 */
function get_domain_url_by_tld($tld) {
    $domain = get_domain_by_tld($tld);
    return $domain ? get_permalink($domain->ID) : null;
}