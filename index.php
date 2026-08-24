<?php
// Public Landing Page - CAVA LMS Premium Editorial & Cinematic Storytelling (2026 Edition)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/models/Event.php';

// Fetch dynamic settings
$settings = [];
try {
    if (function_exists('get_all_settings')) {
        $settings = get_all_settings();
    } else {
        $rows = DB::fetchAll("SELECT * FROM settings");
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
} catch (Exception $e) {
    // Fail silently
}

$heroEyebrow  = $settings['hero_eyebrow'] ?? 'CAVA CAREER ABROAD VISA ACADEMY';
$heroTitle    = $settings['hero_title'] ?? 'Learn Today. Build Your Future.';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Master international career and migration pathways with industry-led masterclasses, locked-syllabus curriculums, and lifetime access.';
$aboutUs      = $settings['about_us'] ?? 'CAVA LMS is a premium e-learning portal designed to provide quality, affordable education for career development.';

$heroStat1Title = $settings['hero_stat_1_title'] ?? 'Global Students';
$heroStat1Desc  = $settings['hero_stat_1_desc'] ?? 'Ages 16-45';
$heroStat2Title = $settings['hero_stat_2_title'] ?? 'Top Instructors';
$heroStat2Desc  = $settings['hero_stat_2_desc'] ?? '28+ Years Expertise';
$heroStat3Title = $settings['hero_stat_3_title'] ?? 'Tailored Guidance';
$heroStat3Desc  = $settings['hero_stat_3_desc'] ?? 'Career & Visa Mentorship';

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
    <!-- =========================================================================
         01. HERO SECTION (Editorial Layout with Single High-Impact Image)
         ========================================================================= -->
    <section class="lp-hero-wa" id="hero">
        <div class="container position-relative" style="z-index: 1;">
            <div class="row align-items-center g-4 g-lg-5 py-3">
                <div class="col-lg-6 pe-lg-4 text-center text-lg-start">
                    <h1 class="lp-hero-wa-title lms-reveal lms-reveal-up">
                        <span class="hero-eyebrow"><?php echo htmlspecialchars($heroEyebrow); ?></span>
                        <span class="hero-main-line"><?php echo htmlspecialchars($heroTitle); ?></span>
                    </h1>
                    <p class="lp-hero-wa-subtitle lms-reveal lms-reveal-up lms-delay-1"><?php echo htmlspecialchars($heroSubtitle); ?></p>

                    <div class="lp-hero-cta-group d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 lms-reveal lms-reveal-up lms-delay-2">
                        <a href="#featured-courses" class="lp-btn-pill-white">
                            <i class="fa-solid fa-book-open me-1"></i> Explore Courses <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </a>
                        <a href="#storytelling" class="lp-btn-pill-outline">
                            <i class="fa-solid fa-compass me-1"></i> Learn More <i class="fa-solid fa-chevron-down ms-1"></i>
                        </a>
                    </div>

                    <!-- Statistics Row -->
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

                <!-- Single Hero Banner Image representation -->
                <div class="col-lg-6 ps-lg-4 mt-4 mt-lg-0">
                    <div class="lp-hero-single-img-wrapper lms-reveal-hero-img lms-img-hover lms-delay-1">
                        <img src="<?php echo $heroImg1; ?>" alt="CAVA LMS - Online Learning Platform" class="lp-hero-single-img" loading="eager" onerror="this.src='assets/images/study_abroad.jpeg'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         02. WHY CAVA (4 Strong Benefit Pillars)
         ========================================================================= -->
    <section class="why-choose-section border-bottom" id="why-choose">
        <div class="container text-center lms-reveal lms-reveal-up">
            <span class="lp-section-badge">CORE ADVANTAGES</span>
            <h2 class="why-choose-title">Why Choose <span class="blue-highlight">CAVA Academy</span>?</h2>
            <p class="why-choose-subtitle">Our programs are designed to inspire, challenge, and elevate ambitious students through world-class instruction and real-world experience.</p>

            <div class="row g-4 mt-2 text-start">
                <!-- Card 01 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h4 class="why-choose-card-title">Expert-Led Learning</h4>
                        <p class="why-choose-card-desc">Learn from seasoned visa and career mentors with over 28 years of proven field experience.</p>
                        <span class="why-choose-number">01</span>
                    </div>
                </div>
                <!-- Card 02 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-1">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <h4 class="why-choose-card-title">Flexible Learning</h4>
                        <p class="why-choose-card-desc">Personalized self-paced video modules accessible 24/7 across mobile, tablet, and desktop.</p>
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
                        <p class="why-choose-card-desc">Actionable curriculums designed to unlock international job pathways and credential readiness.</p>
                        <span class="why-choose-number">03</span>
                    </div>
                </div>
                <!-- Card 04 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-3">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-certificate"></i>
                        </div>
                        <h4 class="why-choose-card-title">Global Opportunities</h4>
                        <p class="why-choose-card-desc">Clear roadmaps for Canada, Australia, the UK, Germany, and 28+ European countries.</p>
                        <span class="why-choose-number">04</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         03. EDITORIAL ALTERNATING STORYTELLING SECTION
         ========================================================================= -->
    <section class="lp-storytelling-section border-bottom" id="storytelling">
        <div class="container">
            <div class="storytelling-intro text-center lms-reveal lms-reveal-up">
                <span class="lp-section-badge">CAVA STORYTELLING</span>
                <h2 class="lp-section-title">Education That Evolves With Your Ambition</h2>
                <p class="text-muted fs-6 mb-0">Four foundational stages crafted to turn complex international career and visa ambitions into structured, predictable milestones.</p>
            </div>

            <div class="storytelling-stages-wrap">
                <!-- ==================== STAGE 01: CONTENT LEFT | IMAGE RIGHT ==================== -->
                <div class="story-stage is-active" data-stage="1">
                    <div class="row align-items-center g-4 g-lg-5">
                        <div class="col-lg-6 lms-reveal lms-reveal-left">
                            <div class="story-stage-meta">
                                <span class="story-stage-num">01 / 04</span>
                                <span class="story-stage-eyebrow">EXPERT-LED LEARNING</span>
                            </div>
                            <h3 class="story-title">Structured, Industry-Focused Masterclasses</h3>
                            <p class="story-desc">
                                Learn directly from seasoned practitioners who navigate global visa regulations, foreign education frameworks, and international career benchmarks every single day. Every curriculum is updated with real-world policy evolutions and battle-tested industry systems.
                            </p>
                            <ul class="story-chips">
                                <li class="story-chip"><i class="fa-solid fa-chalkboard-user"></i> 28+ Years Mentorship</li>
                                <li class="story-chip"><i class="fa-solid fa-file-lines"></i> Real-World Case Studies</li>
                                <li class="story-chip"><i class="fa-solid fa-lock-open"></i> Locked-Syllabus Roadmaps</li>
                            </ul>
                        </div>
                        <div class="col-lg-6 lms-reveal lms-reveal-right lms-delay-1">
                            <div class="story-img-card lms-img-hover">
                                <img src="assets/images/study_abroad.jpeg" 
                                     alt="Expert-Led Learning at CAVA" 
                                     class="story-img" 
                                     loading="lazy" 
                                     onerror="this.src='https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=900&q=80'">
                                <div class="story-img-tag">
                                    <i class="fa-solid fa-award"></i> Mentor-Verified Curriculum
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== STAGE 02: IMAGE LEFT | CONTENT RIGHT ==================== -->
                <div class="story-stage" data-stage="2">
                    <div class="row align-items-center g-4 g-lg-5">
                        <div class="col-lg-6 order-2 order-lg-1 lms-reveal lms-reveal-left lms-delay-1">
                            <div class="story-img-card lms-img-hover">
                                <img src="assets/images/work_abroad.jpeg" 
                                     alt="Flexible Online Education" 
                                     class="story-img" 
                                     loading="lazy" 
                                     onerror="this.src='https://images.unsplash.com/photo-1588702547919-26089e690ecc?auto=format&fit=crop&w=900&q=80'">
                                <div class="story-img-tag">
                                    <i class="fa-solid fa-clock"></i> Learn Anytime, Anywhere
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 order-1 order-lg-2 lms-reveal lms-reveal-right">
                            <div class="story-stage-meta">
                                <span class="story-stage-num">02 / 04</span>
                                <span class="story-stage-eyebrow">FLEXIBLE PACING</span>
                            </div>
                            <h3 class="story-title">Learn Wherever Your Schedule Takes You</h3>
                            <p class="story-desc">
                                Study without disrupting your current career or academic commitments. Enjoy high-speed video playback, progressive lesson unlocking, and instant continuation from any smartphone, tablet, or desktop with 100% lifetime access.
                            </p>
                            <ul class="story-chips">
                                <li class="story-chip"><i class="fa-solid fa-infinity"></i> Lifetime Access</li>
                                <li class="story-chip"><i class="fa-solid fa-mobile-screen"></i> Cross-Device Sync</li>
                                <li class="story-chip"><i class="fa-solid fa-gauge-high"></i> High-Speed Streaming</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- ==================== STAGE 03: CONTENT LEFT | IMAGE RIGHT ==================== -->
                <div class="story-stage" data-stage="3">
                    <div class="row align-items-center g-4 g-lg-5">
                        <div class="col-lg-6 lms-reveal lms-reveal-left">
                            <div class="story-stage-meta">
                                <span class="story-stage-num">03 / 04</span>
                                <span class="story-stage-eyebrow">CAREER PATHWAYS</span>
                            </div>
                            <h3 class="story-title">Build Skills for International Career Goals</h3>
                            <p class="story-desc">
                                Gain competitive clarity on Express Entry, Provincial Nominee Programs (PNP), CRS scoring criteria, Educational Credential Assessments (ECA), and job market benchmarks across Australia, Canada, the UK, Germany, and Europe.
                            </p>
                            <ul class="story-chips">
                                <li class="story-chip"><i class="fa-solid fa-chart-line"></i> CRS Score Optimization</li>
                                <li class="story-chip"><i class="fa-solid fa-passport"></i> Skilled Migration Blueprints</li>
                                <li class="story-chip"><i class="fa-solid fa-file-circle-check"></i> ECA Verification Guides</li>
                            </ul>
                        </div>
                        <div class="col-lg-6 lms-reveal lms-reveal-right lms-delay-1">
                            <div class="story-img-card lms-img-hover">
                                <img src="assets/images/visa_consultancy.jpeg" 
                                     alt="Career Pathways and Visa Guidance" 
                                     class="story-img" 
                                     loading="lazy" 
                                     onerror="this.src='https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=900&q=80'">
                                <div class="story-img-tag">
                                    <i class="fa-solid fa-globe"></i> Global Qualification Clarity
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== STAGE 04: IMAGE LEFT | CONTENT RIGHT ==================== -->
                <div class="story-stage" data-stage="4">
                    <div class="row align-items-center g-4 g-lg-5">
                        <div class="col-lg-6 order-2 order-lg-1 lms-reveal lms-reveal-left lms-delay-1">
                            <div class="story-img-card lms-img-hover">
                                <img src="assets/images/success_formula.jpeg" 
                                     alt="Dedicated Support Desk" 
                                     class="story-img" 
                                     loading="lazy" 
                                     onerror="this.src='https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=900&q=80'">
                                <div class="story-img-tag">
                                    <i class="fa-solid fa-headset"></i> Dedicated Help Desk
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 order-1 order-lg-2 lms-reveal lms-reveal-right">
                            <div class="story-stage-meta">
                                <span class="story-stage-num">04 / 04</span>
                                <span class="story-stage-eyebrow">DEDICATED SUPPORT</span>
                            </div>
                            <h3 class="story-title">Comprehensive Support Throughout Your Journey</h3>
                            <p class="story-desc">
                                Never feel stuck with unanswered doubts. Our integrated support desk, regular evaluation sessions, and mentor guidance ensure that every query is answered with verified precision, clear timelines, and personalized attention.
                            </p>
                            <ul class="story-chips">
                                <li class="story-chip"><i class="fa-solid fa-ticket"></i> Integrated Support Desk</li>
                                <li class="story-chip"><i class="fa-solid fa-comments"></i> 1-on-1 Query Handling</li>
                                <li class="story-chip"><i class="fa-solid fa-users"></i> Live Evaluation Meetups</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         04. FEATURED COURSES (Dynamic PHP Course Catalog)
         ========================================================================= -->
    <section class="lp-section-padding" id="featured-courses">
        <div class="container">
            <div class="lp-section-title-wrap text-center lms-reveal lms-reveal-up">
                <span class="lp-section-badge">SELF-PACED COURSES</span>
                <h2 class="lp-section-title">Featured Study Programs</h2>
                <p class="lp-section-subtitle">High-quality video modules with lifetime access and locked-syllabus structures.</p>
            </div>

            <div class="row lp-card-grid lms-reveal lms-reveal-up lms-delay-1">
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
                <div class="text-center mt-5 lms-reveal lms-reveal-up lms-delay-2">
                    <a href="courses.php" class="lp-btn-pill-dark">
                        Explore All Courses <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- =========================================================================
         05. LEARNING JOURNEY (5-Step Visual Step Architecture)
         ========================================================================= -->
    <section class="lp-journey-section border-top border-bottom bg-light" id="learning-journey">
        <div class="container">
            <div class="text-center lms-reveal lms-reveal-up" style="max-width: 720px; margin: 0 auto;">
                <span class="lp-section-badge">THE BLUEPRINT</span>
                <h2 class="lp-section-title">Your 5-Stage Learning Journey</h2>
                <p class="text-muted fs-6">A structured, proven pathway designed to guide you from initial discovery to international readiness.</p>
            </div>

            <div class="journey-steps-grid lms-reveal lms-reveal-up lms-delay-1">
                <!-- Step 01 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">01</span>
                        <i class="fa-solid fa-compass journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Discover</h5>
                    <p class="journey-step-desc">Identify your target country, evaluate eligible visa streams, and benchmark profile strengths.</p>
                </div>

                <!-- Step 02 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">02</span>
                        <i class="fa-solid fa-layer-group journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Explore</h5>
                    <p class="journey-step-desc">Browse structured course curriculums, locked syllabus roadmaps, and expert mentors.</p>
                </div>

                <!-- Step 03 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">03</span>
                        <i class="fa-solid fa-laptop-code journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Learn</h5>
                    <p class="journey-step-desc">Master video lessons at your own pace with lifetime access and progress checkpoints.</p>
                </div>

                <!-- Step 04 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">04</span>
                        <i class="fa-solid fa-file-circle-check journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Practice</h5>
                    <p class="journey-step-desc">Prepare documentation blueprints, score calculations, and mock evaluation checklists.</p>
                </div>

                <!-- Step 05 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">05</span>
                        <i class="fa-solid fa-rocket journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Grow</h5>
                    <p class="journey-step-desc">Achieve verified milestone readiness and start your international career journey with confidence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         06. CAREER & IMMIGRATION OPPORTUNITIES (Alternating Split Layout)
         ========================================================================= -->
    <section class="lp-immi-section" id="immigration-opportunities">
        <div class="container">
            <!-- Block 1: Left Visual / Right Content (Australia & Canada) -->
            <div class="row align-items-center g-5 lp-career-alternating-block">
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
                                <p class="lp-immi-floating-sub">Expert support for a smooth journey.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 lms-reveal lms-reveal-right lms-delay-1">
                    <span class="lp-content-badge">IMMIGRATION OPPORTUNITIES</span>
                    <h2 class="lp-content-title">Australia & Canada Pathways</h2>
                    <p class="lp-content-desc">
                        Explore the best immigration pathways to build a secure future for you and your family in Australia or Canada with verified, up-to-date procedures.
                    </p>

                    <div class="lp-immi-features-grid">
                        <!-- Item 1: Skilled Migration Pathways -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Skilled Migration Pathways</h5>
                                <p class="lp-immi-feature-desc">Multiple immigration programs for qualified global professionals.</p>
                            </div>
                        </div>

                        <!-- Item 2: Family Sponsorship -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <i class="fa-solid fa-people-roof"></i>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Family Sponsorship</h5>
                                <p class="lp-immi-feature-desc">Clear guidance to sponsor eligible family members under official regulations.</p>
                            </div>
                        </div>

                        <!-- Item 3: Permanent Residency (PR) -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Permanent Residency (PR)</h5>
                                <p class="lp-immi-feature-desc">Actionable steps to maximize points and secure PR status.</p>
                            </div>
                        </div>

                        <!-- Item 4: Healthcare & Education Benefits -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <i class="fa-solid fa-heart-pulse"></i>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Healthcare & Education</h5>
                                <p class="lp-immi-feature-desc">Access world-class social benefits, public healthcare, and institutions.</p>
                            </div>
                        </div>

                        <!-- Item 5: Work & Settle Abroad -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Work & Settle Abroad</h5>
                                <p class="lp-immi-feature-desc">Live, work, and build a rewarding long-term international career.</p>
                            </div>
                        </div>

                        <!-- Item 6: Pathway to Citizenship -->
                        <div class="lp-immi-feature-item">
                            <div class="lp-immi-feature-icon">
                                <i class="fa-solid fa-passport"></i>
                            </div>
                            <div class="lp-immi-feature-content">
                                <h5 class="lp-immi-feature-title">Pathway to Citizenship</h5>
                                <p class="lp-immi-feature-desc">Structured compliance after meeting residency requirements.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Block 2: Alternating (Left Content / Right Visual - Global Career Expansion) -->
            <div class="row align-items-center g-5 lp-career-alternating-block mt-4">
                <div class="col-lg-7 order-2 order-lg-1 lms-reveal lms-reveal-left">
                    <span class="lp-content-badge">GLOBAL EXPANSION</span>
                    <h2 class="lp-content-title">Worldwide Career & Visa Guidance</h2>
                    <p class="lp-content-desc">
                        Gain comprehensive insights into credential equivalencies, language score optimization, and cross-border career transitions across the UK, Germany, Europe, and North America.
                    </p>

                    <ul class="lp-content-checklist">
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#111111"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>ECA & CRS score calculation breakdowns</span>
                        </li>
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#111111"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Step-by-step document checklist and error prevention</span>
                        </li>
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#111111"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Specialized roadmaps for IT, Healthcare, Engineering & Finance</span>
                        </li>
                        <li class="lp-content-checklist-item">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                                <circle cx="10" cy="10" r="10" fill="#111111"/>
                                <path d="M6 10L8.5 12.5L14 7" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Post-landing guidance and professional integration tips</span>
                        </li>
                    </ul>

                    <div>
                        <a href="courses.php" class="lp-btn-pill-dark">
                            Browse Career Programs <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-5 order-1 order-lg-2 text-center text-lg-end lms-reveal lms-reveal-right lms-delay-1">
                    <div class="lp-about-img-box">
                        <div class="lp-img-decor-dots lp-decor-dots-mr"></div>
                        <div class="lp-about-backdrop-card lp-backdrop-offset-bl"></div>
                        <div class="lp-about-img-frame lms-img-hover">
                            <img src="assets/images/buissness_blueprint.png" 
                                 alt="Global Career Expansion Blueprint" 
                                 loading="lazy" 
                                 onerror="this.src='assets/images/start_your_carrer_abroad.png'">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         07. EVENTS SECTION (Dynamic PHP Campus Events)
         ========================================================================= -->
    <section class="lp-section-padding bg-light border-top border-bottom" id="upcoming-events">
        <div class="container">
            <div class="lp-section-title-wrap text-center lms-reveal lms-reveal-up">
                <span class="lp-section-badge">CAMPUS EVENTS</span>
                <h2 class="lp-section-title">Upcoming Events & Fairs</h2>
                <p class="lp-section-subtitle">Stay up to date with visa fairs, university meetups, and mock evaluation sessions.</p>
            </div>

            <div class="row justify-content-center lms-reveal lms-reveal-up lms-delay-1">
                <?php if (empty($upcomingEvents)): ?>
                    <div class="col text-center py-5">
                        <p class="text-muted fs-5">No upcoming events listed at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($upcomingEvents as $ev):
                        $evDate = date('d M, Y', strtotime($ev['date']));
                        $evImg = 'https://placehold.co/600x340/111111/ffffff?text=Event';
                        if ($ev['event_image'] && file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                            $evImg = SITE_URL . '/uploads/' . $ev['event_image'];
                        }
                        ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="custom-card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100 event-card">
                                <div class="card-img-wrapper">
                                    <img src="<?php echo $evImg; ?>" alt="Event banner" class="img-fluid"
                                        style="height: 180px; width: 100%; object-fit: cover;" loading="lazy"
                                        onerror="this.src='assets/images/study_abroad.jpeg'">
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
                <div class="text-center mt-5 lms-reveal lms-reveal-up lms-delay-2">
                    <a href="events.php" class="lp-btn-pill-dark">
                        View All Events <i class="fa-solid fa-arrow-right ms-2"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- =========================================================================
         08. TESTIMONIALS / STUDENT SUCCESS STORIES
         ========================================================================= -->
    <section class="lp-section-padding" id="testimonials">
        <div class="container">
            <div class="lp-section-title-wrap text-center lms-reveal lms-reveal-up">
                <span class="lp-section-badge">STUDENT REVIEWS</span>
                <h2 class="lp-section-title">Success Stories</h2>
                <p class="lp-section-subtitle">Hear from our students who successfully migrated and upgraded their careers.</p>
            </div>

            <div class="row g-4 lms-reveal lms-reveal-up lms-delay-1">
                <div class="col-md-4">
                    <div class="lp-testimonial-card">
                        <div class="quote-icon"><i class="fa-solid fa-quote-left"></i></div>
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="lp-testimonial-text">"The Canada Immigration Masterclass made the Express Entry process so simple. The locked syllabus checked out perfectly, and I cleared my ECA credentials doubts."</p>
                        <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                            <div class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold">AM</div>
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
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="lp-testimonial-text">"Amazing quality! The video modules are extremely thorough and concise. I gained direct clarity on provincial nomination quotas and settled my application flawlessly."</p>
                        <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                            <div class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold">SP</div>
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
                        <div class="mb-3 text-warning">
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                            <i class="fa-solid fa-star"></i>
                        </div>
                        <p class="lp-testimonial-text">"The CRS Point System breakdown videos are top tier. High quality streaming without lag, and I unlocked the entire curriculum in seconds. Highly recommended!"</p>
                        <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                            <div class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold">RK</div>
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

    <!-- =========================================================================
         09. ABOUT CAVA / FOUNDER SPOTLIGHT (Amarnath Singh)
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
                                <img src="assets/images/amarnath.png" alt="Amarnath Singh - Founder & Chief Mentor" loading="lazy" onerror="this.src='https://placehold.co/400x400/230d4a/ffffff?text=Amarnath'">
                            </div>
                            <h4 class="lp-founder-name">Amarnath Singh</h4>
                            <p class="lp-founder-role">Founder & Chief Mentor, CAVA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         10. SUPPORT DESK / QUERY SUBMISSION
         ========================================================================= -->
    <section class="lp-section-padding lp-contact-section" id="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8 lms-reveal lms-reveal-up">
                    <div class="lp-contact-container p-4 p-md-5">
                        <div class="text-center mb-5">
                            <span class="lp-section-badge">SUPPORT DESK</span>
                            <h2 class="fw-bold mb-2">Have a Query? Ask Us!</h2>
                            <p class="text-muted">Submit your question below, and our support team will respond shortly. You can also visit our <a href="support.php" class="text-decoration-underline text-dark fw-semibold">Support Center</a>.</p>
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
                                        <label for="query_message" class="lp-form-label">Your Support Query Message</label>
                                        <textarea class="form-control lp-form-control" id="query_message"
                                            name="query_message" rows="4" placeholder="Describe your doubt here in detail..."
                                            required oninput="updateWordCount(this)" style="resize: none; height: 120px;"></textarea>
                                        <div class="d-flex justify-content-end align-items-center mt-1 gap-2">
                                            <span id="word-limit-error" class="text-danger small fw-semibold" style="display:none; margin-right:auto;">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Word limit reached!
                                            </span>
                                            <small id="word-count-display" class="fw-semibold text-muted">0 / 100 words</small>
                                            <i class="fa-solid fa-circle-info text-muted" style="cursor:help;" data-bs-toggle="tooltip" data-bs-placement="top" title="Maximum 100 words allowed."></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="lp-btn-primary w-100 py-3" style="border-radius: 100px;">
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

    <!-- =========================================================================
         11. FINAL CTA SECTION (Clean, High-Impact Closing Banner)
         ========================================================================= -->
    <section class="lp-final-cta-section" id="final-cta">
        <div class="container">
            <div class="lp-final-cta-box lms-reveal lms-reveal-up">
                <span class="lp-final-cta-badge">START YOUR JOURNEY TODAY</span>
                <h2 class="lp-final-cta-title">Ready to Build Your Global Future?</h2>
                <p class="lp-final-cta-desc">Explore CAVA courses and master proven career and migration blueprints with lifetime access and expert mentor guidance.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="courses.php" class="lp-btn-pill-white">
                        <i class="fa-solid fa-book-open me-2"></i> Explore Courses
                    </a>
                    <a href="support.php" class="lp-btn-pill-outline">
                        <i class="fa-solid fa-headset me-2"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function updateWordCount(textarea) {
    var text = textarea.value.trim();
    var wordsArr = text === '' ? [] : text.split(/\s+/);
    var words = wordsArr.length;
    var display = document.getElementById('word-count-display');
    var errorDiv = document.getElementById('word-limit-error');

    // Hard-cap at 100 words: truncate extra words
    if (words > 100) {
        textarea.value = wordsArr.slice(0, 100).join(' ');
        words = 100;
        errorDiv.style.display = 'inline';
        textarea.style.borderColor = '#dc3545';
    } else {
        errorDiv.style.display = 'none';
        textarea.style.borderColor = '';
    }

    display.textContent = words + ' / 100 words';
    display.style.color = '#198754';
}

// Init Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipEls.forEach(function(el) { new bootstrap.Tooltip(el); });
});
</script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>