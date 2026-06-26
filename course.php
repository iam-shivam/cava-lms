<?php
// Course Details Controller & View
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';

$slug = trim($_GET['slug'] ?? '');
$course = Course::getBySlug($slug);

if (!$course) {
    require_once __DIR__ . '/views/layout/header.php';
    echo '<div class="container my-5 text-center">';
    echo '<h2>Course Not Found</h2>';
    echo '<p class="text-muted">The course you are looking for does not exist or has been disabled.</p>';
    echo '<a href="index.php" class="btn btn-primary mt-3">Back to Home</a>';
    echo '</div>';
    require_once __DIR__ . '/views/layout/footer.php';
    exit;
}

$courseId = $course['id'];
$userId = $_SESSION['user_id'] ?? null;
$isEnrolled = Course::isUserEnrolled($userId, $courseId);

$totalPaid = 0;
$remainingBalance = $course['price'];
if ($userId) {
    require_once __DIR__ . '/models/Payment.php';
    $totalPaid = Payment::getTotalPaid($userId, 'course', $courseId);
    $remainingBalance = max(0, $course['price'] - $totalPaid);
}

$syllabus = Course::getSyllabus($courseId);
$totalLessons = Course::countLessons($courseId);

$thumbnailUrl = SITE_URL . '/assets/images/default_course.jpg';
if (!empty($course['thumbnail'])) {
    if (file_exists(BASE_PATH . '/uploads/' . $course['thumbnail'])) {
        $thumbnailUrl = SITE_URL . '/uploads/' . $course['thumbnail'];
    } else {
        $thumbnailUrl = SITE_URL . '/assets/images/' . $course['thumbnail'];
    }
}

require_once __DIR__ . '/views/layout/header.php';
?>

