<?php
/**
 * Domain Landing Page - Benefits Section (Updated)
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_features = $args['domain_features'] ?? array();
$domain_benefits = $args['domain_benefits'] ?? '';
$domain_ideas = $args['domain_ideas'] ?? '';
$domain_policy = $args['domain_policy'] ?? array();
$domain_registry = $args['domain_registry'] ?? '';

?>

<!-- Benefits Section -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="layout-container">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <!-- Left - Content -->
            <div>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                    <?php printf(__('Is .%s right for you?', 'yoursite'), esc_html($domain_ext)); ?>
                </h2>
                
                <div class="space-y-8">
                    <!-- Benefits -->
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            <?php printf(__('.%s benefits', 'yoursite'), esc_html($domain_ext)); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            <?php if (!empty($domain_benefits)): ?>
                                <?php echo esc_html($domain_benefits); ?>
                            <?php else: ?>
                                <?php printf(__('Looking for an on-brand domain name for your online store but the .com domain is taken? Try using .%s instead. As a modern top-level domain (TLD), it\'s more readily available and can make a great alternative.', 'yoursite'), esc_html($domain_ext)); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Ideas -->
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            <?php printf(__('Ideas for your .%s domain', 'yoursite'), esc_html($domain_ext)); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            <?php if (!empty($domain_ideas)): ?>
                                <?php echo esc_html($domain_ideas); ?>
                            <?php else: ?>
                                <?php printf(__('A .%s domain is a fresh choice for any business, but especially if your products are sold online. Whether you\'re a big brand or just starting out, a .%s domain is a great way to signal to potential customers that they can shop online with your brand.', 'yoursite'), esc_html($domain_ext), esc_html($domain_ext)); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Domain Policy Information -->
                    <?php if (!empty($domain_policy)): ?>
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            <?php printf(__('.%s domain requirements', 'yoursite'), esc_html($domain_ext)); ?>
                        </h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <?php printf(__('Length: %s-%s chars', 'yoursite'), $domain_policy['min_length'], $domain_policy['max_length']); ?>
                                </span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 <?php echo $domain_policy['numbers_allowed'] ? 'text-green-500' : 'text-red-500'; ?> mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $domain_policy['numbers_allowed'] ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'; ?>"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <?php echo $domain_policy['numbers_allowed'] ? __('Numbers allowed', 'yoursite') : __('No numbers', 'yoursite'); ?>
                                </span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 <?php echo $domain_policy['idn_allowed'] ? 'text-green-500' : 'text-red-500'; ?> mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $domain_policy['idn_allowed'] ? 'M5 13l4 4L19 7' : 'M6 18L18 6M6 6l12 12'; ?>"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <?php echo $domain_policy['idn_allowed'] ? __('IDN supported', 'yoursite') : __('ASCII only', 'yoursite'); ?>
                                </span>
                            </div>
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-gray-600 dark:text-gray-300">
                                    <?php 
                                    $hyphens_text = array(
                                        'none' => __('No hyphens', 'yoursite'),
                                        'middle' => __('Hyphens in middle', 'yoursite'),
                                        'start_middle' => __('Hyphens allowed', 'yoursite'),
                                        'all' => __('Hyphens anywhere', 'yoursite')
                                    );
                                    echo $hyphens_text[$domain_policy['hyphens_allowed']] ?? __('Hyphens in middle', 'yoursite');
                                    ?>
                                </span>
                            </div>
                        </div>
                        <?php if (!empty($domain_registry)): ?>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            <?php printf(__('Registry: %s', 'yoursite'), esc_html($domain_registry)); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right - Benefits List -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-lg border border-gray-200 dark:border-gray-700">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
                    <?php _e('What\'s included', 'yoursite'); ?>
                </h3>
                
                <ul class="space-y-4">
                    <?php foreach ($domain_features as $feature) : ?>
                    <li class="flex items-center">
                        <div class="w-6 h-6 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium"><?php echo esc_html(trim($feature)); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                
                <!-- Pricing Summary -->
                <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2"><?php _e('Pricing Summary', 'yoursite'); ?></h4>
                    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                        <div class="flex justify-between">
                            <span><?php _e('Registration:', 'yoursite'); ?></span>
                            <span class="font-medium"><?php echo esc_html($args['current_currency']['symbol'] . $args['domain_price']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span><?php _e('Renewal:', 'yoursite'); ?></span>
                            <span class="font-medium"><?php echo esc_html($args['current_currency']['symbol'] . $args['domain_renewal_price']); ?></span>
                        </div>
                        <?php if (!empty($args['domain_transfer_price'])): ?>
                        <div class="flex justify-between">
                            <span><?php _e('Transfer:', 'yoursite'); ?></span>
                            <span class="font-medium"><?php echo esc_html($args['current_currency']['symbol'] . $args['domain_transfer_price']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>