/**
 * CAVA Studio shared progressive enhancements.
 * Business logic remains in main.js; this file owns presentation-only behavior.
 */
(function () {
    'use strict';

    document.documentElement.classList.add('studio-js');

    function setViewportUnit() {
        document.documentElement.style.setProperty('--studio-vh', (window.innerHeight * 0.01) + 'px');
    }

    function initStudioReveals() {
        var elements = document.querySelectorAll('[data-studio-reveal]');
        if (!elements.length) return;

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion || !('IntersectionObserver' in window)) {
            elements.forEach(function (element) { element.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });

        elements.forEach(function (element) { observer.observe(element); });
    }

    function initImageReadyState() {
        document.querySelectorAll('.studio-course-card__media img').forEach(function (image) {
            var markReady = function () { image.closest('.studio-course-card__media').classList.add('is-ready'); };
            if (image.complete) markReady();
            else image.addEventListener('load', markReady, { once: true });
        });
    }

    setViewportUnit();
    window.addEventListener('resize', setViewportUnit, { passive: true });
    document.addEventListener('DOMContentLoaded', function () {
        initStudioReveals();
        initImageReadyState();
    });
}());
