/**
 * Domain System Frontend JavaScript
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Domain Frontend Controller
     */
    const DomainFrontend = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initFAQs();
            this.initPricingCalculator();
            this.initSearch();
            this.initLazyLoading();
            this.initAnimations();
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // FAQ toggles
            $(document).on('click', '.faq-question', this.toggleFAQ);
            
            // Pricing calculator
            $(document).on('click', '.calculator-button', this.calculatePricing);
            $(document).on('change', '.calculator-years', this.updateCalculator);
            
            // Search functionality
            $(document).on('submit', '.domain-search-form', this.handleSearch);
            $(document).on('change', '.domain-filter-select', this.handleFilter);
            
            // Card interactions
            $(document).on('mouseenter', '.domain-card-item', this.cardHoverIn);
            $(document).on('mouseleave', '.domain-card-item', this.cardHoverOut);
            
            // Smooth scrolling for anchor links
            $(document).on('click', 'a[href^="#"]', this.smoothScroll);
            
            // Copy to clipboard functionality
            $(document).on('click', '.copy-tld', this.copyToClipboard);
            
            // Track analytics events
            $(document).on('click', '.domain-card-button', this.trackDomainClick);
            $(document).on('click', '.domain-hero-cta', this.trackCTAClick);
        },
        
        /**
         * Initialize FAQ functionality
         */
        initFAQs: function() {
            // Close all FAQs initially except the first one
            $('.faq-answer').hide();
            $('.faq-item:first-child .faq-answer').show();
            $('.faq-item:first-child .faq-question').addClass('active');
        },
        
        /**
         * Initialize pricing calculator
         */
        initPricingCalculator: function() {
            // Set default values and calculate initial pricing
            const $calculator = $('.domain-pricing-calculator');
            if ($calculator.length) {
                this.updateCalculatorDisplay();
            }
        },
        
        /**
         * Initialize search functionality
         */
        initSearch: function() {
            // Add search suggestions
            this.initSearchSuggestions();
            
            // Initialize filters
            this.initFilters();
        },
        
        /**
         * Initialize lazy loading for images
         */
        initLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('domain-lazy');
                            observer.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },
        
        /**
         * Initialize scroll animations
         */
        initAnimations: function() {
            if ('IntersectionObserver' in window) {
                const animationObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('animate-in');
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                document.querySelectorAll('.domain-card, .benefit-item, .policy-item').forEach(el => {
                    animationObserver.observe(el);
                });
            }
        },
        
        /**
         * Toggle FAQ answer
         */
        toggleFAQ: function(e) {
            e.preventDefault();
            
            const $question = $(this);
            const $answer = $question.siblings('.faq-answer');
            const $toggle = $question.find('.faq-toggle');
            
            // Close other FAQs
            $('.faq-question').not($question).removeClass('active');
            $('.faq-answer').not($answer).slideUp(300);
            
            // Toggle current FAQ
            $question.toggleClass('active');
            $answer.slideToggle(300);
            
            // Update toggle icon
            if ($question.hasClass('active')) {
                $toggle.text('−');
            } else {
                $toggle.text('+');
            }
        },
        
        /**
         * Calculate pricing
         */
        calculatePricing: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $calculator = $button.closest('.domain-pricing-calculator');
            const regPrice = parseFloat($calculator.data('reg-price')) || 0;
            const renewalPrice = parseFloat($calculator.data('renewal-price')) || regPrice;
            const years = parseInt($calculator.find('.calculator-years').val()) || 1;
            
            if (regPrice <= 0) {
                DomainFrontend.showMessage('Price information not available.', 'error');
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true).text('Calculating...');
            
            // Simulate API call (replace with actual AJAX call)
            setTimeout(() => {
                const results = DomainFrontend.performPricingCalculation(regPrice, renewalPrice, years);
                DomainFrontend.displayCalculatorResults($calculator, results);
                $button.prop('disabled', false).text('Calculate');
            }, 500);
        },
        
        /**
         * Perform pricing calculation
         */
        performPricingCalculation: function(regPrice, renewalPrice, years) {
            const results = {
                years: [],
                totals: [],
                savings: []
            };
            
            let runningTotal = 0;
            
            for (let year = 1; year <= years; year++) {
                const yearlyPrice = year === 1 ? regPrice : renewalPrice;
                runningTotal += yearlyPrice;
                
                results.years.push(year);
                results.totals.push(runningTotal);
                
                const monthlyEquivalent = runningTotal / (year * 12);
                const potentialSaving = (regPrice * year) - runningTotal;
                
                results.savings.push({
                    monthly: monthlyEquivalent,
                    saving: potentialSaving
                });
            }
            
            return results;
        },
        
        /**
         * Display calculator results
         */
        displayCalculatorResults: function($calculator, results) {
            const currency = $calculator.data('currency') || '$';
            let html = '<div class="calculator-results-content">';
            
            html += '<table class="calculator-table">';
            html += '<thead><tr><th>Year</th><th>Total Cost</th><th>Monthly Equivalent</th></tr></thead>';
            html += '<tbody>';
            
            results.years.forEach((year, index) => {
                const total = results.totals[index];
                const monthly = results.savings[index].monthly;
                
                html += `<tr>
                    <td>${year}</td>
                    <td class="calculator-total">${currency}${total.toFixed(2)}</td>
                    <td>${currency}${monthly.toFixed(2)}</td>
                </tr>`;
            });
            
            html += '</tbody></table>';
            
            if (results.years.length > 1) {
                const finalTotal = results.totals[results.totals.length - 1];
                const finalMonthly = results.savings[results.savings.length - 1].monthly;
                
                html += `<div class="calculator-summary">
                    <p><strong>Total for ${results.years.length} years: ${currency}${finalTotal.toFixed(2)}</strong></p>
                    <p>Average monthly cost: ${currency}${finalMonthly.toFixed(2)}</p>
                </div>`;
            }
            
            html += '</div>';
            
            const $results = $calculator.find('.calculator-results');
            $results.html(html).addClass('show');
        },
        
        /**
         * Update calculator when years change
         */
        updateCalculator: function() {
            const $calculator = $(this).closest('.domain-pricing-calculator');
            const $button = $calculator.find('.calculator-button');
            
            // Auto-calculate if data is available
            if ($calculator.data('reg-price')) {
                $button.trigger('click');
            }
        },
        
        /**
         * Handle search form submission
         */
        handleSearch: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const query = $form.find('.domain-search-input').val().trim();
            
            if (!query) {
                DomainFrontend.showMessage('Please enter a search term.', 'warning');
                return;
            }
            
            // Show loading state
            DomainFrontend.showSearchLoading();
            
            // Perform search (this would typically be an AJAX request)
            DomainFrontend.performSearch(query);
        },
        
        /**
         * Perform search
         */
        performSearch: function(query) {
            // Simulate search delay
            setTimeout(() => {
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('search', query);
                window.location.href = currentUrl.toString();
            }, 1000);
        },
        
        /**
         * Handle filter changes
         */
        handleFilter: function() {
            const $select = $(this);
            const filterType = $select.data('filter');
            const value = $select.val();
            
            // Update URL with filter parameters
            const currentUrl = new URL(window.location);
            
            if (value) {
                currentUrl.searchParams.set(filterType, value);
            } else {
                currentUrl.searchParams.delete(filterType);
            }
            
            window.location.href = currentUrl.toString();
        },
        
        /**
         * Initialize search suggestions
         */
        initSearchSuggestions: function() {
            const $searchInput = $('.domain-search-input');
            
            if ($searchInput.length) {
                let searchTimeout;
                
                $searchInput.on('input', function() {
                    const query = $(this).val().trim();
                    
                    clearTimeout(searchTimeout);
                    
                    if (query.length >= 2) {
                        searchTimeout = setTimeout(() => {
                            DomainFrontend.fetchSearchSuggestions(query);
                        }, 300);
                    } else {
                        DomainFrontend.hideSearchSuggestions();
                    }
                });
                
                // Hide suggestions when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.domain-search-form').length) {
                        DomainFrontend.hideSearchSuggestions();
                    }
                });
            }
        },
        
        /**
         * Fetch search suggestions
         */
        fetchSearchSuggestions: function(query) {
            // This would typically be an AJAX request to get suggestions
            const suggestions = [
                '.com domains',
                '.org domains',
                '.net domains',
                '.io domains',
                '.co domains'
            ].filter(item => item.toLowerCase().includes(query.toLowerCase()));
            
            DomainFrontend.displaySearchSuggestions(suggestions);
        },
        
        /**
         * Display search suggestions
         */
        displaySearchSuggestions: function(suggestions) {
            let $dropdown = $('.search-suggestions-dropdown');
            
            if (!$dropdown.length) {
                $dropdown = $('<div class="search-suggestions-dropdown"></div>');
                $('.domain-search-form').append($dropdown);
            }
            
            if (suggestions.length > 0) {
                let html = '';
                suggestions.forEach(suggestion => {
                    html += `<div class="suggestion-item" data-suggestion="${suggestion}">${suggestion}</div>`;
                });
                
                $dropdown.html(html).show();
                
                // Handle suggestion clicks
                $dropdown.find('.suggestion-item').on('click', function() {
                    const suggestion = $(this).data('suggestion');
                    $('.domain-search-input').val(suggestion);
                    DomainFrontend.hideSearchSuggestions();
                });
            } else {
                $dropdown.hide();
            }
        },
        
        /**
         * Hide search suggestions
         */
        hideSearchSuggestions: function() {
            $('.search-suggestions-dropdown').hide();
        },
        
        /**
         * Initialize filters
         */
        initFilters: function() {
            // Set current filter values from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            
            $('.domain-filter-select').each(function() {
                const $select = $(this);
                const filterType = $select.data('filter');
                const value = urlParams.get(filterType);
                
                if (value) {
                    $select.val(value);
                }
            });
        },
        
        /**
         * Card hover effects
         */
        cardHoverIn: function() {
            const $card = $(this);
            $card.find('.domain-card-button').addClass('hover');
        },
        
        cardHoverOut: function() {
            const $card = $(this);
            $card.find('.domain-card-button').removeClass('hover');
        },
        
        /**
         * Smooth scrolling for anchor links
         */
        smoothScroll: function(e) {
            const href = $(this).attr('href');
            
            if (href.indexOf('#') === 0) {
                e.preventDefault();
                
                const target = $(href);
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800, 'easeInOutCubic');
                }
            }
        },
        
        /**
         * Copy TLD to clipboard
         */
        copyToClipboard: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const tld = $button.data('tld') || $button.text();
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(tld).then(() => {
                    DomainFrontend.showCopySuccess($button);
                }).catch(() => {
                    DomainFrontend.fallbackCopyToClipboard(tld, $button);
                });
            } else {
                DomainFrontend.fallbackCopyToClipboard(tld, $button);
            }
        },
        
        /**
         * Fallback copy to clipboard
         */
        fallbackCopyToClipboard: function(text, $button) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            try {
                document.execCommand('copy');
                DomainFrontend.showCopySuccess($button);
            } catch (err) {
                DomainFrontend.showMessage('Failed to copy to clipboard', 'error');
            }
            
            document.body.removeChild(textArea);
        },
        
        /**
         * Show copy success feedback
         */
        showCopySuccess: function($button) {
            const originalText = $button.text();
            $button.text('Copied!').addClass('copied');
            
            setTimeout(() => {
                $button.text(originalText).removeClass('copied');
            }, 2000);
        },
        
        /**
         * Track domain click analytics
         */
        trackDomainClick: function() {
            const $button = $(this);
            const domainId = $button.closest('.domain-card-item').data('domain-id');
            const tld = $button.closest('.domain-card-item').data('tld');
            
            // Track with Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'domain_click', {
                    'event_category': 'domain',
                    'event_label': tld,
                    'value': domainId
                });
            }
            
            // Custom tracking
            DomainFrontend.trackEvent('domain_click', {
                domain_id: domainId,
                tld: tld,
                source: 'card_button'
            });
        },
        
        /**
         * Track CTA click analytics
         */
        trackCTAClick: function() {
            const $button = $(this);
            const tld = $button.closest('.domain-hero').data('tld');
            
            // Track with Google Analytics if available
            if (typeof gtag !== 'undefined') {
                gtag('event', 'cta_click', {
                    'event_category': 'engagement',
                    'event_label': tld,
                    'value': 1
                });
            }
            
            // Custom tracking
            DomainFrontend.trackEvent('cta_click', {
                tld: tld,
                source: 'hero_section'
            });
        },
        
        /**
         * Custom event tracking
         */
        trackEvent: function(eventType, data) {
            // Send to your analytics endpoint
            if (typeof domainFrontend !== 'undefined' && domainFrontend.trackingEnabled) {
                $.ajax({
                    url: domainFrontend.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'track_domain_event',
                        nonce: domainFrontend.nonce,
                        event_type: eventType,
                        event_data: data
                    },
                    success: function(response) {
                        // Handle tracking response if needed
                    }
                });
            }
        },
        
        /**
         * Show search loading state
         */
        showSearchLoading: function() {
            const $searchButton = $('.domain-search-button');
            const originalText = $searchButton.text();
            
            $searchButton.prop('disabled', true)
                        .html('<span class="domain-spinner"></span> Searching...');
            
            $searchButton.data('original-text', originalText);
        },
        
        /**
         * Show message to user
         */
        showMessage: function(message, type = 'info') {
            // Remove existing messages
            $('.domain-message').remove();
            
            const $message = $(`
                <div class="domain-alert domain-alert-${type} domain-message">
                    <span>${message}</span>
                    <button type="button" class="domain-message-close">&times;</button>
                </div>
            `);
            
            // Add to top of content area
            if ($('.domain-container').length) {
                $('.domain-container').first().prepend($message);
            } else {
                $('body').prepend($message);
            }
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 5000);
            
            // Handle close button
            $message.find('.domain-message-close').on('click', function() {
                $message.fadeOut(() => $message.remove());
            });
        },
        
        /**
         * Update calculator display
         */
        updateCalculatorDisplay: function() {
            $('.domain-pricing-calculator').each(function() {
                const $calculator = $(this);
                const regPrice = $calculator.data('reg-price');
                
                if (regPrice) {
                    // Auto-calculate with default year selection
                    $calculator.find('.calculator-button').trigger('click');
                }
            });
        },
        
        /**
         * Initialize responsive tables
         */
        initResponsiveTables: function() {
            $('.domain-table').each(function() {
                const $table = $(this);
                
                if (!$table.parent('.table-responsive').length) {
                    $table.wrap('<div class="table-responsive"></div>');
                }
            });
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('[data-tooltip]').tooltip({
                    placement: 'top',
                    trigger: 'hover'
                });
            }
        },
        
        /**
         * Initialize back to top button
         */
        initBackToTop: function() {
            const $backToTop = $('<button class="back-to-top" title="Back to top">↑</button>');
            $('body').append($backToTop);
            
            $(window).on('scroll', function() {
                if ($(window).scrollTop() > 300) {
                    $backToTop.addClass('visible');
                } else {
                    $backToTop.removeClass('visible');
                }
            });
            
            $backToTop.on('click', function() {
                $('html, body').animate({ scrollTop: 0 }, 600);
            });
        },
        
        /**
         * Initialize print functionality
         */
        initPrintFunctionality: function() {
            $('.print-domain').on('click', function(e) {
                e.preventDefault();
                window.print();
            });
        },
        
        /**
         * Initialize social sharing
         */
        initSocialSharing: function() {
            $('.share-domain').on('click', function(e) {
                e.preventDefault();
                
                const url = encodeURIComponent(window.location.href);
                const title = encodeURIComponent(document.title);
                const platform = $(this).data('platform');
                
                let shareUrl = '';
                
                switch (platform) {
                    case 'twitter':
                        shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
                        break;
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                        break;
                    case 'linkedin':
                        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
                        break;
                    case 'email':
                        shareUrl = `mailto:?subject=${title}&body=${url}`;
                        break;
                }
                
                if (shareUrl) {
                    if (platform === 'email') {
                        window.location.href = shareUrl;
                    } else {
                        window.open(shareUrl, 'share', 'width=600,height=400');
                    }
                }
            });
        },
        
        /**
         * Debounce function
         */
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        
        /**
         * Throttle function
         */
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };
    
    /**
     * Archive Page Controller
     */
    const DomainArchive = {
        init: function() {
            if ($('.domain-archive').length) {
                this.initFilters();
                this.initSorting();
                this.initLoadMore();
            }
        },
        
        initFilters: function() {
            $('.domain-filter-toggle').on('click', function() {
                $('.domain-filters').slideToggle();
            });
        },
        
        initSorting: function() {
            $('.domain-sort').on('change', function() {
                const sortBy = $(this).val();
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('sort', sortBy);
                window.location.href = currentUrl.toString();
            });
        },
        
        initLoadMore: function() {
            let loading = false;
            let page = 2;
            
            $('.load-more-domains').on('click', function(e) {
                e.preventDefault();
                
                if (loading) return;
                
                loading = true;
                const $button = $(this);
                const originalText = $button.text();
                
                $button.text('Loading...').prop('disabled', true);
                
                // Simulate loading more domains
                setTimeout(() => {
                    // This would be an AJAX request in real implementation
                    DomainFrontend.showMessage('Load more functionality would be implemented here.', 'info');
                    
                    $button.text(originalText).prop('disabled', false);
                    loading = false;
                    page++;
                }, 1000);
            });
        }
    };
    
    /**
     * Single Domain Page Controller
     */
    const DomainSingle = {
        init: function() {
            if ($('.single-domain').length) {
                this.initTabs();
                this.initComparisonTable();
                this.initRegistrarLinks();
            }
        },
        
        initTabs: function() {
            $('.domain-tabs .tab-link').on('click', function(e) {
                e.preventDefault();
                
                const target = $(this).attr('href');
                
                // Update active tab
                $('.domain-tabs .tab-link').removeClass('active');
                $(this).addClass('active');
                
                // Show target content
                $('.tab-content').removeClass('active');
                $(target).addClass('active');
            });
        },
        
        initComparisonTable: function() {
            $('.compare-toggle').on('change', function() {
                const $row = $(this).closest('tr');
                $row.toggleClass('selected', this.checked);
            });
        },
        
        initRegistrarLinks: function() {
            $('.registrar-link').on('click', function() {
                const registrar = $(this).data('registrar');
                const tld = $(this).data('tld');
                
                DomainFrontend.trackEvent('registrar_click', {
                    registrar: registrar,
                    tld: tld
                });
            });
        }
    };
    
    /**
     * Performance monitoring
     */
    const DomainPerformance = {
        init: function() {
            this.measureLoadTime();
            this.trackUserInteractions();
        },
        
        measureLoadTime: function() {
            window.addEventListener('load', function() {
                const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
                
                DomainFrontend.trackEvent('page_load_time', {
                    load_time: loadTime,
                    page_type: $('body').attr('class')
                });
            });
        },
        
        trackUserInteractions: function() {
            let scrollDepth = 0;
            
            $(window).on('scroll', DomainFrontend.throttle(function() {
                const currentScrollDepth = Math.round(($(window).scrollTop() + $(window).height()) / $(document).height() * 100);
                
                if (currentScrollDepth > scrollDepth) {
                    scrollDepth = currentScrollDepth;
                    
                    if (scrollDepth >= 25 && scrollDepth < 50) {
                        DomainFrontend.trackEvent('scroll_depth', { depth: '25%' });
                    } else if (scrollDepth >= 50 && scrollDepth < 75) {
                        DomainFrontend.trackEvent('scroll_depth', { depth: '50%' });
                    } else if (scrollDepth >= 75 && scrollDepth < 100) {
                        DomainFrontend.trackEvent('scroll_depth', { depth: '75%' });
                    } else if (scrollDepth >= 100) {
                        DomainFrontend.trackEvent('scroll_depth', { depth: '100%' });
                    }
                }
            }, 500));
        }
    };
    
    /**
     * Add easing function for smooth scrolling
     */
    $.easing.easeInOutCubic = function(x, t, b, c, d) {
        if ((t /= d / 2) < 1) return c / 2 * t * t * t + b;
        return c / 2 * ((t -= 2) * t * t + 2) + b;
    };
    
    /**
     * Add CSS for dynamic elements
     */
    const dynamicCSS = `
        <style>
        .search-suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }
        
        .suggestion-item {
            padding: 12px 16px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .suggestion-item:hover {
            background: #f8f9fa;
        }
        
        .suggestion-item:last-child {
            border-bottom: none;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--domain-primary);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        
        .back-to-top:hover {
            background: var(--domain-secondary);
            transform: translateY(-2px);
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .animate-in {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .domain-message-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            margin-left: auto;
            padding: 0 5px;
        }
        
        .copy-tld.copied {
            background: var(--domain-success) !important;
            color: #fff !important;
        }
        </style>
    `;
    
    // Add dynamic CSS to head
    $('head').append(dynamicCSS);
    
    /**
     * Initialize everything when document is ready
     */
    $(document).ready(function() {
        DomainFrontend.init();
        DomainArchive.init();
        DomainSingle.init();
        DomainPerformance.init();
        
        // Initialize additional features
        DomainFrontend.initResponsiveTables();
        DomainFrontend.initTooltips();
        DomainFrontend.initBackToTop();
        DomainFrontend.initPrintFunctionality();
        DomainFrontend.initSocialSharing();
    });
    
    /**
     * Handle window resize
     */
    $(window).on('resize', DomainFrontend.debounce(function() {
        // Recalculate layouts if needed
        DomainFrontend.initResponsiveTables();
    }, 250));
    
})(jQuery);