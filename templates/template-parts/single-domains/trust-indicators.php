<?php
/**
 * Domain Landing Page - Trust Indicators
 * 
 * @package YourSite
 * @since 1.0.0
 */
?>

<!-- Trust Indicators -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm text-white">
    <div class="flex items-center justify-center lg:justify-start bg-white/10 backdrop-blur-sm rounded-lg px-4 py-3 border border-white/20">
        <svg class="w-5 h-5 text-green-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span class="font-semibold"><?php _e('Instant setup', 'yoursite'); ?></span>
    </div>
    
    <div class="flex items-center justify-center lg:justify-start bg-white/10 backdrop-blur-sm rounded-lg px-4 py-3 border border-white/20">
        <svg class="w-5 h-5 text-blue-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
        </svg>
        <span class="font-semibold"><?php _e('SSL included', 'yoursite'); ?></span>
    </div>
    
    <div class="flex items-center justify-center lg:justify-start bg-gradient-to-r from-green-500/20 to-emerald-500/20 backdrop-blur-sm rounded-lg px-4 py-3 border border-green-400/30 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-green-400/10 to-emerald-400/10 animate-pulse"></div>
        <svg class="w-5 h-5 text-green-300 mr-2 flex-shrink-0 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
        <span class="font-semibold relative z-10"><?php _e('Money-back guarantee', 'yoursite'); ?></span>
    </div>
</div>