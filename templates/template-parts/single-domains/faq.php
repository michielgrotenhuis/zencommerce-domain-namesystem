<?php
/**
 * Domain Landing Page - FAQ Section (Updated)
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_faq = $args['domain_faq'] ?? array();
$domain_price = $args['domain_price'] ?? '12.99';

// If no custom FAQ, provide defaults
if (empty($domain_faq)) {
    $domain_faq = array(
        array(
            'question' => sprintf(__('What is a .%s domain?', 'yoursite'), $domain_ext),
            'answer' => sprintf(__('A .%s domain is a top-level domain (TLD) that\'s perfect for businesses, especially those with an online presence. It\'s a modern, memorable alternative to traditional domains like .com.', 'yoursite'), $domain_ext)
        ),
        array(
            'question' => sprintf(__('Why should I choose a .%s domain?', 'yoursite'), $domain_ext),
            'answer' => sprintf(__'.%s domains are more readily available than .com domains, making it easier to get the exact domain name you want. They also clearly communicate your business focus and are trusted by search engines.', 'yoursite'), $domain_ext)
        ),
        array(
            'question' => sprintf(__('How much does a .%s domain cost?', 'yoursite'), $domain_ext),
            'answer' => sprintf(__('Our .%s domains start at just $%s per year, with transparent pricing and no hidden fees. You\'ll also get free WHOIS privacy protection and DNS management included.', 'yoursite'), $domain_ext, $domain_price)
        )
    );
}

// Only show section if we have FAQ items
if (empty($domain_faq)) {
    return;
}
?>

<!-- FAQ Section -->
<section class="py-20 bg-white dark:bg-gray-800">
    <div class="layout-container">
        <div class="max-w-4xl mx-auto">
            
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    <?php printf(__('Frequently Asked Questions about .%s domains', 'yoursite'), esc_html($domain_ext)); ?>
                </h2>
                <p class="text-xl text-gray-600 dark:text-gray-300">
                    <?php _e('Got questions? We\'ve got answers.', 'yoursite'); ?>
                </p>
            </div>
            
            <!-- FAQ Items -->
            <div class="space-y-6">
                <?php foreach ($domain_faq as $index => $faq_item) : ?>
                    <?php if (!empty($faq_item['question']) && !empty($faq_item['answer'])) : ?>
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <button 
                            class="w-full px-8 py-6 text-left flex justify-between items-center hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-200 faq-toggle"
                            data-target="faq-<?php echo esc_attr($index); ?>"
                            aria-expanded="false"
                        >
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white pr-4">
                                <?php echo esc_html($faq_item['question']); ?>
                            </h3>
                            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400 transform transition-transform duration-200 faq-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div 
                            id="faq-<?php echo esc_attr($index); ?>" 
                            class="px-8 pb-6 hidden faq-content"
                        >
                            <div class="prose prose-lg max-w-none text-gray-600 dark:text-gray-300">
                                <?php echo wp_kses_post(wpautop($faq_item['answer'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            
            <!-- Contact CTA -->
            <div class="text-center mt-16">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-8 border border-blue-200 dark:border-blue-800">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        <?php _e('Still have questions?', 'yoursite'); ?>
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        <?php _e('Our domain experts are here to help you find the perfect domain for your business.', 'yoursite'); ?>
                    </p>
                    <a 
                        href="<?php echo esc_url(home_url('/contact')); ?>" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-200"
                    >
                        <?php _e('Contact Support', 'yoursite'); ?>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqToggles = document.querySelectorAll('.faq-toggle');
    
    faqToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            const content = document.getElementById(target);
            const icon = this.querySelector('.faq-icon');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            // Close all other FAQ items
            faqToggles.forEach(otherToggle => {
                if (otherToggle !== this) {
                    const otherTarget = otherToggle.getAttribute('data-target');
                    const otherContent = document.getElementById(otherTarget);
                    const otherIcon = otherToggle.querySelector('.faq-icon');
                    
                    otherToggle.setAttribute('aria-expanded', 'false');
                    otherContent.classList.add('hidden');
                    otherIcon.style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle current FAQ item
            if (isExpanded) {
                this.setAttribute('aria-expanded', 'false');
                content.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            } else {
                this.setAttribute('aria-expanded', 'true');
                content.classList.remove('hidden');
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });
});
</script>