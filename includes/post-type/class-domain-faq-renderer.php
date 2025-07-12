<?php
/**
 * Domain FAQ Renderer Component
 * 
 * Handles the rendering of the FAQ meta box with all its functionality
 * 
 * @package DomainSystem
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class DomainFaqRenderer {
    
    /**
     * Render the FAQ meta box
     */
    public function render($post) {
        $faqs = get_post_meta($post->ID, '_domain_faq', true) ?: [];
        
        // Ensure minimum FAQ items
        while (count($faqs) < 3) {
            $faqs[] = ['question' => '', 'answer' => ''];
        }
        
        $default_faqs = $this->get_default_faq_templates();
        $has_content = !empty(array_filter($faqs, function($faq) { 
            return !empty($faq['question']); 
        }));
        ?>
        <div class="domain-faq">
            <?php $this->render_header($default_faqs); ?>
            
            <div id="faq-container" class="faq-container">
                <?php if (!$has_content): ?>
                    <?php $this->render_empty_state($default_faqs); ?>
                <?php endif; ?>
                
                <?php foreach ($faqs as $index => $faq): ?>
                    <?php $this->render_faq_item($faq, $index, count($faqs)); ?>
                <?php endforeach; ?>
            </div>
            
            <?php $this->render_footer($faqs); ?>
            <?php $this->render_templates($default_faqs); ?>
            <?php $this->render_styles(); ?>
        </div>
        <?php
    }
    
    /**
     * Render FAQ header
     */
    private function render_header($default_faqs) {
        ?>
        <div class="faq-header">
            <div class="faq-header-content">
                <p class="description"><?php _e('Manage frequently asked questions for this domain extension.', 'domain-system'); ?></p>
                <div class="faq-header-actions">
                    <button type="button" id="add-faq" class="button button-secondary">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Add FAQ', 'domain-system'); ?>
                    </button>
                    <?php if (!empty($default_faqs)): ?>
                    <button type="button" id="load-default-faqs" class="button button-secondary">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Load Defaults', 'domain-system'); ?>
                    </button>
                    <?php endif; ?>
                    <button type="button" id="clear-all-faqs" class="button button-link-delete">
                        <span class="dashicons dashicons-trash"></span>
                        <?php _e('Clear All', 'domain-system'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render empty state
     */
    private function render_empty_state($default_faqs) {
        ?>
        <div class="faq-empty-state">
            <div class="faq-empty-icon">
                <span class="dashicons dashicons-editor-help"></span>
            </div>
            <h3><?php _e('No FAQs Added Yet', 'domain-system'); ?></h3>
            <p><?php _e('Add frequently asked questions to help users understand this domain extension better.', 'domain-system'); ?></p>
            <?php if (!empty($default_faqs)): ?>
            <button type="button" class="button button-primary load-default-faqs-btn">
                <?php _e('Load Default FAQs', 'domain-system'); ?>
            </button>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render individual FAQ item
     */
    private function render_faq_item($faq, $index, $total_count) {
        $is_complete = !empty($faq['question']) && !empty($faq['answer']);
        ?>
        <div class="faq-item" data-index="<?php echo $index; ?>">
            <div class="faq-item-header">
                <div class="faq-item-title">
                    <span class="faq-handle">
                        <span class="dashicons dashicons-menu"></span>
                    </span>
                    <h4><?php printf(__('FAQ %d', 'domain-system'), $index + 1); ?></h4>
                    <div class="faq-status">
                        <?php if ($is_complete): ?>
                            <span class="status-complete" title="<?php _e('Complete', 'domain-system'); ?>">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </span>
                        <?php else: ?>
                            <span class="status-incomplete" title="<?php _e('Incomplete', 'domain-system'); ?>">
                                <span class="dashicons dashicons-warning"></span>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="faq-item-actions">
                    <button type="button" class="button-link faq-toggle" title="<?php _e('Toggle FAQ', 'domain-system'); ?>">
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <button type="button" class="button-link move-up" title="<?php _e('Move Up', 'domain-system'); ?>" <?php echo $index === 0 ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-arrow-up-alt2"></span>
                    </button>
                    <button type="button" class="button-link move-down" title="<?php _e('Move Down', 'domain-system'); ?>" <?php echo $index === $total_count - 1 ? 'disabled' : ''; ?>>
                        <span class="dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <?php if ($index >= 3): ?>
                    <button type="button" class="button-link remove-faq" title="<?php _e('Remove FAQ', 'domain-system'); ?>">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="faq-item-content" style="<?php echo !empty($faq['question']) ? '' : 'display: block;'; ?>">
                <?php $this->render_question_field($faq, $index); ?>
                <?php $this->render_answer_field($faq, $index); ?>
                <?php $this->render_preview($faq); ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render question field
     */
    private function render_question_field($faq, $index) {
        $question = $faq['question'] ?? '';
        ?>
        <div class="faq-field-group">
            <label for="faq_question_<?php echo $index; ?>" class="faq-field-label">
                <?php _e('Question:', 'domain-system'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" 
                   id="faq_question_<?php echo $index; ?>"
                   name="domain_faq[<?php echo $index; ?>][question]" 
                   value="<?php echo esc_attr($question); ?>" 
                   class="widefat faq-question-input" 
                   placeholder="<?php _e('Enter your question here...', 'domain-system'); ?>"
                   maxlength="200" />
            <div class="faq-field-meta">
                <span class="char-counter">
                    <span class="current"><?php echo strlen($question); ?></span>/200
                </span>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render answer field
     */
    private function render_answer_field($faq, $index) {
        $answer = $faq['answer'] ?? '';
        ?>
        <div class="faq-field-group">
            <label for="faq_answer_<?php echo $index; ?>" class="faq-field-label">
                <?php _e('Answer:', 'domain-system'); ?>
                <span class="required">*</span>
            </label>
            <textarea id="faq_answer_<?php echo $index; ?>"
                      name="domain_faq[<?php echo $index; ?>][answer]" 
                      rows="4" 
                      class="widefat faq-answer-input"
                      placeholder="<?php _e('Provide a detailed answer here...', 'domain-system'); ?>"
                      maxlength="1000"><?php echo esc_textarea($answer); ?></textarea>
            <div class="faq-field-meta">
                <span class="char-counter">
                    <span class="current"><?php echo strlen($answer); ?></span>/1000
                </span>
                <span class="faq-formatting-help">
                    <a href="#" class="faq-help-toggle"><?php _e('Formatting Help', 'domain-system'); ?></a>
                </span>
            </div>
            <div class="faq-formatting-help-content" style="display: none;">
                <p><?php _e('You can use basic HTML tags:', 'domain-system'); ?></p>
                <code>&lt;strong&gt;, &lt;em&gt;, &lt;a href=""&gt;, &lt;br&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render preview
     */
    private function render_preview($faq) {
        $question = $faq['question'] ?? '';
        $answer = $faq['answer'] ?? '';
        ?>
        <div class="faq-field-group">
            <label class="faq-field-label"><?php _e('Preview:', 'domain-system'); ?></label>
            <div class="faq-preview">
                <div class="faq-preview-question">
                    <?php if ($question): ?>
                        <strong><?php echo esc_html($question); ?></strong>
                    <?php else: ?>
                        <em><?php _e('Question will appear here...', 'domain-system'); ?></em>
                    <?php endif; ?>
                </div>
                <div class="faq-preview-answer">
                    <?php if ($answer): ?>
                        <?php echo wp_kses_post($answer); ?>
                    <?php else: ?>
                        <em><?php _e('Answer will appear here...', 'domain-system'); ?></em>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render footer
     */
    private function render_footer($faqs) {
        $complete_count = count(array_filter($faqs, function($faq) { 
            return !empty($faq['question']) && !empty($faq['answer']); 
        }));
        ?>
        <div class="faq-footer">
            <div class="faq-summary">
                <span class="faq-count"><?php printf(__('%d FAQs', 'domain-system'), count($faqs)); ?></span>
                <span class="faq-complete-count">
                    <?php printf(__('%d complete', 'domain-system'), $complete_count); ?>
                </span>
            </div>
            <div class="faq-actions">
                <button type="button" id="expand-all-faqs" class="button button-secondary">
                    <?php _e('Expand All', 'domain-system'); ?>
                </button>
                <button type="button" id="collapse-all-faqs" class="button button-secondary">
                    <?php _e('Collapse All', 'domain-system'); ?>
                </button>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render templates
     */
    private function render_templates($default_faqs) {
        ?>
        <!-- FAQ Item Template -->
        <div class="faq-item-template" style="display: none;">
            <div class="faq-item" data-index="__INDEX__">
                <div class="faq-item-header">
                    <div class="faq-item-title">
                        <span class="faq-handle">
                            <span class="dashicons dashicons-menu"></span>
                        </span>
                        <h4><?php _e('FAQ', 'domain-system'); ?> <span class="faq-number">__NUMBER__</span></h4>
                        <div class="faq-status">
                            <span class="status-incomplete" title="<?php _e('Incomplete', 'domain-system'); ?>">
                                <span class="dashicons dashicons-warning"></span>
                            </span>
                        </div>
                    </div>
                    <div class="faq-item-actions">
                        <button type="button" class="button-link faq-toggle" title="<?php _e('Toggle FAQ', 'domain-system'); ?>">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <button type="button" class="button-link move-up" title="<?php _e('Move Up', 'domain-system'); ?>">
                            <span class="dashicons dashicons-arrow-up-alt2"></span>
                        </button>
                        <button type="button" class="button-link move-down" title="<?php _e('Move Down', 'domain-system'); ?>">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <button type="button" class="button-link remove-faq" title="<?php _e('Remove FAQ', 'domain-system'); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="faq-item-content" style="display: block;">
                    <div class="faq-field-group">
                        <label class="faq-field-label">
                            <?php _e('Question:', 'domain-system'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="domain_faq[__INDEX__][question]" 
                               class="widefat faq-question-input" 
                               placeholder="<?php _e('Enter your question here...', 'domain-system'); ?>"
                               maxlength="200" />
                        <div class="faq-field-meta">
                            <span class="char-counter">
                                <span class="current">0</span>/200
                            </span>
                        </div>
                    </div>
                    <div class="faq-field-group">
                        <label class="faq-field-label">
                            <?php _e('Answer:', 'domain-system'); ?>
                            <span class="required">*</span>
                        </label>
                        <textarea name="domain_faq[__INDEX__][answer]" 
                                  rows="4" 
                                  class="widefat faq-answer-input"
                                  placeholder="<?php _e('Provide a detailed answer here...', 'domain-system'); ?>"
                                  maxlength="1000"></textarea>
                        <div class="faq-field-meta">
                            <span class="char-counter">
                                <span class="current">0</span>/1000
                            </span>
                            <span class="faq-formatting-help">
                                <a href="#" class="faq-help-toggle"><?php _e('Formatting Help', 'domain-system'); ?></a>
                            </span>
                        </div>
                        <div class="faq-formatting-help-content" style="display: none;">
                            <p><?php _e('You can use basic HTML tags:', 'domain-system'); ?></p>
                            <code>&lt;strong&gt;, &lt;em&gt;, &lt;a href=""&gt;, &lt;br&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;</code>
                        </div>
                    </div>
                    <div class="faq-field-group">
                        <label class="faq-field-label"><?php _e('Preview:', 'domain-system'); ?></label>
                        <div class="faq-preview">
                            <div class="faq-preview-question">
                                <em><?php _e('Question will appear here...', 'domain-system'); ?></em>
                            </div>
                            <div class="faq-preview-answer">
                                <em><?php _e('Answer will appear here...', 'domain-system'); ?></em>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Default FAQ Templates -->
        <?php if (!empty($default_faqs)): ?>
        <div class="default-faq-templates" style="display: none;">
            <?php foreach ($default_faqs as $template): ?>
            <div class="default-faq-item" 
                 data-question="<?php echo esc_attr($template['question']); ?>"
                 data-answer="<?php echo esc_attr($template['answer']); ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Render CSS styles
     */
    private function render_styles() {
        ?>
        <style>
        .domain-faq {
            background: #fff;
        }
        
        .faq-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .faq-header-actions {
            display: flex;
            gap: 10px;
        }
        
        .faq-empty-state {
            text-align: center;
            padding: 40px 20px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .faq-empty-icon {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .faq-item {
            border: 1px solid #ddd;
            border-radius: 6px;
            margin-bottom: 15px;
            background: #fff;
            transition: all 0.3s ease;
        }
        
        .faq-item:hover {
            border-color: #0073aa;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .faq-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }
        
        .faq-item-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .faq-handle {
            cursor: move;
            color: #666;
        }
        
        .faq-item-title h4 {
            margin: 0;
            font-size: 14px;
        }
        
        .faq-status .status-complete {
            color: #46b450;
        }
        
        .faq-status .status-incomplete {
            color: #dc3232;
        }
        
        .faq-item-actions {
            display: flex;
            gap: 5px;
        }
        
        .faq-item-actions .button-link {
            padding: 5px;
            color: #666;
        }
        
        .faq-item-actions .button-link:hover {
            color: #0073aa;
        }
        
        .faq-item-content {
            padding: 20px;
            display: none;
        }
        
        .faq-item-content.expanded {
            display: block;
        }
        
        .faq-field-group {
            margin-bottom: 20px;
        }
        
        .faq-field-label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .required {
            color: #dc3232;
        }
        
        .faq-field-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
        
        .char-counter .current {
            font-weight: 600;
        }
        
        .faq-preview {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
        }
        
        .faq-preview-question {
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .faq-preview-answer {
            font-size: 14px;
            line-height: 1.6;
        }
        
        .faq-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            margin-top: 20px;
        }
        
        .faq-summary {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #666;
        }
        
        .faq-actions {
            display: flex;
            gap: 10px;
        }
        
        .faq-formatting-help-content {
            background: #f0f0f1;
            padding: 10px;
            border-radius: 4px;
            margin-top: 5px;
            font-size: 12px;
        }
        
        .faq-formatting-help-content p {
            margin: 0 0 5px 0;
        }
        
        .faq-formatting-help-content code {
            background: #fff;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 11px;
        }
        </style>
        <?php
    }
    
    /**
     * Get default FAQ templates
     */
    private function get_default_faq_templates() {
        return apply_filters('domain_default_faq_templates', [
            [
                'question' => __('What is a domain name?', 'domain-system'),
                'answer' => __('A domain name is your website\'s address on the internet. It\'s what people type into their browser to find your website.', 'domain-system')
            ],
            [
                'question' => __('How long can I register a domain for?', 'domain-system'),
                'answer' => __('You can register a domain for 1-10 years. We recommend registering for multiple years to ensure you don\'t lose your domain.', 'domain-system')
            ],
            [
                'question' => __('Can I transfer my domain to another registrar?', 'domain-system'),
                'answer' => __('Yes, you can transfer your domain to another registrar after 60 days from registration. The process typically takes 5-7 days.', 'domain-system')
            ],
            [
                'question' => __('What happens if I don\'t renew my domain?', 'domain-system'),
                'answer' => __('If you don\'t renew your domain before it expires, it will go into a grace period, then redemption period, and finally be released for public registration.', 'domain-system')
            ],
            [
                'question' => __('Do I get email addresses with my domain?', 'domain-system'),
                'answer' => __('Domain registration includes DNS management, but email hosting is typically a separate service. Many registrars offer email hosting packages.', 'domain-system')
            ]
        ]);
    }
}