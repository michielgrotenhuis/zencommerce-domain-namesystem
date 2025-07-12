<?php
/**
 * Domain Landing Page - Hero Section (Updated)
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_price = $args['domain_price'] ?? '12.99';
$domain_renewal_price = $args['domain_renewal_price'] ?? '14.99';
$current_currency = $args['current_currency'] ?? array('code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar');
$hero_h1 = $args['hero_h1'] ?? '';
$hero_subtitle = $args['hero_subtitle'] ?? '';
?>

<!-- Hero Section -->
<section class="domain-hero-section relative overflow-hidden">
    <!-- Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800"></div>
    <div class="absolute inset-0 bg-gradient-to-tl from-purple-600/20 via-transparent to-cyan-400/10"></div>
    
    <!-- Floating Elements -->
    <div class="absolute inset-0 overflow-hidden opacity-20">
        <div class="absolute top-20 left-10 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-32 right-20 w-24 h-24 bg-cyan-300/20 rounded-full blur-lg animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-purple-300/15 rounded-full blur-md animate-pulse delay-500"></div>
    </div>
    
    <div class="relative z-10 py-20 lg:py-32">
        <div class="layout-container">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                
                <!-- Left Content -->
                <div class="text-center lg:text-left text-white">
                    <!-- Badge -->
                    <div class="inline-flex items-center rounded-full px-6 py-3 mb-6 text-sm font-bold bg-gradient-to-r from-yellow-400 to-orange-400 text-gray-900 shadow-xl border-2 border-yellow-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0 9c-1.657 0-3-4.03-3-9s1.343-9 3-9m0 9v9"></path>
                        </svg>
                        <?php printf(__('%s domain registration', 'yoursite'), '.'.esc_html($domain_ext)); ?>
                    </div>
                    
                    <!-- Main Heading -->
                    <h1 class="text-4xl lg:text-6xl font-bold mb-6 leading-tight">
                        <?php if (!empty($hero_h1)): ?>
                            <?php 
                            // Check if the title contains the TLD, if not add styling
                            if (strpos($hero_h1, '.' . $domain_ext) !== false) {
                                echo str_replace('.' . $domain_ext, '<span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-orange-400">.' . esc_html($domain_ext) . '</span>', esc_html($hero_h1));
                            } else {
                                echo esc_html($hero_h1);
                            }
                            ?>
                        <?php else: ?>
                            <?php echo 'Find your perfect '; ?>
                            <span class="block text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-orange-400">
                                <?php echo '.'.esc_html($domain_ext).' domain'; ?>
                            </span>
                        <?php endif; ?>
                    </h1>
                    
                    <!-- Subheading -->
                    <p class="text-xl lg:text-2xl mb-8 leading-relaxed opacity-90">
                        <?php if (!empty($hero_subtitle)): ?>
                            <?php echo esc_html($hero_subtitle); ?>
                        <?php else: ?>
                            <?php printf(__('Showcase your online shop with a branded .%s domain name. Perfect for e-commerce stores, retail brands, and online businesses.', 'yoursite'), esc_html($domain_ext)); ?>
                        <?php endif; ?>
                    </p>
                    
                    <!-- Domain Search Form -->
                    <?php get_template_part('template-parts/domains-single/search-form', null, $args); ?>
                    
                    <!-- Trust Indicators -->
                    <?php get_template_part('template-parts/domains-single/trust-indicators'); ?>
                </div>
                
                <!-- Right Visual -->
                <?php get_template_part('template-parts/domains-single/hero-visual', null, $args); ?>
            </div>
        </div>
    </div>
</section>