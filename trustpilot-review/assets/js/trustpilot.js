(function () {
    'use strict';

    function initTrustBox() {
        var wrapper = document.getElementById('trustpilot-review-widget');
        if (!wrapper) return;

        var trustboxDiv = wrapper.querySelector('.trustpilot-widget');
        if (!trustboxDiv) return;

        if (window.Trustpilot) {
            window.Trustpilot.loadFromElement(trustboxDiv);
            return;
        }

        var checkInterval = setInterval(function () {
            if (window.Trustpilot) {
                clearInterval(checkInterval);
                window.Trustpilot.loadFromElement(trustboxDiv);
            }
        }, 200);

        setTimeout(function () {
            clearInterval(checkInterval);
        }, 10000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTrustBox);
    } else {
        initTrustBox();
    }
})();
