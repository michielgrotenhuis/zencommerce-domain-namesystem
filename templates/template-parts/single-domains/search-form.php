<?php
/**
 * Domain Landing Page - Search Form
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_features = $args['domain_features'] ?? array();
$domain_price = $args['domain_price'] ?? '12.99';
$domain_renewal_price = $args['domain_renewal_price'] ?? '14.99';
$current_currency = $args['current_currency'] ?? array('code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar');
?>

<!-- Domain Search Form with Upmind Integration -->
<div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 mb-8 border border-white/20">
    <!-- Traditional Search Form (fallback) -->
    <div id="traditional-search" class="hidden">
        <form class="domain-search-form flex flex-col sm:flex-row gap-4" action="#" method="get">
            <div class="flex-1 relative">
                <input type="text" 
                       name="domain" 
                       id="domain-search-fallback" 
                       class="w-full px-6 py-4 text-lg bg-white rounded-lg border-2 border-transparent focus:border-blue-500 focus:outline-none transition-colors text-gray-900"
                       placeholder="<?php printf(__('yourstore.%s', 'yoursite'), esc_attr($domain_ext)); ?>"
                       required>
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500">
                    .<?php echo esc_html($domain_ext); ?>
                </div>
            </div>
            <button type="submit" 
                    class="px-8 py-4 bg-gradient-to-r from-yellow-400 to-orange-500 text-gray-900 font-bold rounded-lg hover:from-yellow-500 hover:to-orange-600 transition-all duration-200 transform hover:scale-105 shadow-lg">
                <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <?php _e('Search Domain', 'yoursite'); ?>
            </button>
        </form>
    </div>

    <!-- Upmind Domain Search Widget -->
    <div id="upmind-domain-search" class="upmind-domain-widget">
        <!-- Simple Upmind Integration -->
        <upm-dac
            order-config-url="https://my.zencommerce.net/order/product"
            currency-code="<?php echo esc_attr($current_currency['code']); ?>"
        ></upm-dac>
        
        <!-- Fallback loading indicator -->
        <div class="widget-loading text-center py-8">
            <div class="inline-flex items-center text-white/80">
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <?php _e('Loading domain search...', 'yoursite'); ?>
            </div>
        </div>
    </div>
    
    <!-- Pricing Info -->
    <div class="mt-4 text-center text-white/80">
        <span class="text-2xl font-bold text-yellow-300"><?php echo esc_html($current_currency['symbol'] . $domain_price); ?></span>
        <span class="text-sm"><?php _e('/first year', 'yoursite'); ?></span>
        <span class="mx-2">•</span>
        <span class="text-sm"><?php printf(__('Renews at %s%s/year', 'yoursite'), esc_html($current_currency['symbol']), esc_html($domain_renewal_price)); ?></span>
    </div>
</div>