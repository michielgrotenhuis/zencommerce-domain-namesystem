<?php
/**
 * Domain Landing Page - Why Choose Section (Updated)
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_overview = $args['domain_overview'] ?? '';
$domain_stats = $args['domain_stats'] ?? '';
?>

<!-- Why Choose Section -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="layout-container">
        <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">
            <?php printf(__('Why choose a .%s domain?', 'yoursite'), esc_html($domain_ext)); ?>
        </h2>
        
        <div class="grid lg:grid-cols-3 gap-12 items-start">
            <!-- Column 1: Image -->
            <div class="relative">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-3xl p-8 shadow-2xl">
                    <div class="relative">
                        <!-- Globe illustration -->
                        <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0 9c-1.657 0-3-4.03-3-9s1.343-9 3-9m0 9v9"></path>
                            </svg>
                        </div>

                        <!-- URL Bar -->
                        <div class="bg-white rounded-lg shadow-md p-3 border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-4 h-4 text-green-500 mr-2">🔒</div>
                                <div class="text-sm text-gray-700 font-mono">yourstore.<?php echo esc_html($domain_ext); ?></div>
                                <div class="ml-auto">
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Overview -->
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                    <?php printf(__('.%s overview', 'yoursite'), esc_html($domain_ext)); ?>
                </h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    <?php if (!empty($domain_overview)): ?>
                        <?php echo esc_html($domain_overview); ?>
                    <?php else: ?>
                        <?php printf(__('Showcase your online shop with a branded .%s domain name. With the surge in popularity of online shopping, the .%s domain extension is here to stay. As a generic top-level domain, anyone in the world can register a .%s domain.', 'yoursite'), 
                                   esc_html($domain_ext), esc_html($domain_ext), esc_html($domain_ext)); ?>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Column 3: Facts -->
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                    <?php printf(__('.%s facts, stats & history', 'yoursite'), esc_html($domain_ext)); ?>
                </h3>
                <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                    <?php if (!empty($domain_stats)): ?>
                        <?php echo esc_html($domain_stats); ?>
                    <?php else: ?>
                        <?php printf(__('Join millions of businesses by registering a .%s domain. Many big brands have taken up .%s domains, making it a trendy domain choice. Google treats all new domain extensions the same, meaning you don\'t have to worry about SEO when it comes to .%s domains.', 'yoursite'), 
                                   esc_html($domain_ext), esc_html($domain_ext), esc_html($domain_ext)); ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>
</section>