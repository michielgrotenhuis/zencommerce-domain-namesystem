<?php
/**
 * Domain Archive Template
 * 
 * Template for displaying all domains in archive format
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="domain-archive">
    <!-- Archive Header -->
    <section class="domain-archive-header">
        <div class="domain-container">
            <h1 class="domain-archive-title">
                <?php _e('Browse All Domain Extensions', 'domain-system'); ?>
            </h1>
            <p class="domain-archive-description">
                <?php _e('Discover the perfect domain extension for your business or personal website. Compare features, pricing, and registration requirements.', 'domain-system'); ?>
            </p>
            
            <!-- Archive Stats -->
            <div class="domain-archive-stats">
                <?php
                $total_domains = wp_count_posts('domain')->publish;
                $categories = get_terms(['taxonomy' => 'domain_category', 'hide_empty' => true]);
                $avg_price = get_average_domain_price();
                ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo number_format($total_domains); ?></span>
                    <span class="stat-label"><?php _e('Domain Extensions', 'domain-system'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($categories); ?></span>
                    <span class="stat-label"><?php _e('Categories', 'domain-system'); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo format_domain_price($avg_price); ?></span>
                    <span class="stat-label"><?php _e('Average Price', 'domain-system'); ?></span>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filters -->
    <section class="domain-search-section">
        <div class="domain-container">
            <form class="domain-search-form" method="get" action="<?php echo get_domain_archive_url(); ?>">
                <div class="search-input-wrapper">
                    <input type="text" 
                           class="domain-search-input" 
                           name="search" 
                           value="<?php echo esc_attr(get_query_var('search')); ?>" 
                           placeholder="<?php _e('Search domains (e.g., .com, business, technology)', 'domain-system'); ?>" />
                    <button type="submit" class="domain-search-button">
                        <span class="search-icon">🔍</span>
                        <?php _e('Search', 'domain-system'); ?>
                    </button>
                </div>
            </form>
            
            <!-- Advanced Filters -->
            <div class="domain-filters">
                <div class="filter-toggle">
                    <button class="domain-filter-toggle" type="button">
                        <span class="filter-icon">⚙️</span>
                        <?php _e('Filters', 'domain-system'); ?>
                        <span class="toggle-arrow">▼</span>
                    </button>
                </div>
                
                <div class="filter-options" style="display: none;">
                    <form class="filter-form" method="get" action="<?php echo get_domain_archive_url(); ?>">
                        <?php if (!empty(get_query_var('search'))) : ?>
                            <input type="hidden" name="search" value="<?php echo esc_attr(get_query_var('search')); ?>" />
                        <?php endif; ?>
                        
                        <div class="filter-grid">
                            <!-- Category Filter -->
                            <div class="filter-item">
                                <label for="category-filter"><?php _e('Category', 'domain-system'); ?></label>
                                <select id="category-filter" name="category" class="domain-filter-select" data-filter="category">
                                    <option value=""><?php _e('All Categories', 'domain-system'); ?></option>
                                    <?php
                                    $categories = get_domain_categories();
                                    $current_category = get_query_var('category');
                                    foreach ($categories as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_category, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Type Filter -->
                            <div class="filter-item">
                                <label for="type-filter"><?php _e('Type', 'domain-system'); ?></label>
                                <select id="type-filter" name="type" class="domain-filter-select" data-filter="type">
                                    <option value=""><?php _e('All Types', 'domain-system'); ?></option>
                                    <?php
                                    $types = get_domain_types();
                                    $current_type = get_query_var('type');
                                    foreach ($types as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_type, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Price Range Filter -->
                            <div class="filter-item">
                                <label for="price-range-filter"><?php _e('Price Range', 'domain-system'); ?></label>
                                <select id="price-range-filter" name="price_range" class="domain-filter-select" data-filter="price_range">
                                    <option value=""><?php _e('All Prices', 'domain-system'); ?></option>
                                    <?php
                                    $current_price_range = get_query_var('price_range');
                                    $price_ranges = [
                                        'under-10' => __('Under $10', 'domain-system'),
                                        '10-25' => __('$10 - $25', 'domain-system'),
                                        '25-50' => __('$25 - $50', 'domain-system'),
                                        '50-100' => __('$50 - $100', 'domain-system'),
                                        'over-100' => __('Over $100', 'domain-system')
                                    ];
                                    foreach ($price_ranges as $value => $label) :
                                    ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($current_price_range, $value); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Registry Filter -->
                            <div class="filter-item">
                                <label for="registry-filter"><?php _e('Registry', 'domain-system'); ?></label>
                                <select id="registry-filter" name="registry" class="domain-filter-select" data-filter="registry">
                                    <option value=""><?php _e('All Registries', 'domain-system'); ?></option>
                                    <?php
                                    $registries = get_unique_domain_registries();
                                    $current_registry = get_query_var('registry');
                                    foreach ($registries as $registry) :
                                    ?>
                                        <option value="<?php echo esc_attr($registry); ?>" <?php selected($current_registry, $registry); ?>>
                                            <?php echo esc_html($registry); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="filter-actions">
                            <button type="submit" class="domain-btn domain-btn-primary">
                                <?php _e('Apply Filters', 'domain-system'); ?>
                            </button>
                            <a href="<?php echo get_domain_archive_url(); ?>" class="domain-btn domain-btn-outline">
                                <?php _e('Clear All', 'domain-system'); ?>
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Sort and View Options -->
    <section class="domain-toolbar">
        <div class="domain-container">
            <div class="toolbar-content">
                <div class="results-info">
                    <?php
                    global $wp_query;
                    $total_found = $wp_query->found_posts;
                    $current_page = max(1, get_query_var('paged'));
                    $per_page = get_query_var('posts_per_page', get_domain_system_settings('posts_per_page', 10));
                    $start = (($current_page - 1) * $per_page) + 1;
                    $end = min($total_found, $current_page * $per_page);
                    ?>
                    <span class="results-count">
                        <?php
                        printf(
                            __('Showing %d-%d of %d domains', 'domain-system'),
                            $start,
                            $end,
                            $total_found
                        );
                        ?>
                    </span>
                    
                    <?php if (!empty(get_query_var('search'))) : ?>
                        <span class="search-query">
                            <?php printf(__('for "%s"', 'domain-system'), '<strong>' . esc_html(get_query_var('search')) . '</strong>'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="toolbar-actions">
                    <!-- Sort Options -->
                    <div class="sort-wrapper">
                        <label for="sort-select"><?php _e('Sort by:', 'domain-system'); ?></label>
                        <select id="sort-select" class="domain-sort">
                            <?php
                            $current_sort = get_query_var('orderby', 'title');
                            $sort_options = [
                                'title' => __('Name (A-Z)', 'domain-system'),
                                'title-desc' => __('Name (Z-A)', 'domain-system'),
                                'price-asc' => __('Price (Low to High)', 'domain-system'),
                                'price-desc' => __('Price (High to Low)', 'domain-system'),
                                'date' => __('Newest First', 'domain-system'),
                                'popularity' => __('Most Popular', 'domain-system')
                            ];
                            foreach ($sort_options as $value => $label) :
                            ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($current_sort, $value); ?>>
                                    <?php echo esc_html($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- View Toggle -->
                    <div class="view-toggle">
                        <?php $current_view = get_domain_system_settings('archive_layout', 'grid'); ?>
                        <button class="view-btn <?php echo $current_view === 'grid' ? 'active' : ''; ?>" data-view="grid" title="<?php _e('Grid View', 'domain-system'); ?>">
                            <span class="view-icon">⊞</span>
                        </button>
                        <button class="view-btn <?php echo $current_view === 'list' ? 'active' : ''; ?>" data-view="list" title="<?php _e('List View', 'domain-system'); ?>">
                            <span class="view-icon">☰</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Domains Grid/List -->
    <section class="domain-content">
        <div class="domain-container">
            <?php if (have_posts()) : ?>
                <div class="domain-grid <?php echo esc_attr($current_view); ?>-view" id="domains-container">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('templates/content', 'domain'); ?>
                    <?php endwhile; ?>
                </div>
                
                <!-- Load More Button (if using AJAX pagination) -->
                <?php if ($wp_query->max_num_pages > 1) : ?>
                    <div class="load-more-wrapper">
                        <button class="domain-btn domain-btn-outline load-more-domains" 
                                data-page="<?php echo $current_page + 1; ?>" 
                                data-max-pages="<?php echo $wp_query->max_num_pages; ?>">
                            <?php _e('Load More Domains', 'domain-system'); ?>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <nav class="domain-pagination" role="navigation" aria-label="<?php _e('Domains pagination', 'domain-system'); ?>">
                    <?php
                    echo paginate_links([
                        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                        'format' => '?paged=%#%',
                        'current' => $current_page,
                        'total' => $wp_query->max_num_pages,
                        'prev_text' => __('← Previous', 'domain-system'),
                        'next_text' => __('Next →', 'domain-system'),
                        'mid_size' => 2,
                        'end_size' => 1,
                        'add_args' => array_filter([
                            'search' => get_query_var('search'),
                            'category' => get_query_var('category'),
                            'type' => get_query_var('type'),
                            'price_range' => get_query_var('price_range'),
                            'registry' => get_query_var('registry'),
                            'orderby' => get_query_var('orderby')
                        ])
                    ]);
                    ?>
                </nav>
                
            <?php else : ?>
                <!-- No Results -->
                <div class="no-domains-found">
                    <div class="no-results-content">
                        <h2><?php _e('No domains found', 'domain-system'); ?></h2>
                        
                        <?php if (!empty(get_query_var('search')) || !empty(get_query_var('category'))) : ?>
                            <p><?php _e('Sorry, no domains match your search criteria. Try adjusting your filters or search terms.', 'domain-system'); ?></p>
                            
                            <div class="no-results-actions">
                                <a href="<?php echo get_domain_archive_url(); ?>" class="domain-btn domain-btn-primary">
                                    <?php _e('View All Domains', 'domain-system'); ?>
                                </a>
                                <button class="domain-btn domain-btn-outline clear-filters">
                                    <?php _e('Clear Filters', 'domain-system'); ?>
                                </button>
                            </div>
                            
                            <!-- Suggested Domains -->
                            <?php
                            $suggested_domains = get_posts([
                                'post_type' => 'domain',
                                'posts_per_page' => 3,
                                'orderby' => 'rand'
                            ]);
                            
                            if ($suggested_domains) :
                            ?>
                                <div class="suggested-domains">
                                    <h3><?php _e('You might be interested in:', 'domain-system'); ?></h3>
                                    <div class="suggestions-grid">
                                        <?php foreach ($suggested_domains as $domain_post) : ?>
                                            <div class="suggestion-item">
                                                <a href="<?php echo get_permalink($domain_post->ID); ?>">
                                                    <span class="suggestion-tld"><?php echo esc_html(get_domain_meta($domain_post->ID, 'tld')); ?></span>
                                                    <span class="suggestion-title"><?php echo esc_html($domain_post->post_title); ?></span>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        <?php else : ?>
                            <p><?php _e('No domains have been added yet. Please check back later.', 'domain-system'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Featured Categories -->
    <?php if (!have_posts() || get_query_var('paged') < 2) : ?>
        <section class="featured-categories">
            <div class="domain-container">
                <h2><?php _e('Browse by Category', 'domain-system'); ?></h2>
                <div class="categories-grid">
                    <?php
                    $featured_categories = [
                        'business-professional' => '💼',
                        'e-commerce-retail' => '🛒',
                        'technology' => '💻',
                        'creative-arts' => '🎨',
                        'education' => '📚',
                        'health-medical' => '⚕️'
                    ];
                    
                    $categories = get_domain_categories();
                    
                    foreach ($featured_categories as $cat_key => $icon) :
                        if (!isset($categories[$cat_key])) continue;
                        
                        $category_url = add_query_arg('category', $cat_key, get_domain_archive_url());
                        $category_count = get_domains_count_by_category($cat_key);
                    ?>
                        <a href="<?php echo esc_url($category_url); ?>" class="category-card">
                            <div class="category-icon"><?php echo $icon; ?></div>
                            <div class="category-info">
                                <h3><?php echo esc_html($categories[$cat_key]); ?></h3>
                                <span class="category-count">
                                    <?php printf(_n('%d domain', '%d domains', $category_count, 'domain-system'), $category_count); ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>
</div>

<!-- Archive Page Schema -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "<?php _e('Domain Extensions Directory', 'domain-system'); ?>",
    "description": "<?php _e('Browse and compare domain extensions for your website registration needs.', 'domain-system'); ?>",
    "url": "<?php echo get_domain_archive_url(); ?>",
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": "<?php echo $total_found; ?>",
        "itemListElement": [
            <?php
            $schema_items = [];
            if (have_posts()) {
                rewind_posts();
                $position = 1;
                while (have_posts()) {
                    the_post();
                    $tld = get_domain_meta(get_the_ID(), 'tld');
                    $price = get_domain_meta(get_the_ID(), 'registration_price');
                    
                    $schema_items[] = sprintf('
                    {
                        "@type": "ListItem",
                        "position": %d,
                        "item": {
                            "@type": "Product",
                            "name": "%s",
                            "description": "%s",
                            "url": "%s",
                            "offers": {
                                "@type": "Offer",
                                "price": "%s",
                                "priceCurrency": "USD"
                            }
                        }
                    }',
                        $position++,
                        esc_js(get_the_title()),
                        esc_js(wp_trim_words(get_the_excerpt(), 20)),
                        esc_url(get_permalink()),
                        floatval($price)
                    );
                    
                    if ($position > 10) break; // Limit schema items
                }
                rewind_posts();
            }
            echo implode(',', $schema_items);
            ?>
        ]
    }
}
</script>

<?php get_footer(); ?>

<?php
/**
 * Helper functions for archive template
 */

