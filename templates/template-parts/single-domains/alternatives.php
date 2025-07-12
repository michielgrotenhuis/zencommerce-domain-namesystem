<?php
/**
 * Domain Landing Page - Domain Alternatives Section (Updated)
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_alternatives = $args['domain_alternatives'] ?? array();
$current_currency = $args['current_currency'] ?? array('code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar');

// Only show section if we have alternatives
if (empty($domain_alternatives)) {
    return;
}
?>

<!-- Domain Alternatives Section -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="layout-container">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                <?php printf(__('Find a .%s domain alternative', 'yoursite'), esc_html($domain_ext)); ?>
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-300">
                <?php _e('Couldn\'t find your desired domain name? Try these similar extensions for your brand.', 'yoursite'); ?>
            </p>
        </div>
        
        <div class="grid md:grid-cols-<?php echo min(count($domain_alternatives), 3); ?> gap-8 max-w-5xl mx-auto">
            <?php foreach ($domain_alternatives as $alt_key => $alt_data) : ?>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-8 text-center hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-300 border border-gray-200 dark:border-gray-600 group hover:shadow-lg">
                <div class="mb-4">
                    <!-- Domain Icon -->
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mx-auto flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0 9c-1.657 0-3-4.03-3-9s1.343-9 3-9m0 9v9"></path>
                        </svg>
                    </div>
                </div>
                
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    <?php echo esc_html($alt_data['name']); ?>
                </h3>
                
                <p class="text-gray-600 dark:text-gray-300 mb-6 leading-relaxed">
                    <?php 
                    // Get domain-specific descriptions
                    $alt_tld = ltrim($alt_data['name'], '.');
                    $descriptions = array(
                        'shop' => __('Perfect for retail and e-commerce businesses. Show customers you\'re ready to sell online.', 'yoursite'),
                        'online' => __('Ideal for any business with an online presence. Modern and professional.', 'yoursite'),
                        'store' => __('Great for retail businesses and online shops. Clear and commercial.', 'yoursite'),
                        'boutique' => __('Sophisticated choice for luxury and high-end brands.', 'yoursite'),
                        'com' => __('The classic choice. Universal and trusted by customers worldwide.', 'yoursite'),
                        'net' => __('Professional option for tech companies and service providers.', 'yoursite'),
                        'org' => __('Traditional choice for organizations and non-profits.', 'yoursite'),
                        'biz' => __('Business-focused domain for companies and entrepreneurs.', 'yoursite'),
                        'co' => __('Short and modern. Perfect for companies and startups.', 'yoursite'),
                        'io' => __('Popular with tech startups and development companies.', 'yoursite'),
                        'app' => __('Ideal for mobile apps and software applications.', 'yoursite'),
                        'tech' => __('Perfect for technology companies and startups.', 'yoursite')
                    );
                    
                    echo $descriptions[$alt_tld] ?? sprintf(__('Perfect alternative for your %s business.', 'yoursite'), esc_html($alt_data['name']));
                    ?>
                </p>
                
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-6">
                    <?php echo esc_html($current_currency['symbol'] . $alt_data['price']); ?>
                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400 block mt-1">
                        <?php _e('per year', 'yoursite'); ?>
                    </span>
                </div>
                
                <?php 
                // Get the URL for this alternative domain
                $alt_url = isset($alt_data['url']) ? $alt_data['url'] : get_domain_url_by_tld($alt_tld);
                if (empty($alt_url)) {
                    $alt_url = home_url('/domains/' . str_replace('.', '-', $alt_tld));
                }
                ?>
                
                <a href="<?php echo esc_url($alt_url); ?>" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-all duration-200 group-hover:shadow-md w-full">
                    <?php printf(__('Get %s Domain', 'yoursite'), esc_html($alt_data['name'])); ?>
                    <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- More Alternatives CTA -->
        <div class="text-center mt-12">
            <p class="text-gray-600 dark:text-gray-300 mb-4">
                <?php _e('Need more options?', 'yoursite'); ?>
            </p>
            <a href="<?php echo esc_url(home_url('/domains')); ?>" 
               class="inline-flex items-center px-6 py-3 bg-transparent border-2 border-blue-600 text-blue-600 dark:text-blue-400 dark:border-blue-400 font-semibold rounded-lg hover:bg-blue-600 hover:text-white dark:hover:bg-blue-400 dark:hover:text-gray-900 transition-all duration-200">
                <?php _e('Browse All Domain Extensions', 'yoursite'); ?>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                </svg>
            </a>
        </div>
    </div>
</section>