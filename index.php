<?php
// Public Landing Page - CAVA Studio
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/models/Event.php';

// Fetch dynamic settings.
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
    // The homepage keeps useful static fallbacks if settings cannot be loaded.
}

$heroEyebrow = trim($settings['hero_eyebrow'] ?? '') ?: 'CAVA CAREER ABROAD VISA ACADEMY';
$heroTitle = trim($settings['hero_title'] ?? '') ?: 'Learn with a clearer sense of direction.';
$heroSubtitle = trim($settings['hero_subtitle'] ?? '')
    ?: 'Explore structured courses and practical learning resources for international education, career, and visa pathways.';
$aboutUs = trim($settings['about_us'] ?? '')
    ?: 'CAVA LMS brings courses, lesson resources, learning progress, events, and support into one focused online workspace.';

// Use an administrator-provided hero image when available, with a repository asset as fallback.
$heroImg1 = (!empty($settings['hero_img_1']) && file_exists(BASE_PATH . '/uploads/' . $settings['hero_img_1']))
    ? SITE_URL . '/uploads/' . $settings['hero_img_1']
    : SITE_URL . '/assets/images/e-learning-education-networking-website-study-concept.jpg';

$userId = $_SESSION['user_id'] ?? null;

// Keep the established homepage data sources and enrollment-aware course component.
$featuredCourses = Course::getFeatured(3, $userId);
$upcomingWebinars = Webinar::getAll(2, $userId);
$upcomingEvents = Event::getAll(3);

