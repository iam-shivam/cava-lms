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
                    <h1 class="lp-hero-wa-title">
                        <span class="hero-eyebrow"><?php echo htmlspecialchars($heroEyebrow); ?></span>
                        <span class="hero-main-line"><?php echo htmlspecialchars($heroTitle); ?></span>
                    </h1>
                    <p class="lp-hero-wa-subtitle"><?php echo htmlspecialchars($heroSubtitle); ?></p>

                    <div
                        class="lp-hero-cta-group d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                        <a href="#featured-courses" class="lp-btn-pill-white">
                            <i class="fa-solid fa-book-open me-1"></i> Explore Courses <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </a>
                        <a href="webinars.php" class="lp-btn-pill-outline">
                            <i class="fa-solid fa-tower-broadcast me-1"></i> Join Live Webinar <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </a>
                    </div>

                    <!-- Wix Studio Style Statistics Row inside left column -->
                    <div class="lp-hero-stats-row row g-3 mt-4">
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
                    <div class="lp-hero-single-img-wrapper">
                        <img src="<?php echo $heroImg1; ?>" alt="Hero Banner" class="lp-hero-single-img" loading="eager">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose World Academy Section (Screenshot 2 style) -->
    <section class="why-choose-section border-bottom" id="why-choose">
        <div class="container text-center">
            <h2 class="why-choose-title">Why Choose <span class="blue-highlight">CAVA Academy</span>?</h2>
            <p class="why-choose-subtitle">Our programs are designed to inspire, challenge, and elevate ambitious
                students through world-class instruction and real-world experience.</p>

            <div class="row g-4 mt-2 text-start">
                <!-- Card 01 -->
                <div class="col-md-6 col-lg-3">
                    <div class="why-choose-card">
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
                    <div class="why-choose-card">
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
                    <div class="why-choose-card">
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
                    <div class="why-choose-card">
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

    <!-- Featured Courses Section (Directly following Why Choose) -->
    <section class="lp-section-padding" id="featured-courses">
        <div class="container">
            <div class="lp-section-title-wrap text-center gsap-reveal">
                <span class="lp-section-badge">Self-Paced Courses</span>
                <h2 class="lp-section-title">Featured Study Programs</h2>
                <p class="lp-section-subtitle">High-quality video modules with lifetime access and locked-syllabus
                    structures.</p>
            </div>

            <div class="row lp-card-grid gsap-reveal">
                <?php if (empty($featuredCourses)): ?>
                    <div class="col text-center py-5">
                        <p class="text-muted fs-5">No featured courses available at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featuredCourses as $course):
                        $isEnrolled = Course::isUserEnrolled($userId, $course['id']);
                        require __DIR__ . '/views/components/course_card.php';
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

    <!-- Upcoming Events Section -->
    <section class="lp-section-padding" id="upcoming-events">
        <div class="container">
            <div class="lp-section-title-wrap text-center gsap-reveal">
                <span class="lp-section-badge">Campus Events</span>
                <h2 class="lp-section-title">Upcoming Events & Fairs</h2>
                <p class="lp-section-subtitle">Stay up to date with visa fairs, university meetups, and mock evaluation
                    sessions.</p>
            </div>

            <div class="row justify-content-center gsap-reveal">
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
            <div class="lp-section-title-wrap text-center gsap-reveal">
                <span class="lp-section-badge">Student Reviews</span>
                <h2 class="lp-section-title">Success Stories</h2>
                <p class="lp-section-subtitle">Hear from our students who successfully migrated and upgraded their
                    careers.</p>
            </div>

            <div class="row g-4 gsap-reveal">
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
                <div class="col-md-10 col-lg-8 gsap-reveal">
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