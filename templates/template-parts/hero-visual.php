<?php
/**
 * Domain Landing Page - Hero Visual
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args (passed from the main template)
$domain_ext = $args['domain_ext'] ?? 'store';
$domain_features = $args['domain_features'] ?? array();

?>

<!-- Right Visual -->
<div class="hidden lg:block">
    <div class="relative max-w-md mx-auto">
        <!-- Main mockup container -->
        <div class="relative bg-white/10 rounded-3xl p-8 backdrop-blur-sm border border-white/20">
            <!-- Website mockup -->
            <div class="bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden transform rotate-3 hover:rotate-0 transition-transform duration-500">
                <!-- Browser bar -->
                <div class="bg-gray-100 px-4 py-3 border-b border-gray-200">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                        <div class="flex-1 bg-white rounded-sm h-6 ml-4 flex items-center px-3">
                            <div class="w-3 h-3 text-green-500 mr-2">🔒</div>
                            <div class="text-xs text-gray-500 font-mono">yourstore.<?php echo esc_html($domain_ext); ?></div>
                        </div>
                    </div>
                </div>
                
                <!-- Website content -->
                <div class="p-6 bg-white">
                    <div class="h-4 bg-gradient-to-r from-blue-500 to-purple-600 rounded mb-3 w-3/4"></div>
                    <div class="h-3 bg-gray-200 rounded mb-2 w-full"></div>
                    <div class="h-3 bg-gray-200 rounded mb-4 w-2/3"></div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg"></div>
                        <div class="h-16 bg-gradient-to-br from-green-400 to-blue-500 rounded-lg"></div>
                    </div>
                </div>
            </div>
            
            <!-- Floating checkmark -->
            <div class="absolute -top-2 -right-2 bg-green-500 text-white p-3 rounded-full shadow-lg animate-bounce">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
        </div>
    </div>
</div>