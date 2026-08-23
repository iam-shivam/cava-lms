<?php
// Public Landing Page - Wix Studio / World Academy Design (2026 Edition)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/models/Event.php';

// Fetch settings
$settings = [];
try {
    $rows = DB::fetchAll("SELECT * FROM settings");
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Fail silently
}

$heroEyebrow  = $settings['hero_eyebrow'] ?? 'Cava Career Abroad Visa Academy';
$heroTitle    = $settings['hero_title'] ?? 'Upgrade Your Skills with CAVA LMS';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Access high-quality courses, webinars, and masterclasses designed by industry experts.';
$aboutUs      = $settings['about_us'] ?? 'CAVA LMS is a premium e-learning portal designed to provide quality, affordable education for career development.';

$heroStat1Title = $settings['hero_stat_1_title'] ?? 'Global Students';
$heroStat1Desc  = $settings['hero_stat_1_desc'] ?? 'Ages 12-18';
$heroStat2Title = $settings['hero_stat_2_title'] ?? 'Top Instructors';
$heroStat2Desc  = $settings['hero_stat_2_desc'] ?? 'Ivy League Experts';
$heroStat3Title = $settings['hero_stat_3_title'] ?? 'Tailored Guidance';
$heroStat3Desc  = $settings['hero_stat_3_desc'] ?? 'Micro-group classes';

// Dynamic Hero Banner Single Image
$heroImg1 = (!empty($settings['hero_img_1']) && file_exists(BASE_PATH . '/uploads/' . $settings['hero_img_1']))
    ? SITE_URL . '/uploads/' . $settings['hero_img_1']
    : 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=1000&h=600';

// Fetch dynamic data
$userId = $_SESSION['user_id'] ?? null;

