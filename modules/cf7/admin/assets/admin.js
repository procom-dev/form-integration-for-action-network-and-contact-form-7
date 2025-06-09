/* CF7 ActionNetwork Integration Admin JavaScript */

(function($) {
    'use strict';

    /**
     * Main admin functionality
     */
    const CFANAdmin = {
        
        init: function() {
            this.bindEvents();
            this.validateInitialUrl();
        },

        bindEvents: function() {
            // URL validation on blur
            $('#cfan-actionnetwork-hook-url').on('blur', this.validateUrl);
            
            // Real-time validation on input (debounced)
            $('#cfan-actionnetwork-hook-url').on('input', this.debounce(this.validateUrl, 500));
            
            // Activation checkbox change
            $('#cfan-actionnetwork-activate').on('change', this.toggleActivation);
        },

        validateInitialUrl: function() {
            const urlField = $('#cfan-actionnetwork-hook-url');
            if (urlField.val().trim()) {
                this.validateUrl.call(urlField[0]);
            }
        },

        validateUrl: function() {
            const $this = $(this);
            const url = $this.val().trim();
            const $validation = $('.cfan-url-validation');
            
            // Clear previous validation
            $validation.removeClass('success error warning').hide();
            
            if (!url) {
                return;
            }

            // Basic URL validation
            if (!CFANAdmin.isValidUrl(url)) {
                CFANAdmin.showValidation('error', cfan_admin.invalid_url_message);
                return;
            }

            // ActionNetwork specific validation
            const validation = CFANAdmin.validateActionNetworkUrl(url);
            CFANAdmin.showValidation(validation.type, validation.message);
        },

        isValidUrl: function(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        },

        validateActionNetworkUrl: function(url) {
            const urlObj = new URL(url);
            
            // Check if it's ActionNetwork domain
            if (urlObj.hostname !== 'actionnetwork.org') {
                return {
                    type: 'error',
                    message: cfan_admin.not_actionnetwork_message
                };
            }

            // Check for known patterns
            const knownPatterns = [
                '/api/v2/forms/',
                '/api/v2/petitions/',
                '/api/v2/events/',
                '/api/v2/fundraising_pages/',
                '/api/v2/advocacy_campaigns/',
                '/forms/',
                '/petitions/',
                '/events/'
            ];

            let matchedPattern = null;
            for (const pattern of knownPatterns) {
                if (urlObj.pathname.includes(pattern)) {
                    matchedPattern = pattern;
                    break;
                }
            }

            if (matchedPattern) {
                // Determine the type based on pattern
                let type = 'Unknown';
                if (matchedPattern.includes('forms')) type = 'Form';
                else if (matchedPattern.includes('petitions')) type = 'Petition';
                else if (matchedPattern.includes('events')) type = 'Event';
                else if (matchedPattern.includes('fundraising')) type = 'Fundraising Page';
                else if (matchedPattern.includes('advocacy')) type = 'Advocacy Campaign';

                return {
                    type: 'success',
                    message: cfan_admin.detected_type_message.replace('%s', type)
                };
            }

            // If no known pattern, show warning but allow
            return {
                type: 'warning',
                message: cfan_admin.unknown_pattern_message
            };
        },

        showValidation: function(type, message) {
            let $validation = $('.cfan-url-validation');
            
            // Create validation element if it doesn't exist
            if ($validation.length === 0) {
                $validation = $('<div class="cfan-url-validation"></div>');
                $('#cfan-actionnetwork-hook-url').after($validation);
            }
            
            $validation
                .removeClass('success error warning')
                .addClass(type)
                .text(message)
                .show();
        },


        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        CFANAdmin.init();
    });

})(jQuery);