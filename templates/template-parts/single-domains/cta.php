<?php
/**
 * Domain Landing Page - CTA Section
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_features = $args['domain_features'] ?? array();

?>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white relative overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute top-20 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-32 right-20 w-24 h-24 bg-cyan-300/20 rounded-full blur-lg animate-pulse delay-1000"></div>
    </div>
    
    <div class="relative z-10 layout-container">
        <div class="text-center max-w-4xl mx-auto">
            <h2 class="text-3xl lg:text-5xl font-bold mb-6">
                <?php _e('Start selling with Zencommerce Today', 'yoursite'); ?>
            </h2>
            <p class="text-xl lg:text-2xl mb-8 opacity-90">
                <?php printf(__('Register your .%s domain and build your online store in minutes, not hours.', 'yoursite'), esc_html($domain_ext)); ?>
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#domain-search" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200 scroll-to-top">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <?php printf(__('Search .%s Domains', 'yoursite'), esc_html($domain_ext)); ?>
                </a>
                
                <a href="<?php echo esc_url(home_url('/signup')); ?>" 
                   class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-semibold text-lg rounded-xl hover:bg-white hover:text-blue-600 transition-all duration-200">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <?php _e('Start Free Trial', 'yoursite'); ?>
                </a>
            </div>
            
            <p class="text-white/70 text-sm mt-6">
                <?php _e('No credit card required • 14-day free trial • Cancel anytime', 'yoursite'); ?>
            </p>
        </div>
    </div>
</section>