$pageTitle = 'CAVA Studio';
$pageDescription = 'Explore structured online courses, learning resources, events, and student support through CAVA Studio.';

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <main>
        <section class="lp-hero-wa" id="hero" aria-labelledby="home-hero-title">
            <div class="container position-relative" style="z-index: 1;">
                <div class="row align-items-center g-5">
                    <div class="col-lg-6 text-center text-lg-start">
                        <p class="lp-hero-tag lms-reveal lms-reveal-up" data-studio-reveal>
                            <i class="fa-solid fa-compass text-purple" aria-hidden="true"></i>
                            <?php echo htmlspecialchars($heroEyebrow); ?>
                        </p>

                        <h1 class="lp-hero-wa-title lms-reveal lms-reveal-up lms-delay-1" id="home-hero-title" data-studio-reveal>
                            <span class="hero-main-line"><?php echo htmlspecialchars($heroTitle); ?></span>
                        </h1>

                        <p class="lp-hero-wa-subtitle lms-reveal lms-reveal-up lms-delay-1" data-studio-reveal>
                            <?php echo htmlspecialchars($heroSubtitle); ?>
                        </p>

                        <div class="lp-hero-cta-group d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3 mt-4 lms-reveal lms-reveal-up lms-delay-2" data-studio-reveal>
                            <a href="#featured-courses" class="lp-btn-pill-white">
                                Explore courses <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                            </a>
                            <a href="#why-cava" class="lp-btn-pill-outline">
                                See how it works <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="lp-hero-single-img-wrapper lms-reveal-hero-img" data-studio-reveal>
                            <img src="<?php echo htmlspecialchars($heroImg1); ?>"
                                 alt="Students learning together through CAVA Studio"
                                 class="lp-hero-single-img"
                                 fetchpriority="high">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="why-choose-section border-bottom" id="why-cava" aria-labelledby="why-cava-title">
            <div class="container">
                <div class="text-center mx-auto" style="max-width: 48rem;" data-studio-reveal>
                    <span class="studio-kicker">A focused learning workspace</span>
                    <h2 class="why-choose-title" id="why-cava-title">Everything needed to move from browsing to learning.</h2>
                    <p class="why-choose-subtitle"><?php echo htmlspecialchars($aboutUs); ?></p>
                </div>

                <div class="row g-4 mt-4">
                    <div class="col-md-6 col-lg-4" data-studio-reveal>
                        <article class="why-choose-card">
                            <div class="why-choose-icon d-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <h3 class="why-choose-card-title">Structured course paths</h3>
                            <p class="why-choose-card-desc mb-0">Review course details, lesson sections, pricing, and access terms before deciding what to learn.</p>
                            <span class="why-choose-number" aria-hidden="true">01</span>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-4" data-studio-reveal>
                        <article class="why-choose-card">
                            <div class="why-choose-icon d-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-solid fa-circle-play"></i>
                            </div>
                            <h3 class="why-choose-card-title">One learning space</h3>
                            <p class="why-choose-card-desc mb-0">Open lessons, view available resources, and continue from your student dashboard as your course progresses.</p>
                            <span class="why-choose-number" aria-hidden="true">02</span>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-4" data-studio-reveal>
                        <article class="why-choose-card">
                            <div class="why-choose-icon d-flex align-items-center justify-content-center" aria-hidden="true">
                                <i class="fa-regular fa-circle-question"></i>
                            </div>
                            <h3 class="why-choose-card-title">Support that stays visible</h3>
                            <p class="why-choose-card-desc mb-0">Submit a support request and revisit its status from the same account used for your learning.</p>
                            <span class="why-choose-number" aria-hidden="true">03</span>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-section-padding" id="featured-courses" aria-labelledby="featured-courses-title">
            <div class="container">
                <div class="d-lg-flex align-items-end justify-content-between gap-4 mb-5" data-studio-reveal>
                    <div>
                        <span class="studio-kicker">Course discovery</span>
                        <h2 class="studio-title" id="featured-courses-title">Start with a featured course.</h2>
                        <p class="studio-lede mt-3 mb-0">Compare current course options and open the full syllabus before enrolling.</p>
                    </div>
                    <a href="courses.php" class="btn btn-outline-primary mt-4 mt-lg-0">
                        Browse all courses <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="row g-4 lp-card-grid">
                    <?php if (empty($featuredCourses)): ?>
                        <div class="col-12">
                            <div class="studio-surface text-center p-5" data-studio-reveal>
                                <i class="fa-regular fa-folder-open fa-2x text-muted mb-3" aria-hidden="true"></i>
                                <h3 class="h5 mb-2">No featured courses are available right now.</h3>
                                <p class="mb-0">Visit the course catalog to see other published options.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php
                        $cardIndex = 0;
                        foreach ($featuredCourses as $course):
                            $isEnrolled = Course::isUserEnrolled($userId, $course['id']);
                            require __DIR__ . '/views/components/course_card.php';
                            $cardIndex++;
                        endforeach;
                        ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="studio-mentor-section" id="mentor" aria-labelledby="home-mentor-title">
            <div class="container">
                <div class="studio-mentor-layout">
                    <div class="studio-mentor__media" data-studio-reveal>
                        <div class="studio-mentor__portrait">
                            <img src="<?php echo SITE_URL; ?>/assets/images/amarnath.png"
                                 alt="Amarnath Singh, Founder and Chief Mentor at CAVA"
                                 width="404"
                                 height="410"
                                 loading="lazy">
                        </div>
                    </div>

                    <div class="studio-mentor__content" data-studio-reveal>
                        <span class="studio-kicker">Founder &amp; Chief Mentor, CAVA</span>
                        <h2 class="studio-title" id="home-mentor-title">Why learn from Amarnath?</h2>
                        <p class="studio-mentor__principle">Experience cannot be Googled.</p>
                        <p class="studio-mentor__intro">His teaching is informed by practical work across international education, career, visa, and immigration pathways.</p>

                        <ul class="studio-mentor__facts" role="list">
                            <li>
                                <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                                <span><strong>Experience since 1997</strong> with practical, industry-led guidance.</span>
                            </li>
                            <li>
                                <i class="fa-solid fa-passport" aria-hidden="true"></i>
                                <span><strong>Hands-on application experience</strong> across different visa programs and pathways.</span>
                            </li>
                            <li>
                                <i class="fa-solid fa-earth-americas" aria-hidden="true"></i>
                                <span><strong>International perspective</strong> spanning the USA, UK, Australia, Canada, Germany, and European markets.</span>
                            </li>
                            <li>
                                <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                                <span><strong>Continuous learning</strong> to keep industry knowledge and teaching current.</span>
                            </li>
                        </ul>

                        <div class="studio-mentor__actions">
                            <a href="courses.php" class="btn btn-primary">Explore courses <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                            <a href="webinars.php" class="btn btn-outline-primary">View webinars</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="lp-journey-section border-top border-bottom" id="learning-journey" aria-labelledby="learning-journey-title">
            <div class="container">
                <div class="text-center mx-auto" style="max-width: 46rem;" data-studio-reveal>
                    <span class="studio-kicker">The learning flow</span>
                    <h2 class="studio-title" id="learning-journey-title">A simple path through the platform.</h2>
                    <p class="studio-lede mx-auto mt-3 mb-0">Each step reflects functionality already available in CAVA LMS.</p>
                </div>

                <div class="journey-steps-grid mt-5" data-studio-reveal>
                    <article class="journey-step-card">
                        <div class="journey-step-number-wrap d-flex align-items-center justify-content-between mb-4">
                            <span class="journey-step-num">01</span>
                            <i class="fa-solid fa-magnifying-glass journey-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="journey-step-title h5">Browse</h3>
                        <p class="journey-step-desc mb-0">Search published courses and review the syllabus, price, and access details.</p>
                    </article>

                    <article class="journey-step-card">
                        <div class="journey-step-number-wrap d-flex align-items-center justify-content-between mb-4">
                            <span class="journey-step-num">02</span>
                            <i class="fa-solid fa-user-check journey-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="journey-step-title h5">Sign in</h3>
                        <p class="journey-step-desc mb-0">Use your registered email or mobile number and verify access with an OTP.</p>
                    </article>

                    <article class="journey-step-card">
                        <div class="journey-step-number-wrap d-flex align-items-center justify-content-between mb-4">
                            <span class="journey-step-num">03</span>
                            <i class="fa-solid fa-laptop journey-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="journey-step-title h5">Learn</h3>
                        <p class="journey-step-desc mb-0">Watch available lessons, open lesson resources, and mark progress as you go.</p>
                    </article>

                    <article class="journey-step-card">
                        <div class="journey-step-number-wrap d-flex align-items-center justify-content-between mb-4">
                            <span class="journey-step-num">04</span>
                            <i class="fa-solid fa-chart-line journey-step-icon" aria-hidden="true"></i>
                        </div>
                        <h3 class="journey-step-title h5">Continue</h3>
                        <p class="journey-step-desc mb-0">Return through the dashboard to review course progress, orders, and support requests.</p>
                    </article>
                </div>

                <div class="text-center mt-5" data-studio-reveal>
                    <?php if ($userId): ?>
                        <a href="dashboard.php" class="btn btn-primary">Open your dashboard <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">Create a student account <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="studio-home-webinars" id="upcoming-webinars" aria-labelledby="home-webinars-title">
            <div class="container">
                <div class="d-lg-flex align-items-end justify-content-between gap-4 mb-5" data-studio-reveal>
                    <div>
                        <span class="studio-kicker">Live learning</span>
                        <h2 class="studio-title" id="home-webinars-title">Join an upcoming webinar.</h2>
                        <p class="studio-lede mt-3 mb-0">Review scheduled sessions, timing, and registration details before you join.</p>
                    </div>
                    <a href="webinars.php" class="btn btn-outline-primary mt-4 mt-lg-0">
                        View all webinars <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="row g-4">
                    <?php if (empty($upcomingWebinars)): ?>
                        <div class="col-12">
                            <div class="studio-surface studio-home-empty" data-studio-reveal>
                                <i class="fa-solid fa-video-slash" aria-hidden="true"></i>
                                <div>
                                    <h3 class="h5 mb-1">No upcoming webinars are listed right now.</h3>
                                    <p class="mb-0">Please check the webinar page again later.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingWebinars as $webinar): ?>
                            <?php
                            $isRegistered = Webinar::isUserRegistered($userId, $webinar['id']);
                            $webinarHeadingLevel = 3;
                            require __DIR__ . '/views/components/webinar_card.php';
                            ?>
                        <?php endforeach; ?>
                        <?php unset($webinarHeadingLevel); ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="studio-section" id="events" aria-labelledby="home-events-title">
            <div class="container">
                <div class="d-lg-flex align-items-end justify-content-between gap-4 mb-5" data-studio-reveal>
                    <div>
                        <span class="studio-kicker">Events and sessions</span>
                        <h2 class="studio-title" id="home-events-title">See what is on the calendar.</h2>
                        <p class="studio-lede mt-3 mb-0">Review the latest events published by the CAVA team.</p>
                    </div>
                    <a href="events.php" class="btn btn-outline-primary mt-4 mt-lg-0">
                        View all events <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="row g-4">
                    <?php if (empty($upcomingEvents)): ?>
                        <div class="col-12">
                            <div class="studio-surface text-center p-5" data-studio-reveal>
                                <i class="fa-regular fa-calendar fa-2x text-muted mb-3" aria-hidden="true"></i>
                                <h3 class="h5 mb-2">No events are listed right now.</h3>
                                <p class="mb-0">Please check the events page again later.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcomingEvents as $event): ?>
                            <?php
                            $eventTimestamp = strtotime($event['date']);
                            $isPastEvent = $eventTimestamp < strtotime(date('Y-m-d'));
                            $eventImage = SITE_URL . '/assets/images/study_abroad.jpeg';
                            if (!empty($event['event_image']) && file_exists(BASE_PATH . '/uploads/' . $event['event_image'])) {
                                $eventImage = SITE_URL . '/uploads/' . $event['event_image'];
                            }
                            ?>
                            <div class="col-md-6 col-lg-4" data-studio-reveal>
                                <article class="event-card custom-card h-100 overflow-hidden">
                                    <div class="ratio ratio-16x9 overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($eventImage); ?>"
                                             alt=""
                                             class="w-100 h-100 object-fit-cover"
                                             loading="lazy"
                                             onerror="this.onerror=null;this.src='<?php echo SITE_URL; ?>/assets/images/study_abroad.jpeg';">
                                    </div>
                                    <div class="p-4 d-flex flex-column h-100">
                                        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                            <span class="small fw-semibold text-muted">
                                                <i class="fa-regular fa-calendar me-1" aria-hidden="true"></i>
                                                <?php echo date('d M Y', $eventTimestamp); ?>
                                            </span>
                                            <span class="badge <?php echo $isPastEvent ? 'text-bg-secondary' : 'text-bg-success'; ?>">
                                                <?php echo $isPastEvent ? 'Past' : 'Upcoming'; ?>
                                            </span>
                                        </div>
                                        <h3 class="h5 mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                                        <p class="small mb-4"><?php echo htmlspecialchars(strip_tags($event['description'] ?? '')); ?></p>
                                        <a href="events.php" class="btn btn-outline-primary btn-sm mt-auto align-self-start">
                                            Event details <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="lp-support-section" id="support-cta" aria-labelledby="support-cta-title">
            <div class="container">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8" data-studio-reveal>
                        <span class="studio-kicker">Need a hand?</span>
                        <h2 class="studio-title" id="support-cta-title">Ask about a course, your account, or platform access.</h2>
                        <p class="studio-lede mt-3 mb-0">Send a support request and, when signed in, follow its status from your account.</p>
                    </div>
                    <div class="col-lg-4 d-flex flex-column flex-sm-row flex-lg-column align-items-lg-stretch gap-3" data-studio-reveal>
                        <a href="support.php" class="btn btn-primary">Open support <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                        <a href="courses.php" class="btn btn-outline-primary">Browse courses</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
