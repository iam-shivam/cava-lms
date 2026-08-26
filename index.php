<?php
// Public Landing Page - CAVA LMS Premium International Education & Career Platform
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

$heroEyebrow = $settings['hero_eyebrow'] ?? 'CAVA CAREER ABROAD VISA ACADEMY';
$heroTitle = $settings['hero_title'] ?? 'Learn Today. Build Your Future.';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Master international career and migration pathways with industry-led masterclasses, locked-syllabus curriculums, and lifetime access.';
$aboutUs = $settings['about_us'] ?? 'CAVA LMS is a premium e-learning portal designed to provide quality, affordable education for career development.';

$heroStat1Title = $settings['hero_stat_1_title'] ?? 'Global Students';
$heroStat1Desc = $settings['hero_stat_1_desc'] ?? 'Ages 16-45';
$heroStat2Title = $settings['hero_stat_2_title'] ?? 'Top Instructors';
$heroStat2Desc = $settings['hero_stat_2_desc'] ?? '28+ Years Expertise';
$heroStat3Title = $settings['hero_stat_3_title'] ?? 'Tailored Guidance';
$heroStat3Desc = $settings['hero_stat_3_desc'] ?? 'Career & Visa Mentorship';

// Dynamic Hero Banner Single Image
$heroImg1 = (!empty($settings['hero_img_1']) && file_exists(BASE_PATH . '/uploads/' . $settings['hero_img_1']))
    ? SITE_URL . '/uploads/' . $settings['hero_img_1']
    : 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=1000&h=600';

// Fetch dynamic data
$userId = $_SESSION['user_id'] ?? null;

