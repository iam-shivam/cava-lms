<?php
// Course Details Controller & View
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';

$slug = trim($_GET['slug'] ?? '');
$course = Course::getBySlug($slug);

if (!$course) {
    require_once __DIR__ . '/views/layout/header.php';
    echo '<main class="lp-body-scope studio-detail-page">';
    echo '<section class="studio-detail-section" aria-labelledby="course-not-found-title"><div class="container">';
    echo '<div class="studio-content-card studio-access-state text-center">';
    echo '<div class="lp-empty-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></div>';
    echo '<h1 id="course-not-found-title">Course Not Found</h1>';
    echo '<p>The course you are looking for does not exist or has been disabled.</p>';
    echo '<a href="index.php" class="lp-btn-pill-dark">Back to Home <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>';
    echo '</div></div></section></main>';
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

$thumbnailUrl = SITE_URL . '/assets/images/study_abroad.jpeg';
if (!empty($course['thumbnail'])) {
    if (file_exists(BASE_PATH . '/uploads/' . $course['thumbnail'])) {
        $thumbnailUrl = SITE_URL . '/uploads/' . $course['thumbnail'];
    } else {
        $thumbnailUrl = SITE_URL . '/assets/images/' . $course['thumbnail'];
    }
}

// SEO meta — use actual course data for rich search results
$pageTitle       = $course['title'];
$pageDescription = $course['description'] ? substr(strip_tags($course['description']), 0, 155) : 'Enroll in ' . $course['title'] . ' on CAVA LMS and start learning today.';
$pageImage       = $thumbnailUrl;

require_once __DIR__ . '/views/layout/header.php';
?>

<main class="lp-body-scope studio-detail-page" aria-labelledby="course-detail-title">
    <section class="studio-detail-hero">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['category_name']); ?></li>
                </ol>
            </nav>
            <div class="studio-detail-meta">
                <span class="studio-meta-chip">
                    <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                    <?php echo htmlspecialchars($course['category_name']); ?>
                </span>
                <span class="studio-meta-chip">
                    <i class="fa-regular fa-circle-play" aria-hidden="true"></i>
                    <?php echo $totalLessons; ?> <?php echo (int) $totalLessons === 1 ? 'Lesson' : 'Lessons'; ?>
                </span>
            </div>
            <h1 id="course-detail-title"><?php echo htmlspecialchars($course['title']); ?></h1>
        </div>
    </section>

    <section class="studio-detail-section">
        <div class="container">
            <div class="row g-4 g-xl-5">
                <div class="col-lg-8">
                    <section class="studio-detail-block" aria-labelledby="course-about-heading">
                        <h2 id="course-about-heading">About This Course</h2>
                        <div class="studio-content-card">
                            <p class="studio-detail-copy"><?php echo htmlspecialchars($course['description']); ?></p>
                        </div>
                    </section>

                    <section class="studio-detail-block" aria-labelledby="course-syllabus-heading">
                        <h2 id="course-syllabus-heading">Course Syllabus</h2>
                        <div class="accordion studio-syllabus" id="syllabusAccordion">
                            <?php if (empty($syllabus)): ?>
                                <div class="studio-content-card studio-access-state">
                                    <p class="mb-0">No content is available for this course yet.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($syllabus as $index => $item): 
                                    $section = $item['section'];
                                    $videos = $item['videos'];
                                    $collapseId = "collapseSec_" . $section['id'];
                                    $headingId = "headingSec_" . $section['id'];
                                ?>
                                    <div class="accordion-item studio-syllabus-item">
                                        <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                            <button class="accordion-button studio-syllabus-trigger" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="true" aria-controls="<?php echo $collapseId; ?>">
                                                <span class="studio-syllabus-heading">
                                                    <span><?php echo htmlspecialchars($section['title']); ?></span>
                                                    <span class="studio-meta-chip"><?php echo count($videos); ?> <?php echo count($videos) === 1 ? 'Lesson' : 'Lessons'; ?></span>
                                                </span>
                                            </button>
                                        </h2>
                                        <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse show" aria-labelledby="<?php echo $headingId; ?>" data-bs-parent="#syllabusAccordion">
                                            <div class="accordion-body p-0">
                                                <div class="list-group list-group-flush">
                                                    <?php foreach ($videos as $video): ?>
                                                        <div class="list-group-item studio-lesson-row">
                                                            <div class="studio-lesson-row__title">
                                                                <i class="fa-regular fa-circle-play" aria-hidden="true"></i>
                                                                <span><?php echo htmlspecialchars($video['title']); ?></span>
                                                            </div>
                                                            <div>
                                                                <?php if ($isEnrolled): ?>
                                                                    <a href="course_play.php?slug=<?php echo $course['slug']; ?>&video_id=<?php echo $video['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                                        <i class="fa-solid fa-play me-1" aria-hidden="true"></i>Play
                                                                    </a>
                                                                <?php else: ?>
                                                                    <i class="fa-solid fa-lock" title="Locked" aria-label="Locked"></i>
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
                    </section>
                </div>
                <div class="col-lg-4">
                    <aside class="studio-purchase-card" aria-label="Course access and payment">
                            <img src="<?php echo htmlspecialchars($thumbnailUrl, ENT_QUOTES); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="studio-purchase-card__image" onerror="this.onerror=null;this.src='<?php echo htmlspecialchars(SITE_URL . '/assets/images/study_abroad.jpeg', ENT_QUOTES); ?>'">

                            <?php if ($isEnrolled): ?>
                                <div class="studio-access-state studio-access-state--active">
                                    <h2><i class="fa-solid fa-circle-check" aria-hidden="true"></i>You are enrolled</h2>
                                    <p>Course content is available through the course player.</p>
                                </div>
                                <a href="course_play.php?slug=<?php echo $course['slug']; ?>" class="lp-btn-primary btn-enroll-sweep studio-purchase-action w-100 text-center">
                                    <i class="fa-solid fa-circle-play me-2" aria-hidden="true"></i>Go to Course Player
                                </a>
                            <?php else: ?>
                                <div class="studio-purchase-card__summary">
                                    <span class="studio-purchase-card__label">Course fee</span>
                                    <strong class="studio-purchase-card__price">&#8377;<?php echo number_format($course['price'], 2); ?></strong>
                                    <?php if ($totalPaid > 0 && $remainingBalance > 0): ?>
                                        <span class="studio-purchase-card__balance">Remaining balance: &#8377;<?php echo number_format($remainingBalance, 2); ?></span>
                                    <?php endif; ?>
                                    <div class="studio-detail-meta">
                                        <?php if ($course['course_duration'] > 0): ?>
                                            <span class="studio-meta-chip">Duration: <?php echo $course['course_duration']; ?> <?php echo $course['course_duration'] == 1 ? 'month' : 'months'; ?></span>
                                        <?php else: ?>
                                            <span class="studio-meta-chip">Duration not specified</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="studio-access-state studio-access-state--locked">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    <p>Complete enrollment to access the course lessons and resources.</p>
                                </div>

                                <?php if ($userId): ?>
                                    <form action="payment_process.php" method="POST">
                                        <input type="hidden" name="item_type" value="course">
                                        <input type="hidden" name="item_id" value="<?php echo $courseId; ?>">

                                        <?php if ($course['allow_partial_payment'] && $remainingBalance > 0): 
                                            $minPayment = min($course['min_installment'], $remainingBalance);
                                        ?>
                                            <div class="mb-3 text-start">
                                                <label for="amount_to_pay" class="lp-form-label">Amount to Pay (INR)</label>
                                                <input type="number" step="0.01" min="<?php echo $minPayment; ?>" max="<?php echo $remainingBalance; ?>" class="form-control lp-form-control" id="amount_to_pay" name="amount_to_pay" value="<?php echo $remainingBalance; ?>" required>
                                                <small class="form-text">Min: &#8377;<?php echo number_format($minPayment, 2); ?> | Max: &#8377;<?php echo number_format($remainingBalance, 2); ?></small>
                                            </div>
                                        <?php endif; ?>

                                        <button type="submit" class="lp-btn-primary studio-purchase-action w-100 text-center">
                                            <i class="fa-solid fa-cart-shopping me-2" aria-hidden="true"></i><?php echo ($totalPaid > 0) ? 'Pay Remaining' : 'Buy Now'; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="lp-btn-primary studio-purchase-action w-100 text-center">
                                        <i class="fa-solid fa-right-to-bracket me-2" aria-hidden="true"></i>Login to Buy Course
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
