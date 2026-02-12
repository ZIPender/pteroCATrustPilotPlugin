(function () {
    'use strict';

    var currentSlide = 0;
    var autoAdvanceTimer = null;
    var AUTO_ADVANCE_INTERVAL = 6000;

    function initCarousel() {
        var carousel = document.getElementById('trustpilot-carousel');
        if (!carousel) return;

        var slides = carousel.querySelectorAll('.trustpilot-carousel-slide');
        var dots = carousel.querySelectorAll('.trustpilot-dot');
        var prevBtn = carousel.querySelector('.trustpilot-carousel-prev');
        var nextBtn = carousel.querySelector('.trustpilot-carousel-next');

        if (slides.length <= 1) return;

        function showSlide(index) {
            if (index < 0) index = slides.length - 1;
            if (index >= slides.length) index = 0;
            currentSlide = index;

            for (var i = 0; i < slides.length; i++) {
                slides[i].classList.remove('active');
            }
            slides[currentSlide].classList.add('active');

            for (var j = 0; j < dots.length; j++) {
                dots[j].classList.remove('active');
            }
            if (dots[currentSlide]) {
                dots[currentSlide].classList.add('active');
            }
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                showSlide(currentSlide - 1);
                resetAutoAdvance();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                showSlide(currentSlide + 1);
                resetAutoAdvance();
            });
        }

        for (var d = 0; d < dots.length; d++) {
            (function (idx) {
                dots[idx].addEventListener('click', function () {
                    showSlide(idx);
                    resetAutoAdvance();
                });
            })(d);
        }

        // Auto-advance
        function startAutoAdvance() {
            autoAdvanceTimer = setInterval(function () {
                showSlide(currentSlide + 1);
            }, AUTO_ADVANCE_INTERVAL);
        }

        function resetAutoAdvance() {
            if (autoAdvanceTimer) clearInterval(autoAdvanceTimer);
            startAutoAdvance();
        }

        // Pause on hover
        carousel.addEventListener('mouseenter', function () {
            if (autoAdvanceTimer) clearInterval(autoAdvanceTimer);
        });

        carousel.addEventListener('mouseleave', function () {
            startAutoAdvance();
        });

        // Touch/swipe support
        var touchStartX = 0;
        var touchEndX = 0;

        carousel.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        carousel.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    showSlide(currentSlide + 1);
                } else {
                    showSlide(currentSlide - 1);
                }
                resetAutoAdvance();
            }
        }, { passive: true });

        startAutoAdvance();
    }

    function initTrustBox() {
        var wrapper = document.getElementById('trustpilot-review-widget');
        if (!wrapper) return;

        var trustboxDiv = wrapper.querySelector('.trustpilot-widget');
        if (!trustboxDiv) return;

        // If Trustpilot script already loaded, initialize the widget
        if (window.Trustpilot) {
            window.Trustpilot.loadFromElement(trustboxDiv);
            return;
        }

        // Otherwise wait for it to load
        var checkInterval = setInterval(function () {
            if (window.Trustpilot) {
                clearInterval(checkInterval);
                window.Trustpilot.loadFromElement(trustboxDiv);
            }
        }, 200);

        // Stop checking after 10 seconds
        setTimeout(function () {
            clearInterval(checkInterval);
        }, 10000);
    }

    function initStarAnimation() {
        var widget = document.getElementById('trustpilot-review-widget');
        if (!widget) return;

        var stars = widget.querySelectorAll('.trustpilot-star');
        for (var i = 0; i < stars.length; i++) {
            stars[i].style.animationDelay = (i * 0.1) + 's';
        }
    }

    function init() {
        initCarousel();
        initTrustBox();
        initStarAnimation();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.TrustpilotReview = {
        init: init,
        showSlide: function (n) {
            var carousel = document.getElementById('trustpilot-carousel');
            if (carousel) {
                var slides = carousel.querySelectorAll('.trustpilot-carousel-slide');
                var dots = carousel.querySelectorAll('.trustpilot-dot');
                if (n >= 0 && n < slides.length) {
                    currentSlide = n;
                    for (var i = 0; i < slides.length; i++) {
                        slides[i].classList.remove('active');
                    }
                    slides[n].classList.add('active');
                    for (var j = 0; j < dots.length; j++) {
                        dots[j].classList.remove('active');
                    }
                    if (dots[n]) dots[n].classList.add('active');
                }
            }
        }
    };
})();
