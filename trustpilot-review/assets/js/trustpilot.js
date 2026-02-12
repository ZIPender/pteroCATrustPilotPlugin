/**
 * Trustpilot Review Plugin JavaScript
 *
 * Handles widget initialization and dynamic rating display.
 */

(function() {
    'use strict';

    /**
     * Initialize Trustpilot widget functionality
     */
    function initTrustpilotWidget() {
        var widget = document.getElementById('trustpilot-review-widget');
        if (!widget) return;

        // Add hover animation to stars
        var stars = widget.querySelectorAll('.trustpilot-star');
        stars.forEach(function(star, index) {
            star.style.animationDelay = (index * 0.1) + 's';
        });
    }

    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrustpilotWidget);
    } else {
        initTrustpilotWidget();
    }

    // Expose functions globally for external use
    window.TrustpilotReview = {
        init: initTrustpilotWidget
    };
})();
