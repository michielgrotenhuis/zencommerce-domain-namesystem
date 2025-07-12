<?php
/**
 * Domain Landing Page - Upmind Integration Scripts and Styles
 * 
 * @package YourSite
 * @since 1.0.0
 */

// Extract data from args
$domain_ext = $args['domain_ext'] ?? 'store';
$current_currency = $args['current_currency'] ?? array('code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar');
?>

<!-- Upmind Domain Search Script -->
<script src="https://widgets.upmind.app/dac/upm-dac.min.js"></script>

<!-- Custom Styling for Upmind Widget -->
<style>
/* Custom styles for Upmind widget to match Zencommerce theme */
.upmind-domain-widget {
    --upm-primary-color: #f4b400;
    --upm-secondary-color: #126fb7;
    --upm-border-radius: 8px;
    --upm-font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Override widget styles to match our design */
upm-dac {
    --primary-color: var(--zc-primary, #f4b400);
    --secondary-color: var(--zc-secondary, #126fb7);
    --success-color: var(--zc-success, #7dc641);
    --border-radius: var(--zc-radius-sm, 8px);
    --font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

/* Ensure widget blends with our hero section */
upm-dac .dac-container {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
}

upm-dac .dac-search-form {
    background: rgba(255, 255, 255, 0.95) !important;
    border-radius: var(--zc-radius-sm, 8px) !important;
    padding: 1rem !important;
    backdrop-filter: blur(10px);
}

upm-dac .dac-input {
    border: 2px solid transparent !important;
    border-radius: var(--zc-radius-sm, 8px) !important;
    padding: 1rem 1.5rem !important;
    font-size: 1.125rem !important;
    transition: all 0.2s ease !important;
}

upm-dac .dac-input:focus {
    border-color: var(--zc-primary, #f4b400) !important;
    box-shadow: 0 0 0 3px rgba(244, 180, 0, 0.1) !important;
    outline: none !important;
}

upm-dac .dac-button {
    background: linear-gradient(135deg, var(--zc-primary, #f4b400), var(--zc-primary-dark, #e09f00)) !important;
    border: none !important;
    border-radius: var(--zc-radius-sm, 8px) !important;
    padding: 1rem 2rem !important;
    font-weight: 600 !important;
    font-size: 1.125rem !important;
    color: #1f2937 !important;
    transition: all 0.2s ease !important;
    text-transform: none !important;
}

ump-dac .dac-button:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 12px rgba(244, 180, 0, 0.3) !important;
}

/* Loading state styling */
.widget-loading {
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Hide loading when widget is ready */
upm-dac:not([hidden]) + .widget-loading,
upm-dac[ready] + .widget-loading {
    display: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    upm-dac .dac-search-form {
        padding: 0.75rem !important;
    }
    
    upm-dac .dac-input {
        padding: 0.875rem 1rem !important;
        font-size: 1rem !important;
    }
    
    upm-dac .dac-button {
        padding: 0.875rem 1.5rem !important;
        font-size: 1rem !important;
        width: 100% !important;
        margin-top: 0.5rem !important;
    }
}
</style>

<!-- JavaScript for Upmind Widget Integration -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 Initializing Upmind Domain Search Widget');
    
    // Configuration
    const UPMIND_CONFIG = {
        orderConfigUrl: 'https://my.zencommerce.net/order/product',
        currencyCode: '<?php echo esc_js($current_currency['code']); ?>',
        fallbackTimeout: 8000 // 8 seconds before showing fallback
    };
    
    // Show fallback form
    function showFallback() {
        const fallbackForm = document.getElementById('traditional-search');
        const widgetContainer = document.getElementById('upmind-domain-search');
        const loadingEl = document.querySelector('.widget-loading');
        
        console.log('📋 Showing fallback form');
        
        if (fallbackForm) {
            fallbackForm.classList.remove('hidden');
        }
        
        if (widgetContainer) {
            widgetContainer.style.display = 'none';
        }
        
        if (loadingEl) {
            loadingEl.style.display = 'none';
        }
    }
    
    // Monitor widget loading
    function monitorWidget() {
        const widget = document.querySelector('upm-dac');
        const loadingEl = document.querySelector('.widget-loading');
        
        if (!widget) {
            console.warn('⚠️ Upmind widget element not found');
            showFallback();
            return;
        }
        
        // Set timeout for fallback
        const fallbackTimer = setTimeout(() => {
            console.warn('⚠️ Upmind widget timeout, showing fallback');
            showFallback();
        }, UPMIND_CONFIG.fallbackTimeout);
        
        // Monitor for widget events
        widget.addEventListener('ready', function() {
            console.log('✅ Upmind widget ready');
            clearTimeout(fallbackTimer);
            
            if (loadingEl) {
                loadingEl.style.display = 'none';
            }
        });
        
        widget.addEventListener('error', function(e) {
            console.error('❌ Upmind widget error:', e.detail || e);
            clearTimeout(fallbackTimer);
            showFallback();
        });
        
        // Check if widget loaded content
        const checkContentLoaded = () => {
            const widgetContent = widget.shadowRoot || widget.querySelector('*');
            if (widgetContent) {
                console.log('✅ Upmind widget content detected');
                clearTimeout(fallbackTimer);
                
                if (loadingEl) {
                    loadingEl.style.display = 'none';
                }
                
                return true;
            }
            return false;
        };
        
        // Periodically check if content loaded
        let contentCheckCount = 0;
        const contentChecker = setInterval(() => {
            contentCheckCount++;
            
            if (checkContentLoaded() || contentCheckCount > 40) { // 4 seconds of checking
                clearInterval(contentChecker);
            }
        }, 100);
    }
    
    // Wait for Upmind script to load
    function waitForUpmind() {
        if (typeof customElements !== 'undefined') {
            // Script loaded, monitor the widget
            setTimeout(monitorWidget, 500);
        } else {
            // Script not loaded yet, show fallback
            console.warn('⚠️ Custom elements not supported or Upmind script not loaded');
            showFallback();
        }
    }
    
    // Initialize with delay to allow script loading
    setTimeout(waitForUpmind, 1000);
    
    // Fallback form functionality
    const fallbackForm = document.querySelector('#traditional-search .domain-search-form');
    if (fallbackForm) {
        fallbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const domainInput = document.getElementById('domain-search-fallback');
            const domain = domainInput.value.trim();
            
            if (domain) {
                // Add loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<svg class="w-5 h-5 animate-spin mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Searching...';
                
                // Track fallback search
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'domain_search_fallback', {
                        'event_category': 'engagement',
                        'event_label': domain + '.<?php echo esc_js($domain_ext); ?>'
                    });
                }
                
                // Redirect to domain search results
                setTimeout(function() {
                    const resultsUrl = '<?php echo esc_url(home_url('/domain-search')); ?>?domain=' + encodeURIComponent(domain) + '&ext=<?php echo esc_js($domain_ext); ?>';
                    window.location.href = resultsUrl;
                }, 1000);
            }
        });
    }
    
    // Scroll to search functionality
    const scrollToTopLinks = document.querySelectorAll('.scroll-to-top');
    scrollToTopLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector('#upmind-domain-search, #traditional-search');
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                
                // Focus on search input after scroll
                setTimeout(() => {
                    const searchInput = target.querySelector('input[type="text"]');
                    if (searchInput && searchInput.offsetParent !== null) {
                        searchInput.focus();
                    }
                }, 500);
            }
        });
    });
    
    // Manual fallback trigger for testing
    window.showDomainFallback = showFallback;
    
    // Debug information
    console.log('🔧 Upmind Config:', {
        orderConfigUrl: UPMIND_CONFIG.orderConfigUrl,
        currencyCode: UPMIND_CONFIG.currencyCode,
        currentDomain: '.<?php echo esc_js($domain_ext); ?>'
    });
});
</script>