/**
 * CAVA LMS - Multi-Page Distinct Animation System (2026 Edition)
 * Grants every public & dashboard page its own unique transition personality!
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Reduced Motion Check
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        document.querySelectorAll('.gsap-reveal, .lp-page-hero-title, .custom-card, .event-card, .lp-auth-card').forEach(el => {
            el.style.opacity = '1';
            el.style.transform = 'none';
        });
        return;
    }

    // 2. Sticky Navbar Glassmorphism Scroll Throttler
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

    // 3. GSAP Motion Orchestration per Page
    if (typeof gsap === 'undefined') return;
    if (typeof ScrollTrigger !== 'undefined') {
        gsap.registerPlugin(ScrollTrigger);
    }

    // Helper function to clear GSAP inline styles and guarantee element visibility
    const clearElementProps = (selector) => {
        if (typeof gsap !== 'undefined') {
            gsap.set(selector, { clearProps: 'transform,opacity,visibility,scale' });
        }
        document.querySelectorAll(selector).forEach(el => {
            el.style.opacity = '1';
            el.style.visibility = 'visible';
            el.style.pointerEvents = 'auto';
        });
    };

    const path = window.location.pathname.toLowerCase();

    // =========================================================================
    // PAGE STYLE 1: INDEX LANDING PAGE (Wix Studio Staged Elevator)
    // =========================================================================
    if (path.endsWith('index.php') || path === '/' || path.endsWith('/cava-lms/')) {
        document.body.classList.add('page-index');
        const heroTl = gsap.timeline({
            defaults: { ease: 'power3.out' },
            onComplete: () => {
                clearElementProps('.lp-hero-tag, .lp-hero-wa-title, .lp-hero-wa-subtitle, .lp-hero-cta-group, .lp-hero-single-img-wrapper, .lp-hero-stat-item, .custom-card, button, .lp-filter-link');
            }
        });
        
        heroTl.from('.lp-hero-tag', { opacity: 0, y: 15, duration: 0.5, delay: 0.1 })
              .from('.lp-hero-wa-title', { opacity: 0, y: 25, duration: 0.7 }, '-=0.3')
              .from('.lp-hero-wa-subtitle', { opacity: 0, y: 15, duration: 0.5 }, '-=0.4')
              .from('.lp-hero-cta-group', { opacity: 0, y: 15, duration: 0.5 }, '-=0.4')
              .from('.lp-hero-single-img-wrapper', { opacity: 0, scale: 0.94, y: 20, duration: 0.7 }, '-=0.3')
              .from('.lp-hero-stat-item', { opacity: 0, y: 10, stagger: 0.05, duration: 0.5 }, '-=0.4');
    }

    // =========================================================================
    // PAGE STYLE 2: COURSES CATALOG (Elastic Grid & Filter Waves)
    // =========================================================================
    else if (path.includes('courses.php')) {
        document.body.classList.add('page-courses');
        const coursesTl = gsap.timeline({
            defaults: { ease: 'back.out(1.5)' },
            onComplete: () => {
                clearElementProps('.lp-page-hero-title, .lp-page-hero-subtitle, .lp-search-box, .lp-filter-link, .page-courses .custom-card, button');
            }
        });

        coursesTl.from('.lp-page-hero-title', { opacity: 0, y: -20, duration: 0.5 })
                 .from('.lp-page-hero-subtitle', { opacity: 0, y: 15, duration: 0.4 }, '-=0.3')
                 .from('.lp-search-box', { opacity: 0, scale: 0.9, duration: 0.4 }, '-=0.2')
                 .from('.lp-filter-link', { opacity: 0, x: -15, stagger: 0.03, duration: 0.35 }, '-=0.2')
                 .from('.page-courses .custom-card', { opacity: 0, scale: 0.88, y: 30, stagger: 0.05, duration: 0.5, ease: 'back.out(1.4)' }, '-=0.2');
    }

    // =========================================================================
    // PAGE STYLE 3: COURSE DETAIL PAGE (Floating Levitation & Accordion Slide)
    // =========================================================================
    else if (path.includes('course.php')) {
        document.body.classList.add('page-course-detail');
        const detailTl = gsap.timeline({
            defaults: { ease: 'power3.out' },
            onComplete: () => {
                clearElementProps('.lp-page-hero .breadcrumb, .lp-page-hero-title, .lp-page-hero-subtitle, .sticky-sidebar-card, .accordion-item, button');
            }
        });

        detailTl.from('.lp-page-hero .breadcrumb', { opacity: 0, y: -10, duration: 0.4 })
                .from('.lp-page-hero-title', { opacity: 0, x: -30, duration: 0.6 }, '-=0.2')
                .from('.lp-page-hero-subtitle', { opacity: 0, x: -20, duration: 0.5 }, '-=0.3')
                .from('.sticky-sidebar-card', { opacity: 0, x: 40, duration: 0.7, ease: 'power2.out' }, '-=0.4')
                .from('.accordion-item', { opacity: 0, y: 15, stagger: 0.06, duration: 0.5 }, '-=0.3');
    }

    // =========================================================================
    // PAGE STYLE 4: WEBINARS CATALOG & DETAIL (Live Radar Signal & Prism Glass)
    // =========================================================================
    else if (path.includes('webinars.php') || path.includes('webinar.php')) {
        document.body.classList.add('page-webinars');
        const webinarTl = gsap.timeline({
            defaults: { ease: 'power3.out' },
            onComplete: () => {
                clearElementProps('.lp-page-hero-title, .lp-page-hero-subtitle, .live-radar-badge, .lp-search-box, .lp-filter-link, .webinar-prism-card, .page-webinars .custom-card, button');
            }
        });

        webinarTl.from('.lp-page-hero-title', { opacity: 0, y: 20, duration: 0.5 })
                 .from('.lp-page-hero-subtitle', { opacity: 0, y: 15, duration: 0.4 }, '-=0.3')
                 .from('.lp-search-box', { opacity: 0, scale: 0.9, duration: 0.4 }, '-=0.2')
                 .from('.lp-filter-link', { opacity: 0, x: -15, stagger: 0.03, duration: 0.35 }, '-=0.2')
                 .from('.webinar-prism-card, .page-webinars .custom-card', { opacity: 0, x: 30, y: 20, stagger: 0.07, duration: 0.5 }, '-=0.2');
    }

    // =========================================================================
    // PAGE STYLE 5: EVENTS CATALOG (Ticket Stamp & 3D Flip)
    // =========================================================================
    else if (path.includes('events.php')) {
        document.body.classList.add('page-events');
        const eventsTl = gsap.timeline({
            defaults: { ease: 'back.out(1.5)' },
            onComplete: () => {
                clearElementProps('.lp-page-hero-title, .lp-page-hero-subtitle, .lp-search-box, .lp-filter-link, .page-events .event-card, button');
            }
        });

        eventsTl.from('.lp-page-hero-title', { opacity: 0, scale: 0.9, duration: 0.5 })
                .from('.lp-page-hero-subtitle', { opacity: 0, y: 15, duration: 0.4 }, '-=0.3')
                .from('.lp-search-box', { opacity: 0, scale: 0.9, duration: 0.4 }, '-=0.2')
                .from('.lp-filter-link', { opacity: 0, x: -15, stagger: 0.03, duration: 0.35 }, '-=0.2')
                .from('.page-events .event-card', { opacity: 0, rotateX: -25, y: 40, stagger: 0.08, duration: 0.6 }, '-=0.2');
    }

    // =========================================================================
    // PAGE STYLE 6: SUPPORT CENTER (Spring Category Cards)
    // =========================================================================
    else if (path.includes('support.php')) {
        document.body.classList.add('page-support');
        const supportTl = gsap.timeline({
            defaults: { ease: 'back.out(1.8)' },
            onComplete: () => {
                clearElementProps('.lp-page-hero-title, .lp-page-hero-subtitle, .support-cat-card, .lp-contact-container, button');
            }
        });

        supportTl.from('.lp-page-hero-title', { opacity: 0, y: -20, duration: 0.5 })
                 .from('.lp-page-hero-subtitle', { opacity: 0, y: 15, duration: 0.4 }, '-=0.3')
                 .from('.support-cat-card', { opacity: 0, scale: 0.85, y: 20, stagger: 0.08, duration: 0.5 }, '-=0.2')
                 .from('.lp-contact-container', { opacity: 0, y: 30, duration: 0.5, ease: 'power2.out' }, '-=0.2');
    }

    // =========================================================================
    // PAGE STYLE 7: AUTH PAGES (LOGIN / REGISTER / OTP)
    // =========================================================================
    else if (path.includes('login.php') || path.includes('register.php') || path.includes('otp')) {
        document.body.classList.add('page-auth');
        const authTl = gsap.timeline({
            defaults: { ease: 'back.out(1.6)' },
            onComplete: () => {
                clearElementProps('.lp-auth-card, .lp-auth-icon, .form-group, .mb-3, button');
            }
        });

        authTl.from('.lp-auth-card', { opacity: 0, scale: 0.9, y: 30, duration: 0.6 })
              .from('.lp-auth-icon', { opacity: 0, scale: 0, duration: 0.4 }, '-=0.3')
              .from('.form-group, .mb-3', { opacity: 0, y: 10, stagger: 0.05, duration: 0.4 }, '-=0.3');
    }

    // =========================================================================
    // PAGE STYLE 8: DASHBOARD (Progress Shimmer & Metric Bounce)
    // =========================================================================
    else if (path.includes('dashboard.php') || path.includes('my_courses.php')) {
        document.body.classList.add('page-dashboard');
        const dashTl = gsap.timeline({
            defaults: { ease: 'back.out(1.4)' },
            onComplete: () => {
                clearElementProps('.dashboard-welcome-banner, .dashboard-stat-card, .dashboard-course-card, button');
            }
        });

        dashTl.from('.dashboard-welcome-banner', { opacity: 0, y: -15, duration: 0.5 })
              .from('.dashboard-stat-card', { opacity: 0, scale: 0.9, stagger: 0.07, duration: 0.5 }, '-=0.3')
              .from('.dashboard-course-card', { opacity: 0, y: 20, stagger: 0.08, duration: 0.5 }, '-=0.2');

        // Animate Dashboard Progress Bars
        document.querySelectorAll('.dashboard-progress-bar').forEach(bar => {
            const targetWidth = bar.getAttribute('data-target-width') || bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = targetWidth;
            }, 300);
        });
    }

    // Hardware-accelerated ScrollTriggers for elements with .gsap-reveal class
    const revealElements = document.querySelectorAll('.gsap-reveal');
    if (revealElements.length > 0 && typeof ScrollTrigger !== 'undefined') {
        revealElements.forEach((el) => {
            gsap.to(el, {
                opacity: 1,
                y: 0,
                duration: 0.5,
                ease: 'power2.out',
                scrollTrigger: {
                    trigger: el,
                    start: 'top 92%',
                    toggleActions: 'play none none none',
                    onComplete: () => {
                        gsap.set(el, { clearProps: 'transform,opacity,visibility' });
                        el.style.opacity = '1';
                        el.style.visibility = 'visible';
                    }
                }
            });
        });
    }

    // SAFETY FALLBACK TIMER: Automatically reveals any delayed buttons, filters, or cards after 1200ms
    // Guarantees that no button or filter ever remains hidden after the configured timing window!
    setTimeout(() => {
        clearElementProps('.lp-filter-link, .lp-search-box, .custom-card, .event-card, .webinar-prism-card, .gsap-reveal, button, input, select');
    }, 1250);
});


