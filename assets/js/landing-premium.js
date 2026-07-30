/**
 * CAVA LMS - Optimized Landing Page Script (Wix Studio / World Academy Edition)
 * Fast, lightweight entrance animations and counters without Lenis/Scroll parallax jank.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Reduced motion check
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        document.querySelectorAll('.gsap-reveal').forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }

    // 2. Sticky Navbar scroll state toggle
    const initNavbarScroll = () => {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        let ticking = false;
        const checkScroll = () => {
            if (window.scrollY > 20) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(checkScroll);
                ticking = true;
            }
        });
        checkScroll();
    };
    initNavbarScroll();

    // 3. Lightweight GSAP Entrance Timelines (No Lenis/Parallax dependencies)
    if (typeof gsap !== 'undefined') {
        if (typeof ScrollTrigger !== 'undefined') {
            gsap.registerPlugin(ScrollTrigger);
        }

        // Hero Staged Entry
        const heroTl = gsap.timeline({ defaults: { ease: 'power3.out' } });
        
        heroTl.from('.lp-hero-tag', {
            opacity: 0,
            y: 15,
            duration: 0.5,
            delay: 0.1
        })
        .from('.lp-hero-wa-title', {
            opacity: 0,
            y: 20,
            duration: 0.7
        }, '-=0.3')
        .from('.lp-hero-wa-subtitle', {
            opacity: 0,
            y: 15,
            duration: 0.5
        }, '-=0.4')
        .from('.lp-hero-cta-group', {
            opacity: 0,
            y: 15,
            duration: 0.5
        }, '-=0.4')
        .from('.collage-capsule', {
            opacity: 0,
            scale: 0.95,
            y: 25,
            stagger: 0.08,
            duration: 0.7
        }, '-=0.3')
        .from('.lp-hero-stat-item', {
            opacity: 0,
            y: 10,
            stagger: 0.05,
            duration: 0.5
        }, '-=0.4');

        // Simple hardware-accelerated Scroll-reveals
        const revealElements = document.querySelectorAll('.gsap-reveal');
        if (revealElements.length > 0 && typeof ScrollTrigger !== 'undefined') {
            revealElements.forEach((el) => {
                gsap.to(el, {
                    opacity: 1,
                    y: 0,
                    duration: 0.6,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: el,
                        start: 'top 85%',
                        toggleActions: 'play none none none'
                    }
                });
            });
        }
    }
});
