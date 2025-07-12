<?php
/**
 * Domain Landing Page - Registration Steps Section
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args
$domain_ext = $args['domain_ext'] ?? 'store';

// Get custom registration steps from post meta, or use defaults
$registration_steps = get_post_meta(get_the_ID(), '_registration_steps', true);

// Default registration steps if none are set
if (empty($registration_steps)) {
    $registration_steps = array(
        array(
            'number' => '1',
            'title' => __('Check your domain availability', 'yoursite'),
            'description' => sprintf(__('Search for your perfect .%s domain name using our domain checker. We\'ll show you available options instantly.', 'yoursite'), $domain_ext),
            'icon' => 'search'
        ),
        array(
            'number' => '2',
            'title' => __('Add your shop and domain ownership details', 'yoursite'),
            'description' => __('Provide your contact information and choose your registration period. We\'ll handle all the technical setup for you.', 'yoursite'),
            'icon' => 'user'
        ),
        array(
            'number' => '3',
            'title' => __('Your domain name and online store are ready', 'yoursite'),
            'description' => __('Start building your online presence immediately. Your domain is active and ready to use with full support included.', 'yoursite'),
            'icon' => 'check'
        )
    );
}

// Ensure steps is properly formatted as array
if (!is_array($registration_steps)) {
    $registration_steps = array();
}

// Function to get SVG icon
function get_step_icon($icon_type) {
    $icons = array(
        'search' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>',
        'user' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                   </svg>',
        'check' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>',
        'globe' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                    </svg>',
        'settings' => '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                       </svg>'
    );
    
    return $icons[$icon_type] ?? $icons['check'];
}
?>

<!-- Registration Steps Section -->
<section class="py-20 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <div class="layout-container">
        <div class="max-w-6xl mx-auto">
            
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    <?php _e('How to register a domain with Zencommerce', 'yoursite'); ?>
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto">
                    <?php printf(__('Get your .%s domain up and running in just 3 simple steps. It takes less than 5 minutes to get started.', 'yoursite'), esc_html($domain_ext)); ?>
                </p>
            </div>
            
            <!-- Steps Grid -->
            <?php if (!empty($registration_steps)) : ?>
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                <?php foreach ($registration_steps as $index => $step) : ?>
                    <?php if (!empty($step['title'])) : ?>
                    <div class="relative text-center group">
                        
                        <!-- Step Number & Icon -->
                        <div class="relative mb-6">
                            <!-- Background Circle -->
                            <div class="w-20 h-20 mx-auto bg-white dark:bg-gray-800 rounded-full shadow-lg border-4 border-blue-200 dark:border-blue-800 flex items-center justify-center group-hover:border-blue-400 dark:group-hover:border-blue-600 transition-colors duration-300">
                                <!-- Step Number (Background) -->
                                <div class="absolute -top-2 -right-2 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-lg">
                                    <?php echo esc_html($step['number'] ?? ($index + 1)); ?>
                                </div>
                                <!-- Icon -->
                                <div class="text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors duration-300">
                                    <?php echo get_step_icon($step['icon'] ?? 'check'); ?>
                                </div>
                            </div>
                            
                            <!-- Connector Line (except for last item) -->
                            <?php if ($index < count($registration_steps) - 1) : ?>
                            <div class="hidden md:block absolute top-10 left-1/2 w-full h-0.5 bg-gradient-to-r from-blue-300 to-blue-200 dark:from-blue-700 dark:to-blue-800 transform translate-x-1/2 z-0"></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Step Content -->
                        <div class="relative z-10">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                                <?php echo esc_html($step['title']); ?>
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                                <?php echo esc_html($step['description'] ?? ''); ?>
                            </p>
                        </div>
                        
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- CTA Section -->
            <div class="text-center mt-16">
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-xl border border-gray-200 dark:border-gray-700 max-w-2xl mx-auto">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        <?php printf(__('Ready to get your .%s domain?', 'yoursite'), esc_html($domain_ext)); ?>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        <?php _e('Join thousands of businesses who trust Zencommerce for their domain needs.', 'yoursite'); ?>
                    </p>
                    
                    
                    <!-- Domain Search Form -->
                    <?php get_template_part('template-parts/domains-single/search-form', null, $args); ?>
                </div>
            </div>
        </div>
    </div>
</section>