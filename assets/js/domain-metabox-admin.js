// WordPress Admin Meta Box JavaScript - Fix for Tabs and FAQ Buttons
// This should be enqueued in your admin scripts

jQuery(document).ready(function($) {
    
    // ==========================================
    // TAB FUNCTIONALITY FIX
    // ==========================================
    
    // Tab switching functionality
    $('.content-tabs .tab-nav a').on('click', function(e) {
        e.preventDefault();
        
        var $this = $(this);
        var targetTab = $this.attr('href');
        
        // Remove active class from all tabs and content
        $('.content-tabs .tab-nav a').removeClass('active');
        $('.content-tabs .tab-content').removeClass('active').hide();
        
        // Add active class to clicked tab
        $this.addClass('active');
        
        // Show target content
        $(targetTab).addClass('active').show();
        
        // Re-initialize TinyMCE for the active tab if needed
        if (typeof tinyMCE !== 'undefined') {
            var editorId = $(targetTab).find('textarea[id^="domain_"]').attr('id');
            if (editorId && tinyMCE.get(editorId)) {
                tinyMCE.get(editorId).show();
            }
        }
    });
    
    // Initialize tabs on page load
    function initializeTabs() {
        $('.content-tabs .tab-content').hide();
        $('.content-tabs .tab-content.active').show();
        
        // If no active tab, make first one active
        if ($('.content-tabs .tab-nav a.active').length === 0) {
            $('.content-tabs .tab-nav a:first').addClass('active');
            $('.content-tabs .tab-content:first').addClass('active').show();
        }
    }
    
    // Run on page load
    initializeTabs();
    
    // ==========================================
    // FAQ FUNCTIONALITY FIX
    // ==========================================
    
    // Add FAQ Item
    $(document).on('click', '.add-faq-item', function(e) {
        e.preventDefault();
        
        var $container = $(this).closest('.domain-faq').find('.faq-items');
        var index = $container.find('.faq-item').length;
        
        var faqTemplate = `
            <div class="faq-item" data-index="${index}">
                <div class="faq-item-header">
                    <span class="faq-item-title">FAQ Item ${index + 1}</span>
                    <div class="faq-item-actions">
                        <button type="button" class="button toggle-faq-item">
                            <span class="dashicons dashicons-arrow-down"></span>
                        </button>
                        <button type="button" class="button remove-faq-item">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="faq-item-content">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="faq_question_${index}">Question</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="faq_question_${index}" 
                                       name="domain_faq[${index}][question]" 
                                       value="" 
                                       class="large-text" 
                                       placeholder="Enter FAQ question..." />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="faq_answer_${index}">Answer</label>
                            </th>
                            <td>
                                <textarea id="faq_answer_${index}" 
                                          name="domain_faq[${index}][answer]" 
                                          rows="4" 
                                          class="large-text" 
                                          placeholder="Enter FAQ answer..."></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        
        $container.append(faqTemplate);
        
        // Initialize TinyMCE for new textarea if available
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.init({
                selector: `#faq_answer_${index}`,
                height: 200,
                menubar: false,
                plugins: 'link lists',
                toolbar: 'bold italic | bullist numlist | link'
            });
        }
        
        // Update FAQ count
        updateFaqCount();
    });
    
    // Remove FAQ Item
    $(document).on('click', '.remove-faq-item', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to remove this FAQ item?')) {
            var $item = $(this).closest('.faq-item');
            var editorId = $item.find('textarea').attr('id');
            
            // Remove TinyMCE instance if it exists
            if (typeof tinyMCE !== 'undefined' && editorId && tinyMCE.get(editorId)) {
                tinyMCE.remove('#' + editorId);
            }
            
            $item.remove();
            updateFaqCount();
            reindexFaqItems();
        }
    });
    
    // Toggle FAQ Item
    $(document).on('click', '.toggle-faq-item', function(e) {
        e.preventDefault();
        
        var $content = $(this).closest('.faq-item').find('.faq-item-content');
        var $icon = $(this).find('.dashicons');
        
        $content.slideToggle();
        $icon.toggleClass('dashicons-arrow-down dashicons-arrow-up');
    });
    
    // Update FAQ count in add button
    function updateFaqCount() {
        var count = $('.faq-item').length;
        $('.add-faq-item').text(`Add FAQ Item (${count})`);
    }
    
    // Reindex FAQ items after deletion
    function reindexFaqItems() {
        $('.faq-item').each(function(index) {
            var $item = $(this);
            $item.attr('data-index', index);
            $item.find('.faq-item-title').text(`FAQ Item ${index + 1}`);
            
            // Update input names and IDs
            $item.find('input, textarea').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                var id = $input.attr('id');
                
                if (name) {
                    $input.attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
                if (id) {
                    $input.attr('id', id.replace(/_\d+$/, `_${index}`));
                }
            });
            
            // Update labels
            $item.find('label').each(function() {
                var $label = $(this);
                var forAttr = $label.attr('for');
                if (forAttr) {
                    $label.attr('for', forAttr.replace(/_\d+$/, `_${index}`));
                }
            });
        });
    }
    
    // ==========================================
    // DOMAIN TOOLS FUNCTIONALITY
    // ==========================================
    
    // Duplicate Domain
    $('#duplicate-domain').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to duplicate this domain?')) {
            var postId = $('#post_ID').val();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'duplicate_domain',
                    post_id: postId,
                    nonce: domain_admin_nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.edit_url;
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while duplicating the domain.');
                }
            });
        }
    });
    
    // Generate Content
    $('#generate-content').on('click', function(e) {
        e.preventDefault();
        
        var tld = $('#domain_tld').val();
        if (!tld) {
            alert('Please enter a TLD first.');
            return;
        }
        
        if (confirm('This will generate content for all sections. Continue?')) {
            var $button = $(this);
            $button.prop('disabled', true).text('Generating...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_domain_content',
                    tld: tld,
                    nonce: domain_admin_nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update form fields with generated content
                        if (response.data.hero_h1) {
                            $('#domain_hero_h1').val(response.data.hero_h1);
                        }
                        if (response.data.hero_subtitle) {
                            $('#domain_hero_subtitle').val(response.data.hero_subtitle);
                        }
                        
                        // Update TinyMCE editors
                        ['overview', 'stats', 'benefits', 'ideas'].forEach(function(section) {
                            if (response.data[section]) {
                                var editorId = 'domain_' + section;
                                if (typeof tinyMCE !== 'undefined' && tinyMCE.get(editorId)) {
                                    tinyMCE.get(editorId).setContent(response.data[section]);
                                } else {
                                    $('#' + editorId).val(response.data[section]);
                                }
                            }
                        });
                        
                        alert('Content generated successfully!');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while generating content.');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Auto-Generate Content');
                }
            });
        }
    });
    
    // ==========================================
    // VALIDATION AND PREVIEW
    // ==========================================
    
    // TLD validation and preview
    $('#domain_tld').on('input', function() {
        var tld = $(this).val();
        var $preview = $('#domain-preview');
        
        if (tld) {
            var slug = tld.replace(/^\./, '').toLowerCase();
            var url = domain_system_vars.site_url + '/domains/' + slug + '/';
            
            $('#preview-url').text(url);
            $('#preview-slug').text(slug);
            $preview.show();
        } else {
            $preview.hide();
        }
    });
    
    // Character count for SEO fields
    $('#domain_seo_title').on('input', function() {
        var count = $(this).val().length;
        $(this).next('.description').find('.char-count').text(count + '/60');
    });
    
    $('#domain_seo_description').on('input', function() {
        var count = $(this).val().length;
        $(this).next('.description').find('.char-count').text(count + '/160');
    });
    
    // ==========================================
    // IMAGE UPLOAD FUNCTIONALITY
    // ==========================================
    
    // OG Image upload
    $('.upload-og-image').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $container = $button.closest('.og-image-upload');
        var $input = $container.find('#domain_og_image');
        var $preview = $container.find('.og-image-preview');
        
        var frame = wp.media({
            title: 'Select Social Media Image',
            button: {
                text: 'Use this image'
            },
            multiple: false
        });
        
        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            
            $input.val(attachment.url);
            $preview.find('img').attr('src', attachment.url);
            $preview.show();
            $button.hide();
        });
        
        frame.open();
    });
    
    // Remove OG Image
    $('.remove-og-image').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $container = $button.closest('.og-image-upload');
        var $input = $container.find('#domain_og_image');
        var $preview = $container.find('.og-image-preview');
        var $upload = $container.find('.upload-og-image');
        
        $input.val('');
        $preview.hide();
        $upload.show();
    });
    
    // ==========================================
    // INITIALIZATION
    // ==========================================
    
    // Initialize character counts on page load
    $('#domain_seo_title').trigger('input');
    $('#domain_seo_description').trigger('input');
    
    // Initialize TLD preview
    $('#domain_tld').trigger('input');
    
    // Initialize FAQ count
    updateFaqCount();
    
    // Make FAQ items sortable if jQuery UI is available
    if (typeof $.fn.sortable !== 'undefined') {
        $('.faq-items').sortable({
            handle: '.faq-item-header',
            axis: 'y',
            update: function(event, ui) {
                reindexFaqItems();
            }
        });
    }
});

// ==========================================
// GLOBAL VARIABLES (should be localized from PHP)
// ==========================================

// These should be passed from PHP using wp_localize_script
var domain_admin_nonce = domain_admin_nonce || '';
var domain_system_vars = domain_system_vars || {
    site_url: '',
    admin_url: ''
};