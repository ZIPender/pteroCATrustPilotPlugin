/**
 * Trustpilot Review Plugin JavaScript
 *
 * Handles widget interactions and popup dismissal functionality.
 */

(function() {
    'use strict';

    // Animation duration in milliseconds (must match CSS animation)
    var ANIMATION_DURATION_MS = 300;

    /**
     * Initialize Trustpilot widget functionality
     */
    function initTrustpilotWidget() {
        var widget = document.getElementById('trustpilot-review-widget');
        if (!widget) return;

        var dismissBtn = widget.querySelector('.trustpilot-dismiss');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function(e) {
                e.preventDefault();
                dismissWidget(widget);
            });
        }
    }

    /**
     * Dismiss the widget with animation
     * @param {HTMLElement} widget - The widget element to dismiss
     */
    function dismissWidget(widget) {
        widget.classList.add('dismissing');
        
        // Wait for animation to complete (uses ANIMATION_DURATION_MS constant)
        setTimeout(function() {
            widget.style.display = 'none';
            
            // Store dismissal in localStorage for session persistence
            try {
                localStorage.setItem('trustpilot_widget_dismissed', 'true');
                localStorage.setItem('trustpilot_widget_dismissed_at', Date.now().toString());
            } catch (e) {
                // localStorage not available, ignore
            }
        }, ANIMATION_DURATION_MS);
    }

    /**
     * Check if widget was recently dismissed
     * @returns {boolean} True if dismissed within last 24 hours
     */
    function wasRecentlyDismissed() {
        try {
            var dismissed = localStorage.getItem('trustpilot_widget_dismissed');
            var dismissedAt = localStorage.getItem('trustpilot_widget_dismissed_at');
            
            if (dismissed === 'true' && dismissedAt) {
                var dismissedTime = parseInt(dismissedAt, 10);
                var now = Date.now();
                var hoursSinceDismissed = (now - dismissedTime) / (1000 * 60 * 60);
                
                // Don't show if dismissed within last 24 hours
                if (hoursSinceDismissed < 24) {
                    return true;
                }
            }
        } catch (e) {
            // localStorage not available
        }
        return false;
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
        init: initTrustpilotWidget,
        dismiss: function() {
            var widget = document.getElementById('trustpilot-review-widget');
            if (widget) dismissWidget(widget);
        },
        wasRecentlyDismissed: wasRecentlyDismissed
    };
})();