// Fetch dynamic data (excluding ones already bought/registered by the user)
$featuredCourses = Course::getFeatured(3, $userId);
$upcomingEvents = Event::getAll(3);

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <!-- =========================================================================
         01. HERO SECTION (Editorial Layout with Single High-Impact Image)
         ========================================================================= -->
    <section class="lp-hero-wa" id="hero">
        <div class="container position-relative" style="z-index: 1;">
            <div class="row align-items-center g-4 g-lg-5 py-4">
                <div class="col-lg-6 pe-lg-4 text-center text-lg-start">
                    <span
                        class="lp-hero-tag d-inline-block text-uppercase fw-bold text-white-50 fs-8 mb-3 letter-spacing-1 lms-reveal lms-reveal-up">
                        <i class="fa-solid fa-graduation-cap me-1 text-purple"></i>
                        <?php echo htmlspecialchars($heroEyebrow); ?>
                    </span>
                    <h1 class="lp-hero-wa-title lms-reveal lms-reveal-up">
                        <span class="hero-main-line"><?php echo htmlspecialchars($heroTitle); ?></span>
                    </h1>
                    <p class="lp-hero-wa-subtitle lms-reveal lms-reveal-up lms-delay-1 fs-6 text-white-50 mb-4"
                        style="line-height: 1.7; max-width: 540px;">
                        <?php echo htmlspecialchars($heroSubtitle); ?>
                    </p>

                    <div
                        class="lp-hero-cta-group d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 lms-reveal lms-reveal-up lms-delay-2">
                        <a href="#featured-courses" class="lp-btn-pill-white">
                            <i class="fa-solid fa-book-open me-1"></i> Explore Courses <i
                                class="fa-solid fa-arrow-right-long ms-1"></i>
                        </a>
                        <a href="#why-cava" class="lp-btn-pill-outline">
                            <i class="fa-solid fa-compass me-1"></i> Learn More <i
                                class="fa-solid fa-chevron-down ms-1"></i>
                        </a>
                    </div>

                    <!-- Statistics / Trust Blocks Row -->
                    <div class="lp-hero-stats-row lms-reveal lms-reveal-up lms-delay-3">
                        <div class="lp-hero-stat-item">
                            <div class="lp-hero-stat-heading"><?php echo htmlspecialchars($heroStat1Title); ?></div>
                            <div class="lp-hero-stat-desc"><?php echo htmlspecialchars($heroStat1Desc); ?></div>
                        </div>
                        <div class="lp-hero-stat-item">
                            <div class="lp-hero-stat-heading"><?php echo htmlspecialchars($heroStat2Title); ?></div>
                            <div class="lp-hero-stat-desc"><?php echo htmlspecialchars($heroStat2Desc); ?></div>
                        </div>
                        <div class="lp-hero-stat-item">
                            <div class="lp-hero-stat-heading"><?php echo htmlspecialchars($heroStat3Title); ?></div>
                            <div class="lp-hero-stat-desc"><?php echo htmlspecialchars($heroStat3Desc); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Single Hero Banner Image representation -->
                <div class="col-lg-6 ps-lg-4 mt-4 mt-lg-0">
                    <div class="lp-hero-single-img-wrapper lms-reveal-hero-img lms-img-hover lms-delay-1">
                        <img src="<?php echo $heroImg1; ?>" alt="CAVA LMS - International Education & Career Platform"
                            class="lp-hero-single-img" loading="eager"
                            onerror="this.src='assets/images/study_abroad.jpeg'">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         02. WHY CAVA (Strong Editorial Section with 4 Benefit Items)
         ========================================================================= -->
    <section class="why-choose-section border-bottom" id="why-cava">
        <div class="container text-center lms-reveal lms-reveal-up">
            <span class="lp-section-badge">WHY CAVA</span>
            <h2 class="why-choose-title">More Than Courses. <span class="blue-highlight">A Clearer Path Forward.</span>
            </h2>
            <p class="why-choose-subtitle">CAVA combines structured masterclasses, experienced mentors, career
                preparation, and international migration guidance into a unified, predictable milestone roadmap.</p>

            <div class="row g-4 mt-2 text-start">
                <!-- Benefit 01 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                        <h4 class="why-choose-card-title">Expert-Led Learning</h4>
                        <p class="why-choose-card-desc">Learn directly from seasoned visa practitioners and career
                            mentors with over 28 years of practical field experience.</p>
                        <span class="why-choose-number">01</span>
                    </div>
                </div>
                <!-- Benefit 02 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-1">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-laptop-code"></i>
                        </div>
                        <h4 class="why-choose-card-title">Flexible Learning</h4>
                        <p class="why-choose-card-desc">Self-paced video modules accessible 24/7 across mobile, tablet,
                            and desktop with 100% lifetime curriculum access.</p>
                        <span class="why-choose-number">02</span>
                    </div>
                </div>
                <!-- Benefit 03 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-2">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <h4 class="why-choose-card-title">Career-Focused Preparation</h4>
                        <p class="why-choose-card-desc">Actionable curriculums designed to unlock international job
                            benchmarks, credential assessments, and interview readiness.</p>
                        <span class="why-choose-number">03</span>
                    </div>
                </div>
                <!-- Benefit 04 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card lms-reveal lms-reveal-up lms-delay-3">
                        <div class="why-choose-icon">
                            <i class="fa-solid fa-globe"></i>
                        </div>
                        <h4 class="why-choose-card-title">Global Guidance & Support</h4>
                        <p class="why-choose-card-desc">Clear roadmaps for Canada, Australia, the UK, Germany, USA, and
                            28+ European countries with dedicated query resolution.</p>
                        <span class="why-choose-number">04</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         03. EDITORIAL ALTERNATING STORYTELLING SECTION (4 Stages)
         ========================================================================= -->
    <section class="lp-storytelling-section border-bottom" id="storytelling">
        <div class="container">
            <div class="storytelling-intro text-center lms-reveal lms-reveal-up">
                <span class="lp-section-badge">CAVA STORYTELLING</span>
                <h2 class="lp-section-title">Education That Evolves With Your Ambition</h2>
                <p class="text-muted fs-6 mb-0">Four foundational stages crafted to turn complex international career
                    and visa ambitions into structured, predictable milestones.</p>
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
                                Learn directly from seasoned practitioners who navigate global visa regulations, foreign
                                education frameworks, and international career benchmarks every single day. Every
                                curriculum is updated with real-world policy evolutions and battle-tested industry
                                systems.
                            </p>
                            <ul class="story-chips">
                                <li class="story-chip"><i class="fa-solid fa-chalkboard-user"></i> 28+ Years Mentorship
                                </li>
                                <li class="story-chip"><i class="fa-solid fa-file-lines"></i> Real-World Case Studies
                                </li>
                                <li class="story-chip"><i class="fa-solid fa-lock-open"></i> Locked-Syllabus Roadmaps
                                </li>
                            </ul>
                        </div>
                        <div class="col-lg-6 lms-reveal lms-reveal-right lms-delay-1">
                            <div class="story-img-card lms-img-hover">
                                <img src="assets/images/study_abroad.jpeg" alt="Expert-Led Learning at CAVA"
                                    class="story-img" loading="lazy"
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
                                <img src="assets/images/work_abroad.jpeg" alt="Flexible Online Education"
                                    class="story-img" loading="lazy"
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
                                Study without disrupting your current career or academic commitments. Enjoy high-speed
                                video playback, progressive lesson unlocking, and instant continuation from any
                                smartphone, tablet, or desktop with 100% lifetime access.
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
                                Gain competitive clarity on Express Entry, Provincial Nominee Programs (PNP), CRS
                                scoring criteria, Educational Credential Assessments (ECA), and job market benchmarks
                                across Australia, Canada, the UK, Germany, and Europe.
                            </p>
                            <ul class="story-chips">
                                <li class="story-chip"><i class="fa-solid fa-chart-line"></i> CRS Score Optimization
                                </li>
                                <li class="story-chip"><i class="fa-solid fa-passport"></i> Skilled Migration Blueprints
                                </li>
                                <li class="story-chip"><i class="fa-solid fa-file-circle-check"></i> ECA Verification
                                    Guides</li>
                            </ul>
                        </div>
                        <div class="col-lg-6 lms-reveal lms-reveal-right lms-delay-1">
                            <div class="story-img-card lms-img-hover">
                                <img src="assets/images/visa_consultancy.jpeg" alt="Career Pathways and Visa Guidance"
                                    class="story-img" loading="lazy"
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
                                <img src="assets/images/success_formula.jpeg" alt="Dedicated Support Desk"
                                    class="story-img" loading="lazy"
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
                                Never feel stuck with unanswered doubts. Our integrated support desk, regular evaluation
                                sessions, and mentor guidance ensure that every query is answered with verified
                                precision, clear timelines, and personalized attention.
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
         04. FEATURED STUDY PROGRAMS (Dynamic PHP Course Catalog)
         ========================================================================= -->
    <section class="lp-section-padding" id="featured-courses">
        <div class="container">
            <div class="lp-section-title-wrap text-center lms-reveal lms-reveal-up">
                <span class="lp-section-badge">SELF-PACED COURSES</span>
                <h2 class="lp-section-title">Featured Study Programs</h2>
                <p class="lp-section-subtitle text-muted">High-quality video modules with lifetime access and
                    locked-syllabus structures.</p>
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
         05. GLOBAL DESTINATIONS / CAREER PATHWAYS (Editorial Image Tiles)
         ========================================================================= -->
    <section class="lp-destinations-section border-top border-bottom bg-light" id="global-destinations">
        <div class="container">
            <div class="text-center lms-reveal lms-reveal-up" style="max-width: 760px; margin: 0 auto;">
                <span class="lp-section-badge">GLOBAL DESTINATIONS</span>
                <h2 class="lp-section-title">Where Could Your Journey Take You?</h2>
                <p class="text-muted fs-6">Explore the destinations and international career pathways supported through
                    CAVA's learning and guidance ecosystem.</p>
            </div>

            <div class="destination-card-grid lms-reveal lms-reveal-up lms-delay-1">
                <!-- Destination 1: Canada -->
                <a href="courses.php" class="destination-card">
                    <img src="assets/images/study_abroad.jpeg" alt="Canada Pathways" class="destination-bg-img"
                        loading="lazy">
                    <div class="destination-overlay"></div>
                    <div class="destination-content">
                        <span class="destination-badge"><i class="fa-solid fa-passport"></i> Express Entry & PNP</span>
                        <h3 class="destination-title">Canada</h3>
                        <p class="destination-desc">Comprehensive roadmaps for Express Entry (FSW/CEC), Provincial
                            Nominee Programs, CRS points calculation, and ECA credentials.</p>
                        <span class="destination-cta">Explore Pathways <i
                                class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>

                <!-- Destination 2: Australia -->
                <a href="courses.php" class="destination-card">
                    <img src="assets/images/immigration_opportunities.png" alt="Australia Pathways"
                        class="destination-bg-img" loading="lazy" onerror="this.src='assets/images/work_abroad.jpeg'">
                    <div class="destination-overlay"></div>
                    <div class="destination-content">
                        <span class="destination-badge"><i class="fa-solid fa-briefcase"></i> Skilled Migration</span>
                        <h3 class="destination-title">Australia</h3>
                        <p class="destination-desc">Step-by-step guidance on Subclass 189, 190, 491 visas, SkillSelect
                            points assessments, and occupation list verifications.</p>
                        <span class="destination-cta">Explore Pathways <i
                                class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>

                <!-- Destination 3: United Kingdom -->
                <a href="courses.php" class="destination-card">
                    <img src="assets/images/buissness_blueprint.png" alt="United Kingdom Pathways"
                        class="destination-bg-img" loading="lazy"
                        onerror="this.src='assets/images/visa_consultancy.jpeg'">
                    <div class="destination-overlay"></div>
                    <div class="destination-content">
                        <span class="destination-badge"><i class="fa-solid fa-award"></i> Skilled Worker &
                            Graduate</span>
                        <h3 class="destination-title">United Kingdom</h3>
                        <p class="destination-desc">Structured curriculums covering UK Skilled Worker sponsorships,
                            Graduate Route extensions, and professional licensing benchmarkings.</p>
                        <span class="destination-cta">Explore Pathways <i
                                class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>

                <!-- Destination 4: USA -->
                <a href="courses.php" class="destination-card">
                    <img src="assets/images/start_your_carrer_abroad.png" alt="USA Pathways" class="destination-bg-img"
                        loading="lazy" onerror="this.src='assets/images/study_abroad.jpeg'">
                    <div class="destination-overlay"></div>
                    <div class="destination-content">
                        <span class="destination-badge"><i class="fa-solid fa-building-columns"></i> Academic &
                            Career</span>
                        <h3 class="destination-title">USA</h3>
                        <p class="destination-desc">Master academic program selections, STEM OPT extensions, and
                            specialized career pathways with structured mentor insights.</p>
                        <span class="destination-cta">Explore Pathways <i
                                class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>

                <!-- Destination 5: Germany -->
                <a href="courses.php" class="destination-card">
                    <img src="assets/images/success_formula.jpeg" alt="Germany Pathways" class="destination-bg-img"
                        loading="lazy">
                    <div class="destination-overlay"></div>
                    <div class="destination-content">
                        <span class="destination-badge"><i class="fa-solid fa-certificate"></i> Opportunity Card & Blue
                            Card</span>
                        <h3 class="destination-title">Germany</h3>
                        <p class="destination-desc">Navigate the Chancenkarte (Opportunity Card), EU Blue Card rules,
                            language requirements, and tech/engineering job benchmarks.</p>
                        <span class="destination-cta">Explore Pathways <i
                                class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>

                <!-- Destination 6: Europe -->
                <a href="courses.php" class="destination-card">
                    <img src="assets/images/every_international_dream.jpeg" alt="Europe Pathways"
                        class="destination-bg-img" loading="lazy"
                        onerror="this.src='assets/images/visa_consultancy.jpeg'">
                    <div class="destination-overlay"></div>
                    <div class="destination-content">
                        <span class="destination-badge"><i class="fa-solid fa-globe"></i> 28+ European Nations</span>
                        <h3 class="destination-title">Europe</h3>
                        <p class="destination-desc">Broad roadmaps covering Schengen zone skilled worker frameworks,
                            post-study work rights, and cross-border European mobility.</p>
                        <span class="destination-cta">Explore Pathways <i
                                class="fa-solid fa-arrow-right-long"></i></span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         06. COMBINED HIGH-VALUE SECTION:
             PART A — LEARNING JOURNEY (5 Steps)
             PART B — UPCOMING EVENTS (Dynamic PHP)
             PART C — SUCCESS STORIES (Student Reviews)
         ========================================================================= -->
    <section class="lp-journey-section border-bottom" id="learning-journey">
        <div class="container">
            <!-- PART A — LEARNING JOURNEY -->
            <div class="text-center lms-reveal lms-reveal-up" style="max-width: 720px; margin: 0 auto;">
                <span class="lp-section-badge">THE BLUEPRINT</span>
                <h2 class="lp-section-title">Your 5-Stage Learning Journey</h2>
                <p class="text-muted fs-6">A structured, proven pathway designed to guide you from initial discovery to
                    international readiness.</p>
            </div>

            <div class="journey-steps-grid lms-reveal lms-reveal-up lms-delay-1 mb-5 pb-4">
                <!-- Step 01 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">01</span>
                        <i class="fa-solid fa-compass journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Discover</h5>
                    <p class="journey-step-desc">Identify your target country, evaluate eligible visa streams, and
                        benchmark profile strengths.</p>
                </div>

                <!-- Step 02 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">02</span>
                        <i class="fa-solid fa-layer-group journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Explore</h5>
                    <p class="journey-step-desc">Browse structured course curriculums, locked syllabus roadmaps, and
                        mentor profiles.</p>
                </div>

                <!-- Step 03 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">03</span>
                        <i class="fa-solid fa-laptop-code journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Learn</h5>
                    <p class="journey-step-desc">Master video lessons at your own pace with lifetime access and
                        progressive checkpoints.</p>
                </div>

                <!-- Step 04 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">04</span>
                        <i class="fa-solid fa-file-circle-check journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Practice</h5>
                    <p class="journey-step-desc">Prepare documentation blueprints, score calculations, and mock
                        evaluation checklists.</p>
                </div>

                <!-- Step 05 -->
                <div class="journey-step-card">
                    <div class="journey-step-number-wrap">
                        <span class="journey-step-num">05</span>
                        <i class="fa-solid fa-rocket journey-step-icon"></i>
                    </div>
                    <h5 class="journey-step-title">Grow</h5>
                    <p class="journey-step-desc">Achieve verified milestone readiness and start your international
                        career journey with confidence.</p>
                </div>
            </div>

            <!-- PART B — UPCOMING EVENTS (Dynamic Events) -->
            <div class="pt-5 border-top" id="upcoming-events">
                <div class="lp-section-title-wrap text-center lms-reveal lms-reveal-up">
                    <span class="lp-section-badge">CAMPUS & ONLINE EVENTS</span>
                    <h2 class="lp-section-title">Upcoming Events & Fairs</h2>
                    <p class="text-muted fs-6">Stay up to date with visa fairs, university meetups, and live evaluation
                        sessions.</p>
                </div>

                <div class="row justify-content-center lms-reveal lms-reveal-up lms-delay-1 mt-4">
                    <?php if (empty($upcomingEvents)): ?>
                        <div class="col text-center py-4">
                            <p class="text-muted fs-6">No upcoming events listed at the moment.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingEvents as $ev):
                            $evDate = date('d M, Y', strtotime($ev['date']));
                            $evImg = 'assets/images/study_abroad.jpeg';
                            if ($ev['event_image'] && file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                                $evImg = SITE_URL . '/uploads/' . $ev['event_image'];
                            }
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="custom-card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100 event-card">
                                    <div class="card-img-wrapper" style="height: 180px;">
                                        <img src="<?php echo $evImg; ?>" alt="Event banner" class="img-fluid"
                                            style="height: 100%; width: 100%; object-fit: cover;" loading="lazy"
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
                                        <p class="text-muted fs-7 mb-3"
                                            style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($ev['description']); ?></p>
                                        <div class="mt-auto pt-2 border-top">
                                            <a href="events.php" class="btn btn-outline-primary btn-sm rounded-pill px-3 w-100">
                                                View Event Details <i class="fa-solid fa-arrow-right-long ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <?php if (!empty($upcomingEvents)): ?>
                    <div class="text-center mt-4 lms-reveal lms-reveal-up lms-delay-2">
                        <a href="events.php" class="lp-btn-pill-dark">
                            View All Events <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PART C — SUCCESS STORIES -->
            <div class="pt-5 mt-5 border-top" id="testimonials">
                <div class="lp-section-title-wrap text-center lms-reveal lms-reveal-up">
                    <span class="lp-section-badge">STUDENT REVIEWS</span>
                    <h2 class="lp-section-title">Success Stories</h2>
                    <p class="text-muted fs-6">Hear from our students who successfully migrated and upgraded their
                        careers.</p>
                </div>

                <div class="row g-4 lms-reveal lms-reveal-up lms-delay-1 mt-2">
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
                            <p class="lp-testimonial-text">"The Canada Immigration Masterclass made the Express Entry
                                process so simple. The locked syllabus checked out perfectly, and I cleared my ECA
                                credentials doubts."</p>
                            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                                <div
                                    class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                    AM</div>
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
                            <p class="lp-testimonial-text">"Amazing quality! The video modules are extremely thorough
                                and concise. I gained direct clarity on provincial nomination quotas and settled my
                                application flawlessly."</p>
                            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                                <div
                                    class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                    SP</div>
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
                            <p class="lp-testimonial-text">"The CRS Point System breakdown videos are top tier. High
                                quality streaming without lag, and I unlocked the entire curriculum in seconds. Highly
                                recommended!"</p>
                            <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top">
                                <div
                                    class="lp-avatar-initials rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                    RK</div>
                                <div>
                                    <h6 class="fw-bold m-0 text-dark">Rajesh Kumar</h6>
                                    <small class="text-muted">PR Holder, Alberta</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================================================================
         07. TRUST / SUPPORT / CLOSING BANNER:
             PART A — FOUNDER & CHIEF MENTOR SPOTLIGHT (Amarnath Singh)
             PART B — COMPACT SUPPORT CONVERSION BLOCK
             PART C — FINAL CTA (Closing Banner)
         ========================================================================= -->
    <!-- PART A: Founder Spotlight -->
    <section class="lp-founder-section" id="founder-spotlight">
        <div class="container">
            <div class="lp-founder-card-wrap">
                <div class="row align-items-center g-5">
                    <!-- Left: Founder Portrait -->
                    <div class="col-lg-5 order-2 order-lg-1 lms-reveal lms-reveal-left">
                        <div class="lp-founder-img-box text-center">
                            <div class="lp-founder-avatar-frame lms-img-hover mx-auto">
                                <img src="assets/images/amarnath.png" alt="Amarnath Singh - Founder & Chief Mentor"
                                    loading="lazy" onerror="this.src='assets/images/logo.jpeg'">
                            </div>
                            <h4 class="lp-founder-name mt-3">Amarnath Singh</h4>
                            <p class="lp-founder-role">Founder & Chief Mentor, CAVA</p>
                        </div>
                    </div>

                    <!-- Right: Details -->
                    <div class="col-lg-7 order-1 order-lg-2 lms-reveal lms-reveal-right lms-delay-1">
                        <span class="lp-founder-badge">MEET YOUR MENTOR</span>
                        <h2 class="lp-founder-title">Why Learn From <span class="gold-accent">Amarnath</span>?</h2>
                        <p class="lp-founder-quote">"Experience Cannot Be Googled."</p>

                        <ul class="lp-founder-points">
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-certificate"></i>
                                <span><strong>28 years of practical experience</strong> handling and guiding thousands
                                    of successful applicants globally every year.</span>
                            </li>
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-passport"></i>
                                <span><strong>Thousands of Visa Applications</strong> processed with hands-on expertise
                                    across diverse international pathways.</span>
                            </li>
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-globe"></i>
                                <span><strong>Global Footprint:</strong> Real-world experience across USA, United
                                    Kingdom, Australia, Canada, Germany, and 28 European Countries.</span>
                            </li>
                            <li class="lp-founder-point-item">
                                <i class="fa-solid fa-arrows-rotate"></i>
                                <span><strong>Continuous Evolution:</strong> Learning never stops — every strategy is
                                    backed by dynamic policy updates and battle-tested industry systems.</span>
                            </li>
                        </ul>

                        <div class="mt-4">
                            <a href="#featured-courses" class="lp-btn-pill-white">
                                Start Learning With Amarnath <i class="fa-solid fa-arrow-down ms-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PART B: Compact Support Block -->
    <section class="lp-support-section" id="support-cta">
        <div class="container">
            <div class="lp-support-block lms-reveal lms-reveal-up">
                <span class="lp-support-badge">NEED GUIDANCE?</span>
                <h3 class="lp-support-title">Need Help Choosing Your Next Step?</h3>
                <p class="lp-support-desc">Have a question regarding course curriculums, eligibility roadmaps, or
                    international career pathways? Our dedicated support team is ready to assist you.</p>
                <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                    <a href="support.php" class="btn btn-primary rounded-pill px-4 py-3 fw-semibold">
                        <i class="fa-solid fa-circle-question me-2"></i> Ask a Question
                    </a>
                    <a href="support.php" class="btn btn-outline-dark rounded-pill px-4 py-3 fw-semibold">
                        <i class="fa-solid fa-headset me-2"></i> Get Support
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- PART C: Final Closing Banner -->
    <!-- <section class="lp-final-cta-section" id="final-cta">
        <div class="container">
            <div class="lp-final-cta-box lms-reveal lms-reveal-up">
                <span class="lp-final-cta-badge">START YOUR JOURNEY TODAY</span>
                <h2 class="lp-final-cta-title">Ready to Take the Next Step?</h2>
                <p class="lp-final-cta-desc">Explore structured courses, expert guidance and international career opportunities with CAVA LMS.</p>
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
    </section> -->
</div>

<script>
    // Init Bootstrap tooltips if any
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipEls.forEach(function (el) { new bootstrap.Tooltip(el); });
    });
</script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>