<!-- Header Hero for Course details -->
<div class="bg-light py-5 mb-5 border-bottom">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['category_name']); ?></li>
                    </ol>
                </nav>
                <h1 class="fw-extrabold display-5 mb-3 text-dark"><?php echo htmlspecialchars($course['title']); ?></h1>
                <div class="d-flex align-items-center gap-3 text-muted">
                    <span><i class="fa-regular fa-folder-open me-1"></i><?php echo htmlspecialchars($course['category_name']); ?></span>
                    <span>•</span>
                    <span><i class="fa-regular fa-circle-play me-1"></i><?php echo $totalLessons; ?> Lessons</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <div class="row">
        <!-- Main details -->
        <div class="col-lg-8">
            <div class="mb-5">
                <h3 class="fw-bold mb-3">About This Course</h3>
                <div class="card border-0 bg-white p-4 shadow-sm rounded-4">
                    <p style="white-space: pre-line; font-size: 1.1rem; line-height: 1.7; color: var(--text-main);">
                        <?php echo htmlspecialchars($course['description']); ?>
                    </p>
                </div>
            </div>
            
            <div class="mb-5">
                <h3 class="fw-bold mb-3">Course Syllabus</h3>
                <div class="accordion" id="syllabusAccordion">
                    <?php if (empty($syllabus)): ?>
                        <p class="text-muted">No content available for this course yet.</p>
                    <?php else: ?>
                        <?php foreach ($syllabus as $index => $item): 
                            $section = $item['section'];
                            $videos = $item['videos'];
                            $collapseId = "collapseSec_" . $section['id'];
                            $headingId = "headingSec_" . $section['id'];
                        ?>
                            <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                                <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                    <button class="accordion-button bg-white fw-bold py-3 fs-5" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="true" aria-controls="<?php echo $collapseId; ?>">
                                        <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                            <span><?php echo htmlspecialchars($section['title']); ?></span>
                                            <span class="badge bg-primary-light text-primary rounded-pill fs-7 fw-normal"><?php echo count($videos); ?> Lessons</span>
                                        </div>
                                    </button>
                                </h2>
                                <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse show" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#syllabusAccordion">
                                    <div class="accordion-body bg-white border-top p-0">
                                        <div class="list-group list-group-flush">
                                            <?php foreach ($videos as $video): ?>
                                                <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-4 border-0 border-bottom">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <i class="fa-regular fa-circle-play text-primary"></i>
                                                        <span class="fw-medium text-dark"><?php echo htmlspecialchars($video['title']); ?></span>
                                                    </div>
                                                    <div>
                                                        <?php if ($isEnrolled): ?>
                                                            <a href="course_play.php?slug=<?php echo $course['slug']; ?>&video_id=<?php echo $video['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                                                <i class="fa-solid fa-play me-1"></i>Play
                                                            </a>
                                                        <?php else: ?>
                                                            <i class="fa-solid fa-lock text-muted" title="Locked"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Widget -->
        <div class="col-lg-4">
            <div class="sticky-top" style="top: 90px; z-index: 100;">
                <div class="custom-card border-0 p-4 p-md-5">
                    <img src="<?php echo $thumbnailUrl; ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="img-fluid rounded-4 mb-4 shadow-sm" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Course+Thumbnail'">
                    
                    <?php if ($isEnrolled): ?>
                        <div class="alert alert-success text-center border-0 mb-4 rounded-3 py-3">
                            <h5 class="alert-heading fw-bold mb-1"><i class="fa-solid fa-circle-check me-2"></i>You are Enrolled</h5>
                            <p class="mb-0 fs-7">Access to all course content is unlocked.</p>
                        </div>
                        <a href="course_play.php?slug=<?php echo $course['slug']; ?>" class="btn btn-primary w-100 py-3 rounded-pill fw-bold">
                            <i class="fa-solid fa-circle-play me-2"></i>Go to Course Player
                        </a>
                    <?php else: ?>
                        <div class="mb-4 text-center text-lg-start">
                            <span class="text-muted d-block mb-1">Course Fee</span>
                            <span class="h1 fw-extrabold text-primary">₹<?php echo number_format($course['price'], 2); ?></span>
                            <?php if ($totalPaid > 0 && $remainingBalance > 0): ?>
                                <span class="d-block text-warning fw-bold mt-2">Remaining Balance: ₹<?php echo number_format($remainingBalance, 2); ?></span>
                            <?php endif; ?>
                            <?php if ($course['course_duration'] > 0): ?>
                                <span class="badge bg-info mt-2">Duration: <?php echo $course['course_duration']; ?> Months</span>
                            <?php else: ?>
                                <span class="badge bg-success mt-2">Lifetime Access</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="locked-banner">
                            <i class="fa-solid fa-lock fs-5"></i>
                            <div class="fs-7">Purchase this course to unlock all sections and video tutorials.</div>
                        </div>

                        <?php if ($userId): ?>
                            <!-- Checkout with Razorpay Form -->
                            <form action="payment_process.php" method="POST">
                                <input type="hidden" name="item_type" value="course">
                                <input type="hidden" name="item_id" value="<?php echo $courseId; ?>">
                                
                                <?php if ($course['allow_partial_payment'] && $remainingBalance > 0): ?>
                                    <div class="mb-3 text-start">
                                        <label for="amount_to_pay" class="form-label fw-semibold fs-7 text-dark">Amount to Pay (INR)</label>
                                        <input type="number" step="0.01" min="<?php echo $course['min_installment']; ?>" max="<?php echo $remainingBalance; ?>" class="form-control" id="amount_to_pay" name="amount_to_pay" value="<?php echo $remainingBalance; ?>" required>
                                        <small class="text-muted fs-8 mt-1 d-block">Min: ₹<?php echo number_format($course['min_installment'], 2); ?> | Max: ₹<?php echo number_format($remainingBalance, 2); ?></small>
                                    </div>
                                <?php endif; ?>

                                <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5">
                                    <i class="fa-solid fa-cart-shopping me-2"></i><?php echo ($totalPaid > 0) ? 'Pay Remaining' : 'Buy Now'; ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary w-100 py-3 rounded-pill fw-bold fs-5">
                                <i class="fa-solid fa-sign-in me-2"></i>Login to Buy Course
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Disable Right-Click and Inspect Elements
document.addEventListener('contextmenu', event => event.preventDefault());
document.onkeydown = function(e) {
    if (e.key === "F12") return false;
    if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "J" || e.key === "C")) return false;
    if (e.ctrlKey && e.key === "U") return false;
};
</script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
