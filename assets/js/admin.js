/**
 * Domain System Admin JavaScript - Fixed Version
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    // Check if required globals exist
    if (typeof domainAdmin === 'undefined') {
        console.warn('domainAdmin object not found. Some functionality may not work.');
        window.domainAdmin = {
            ajaxUrl: '',
            nonce: '',
            strings: {
                generating: 'Generating...',
                error: 'An error occurred',
                confirm_delete: 'Are you sure you want to delete this FAQ?',
                confirm_duplicate: 'Are you sure you want to duplicate this domain?'
            }
        };
    }
    
    /**
     * Domain Admin Controller
     */
    const DomainAdmin = {
        
        /**
         * Initialize
         */
        init: function() {
            try {
                this.bindEvents();
                this.initSortable();
                this.initValidation();
                this.initSuggestions();
                this.initTooltips();
            } catch (error) {
                console.error('DomainAdmin initialization error:', error);
            }
        },
        
        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Content generation
            $(document).on('click', '.generate-content', this.generateContent);
            
            // Load default FAQs
            $(document).on('click', '.load-default-faqs', this.loadDefaultFAQs);
            
            // Add/Remove FAQ
            $(document).on('click', '.add-faq', this.addFAQ);
            $(document).on('click', '.remove-faq', this.removeFAQ);
            
            // Duplicate domain
            $(document).on('click', '.duplicate-domain', this.duplicateDomain);
            
            // Calculate pricing
            $(document).on('click', '.calculate-pricing', this.calculatePricing);
            
            // Generate policy preview
            $(document).on('click', '.generate-policy-preview', this.generatePolicyPreview);
            
            // Field validation
            $(document).on('blur', '[data-validation]', this.validateField);
            $(document).on('input', '[data-validation]', this.debounceValidation);
            
            // Registry suggestions
            $(document).on('input', '[data-suggestions="registry"]', this.getRegistrySuggestions);
            $(document).on('click', '.suggestion-item', this.selectSuggestion);
            
            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.domain-field-wrapper').length) {
                    $('.domain-suggestions-dropdown').removeClass('show');
                }
            });
            
            // Check availability
            $(document).on('click', '.check-availability', this.checkAvailability);
            
            // Form submit prevention during loading
            $(document).on('submit', '#post', this.handleFormSubmit);
        },
        
        /**
         * Initialize sortable FAQs
         */
        initSortable: function() {
            const $container = $('#domain-faqs-container');
            if ($container.length && typeof $.fn.sortable !== 'undefined') {
                $container.sortable({
                    handle: '.faq-handle',
                    placeholder: 'faq-placeholder',
                    axis: 'y',
                    tolerance: 'pointer',
                    update: function() {
                        DomainAdmin.updateFAQIndices();
                    }
                });
            }
        },
        
        /**
         * Initialize field validation
         */
        initValidation: function() {
            // Validate required fields on page load
            $('[data-validation][required]').each(function() {
                if ($(this).val()) {
                    DomainAdmin.validateField.call(this);
                }
            });
        },
        
        /**
         * Initialize suggestions
         */
        initSuggestions: function() {
            // Close suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.domain-field-wrapper').length) {
                    $('.domain-suggestions-dropdown').hide();
                }
            });
        },
        
        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            // Add tooltips to buttons and icons
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[title]').tooltip();
            }
        },
        
        /**
         * Generate content
         */
        generateContent: function(e) {
            e.preventDefault();
            
            try {
                const $button = $(this);
                const $icon = $button.find('.dashicons');
                const originalText = $button.text().trim();
                
                // Get TLD and category
                const tld = $('#domain_tld').val();
                const category = $('#domain_category').val();
                const postId = $('#post_ID').val();
                
                if (!tld) {
                    DomainAdmin.showNotification('Please enter a TLD first.', 'error');
                    return;
                }
                
                // Update button state
                $button.addClass('generating').prop('disabled', true);
                $button.find('span:not(.dashicons)').text(domainAdmin.strings.generating || 'Generating...');
                
                // Make AJAX request
                $.ajax({
                    url: domainAdmin.ajaxUrl || ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'generate_domain_content',
                        nonce: domainAdmin.nonce,
                        tld: tld,
                        category: category,
                        post_id: postId
                    },
                    success: function(response) {
                        if (response && response.success) {
                            // Populate fields with generated content
                            if (response.data && response.data.content) {
                                $.each(response.data.content, function(field, value) {
                                    const $field = $('#domain_' + field);
                                    if ($field.length && !$field.val()) {
                                        $field.val(value).trigger('change');
                                    }
                                });
                            }
                            
                            DomainAdmin.showNotification(
                                (response.data && response.data.message) || 'Content generated successfully', 
                                'success'
                            );
                        } else {
                            DomainAdmin.showNotification(
                                (response.data && response.data.message) || domainAdmin.strings.error || 'An error occurred', 
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        DomainAdmin.showNotification(domainAdmin.strings.error || 'An error occurred', 'error');
                    },
                    complete: function() {
                        $button.removeClass('generating').prop('disabled', false);
                        $button.find('span:not(.dashicons)').text(originalText);
                    }
                });
            } catch (error) {
                console.error('generateContent error:', error);
            }
        },
        
        /**
         * Load default FAQs
         */
        loadDefaultFAQs: function(e) {
            e.preventDefault();
            
            try {
                const $button = $(this);
                const tld = $('#domain_tld').val();
                const category = $('#domain_category').val();
                
                if (!tld) {
                    DomainAdmin.showNotification('Please enter a TLD first.', 'error');
                    return;
                }
                
                $button.prop('disabled', true);
                
                $.ajax({
                    url: domainAdmin.ajaxUrl || ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'load_default_faqs',
                        nonce: domainAdmin.nonce,
                        tld: tld,
                        category: category
                    },
                    success: function(response) {
                        if (response && response.success && response.data && response.data.faqs) {
                            // Clear existing FAQs
                            $('#domain-faqs-container').empty();
                            
                            // Add new FAQs
                            $.each(response.data.faqs, function(index, faq) {
                                DomainAdmin.addFAQItem(faq.question || '', faq.answer || '', index);
                            });
                            
                            DomainAdmin.showNotification(
                                (response.data && response.data.message) || 'FAQs loaded successfully', 
                                'success'
                            );
                        } else {
                            DomainAdmin.showNotification(
                                (response.data && response.data.message) || domainAdmin.strings.error || 'An error occurred', 
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        DomainAdmin.showNotification(domainAdmin.strings.error || 'An error occurred', 'error');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                    }
                });
            } catch (error) {
                console.error('loadDefaultFAQs error:', error);
            }
        },
        
        /**
         * Add FAQ
         */
        addFAQ: function(e) {
            e.preventDefault();
            
            try {
                const index = $('#domain-faqs-container .domain-faq-item').length;
                DomainAdmin.addFAQItem('', '', index);
            } catch (error) {
                console.error('addFAQ error:', error);
            }
        },
        
        /**
         * Add FAQ item
         */
        addFAQItem: function(question, answer, index) {
            try {
                const $template = $('.domain-faq-template .domain-faq-item');
                if (!$template.length) {
                    console.warn('FAQ template not found');
                    return;
                }
                
                const template = $template.clone();
                
                // Replace placeholders
                const html = template.prop('outerHTML')
                    .replace(/__INDEX__/g, index)
                    .replace('value=""', question ? `value="${DomainAdmin.escapeHtml(question)}"` : 'value=""');
                
                const $item = $(html);
                $item.find('textarea').val(answer || '');
                
                $('#domain-faqs-container').append($item);
                
                // Focus on question field if empty
                if (!question) {
                    $item.find('input[type="text"]').focus();
                }
            } catch (error) {
                console.error('addFAQItem error:', error);
            }
        },
        
        /**
         * Remove FAQ
         */
        removeFAQ: function(e) {
            e.preventDefault();
            
            try {
                if (confirm(domainAdmin.strings.confirm_delete || 'Are you sure you want to delete this FAQ?')) {
                    $(this).closest('.domain-faq-item').remove();
                    DomainAdmin.updateFAQIndices();
                }
            } catch (error) {
                console.error('removeFAQ error:', error);
            }
        },
        
        /**
         * Update FAQ indices
         */
        updateFAQIndices: function() {
            try {
                $('#domain-faqs-container .domain-faq-item').each(function(index) {
                    $(this).attr('data-index', index);
                    $(this).find('input, textarea').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const newName = name.replace(/\[\d+\]/, `[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });
            } catch (error) {
                console.error('updateFAQIndices error:', error);
            }
        },
        
        /**
         * Duplicate domain
         */
        duplicateDomain: function(e) {
            e.preventDefault();
            
            try {
                if (!confirm(domainAdmin.strings.confirm_duplicate || 'Are you sure you want to duplicate this domain?')) {
                    return;
                }
                
                const $button = $(this);
                const postId = $button.data('post-id');
                
                if (!postId) {
                    DomainAdmin.showNotification('Post ID not found', 'error');
                    return;
                }
                
                $button.prop('disabled', true);
                
                $.ajax({
                    url: domainAdmin.ajaxUrl || ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'duplicate_domain',
                        nonce: domainAdmin.nonce,
                        post_id: postId
                    },
                    success: function(response) {
                        if (response && response.success) {
                            DomainAdmin.showNotification(
                                (response.data && response.data.message) || 'Domain duplicated successfully', 
                                'success'
                            );
                            
                            // Redirect to edit the new domain
                            if (response.data && response.data.edit_url) {
                                setTimeout(function() {
                                    window.location.href = response.data.edit_url;
                                }, 1500);
                            }
                        } else {
                            DomainAdmin.showNotification(
                                (response.data && response.data.message) || domainAdmin.strings.error || 'An error occurred', 
                                'error'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                        DomainAdmin.showNotification(domainAdmin.strings.error || 'An error occurred', 'error');
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                    }
                });
            } catch (error) {
                console.error('duplicateDomain error:', error);
            }
        },
        
        /**
         * Calculate pricing - COMPLETED FUNCTION
         */
        calculatePricing: function(e) {
            e.preventDefault();
            
            try {
                const regPrice = parseFloat($('#domain_registration_price').val()) || 0;
                const renewalPrice = parseFloat($('#domain_renewal_price').val()) || 0;
                const transferPrice = parseFloat($('#domain_transfer_price').val()) || 0;
                const years = parseInt($('#calc_years').val()) || 3;
                
                if (regPrice <= 0) {
                    DomainAdmin.showNotification('Please enter a registration price first.', 'error');
                    return;
                }
                
                const $button = $(this);
                $button.prop('disabled', true);
                
                // Calculate total costs
                const totalCost = regPrice + (renewalPrice * (years - 1));
                const averageYearly = totalCost / years;
                
                // Update display
                const $results = $('#pricing-calculator-results');
                if ($results.length) {
                    $results.html(`
                        <h4>Pricing Calculation (${years} years)</h4>
                        <p><strong>Registration:</strong> $${regPrice.toFixed(2)}</p>
                        <p><strong>Renewals:</strong> $${(renewalPrice * (years - 1)).toFixed(2)} (${years - 1} years)</p>
                        <p><strong>Total Cost:</strong> $${totalCost.toFixed(2)}</p>
                        <p><strong>Average per Year:</strong> $${averageYearly.toFixed(2)}</p>
                    `).show();
                }
                
                DomainAdmin.showNotification('Pricing calculated successfully', 'success');
            } catch (error) {
                console.error('calculatePricing error:', error);
                DomainAdmin.showNotification('Error calculating pricing', 'error');
            } finally {
                $(this).prop('disabled', false);
            }
        },
        
        /**
         * Show notification
         */
        showNotification: function(message, type) {
            try {
                // Try to use WordPress notices if available
                if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                    wp.data.dispatch('core/notices').createNotice(type, message);
                } else {
                    // Fallback to simple alert or custom notification
                    console.log(`${type.toUpperCase()}: ${message}`);
                    
                    // Create a simple notification div
                    const $notification = $(`
                        <div class="notice notice-${type} is-dismissible" style="margin: 10px 0; padding: 10px;">
                            <p>${DomainAdmin.escapeHtml(message)}</p>
                            <button type="button" class="notice-dismiss" onclick="this.parentElement.remove()">
                                <span class="screen-reader-text">Dismiss this notice.</span>
                            </button>
                        </div>
                    `);
                    
                    // Add to the page
                    $('.wrap h1').after($notification);
                    
                    // Auto-remove after 5 seconds
                    setTimeout(function() {
                        $notification.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 5000);
                }
            } catch (error) {
                console.error('showNotification error:', error);
                // Ultimate fallback
                alert(`${type.toUpperCase()}: ${message}`);
            }
        },
        
        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        /**
         * Placeholder functions for missing methods
         */
        validateField: function() {
            // TODO: Implement field validation
            console.log('validateField called');
        },
        
        debounceValidation: function() {
            // TODO: Implement debounced validation
            console.log('debounceValidation called');
        },
        
        getRegistrySuggestions: function() {
            // TODO: Implement registry suggestions
            console.log('getRegistrySuggestions called');
        },
        
        selectSuggestion: function() {
            // TODO: Implement suggestion selection
            console.log('selectSuggestion called');
        },
        
        checkAvailability: function() {
            // TODO: Implement availability checking
            console.log('checkAvailability called');
        },
        
        handleFormSubmit: function() {
            // TODO: Implement form submit handling
            console.log('handleFormSubmit called');
        },
        
        generatePolicyPreview: function() {
            // TODO: Implement policy preview generation
            console.log('generatePolicyPreview called');
        }
    };
    
    // Initialize when DOM is ready
    $(document).ready(function() {
        if (typeof $ !== 'undefined') {
            DomainAdmin.init();
        } else {
            console.error('jQuery not available');
        }
    });
    
    // Make available globally for debugging
    window.DomainAdmin = DomainAdmin;
    
})(jQuery);