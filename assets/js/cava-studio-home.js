/** CAVA Studio homepage motion. Static content remains visible without GSAP. */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduceMotion || typeof window.gsap === 'undefined') return;

        var gsap = window.gsap;
        if (typeof window.ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(window.ScrollTrigger);
        }

        var hero = gsap.timeline({ defaults: { ease: 'power3.out' } });
        hero.fromTo('.lp-hero-tag', { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: 0.45 })
            .fromTo('.lp-hero-wa-title', { autoAlpha: 0, y: 28 }, { autoAlpha: 1, y: 0, duration: 0.7 }, '-=0.2')
            .fromTo('.lp-hero-wa-subtitle', { autoAlpha: 0, y: 18 }, { autoAlpha: 1, y: 0, duration: 0.5 }, '-=0.42')
            .fromTo('.lp-hero-cta-group', { autoAlpha: 0, y: 14 }, { autoAlpha: 1, y: 0, duration: 0.45 }, '-=0.32')
            .fromTo('.lp-hero-single-img-wrapper', { autoAlpha: 0, x: 28 }, { autoAlpha: 1, x: 0, duration: 0.75 }, '-=0.55');

        if (typeof window.ScrollTrigger === 'undefined') return;

        document.querySelectorAll('.studio-home-section, .why-choose-section, #featured-courses').forEach(function (section) {
            var targets = section.querySelectorAll('.studio-section-heading, .why-choose-card, .studio-course-card, .studio-path-card');
            if (!targets.length) return;
            gsap.fromTo(targets,
                { autoAlpha: 0, y: 24 },
                {
                    autoAlpha: 1,
                    y: 0,
                    duration: 0.6,
                    stagger: 0.07,
                    ease: 'power2.out',
                    clearProps: 'opacity,visibility,transform',
                    scrollTrigger: { trigger: section, start: 'top 82%', once: true }
                }
            );
        });
    });
}());
