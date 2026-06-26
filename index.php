<?php
// Public Landing Page
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

$heroTitle = $settings['hero_title'] ?? 'Upgrade Your Skills with CAVA LMS';
$heroSubtitle = $settings['hero_subtitle'] ?? 'Access high-quality courses, webinars, and masterclasses designed by industry experts.';
$aboutUs = $settings['about_us'] ?? 'CAVA LMS is a premium e-learning portal designed to provide quality, affordable education for career development.';

// Fetch dynamic data
$featuredCourses = Course::getFeatured(3);
$upcomingWebinars = Webinar::getAll();
$upcomingEvents = Event::getAll();

$userId = $_SESSION['user_id'] ?? null;

require_once __DIR__ . '/views/layout/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center min-vh-75 py-5">
            <div class="col-lg-6 text-center text-lg-start animate-fade-in-up">
                <h1 class="hero-title mb-4"><?php echo htmlspecialchars($heroTitle); ?></h1>
                <p class="fs-5 mb-5 text-muted"><?php echo htmlspecialchars($heroSubtitle); ?></p>
                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                    <a href="#featured-courses" class="btn btn-primary btn-lg px-4 rounded-pill">Explore Courses</a>
                    <a href="#upcoming-webinars" class="btn btn-outline-primary btn-lg px-4 rounded-pill">Join Live Webinars</a>
                </div>
            </div>
            <div class="col-lg-6 mt-5 mt-lg-0 text-center animate-fade-in-up">
                <img src="https://placehold.co/600x400/6f42c1/ffffff?text=CAVA+LMS+Dashboard" alt="LMS Portal Mockup" class="img-fluid rounded-4 shadow-lg border" style="max-height: 420px;">
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5 bg-light" id="about-section">
    <div class="container my-4">
        <div class="row align-items-center">
            <div class="col-md-5 mb-4 mb-md-0">
                <h2 class="fw-bold mb-3">Learn from the best. Achieve your goals.</h2>
                <div class="h-1 bg-primary rounded" style="width: 80px; height: 4px;"></div>
            </div>
            <div class="col-md-7">
                <p class="fs-6 text-muted mb-0"><?php echo htmlspecialchars($aboutUs); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section class="py-5" id="featured-courses">
    <div class="container my-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Self-Paced Courses</span>
            <h2 class="fw-bold text-dark">Featured Study Programs</h2>
            <p class="text-muted">High-quality video modules with lifetime access and locked-syllabus structures.</p>
        </div>
        
        <div class="row">
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
    </div>
</section>

<!-- Upcoming Webinars Section -->
<section class="py-5 bg-light border-top border-bottom" id="upcoming-webinars">
    <div class="container my-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Live Masterclasses</span>
            <h2 class="fw-bold text-dark">Upcoming Live Webinars</h2>
            <p class="text-muted">Interact live with regulated immigration consultants and career experts.</p>
        </div>
        
        <div class="row justify-content-center">
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
    </div>
</section>

<!-- Upcoming Events Section -->
<section class="py-5" id="upcoming-events">
    <div class="container my-4">
        <div class="text-center mb-5">
            <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold mb-2">Campus Events</span>
            <h2 class="fw-bold text-dark">Upcoming Events & Fairs</h2>
            <p class="text-muted">Stay up to date with visa fairs, university meetups, and mock evaluation sessions.</p>
        </div>
        
        <div class="row justify-content-center">
            <?php if (empty($upcomingEvents)): ?>
                <div class="col text-center py-5">
                    <p class="text-muted fs-5">No upcoming events listed at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingEvents as $ev): 
                    $evDate = date('d M, Y', strtotime($ev['date']));
                    $evImg = 'https://placehold.co/600x340/6f42c1/ffffff?text=Event';
                    if ($ev['event_image'] && file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                        $evImg = SITE_URL . '/uploads/' . $ev['event_image'];
                    }
                ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="custom-card border-0 shadow-sm bg-white rounded-4 overflow-hidden h-100">
                            <img src="<?php echo $evImg; ?>" alt="Event banner" class="img-fluid" style="height: 180px; width: 100%; object-fit: cover;" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Event'">
                            <div class="p-4">
                                <span class="text-primary fw-semibold fs-8 d-block mb-1">
                                    <i class="fa-regular fa-calendar me-1"></i><?php echo $evDate; ?>
                                    <?php if (strtotime($ev['date']) < strtotime(date('Y-m-d'))): ?>
                                        <span class="badge bg-secondary ms-2">Closed</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-light text-success ms-2">Upcoming</span>
                                    <?php endif; ?>
                                </span>
                                <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($ev['title']); ?></h5>
                                <p class="text-muted fs-7 mb-0"><?php echo htmlspecialchars($ev['description']); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light" id="testimonials">
    <div class="container my-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Success Stories</h2>
            <p class="text-muted">Hear from our students who successfully migrated and upgraded their careers.</p>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 p-4 shadow-sm rounded-4 h-100 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">AM</div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">Aman Mehta</h6>
                            <small class="text-muted">FSW Immigrant, Toronto</small>
                        </div>
                    </div>
                    <p class="text-muted fs-7 mb-0">"The Canada Immigration Masterclass made the Express Entry process so simple. The locked syllabus checked out perfectly, and I cleared my ECA credentials doubts."</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 p-4 shadow-sm rounded-4 h-100 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">SP</div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">Simran Patel</h6>
                            <small class="text-muted">Student, Vancouver</small>
                        </div>
                    </div>
                    <p class="text-muted fs-7 mb-0">"Amazing webinar session! I registered for ₹99 and got direct access to the visa consultant. The transactional receipt was emailed immediately."</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card border-0 p-4 shadow-sm rounded-4 h-100 bg-white">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 45px; height: 45px;">RK</div>
                        <div>
                            <h6 class="fw-bold m-0 text-dark">Rajesh Kumar</h6>
                            <small class="text-muted">PR Holder, Alberta</small>
                        </div>
                    </div>
                    <p class="text-muted fs-7 mb-0">"The CRS Point System breakdown videos are top tier. Simple YouTube embeds which play without lag, and I unlocked the program through Razorpay in seconds."</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form / Support Section -->
<section class="py-5" id="contact-section">
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="custom-card border-0 shadow-lg p-4 p-md-5 bg-white rounded-4">
                    <h3 class="fw-bold text-center mb-2">Have a Query? Ask Us!</h3>
                    <p class="text-muted text-center mb-4">Submit your question below, and our support team will respond shortly.</p>
                    
                    <form action="submit_query.php" method="POST">
                        <div class="mb-3">
                            <label for="query_name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control bg-light" id="query_name" name="name" 
                                   value="<?php echo $userId ? htmlspecialchars($_SESSION['user_name']) : ''; ?>" placeholder="e.g. Aman Mehta" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="query_email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control bg-light" id="query_email" name="email" 
                                   value="<?php echo $userId ? htmlspecialchars($_SESSION['user_email']) : ''; ?>" placeholder="aman@example.com" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="query_mobile" class="form-label fw-semibold">Mobile Number</label>
                            <input type="tel" class="form-control bg-light" id="query_mobile" name="mobile_number" placeholder="9876543210" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="query_message" class="form-label fw-semibold">Your Support Query Message</label>
                            <textarea class="form-control bg-light" id="query_message" name="query_message" rows="4" placeholder="Describe your doubt here..." required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">Submit Query Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