// Fetch dynamic data (excluding ones already bought/registered by the user)
$featuredCourses = Course::getFeatured(3, $userId);
$upcomingWebinars = Webinar::getAll(3, $userId);
$upcomingEvents = Event::getAll(3);

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <!-- Hero Section (World Academy Green / Wix Studio Layout) -->
    <section class="lp-hero-wa">
        <div class="container position-relative" style="z-index: 1;">
            <div class="row align-items-center g-4 g-lg-5 py-2">
                <div class="col-lg-6 pe-lg-4 text-center text-lg-start">
                    <h1 class="lp-hero-wa-title lms-reveal lms-reveal-up">
                        <span class="hero-eyebrow"><?php echo htmlspecialchars($heroEyebrow); ?></span>
                        <span class="hero-main-line"><?php echo htmlspecialchars($heroTitle); ?></span>
                    </h1>
                    <p class="lp-hero-wa-subtitle lms-reveal lms-reveal-up lms-delay-1"><?php echo htmlspecialchars($heroSubtitle); ?></p>

                    <div
                        class="lp-hero-cta-group d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 lms-reveal lms-reveal-up lms-delay-2">
                        <a href="#featured-courses" class="lp-btn-pill-white">
                            <i class="fa-solid fa-book-open me-1"></i> Explore Courses <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </a>
                        <?php if (false): ?>
                        <a href="webinars.php" class="lp-btn-pill-outline">
                            <i class="fa-solid fa-tower-broadcast me-1"></i> Join Live Webinar <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </a>
                        <?php endif; ?>
                    </div>

                    <!-- Wix Studio Style Statistics Row inside left column -->
                    <div class="lp-hero-stats-row row g-3 mt-4 lms-reveal lms-reveal-up lms-delay-3">
                        <div class="col-4">
                            <div class="lp-hero-stat-item">
                                <div class="lp-hero-stat-heading"><?php echo htmlspecialchars($heroStat1Title); ?></div>
                                <div class="lp-hero-stat-desc"><?php echo htmlspecialchars($heroStat1Desc); ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="lp-hero-stat-item">
                                <div class="lp-hero-stat-heading"><?php echo htmlspecialchars($heroStat2Title); ?></div>
                                <div class="lp-hero-stat-desc"><?php echo htmlspecialchars($heroStat2Desc); ?></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="lp-hero-stat-item">
                                <div class="lp-hero-stat-heading"><?php echo htmlspecialchars($heroStat3Title); ?></div>
                                <div class="lp-hero-stat-desc"><?php echo htmlspecialchars($heroStat3Desc); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Hero Banner Image representation with gap -->
                <div class="col-lg-6 ps-lg-4 mt-4 mt-lg-0">
                    <div class="lp-hero-single-img-wrapper lms-reveal-hero-img lms-img-hover lms-delay-1">
                        <img src="<?php echo $heroImg1; ?>" alt="Hero Banner" class="lp-hero-single-img" loading="eager">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose World Academy Section (Screenshot 2 style) -->
    <section class="why-choose-section border-bottom" id="why-choose">
        <div class="container text-center lms-reveal lms-reveal-up">
            <h2 class="why-choose-title">Why Choose <span class="blue-highlight">CAVA Academy</span>?</h2>
            <p class="why-choose-subtitle">Our programs are designed to inspire, challenge, and elevate ambitious
                students through world-class instruction and real-world experience.</p>

            <div class="row g-4 mt-2 text-start">
                <!-- Card 01 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h4 class="why-choose-card-title">World-Class Instructors</h4>
                        <p class="why-choose-card-desc">From top universities like Oxford, Harvard & Stanford.</p>
                        <span class="why-choose-number">01</span>
                    </div>
                </div>
                <!-- Card 02 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-1">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <h4 class="why-choose-card-title">Online & Interactive</h4>
                        <p class="why-choose-card-desc">Personalized learning in small-group live interactive classes.
                        </p>
                        <span class="why-choose-number">02</span>
                    </div>
                </div>
                <!-- Card 03 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-2">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <h4 class="why-choose-card-title">Career-Focused</h4>
                        <p class="why-choose-card-desc">Discover career pathways and gain valuable real-world
                            experience.</p>
                        <span class="why-choose-number">03</span>
                    </div>
                </div>
                <!-- Card 04 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-3">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                        <h4 class="why-choose-card-title">Global Qualifications</h4>
                        <p class="why-choose-card-desc">Open doors to top international colleges and exciting futures.
                        </p>
                        <span class="why-choose-number">04</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION: ABOUT CAVA LMS (Learn. Grow. Succeed.)
         ========================================================================= -->
    <section class="lp-overview-section" id="about-cava">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Left Column: Layered Image with offset backdrop & dot grid -->
                <div class="col-lg-6 text-center text-lg-start lms-reveal lms-reveal-left">
                    <div class="lp-about-img-box">
                        <div class="lp-img-decor-dots lp-decor-dots-tl"></div>
                        <div class="lp-about-backdrop-card lp-backdrop-offset-br"></div>
                        <div class="lp-about-img-frame lms-img-hover">
                            <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=1000&q=80" 
                                 alt="Student Learning at CAVA LMS" 
                                 loading="lazy" 
                                 onerror="this.src='assets/images/study_abroad.jpeg'">
                        </div>
                    </div>
                </div>

                <!-- Right Column: Content -->
                <div class="col-lg-6 lms-reveal lms-reveal-right lms-delay-1">
                    <span class="lp-content-badge">ABOUT CAVA LMS</span>
                    <h2 class="lp-content-title">Learn. Grow. Succeed.</h2>
                    <p class="lp-content-desc">
                        CAVA LMS is your one-stop platform for career growth and skill development. We provide expert-led learning, interactive sessions, and real-world knowledge to help you achieve your goals.
                    </p>

                    <ul class="lp-content-checklist">
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#6366F1"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Industry-relevant, up-to-date content</span>
                        </li>
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#6366F1"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Interactive learning experience</span>
                        </li>
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#6366F1"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Learn anytime, anywhere</span>
                        </li>
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#6366F1"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Certificates to boost your profile</span>
                        </li>
                    </ul>

                    <div>
                        <a href="about.php" class="lp-btn-purple-pill">
                            Know More About Us 
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-left:4px;">
                                <path d="M3.33334 8H12.6667" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 3.33334L12.6667 8L8 12.6667" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION: FLEXIBLE LEARNING (Learn From Anywhere, Anytime)
         ========================================================================= -->
    <section class="lp-overview-section" id="flexible-learning">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Left Column: Content -->
                <div class="col-lg-6 order-2 order-lg-1 lms-reveal lms-reveal-left">
                    <span class="lp-content-badge">FLEXIBLE LEARNING</span>
                    <h2 class="lp-content-title">Learn From Anywhere,<br>Anytime</h2>
                    <p class="lp-content-desc">
                        Access our courses, webinars, and events on any device, anytime you want. Flexible learning that fits your schedule and lifestyle.
                    </p>

                    <div class="lp-feature-grid">
                        <!-- Mini Feature 1 -->
                        <div class="lp-feature-item">
                            <div class="lp-feature-icon-badge">
                                <i class="fa-regular fa-clock"></i>
                            </div>
                            <h5 class="lp-feature-title">Anytime Access</h5>
                            <p class="lp-feature-desc">Learn at your own pace</p>
                        </div>

                        <!-- Mini Feature 2 -->
                        <div class="lp-feature-item">
                            <div class="lp-feature-icon-badge">
                                <i class="fa-solid fa-headset"></i>
                            </div>
                            <h5 class="lp-feature-title">Expert Support</h5>
                            <p class="lp-feature-desc">Get guidance whenever you need</p>
                        </div>

                        <!-- Mini Feature 3 -->
                        <div class="lp-feature-item">
                            <div class="lp-feature-icon-badge">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </div>
                            <h5 class="lp-feature-title">Mobile Friendly</h5>
                            <p class="lp-feature-desc">Seamless learning on any device</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Layered Image with offset backdrop & dot grid -->
                <div class="col-lg-6 order-1 order-lg-2 text-center text-lg-end lms-reveal lms-reveal-right lms-delay-1">
                    <div class="lp-about-img-box">
                        <div class="lp-img-decor-dots lp-decor-dots-mr"></div>
                        <div class="lp-about-backdrop-card lp-backdrop-offset-bl"></div>
                        <div class="lp-about-img-frame lms-img-hover">
                            <img src="https://images.unsplash.com/photo-1588702547919-26089e690ecc?auto=format&fit=crop&w=1000&q=80" 
                                 alt="Flexible Online Education" 
                                 loading="lazy" 
                                 onerror="this.src='assets/images/work_abroad.jpeg'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION: IMMIGRATION OPPORTUNITIES (Australia & Canada)
         ========================================================================= -->
    <section class="lp-immi-section" id="immigration-opportunities">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Left Column: Visual with Floating Badge, Dot Matrix & Backdrop -->
                <div class="col-lg-5 text-center text-lg-start lms-reveal lms-reveal-left">
                    <div class="lp-immi-visual-box">
                        <div class="lp-img-decor-dots lp-decor-dots-tl"></div>
                        <div class="lp-immi-backdrop-card"></div>
                        <div class="lp-immi-img-frame lms-img-hover">
                            <img src="assets/images/immigration_opportunities.png" 
                                 alt="Australia and Canada Immigration Opportunities" 
                                 loading="lazy" 
                                 onerror="this.src='https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=900&q=80'">
                        </div>

                        <!-- Floating Overlay Pill Card -->
                        <div class="lp-immi-floating-badge">
                            <div class="lp-immi-floating-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 16V14L13 9V3.5C13 2.67 12.33 2 11.5 2C10.67 2 10 2.67 10 3.5V9L2 14V16L10 13.5V19L8 20.5V22L11.5 21L15 22V20.5L13 19V13.5L21 16Z" fill="currentColor"/>
                                </svg>
                            </div>
                            <div>
                                <h6 class="lp-immi-floating-title">Your Future, Our Guidance</h6>
                                <p class="lp-immi-floating-sub">Expert support for a smooth immigration journey.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Content & 2x3 Feature Grid -->
                <div class="col-lg-7 lms-reveal lms-reveal-right lms-delay-1">
                    <span class="lp-content-badge">IMMIGRATION OPPORTUNITIES</span>
                    <h2 class="lp-content-title">Australia & Canada</h2>
                    <p class="lp-content-desc">
                        Explore the best immigration pathways to build a secure future for you and your family in Australia or Canada.
                    </p>

                    <div class="lp-immi-features-grid">
                        <!-- Item 1: Skilled Migration Pathways -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="8" r="6"></circle>
                                    <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
                                </svg>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Skilled Migration Pathways</h5>
                                <p class="lp-immi-feature-desc">Multiple immigration programs for qualified professionals.</p>
                            </div>
                        </div>

                        <!-- Item 2: Family Sponsorship -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                </svg>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Family Sponsorship</h5>
                                <p class="lp-immi-feature-desc">Opportunities to sponsor eligible family members after meeting immigration requirements.</p>
                            </div>
                        </div>

                        <!-- Item 3: Permanent Residency (PR) -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Permanent Residency (PR)</h5>
                                <p class="lp-immi-feature-desc">Clear pathways to obtain permanent residency for eligible applicants.</p>
                            </div>
                        </div>

                        <!-- Item 4: Healthcare & Education Benefits -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"></path>
                                    <path d="M12 5v14"></path>
                                    <path d="M5 12h14"></path>
                                </svg>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Healthcare & Education Benefits</h5>
                                <p class="lp-immi-feature-desc">Access to world-class healthcare and quality education systems.</p>
                            </div>
                        </div>

                        <!-- Item 5: Work & Settle Abroad -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="20" height="14" x="2" y="7" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Work & Settle Abroad</h5>
                                <p class="lp-immi-feature-desc">Live, work, and build a long-term career in Australia or Canada.</p>
                            </div>
                        </div>

                        <!-- Item 6: Pathway to Citizenship -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="2" x2="22" y1="12" y2="12"></line>
                                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                </svg>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Pathway to Citizenship</h5>
                                <p class="lp-immi-feature-desc">Eligible permanent residents can apply for citizenship after fulfilling residency requirements.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         SECTION: WHY LEARN FROM AMARNATH? (Founder & Chief Mentor Spotlight)
         ========================================================================= -->
    <section class="lp-founder-section" id="founder-spotlight">
        <div class="container">
            <div class="lp-founder-card-wrap">
                <div class="row align-items-center g-5">
                    <!-- Left: Details -->
                    <div class="col-lg-7 lms-reveal lms-reveal-left">
                        <span class="lp-founder-badge">FOUNDER & CHIEF MENTOR</span>
                        <h2 class="lp-founder-title">Why Learn From <span class="gold-accent">Amarnath</span>?</h2>
                        <p class="lp-founder-quote">"Experience Cannot Be Googled."</p>

                        <ul class="lp-founder-points">
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-certificate"></i>
                                <span><strong>28 years of practical experience</strong> handling and guiding thousands of successful applicants globally every year.</span>
                            </li>
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-passport"></i>
                                <span><strong>Thousands of Visa Applications</strong> processed with hands-on expertise across diverse international pathways.</span>
                            </li>
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-globe"></i>
                                <span><strong>Global Footprint:</strong> Real-world experience across USA, United Kingdom, Australia, Canada, Germany, and 28 European Countries.</span>
                            </li>
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-arrows-rotate"></i>
                                <span><strong>Continuous Evolution:</strong> Learning never stops — every strategy is backed by dynamic policy updates and battle-tested industry systems.</span>
                            </li>
                        </ul>

                        <div class="mt-4">
                            <a href="#featured-courses" class="lp-btn-pill-white">
                                Start Learning With Amarnath <i class="fa-solid fa-arrow-down ms-2"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Right: Founder Portrait -->
                    <div class="col-lg-5 lms-reveal lms-reveal-right lms-delay-1">
                        <div class="lp-founder-img-box">
                            <div class="lp-founder-avatar-frame lms-img-hover">
                                <img src="assets/images/amarnath.png" alt="Amarnath - Founder & Chief Mentor" loading="lazy" onerror="this.src='https://placehold.co/400x400/230d4a/ffffff?text=Amarnath'">
                            </div>
                            <h4 class="lp-founder-name">Amarnath Singh</h4>
                            <p class="lp-founder-role">Founder & Chief Mentor, CAVA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses Section (Directly following Why Choose) -->
    <section class="lp-section-padding" id="featured-courses">
        <div class="container">
            <div class="lp-section-title-wrap text-center gsap-reveal lms-reveal lms-reveal-up">
                <span class="lp-section-badge">Self-Paced Courses</span>
                <h2 class="lp-section-title">Featured Study Programs</h2>
                <p class="lp-section-subtitle">High-quality video modules with lifetime access and locked-syllabus
                    structures.</p>
            </div>

            <div class="row lp-card-grid gsap-reveal lms-reveal lms-reveal-up lms-delay-1">
                <?php if (empty($featuredCourses)): ?>
                    <div class="col text-center py-5">
                        <p class="text-muted fs-5">No featured courses available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $cardIndex = 0;
                    foreach ($featuredCourses as $course):
                        $isEnrolled = Course::isUserEnrolled($userId, $course['id']);
                        require __DIR__ . '/views/components/course_card.php';
                        $cardIndex++;
                    endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($featuredCourses)): ?>
                <div class="text-center mt-5 gsap-reveal">
                    <a href="courses.php" class="lp-btn-pill-dark">
                        Explore All Courses <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Upcoming Webinars Section -->
    <?php if (false): ?>
    <section class="lp-section-padding bg-light border-top border-bottom" id="upcoming-webinars">
        <div class="container">
            <div class="lp-section-title-wrap text-center gsap-reveal">
                <span class="lp-section-badge">Live Masterclasses</span>
                <h2 class="lp-section-title">Upcoming Live Webinars</h2>
                <p class="lp-section-subtitle">Interact live with regulated immigration consultants and career experts.
                </p>
            </div>

            <div class="row justify-content-center lp-card-grid gsap-reveal" style="max-width: 960px; margin: 0 auto;">
                <?php if (empty($upcomingWebinars)): ?>
                    <div class="col text-center py-5">
                        <p class="text-muted fs-5">No webinars scheduled currently. Check back later!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingWebinars as $webinar):
                        $isRegistered = Webinar::isUserRegistered($userId, $webinar['id']);
                        require __DIR__ . '/views/components/webinar_card.php';
                    endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($upcomingWebinars)): ?>
                <div class="text-center mt-5 gsap-reveal">
                    <a href="webinars.php" class="lp-btn-pill-dark">
                        Browse All Webinars <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Upcoming Events Section -->
    <section class="lp-section-padding" id="upcoming-events">
        <div class="container">
            <div class="lp-section-title-wrap text-center gsap-reveal lms-reveal lms-reveal-up">
                <span class="lp-section-badge">Campus Events</span>
                <h2 class="lp-section-title">Upcoming Events & Fairs</h2>
                <p class="lp-section-subtitle">Stay up to date with visa fairs, university meetups, and mock evaluation
                    sessions.</p>
            </div>

            <div class="row justify-content-center gsap-reveal lms-reveal lms-reveal-up lms-delay-1">
                <?php if (empty($upcomingEvents)): ?>
                    <div class="col text-center py-5">
                        <p class="text-muted fs-5">No upcoming events listed at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $ev):
                        $evDate = date('d M, Y', strtotime($ev['date']));
                        $evImg = 'https://placehold.co/600x340/7c3aed/ffffff?text=Event';
                        if ($ev['event_image'] && file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                            $evImg = SITE_URL . '/uploads/' . $ev['event_image'];
                        }
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="custom-card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100 event-card">
                                <div class="card-img-wrapper">
                                    <img src="<?php echo $evImg; ?>" alt="Event banner" class="img-fluid"
                                        style="height: 180px; width: 100%; object-fit: cover;" loading="lazy"
                                        onerror="this.src='https://placehold.co/600x340/7c3aed/ffffff?text=Event'">
                                </div>
                                <div class="p-4 d-flex flex-column h-100">
                                    <div class="mb-2">
                                        <span class="calendar-badge">
                                            <i class="fa-regular fa-calendar-days"></i> <?php echo $evDate; ?>
                                        </span>
                                        <?php if (strtotime($ev['date']) < strtotime(date('Y-m-d'))): ?>
                                            <span class="badge bg-secondary ms-2">Closed</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-light text-success ms-2">Upcoming</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                    <p class="text-muted fs-7 mb-0"><?php echo htmlspecialchars($ev['description']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($upcomingEvents)): ?>
                <div class="text-center mt-5 gsap-reveal">
                    <a href="events.php" class="lp-btn-pill-dark">
                        View All Events <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="lp-section-padding bg-light border-top border-bottom" id="testimonials">
        <div class="container">
            <div class="lp-section-title-wrap text-center gsap-reveal lms-reveal lms-reveal-up">
                <span class="lp-section-badge">Student Reviews</span>
                <h2 class="lp-section-title">Success Stories</h2>
                <p class="lp-section-subtitle">Hear from our students who successfully migrated and upgraded their
                    careers.</p>
            </div>

            <div class="row g-4 gsap-reveal lms-reveal lms-reveal-up lms-delay-1">
                <div class="col-md-4">
                    <div class="lp-testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <p class="lp-testimonial-text">"The Canada Immigration Masterclass made the Express Entry
                            process so simple. The locked syllabus checked out perfectly, and I cleared my ECA
                            credentials doubts."</p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="lp-avatar-initials">AM</div>
                            <div>
                                <h6 class="fw-bold m-0 text-dark">Aman Mehta</h6>
                                <small class="text-muted">FSW Immigrant, Toronto</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="lp-testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <p class="lp-testimonial-text">"Amazing webinar session! I registered for ₹99 and got direct
                            access to the visa consultant. The transactional receipt was emailed immediately."</p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="lp-avatar-initials">SP</div>
                            <div>
                                <h6 class="fw-bold m-0 text-dark">Simran Patel</h6>
                                <small class="text-muted">Student, Vancouver</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="lp-testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <p class="lp-testimonial-text">"The CRS Point System breakdown videos are top tier. Simple
                            YouTube embeds which play without lag, and I unlocked the program through Razorpay in
                            seconds."</p>
                        <div class="d-flex align-items-center gap-3">
                            <div class="lp-avatar-initials">RK</div>
                            <div>
                                <h6 class="fw-bold m-0 text-dark">Rajesh Kumar</h6>
                                <small class="text-muted">PR Holder, Alberta</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form / Support Section -->
    <section class="lp-section-padding lp-contact-section" id="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 gsap-reveal lms-reveal lms-reveal-up">
                    <div class="lp-contact-container p-4 p-md-5">
                        <div class="text-center mb-5">
                            <span class="lp-section-badge">Support Desk</span>
                            <h2 class="fw-bold mb-2">Have a Query? Ask Us!</h2>
                            <p class="text-muted">Submit your question below, and our support team will respond shortly.
                            </p>
                        </div>

                        <form action="submit_query.php" method="POST">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group text-start">
                                        <label for="query_name" class="lp-form-label">Full Name</label>
                                        <input type="text" class="form-control lp-form-control" id="query_name"
                                            name="name"
                                            value="<?php echo $userId ? htmlspecialchars($_SESSION['user_name']) : ''; ?>"
                                            placeholder="e.g. Aman Mehta" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group text-start">
                                        <label for="query_email" class="lp-form-label">Email Address</label>
                                        <input type="email" class="form-control lp-form-control" id="query_email"
                                            name="email"
                                            value="<?php echo $userId ? htmlspecialchars($_SESSION['user_email']) : ''; ?>"
                                            placeholder="aman@example.com" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group text-start">
                                        <label for="query_mobile" class="lp-form-label">Mobile Number</label>
                                        <input type="tel" class="form-control lp-form-control" id="query_mobile"
                                            name="mobile_number" placeholder="9876543210" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group text-start mb-2">
                                        <label for="query_message" class="lp-form-label">Your Support Query
                                            Message</label>
                                        <textarea class="form-control lp-form-control" id="query_message"
                                            name="query_message" rows="4" placeholder="Describe your doubt here..."
                                            required></textarea>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="lp-btn-primary w-100 py-3"
                                        style="border-radius: 100px;">
                                        Submit Query Request <i class="fa-solid fa-paper-plane ms-2"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>