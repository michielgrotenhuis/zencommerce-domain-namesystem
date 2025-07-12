/**
 * Domain System Admin JavaScript
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Domain Admin Controller
     */
    const DomainAdmin = {
        
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.initValidation();
            this.initSuggestions();
            this.initTooltips();
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
            if ($('#domain-faqs-container').length) {
                $('#domain-faqs-container').sortable({
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
            $('[title]').tooltip();
        },
        
        /**
         * Generate content
         */
        generateContent: function(e) {
            e.preventDefault();
            
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
            $button.find('span:not(.dashicons)').text(domainAdmin.strings.generating);
            
            // Make AJAX request
            $.ajax({
                url: domainAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'generate_domain_content',
                    nonce: domainAdmin.nonce,
                    tld: tld,
                    category: category,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Populate fields with generated content
                        if (response.data.content) {
                            $.each(response.data.content, function(field, value) {
                                const $field = $('#domain_' + field);
                                if ($field.length && !$field.val()) {
                                    $field.val(value).trigger('change');
                                }
                            });
                        }
                        
                        DomainAdmin.showNotification(response.data.message, 'success');
                    } else {
                        DomainAdmin.showNotification(response.data.message || domainAdmin.strings.error, 'error');
                    }
                },
                error: function() {
                    DomainAdmin.showNotification(domainAdmin.strings.error, 'error');
                },
                complete: function() {
                    $button.removeClass('generating').prop('disabled', false);
                    $button.find('span:not(.dashicons)').text(originalText);
                }
            });
        },
        
        /**
         * Load default FAQs
         */
        loadDefaultFAQs: function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const tld = $('#domain_tld').val();
            const category = $('#domain_category').val();
            
            if (!tld) {
                DomainAdmin.showNotification('Please enter a TLD first.', 'error');
                return;
            }
            
            $button.prop('disabled', true);
            
            $.ajax({
                url: domainAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'load_default_faqs',
                    nonce: domainAdmin.nonce,
                    tld: tld,
                    category: category
                },
                success: function(response) {
                    if (response.success && response.data.faqs) {
                        // Clear existing FAQs
                        $('#domain-faqs-container').empty();
                        
                        // Add new FAQs
                        $.each(response.data.faqs, function(index, faq) {
                            DomainAdmin.addFAQItem(faq.question, faq.answer, index);
                        });
                        
                        DomainAdmin.showNotification(response.data.message, 'success');
                    } else {
                        DomainAdmin.showNotification(response.data.message || domainAdmin.strings.error, 'error');
                    }
                },
                error: function() {
                    DomainAdmin.showNotification(domainAdmin.strings.error, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        },
        
        /**
         * Add FAQ
         */
        addFAQ: function(e) {
            e.preventDefault();
            
            const index = $('#domain-faqs-container .domain-faq-item').length;
            DomainAdmin.addFAQItem('', '', index);
        },
        
        /**
         * Add FAQ item
         */
        addFAQItem: function(question, answer, index) {
            const template = $('.domain-faq-template .domain-faq-item').clone();
            
            // Replace placeholders
            const html = template.prop('outerHTML')
                .replace(/__INDEX__/g, index)
                .replace('value=""', question ? `value="${question}"` : 'value=""');
            
            const $item = $(html);
            $item.find('textarea').val(answer);
            
            $('#domain-faqs-container').append($item);
            
            // Focus on question field if empty
            if (!question) {
                $item.find('input[type="text"]').focus();
            }
        },
        
        /**
         * Remove FAQ
         */
        removeFAQ: function(e) {
            e.preventDefault();
            
            if (confirm(domainAdmin.strings.confirm_delete)) {
                $(this).closest('.domain-faq-item').remove();
                DomainAdmin.updateFAQIndices();
            }
        },
        
        /**
         * Update FAQ indices
         */
        updateFAQIndices: function() {
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
        },
        
        /**
         * Duplicate domain
         */
        duplicateDomain: function(e) {
            e.preventDefault();
            
            if (!confirm(domainAdmin.strings.confirm_duplicate)) {
                return;
            }
            
            const $button = $(this);
            const postId = $button.data('post-id');
            
            $button.prop('disabled', true);
            
            $.ajax({
                url: domainAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'duplicate_domain',
                    nonce: domainAdmin.nonce,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        DomainAdmin.showNotification(response.data.message, 'success');
                        
                        // Redirect to edit the new domain
                        if (response.data.edit_url) {
                            setTimeout(function() {
                                window.location.href = response.data.edit_url;
                            }, 1500);
                        }
                    } else {
                        DomainAdmin.showNotification(response.data.message || domainAdmin.strings.error, 'error');
                    }
                },
                error: function() {
                    DomainAdmin.showNotification(domainAdmin.strings.error, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        },
        
        /**
         * Calculate pricing
         */
        calculatePricing: function(e) {
            e.preventDefault();
            
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