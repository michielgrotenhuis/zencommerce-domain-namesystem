<?php
/**
 * Template part for domains single - Content sections
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Get data from args
// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$current_currency = $args['current_currency'] ?? array('code' => 'USD', 'symbol' => '$');
$domain_alternatives = $args['domain_alternatives'] ?? array();
$domain_features = $args['domain_features'] ?? array();
?>

<!-- Page Content Section (if there's content in the editor) -->
<?php if (get_the_content()) : ?>
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="layout-container">
        <div class="max-w-4xl mx-auto prose prose-lg dark:prose-invert">
            <?php the_content(); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Why Choose Section -->
<section class="py-20 bg-gray-50 dark:bg-gray-900">
    <div class="layout-container">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            
            <!-- Left - Image Placeholder -->
            <div class="relative">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-3xl p-8 shadow-2xl">
                    <div class="relative">
                        <!-- Globe illustration -->
                        <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0 9c-1.657 0-3-4.03-3-9s1.343-9 3-9m0 9v9"></path>
                            </svg>
                        </div>
                        
                        <!-- URL Bar Mockup -->
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
            
            <!-- Right - Content -->
            <div>
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-6">
                    <?php printf(__('Why choose a .%s domain?', 'yoursite'), esc_html($domain_ext)); ?>
                </h2>
                
                <div class="space-y-6">
                    <!-- Overview -->
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                            <?php printf(__('.%s overview', 'yoursite'), esc_html($domain_ext)); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            <?php 
                            $domain_overview = get_post_meta(get_the_ID(), '_domain_overview', true);
                            if (empty($domain_overview)) {
                                printf(__('Showcase your online shop with a branded .%s domain name. With the surge in popularity of online shopping, the .%s domain extension is here to stay. As a generic top-level domain, anyone in the world can register a .%s domain.', 'yoursite'), 
                                       esc_html($domain_ext), esc_html($domain_ext), esc_html($domain_ext));
                            } else {
                                echo esc_html($domain_overview);
                            }
                            ?>
                        </p>
                    </div>
                    
                    <!-- Facts -->
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                            <?php printf(__('.%s facts, stats & history', 'yoursite'), esc_html($domain_ext)); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            <?php 
                            $domain_facts = get_post_meta(get_the_ID(), '_domain_facts', true);
                            if (empty($domain_facts)) {
                                printf(__('Join millions of businesses by registering a .%s domain. Many big brands have taken up .%s domains, making it a trendy domain choice. Google treats all new domain extensions the same, meaning you don\'t have to worry about SEO when it comes to .%s domains.', 'yoursite'), 
                                       esc_html($domain_ext), esc_html($domain_ext), esc_html($domain_ext));
                            } else {
                                echo esc_html($domain_facts);
                            }
                            ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Domain Alternatives Section -->
<?php if (!empty($domain_alternatives)) : ?>
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
        
        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php foreach ($domain_alternatives as $alt_key => $alt_data) : ?>
            <div class="bg-gray-50 dark:bg-gray-700 rounded-2xl p-8 text-center hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-300 border border-gray-200 dark:border-gray-600">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    <?php echo esc_html($alt_data['name']); ?>
                </h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    <?php 
                    switch ($alt_key) {
                        case 'shop':
                            _e('Couldn\'t find your desired domain name? Try registering .shop domain for your brand.', 'yoursite');
                            break;
                        case 'online':
                            _e('Register your brand on a similar TLD with .online.', 'yoursite');
                            break;
                        case 'boutique':
                            _e('Secure your brand on a different TLD with .boutique.', 'yoursite');
                            break;
                        default:
                            printf(__('Perfect alternative for your %s business.', 'yoursite'), esc_html($alt_data['name']));
                    }
                    ?>
                </p>
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-4">
                    <?php echo esc_html($current_currency['symbol'] . $alt_data['price']); ?>
                </div>
                <a href="<?php echo esc_url(home_url('/domains/' . $alt_key)); ?>" 
                   class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <?php printf(__('Buy %s Domain', 'yoursite'), esc_html($alt_data['name'])); ?>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

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
                            <?php 
                            $domain_benefits = get_post_meta(get_the_ID(), '_domain_benefits', true);
                            if (empty($domain_benefits)) {
                                printf(__('Looking for an on-brand domain name for your online store but the .com domain is taken? Try using .%s instead. As a modern top-level domain (TLD), it\'s more readily available and can make a great alternative.', 'yoursite'), esc_html($domain_ext));
                            } else {
                                echo esc_html($domain_benefits);
                            }
                            ?>
                        </p>
                    </div>
                    
                    <!-- Ideas -->
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                            <?php printf(__('Ideas for your .%s domain', 'yoursite'), esc_html($domain_ext)); ?>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-300 leading-relaxed">
                            <?php 
                            $domain_ideas = get_post_meta(get_the_ID(), '_domain_ideas', true);
                            if (empty($domain_ideas)) {
                                printf(__('A .%s domain is a fresh choice for any business, but especially if your products are sold online. Whether you\'re a big brand or just starting out, a .%s domain is a great way to signal to potential customers that they can shop online with your brand.', 'yoursite'), esc_html($domain_ext), esc_html($domain_ext));
                            } else {
                                echo esc_html($domain_ideas);
                            }
                            ?>
                        </p>
                    </div>
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
            </div>
        </div>
    </div>
</section>