/**
 * Get average domain price
 */
function get_average_domain_price() {
    global $wpdb;
    
    $avg_price = wp_cache_get('domain_average_price', 'domain_system');
    if ($avg_price === false) {
        $avg_price = $wpdb->get_var("
            SELECT AVG(CAST(meta_value AS DECIMAL(10,2))) 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_domain_registration_price' 
            AND p.post_type = 'domain' 
            AND p.post_status = 'publish'
            AND CAST(meta_value AS DECIMAL(10,2)) > 0
        ");
        
        $avg_price = $avg_price ?: 0;
        wp_cache_set('domain_average_price', $avg_price, 'domain_system', 3600);
    }
    
    return $avg_price;
}

/**
 * Get unique registries from published domains
 */
function get_unique_domain_registries() {
    global $wpdb;
    
    $registries = wp_cache_get('domain_unique_registries', 'domain_system');
    if ($registries === false) {
        $registries = $wpdb->get_col("
            SELECT DISTINCT meta_value 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_domain_registry' 
            AND p.post_type = 'domain' 
            AND p.post_status = 'publish'
            AND meta_value != ''
            ORDER BY meta_value ASC
        ");
        
        $registries = $registries ?: [];
        wp_cache_set('domain_unique_registries', $registries, 'domain_system', 3600);
    }
    
    return $registries;
}

/**
 * Get domain count by category
 */
function get_domains_count_by_category($category) {
    global $wpdb;
    
    $cache_key = 'domain_count_' . $category;
    $count = wp_cache_get($cache_key, 'domain_system');
    
    if ($count === false) {
        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_domain_category' 
            AND pm.meta_value = %s
            AND p.post_type = 'domain' 
            AND p.post_status = 'publish'
        ", $category));
        
        $count = intval($count);
        wp_cache_set($cache_key, $count, 'domain_system', 1800);
    }
    
    return $count;
}
?>