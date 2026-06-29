<?php
// Course Video Player Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/VideoOTP.php';

// 1. Require Login
if (!isset($_SESSION['user_id'])) {
    set_flash_message('warning', 'Please login to access your courses.');
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$slug = trim($_GET['slug'] ?? '');
$course = Course::getBySlug($slug);

if (!$course) {
    set_flash_message('danger', 'Course not found.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

$courseId = $course['id'];

// 2. Check Enrollment
$enrollment = DB::fetch("SELECT status, expiry_date FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $courseId]);
if (!$enrollment) {
    set_flash_message('danger', 'You must be enrolled to access this course content.');
    header("Location: course.php?slug=" . urlencode($slug));
    exit;
}

$isExpired = false;
if ($enrollment['expiry_date'] && strtotime($enrollment['expiry_date']) < time()) {
    $isExpired = true;
}

if ($isExpired) {
    set_flash_message('danger', 'Your course access has expired. Please renew.');
    header("Location: course.php?slug=" . urlencode($slug));
    exit;
}

$enrollmentStatus = $enrollment['status'];

// Fetch Course Details and Syllabus
$course = Course::getById($courseId);
$syllabus = Course::getSyllabus($courseId);

$allVideos = [];
foreach ($syllabus as $sec) {
    if (!empty($sec['videos'])) {
        foreach ($sec['videos'] as $v) {
            $allVideos[] = $v;
        }
    }
}

if (empty($allVideos)) {
    require_once __DIR__ . '/views/layout/header.php';
    echo '<div class="container my-5 text-center">';
    echo '<h2>No Videos Available</h2>';
    echo '<p class="text-muted">This course does not contain any videos yet.</p>';
    echo '<a href="dashboard.php" class="btn btn-primary mt-3">Back to Dashboard</a>';
    echo '</div>';
    require_once __DIR__ . '/views/layout/footer.php';
    exit;
}

// Find Active Video
$videoId = intval($_GET['video_id'] ?? 0);
$activeVideo = null;
$activeVideoIndex = 0;

if ($videoId > 0) {
    foreach ($allVideos as $index => $video) {
        if ($video['id'] === $videoId) {
            $activeVideo = $video;
            $activeVideoIndex = $index;
            break;
        }
    }
}

// Fallback to first video
if (!$activeVideo) {
    $activeVideo = $allVideos[0];
    $activeVideoIndex = 0;
}

require_once __DIR__ . '/views/layout/header.php';
?>

<div class="container-fluid py-4" style="background-color: var(--bg-gray); min-height: 90vh;">
    <div class="container">
        
        <div class="row mb-4 align-items-center">
            <div class="col">
                <a href="dashboard.php" class="text-decoration-none text-muted mb-2 d-inline-block">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
                </a>
                <h2 class="fw-bold m-0"><?php echo htmlspecialchars($course['title']); ?></h2>
            </div>
        </div>

        <div class="row">
            <!-- Left Side: Player -->
            <div class="col-lg-8 mb-4">
                <div class="video-player-container mb-3 border border-dark rounded-4" style="position: relative; overflow: hidden; background: #000;">
                    <?php 
                    $requiresOtp = true; // All videos require OTP
                    $hasAccess = VideoOTP::hasValidSession($userId, $activeVideo['id']);
                    ?>
                    
                    <?php if ($enrollmentStatus === 'Pending' && $activeVideoIndex >= 2): ?>
                        <div class="d-flex align-items-center justify-content-center h-100 bg-dark text-white rounded-4" style="min-height: 450px;">
                            <div class="text-center p-4">
                                <i class="fa-solid fa-lock fs-1 text-warning mb-3"></i>
                                <h4>Restricted Access</h4>
                                <p class="text-light fs-7 mb-4">You have reached the end of your preview. Please pay the remaining balance to unlock the rest of the course videos.</p>
                                <a href="course.php?slug=<?php echo urlencode($slug); ?>" class="btn btn-warning fw-bold px-4 rounded-pill">Pay Remaining Balance</a>
                            </div>
                        </div>
                    <?php elseif ($hasAccess): ?>
                        <?php if (!empty($activeVideo['video_url'])): ?>
                            <video controls controlsList="nodownload" style="width: 100%; height: 100%; min-height: 450px; background: #000;">
                                <source src="video_stream.php?id=<?php echo $activeVideo['id']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center text-white h-100 bg-dark" style="min-height: 450px;">
                                <div><i class="fa-solid fa-video-slash fs-1 mb-2 d-block text-center"></i>Video URL is not set.</div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- OTP Locked Screen -->
                        <div class="d-flex align-items-center justify-content-center h-100 bg-dark text-white" style="min-height: 450px;">
                            <div class="text-center p-4">
                                <i class="fa-solid fa-lock fs-1 text-warning mb-3"></i>
                                <h4>Premium Content Locked</h4>
                                <p class="text-light fs-7 mb-4">This video requires OTP verification to unlock.</p>
                                
                                <div id="otp-request-block">
                                    <button class="btn btn-warning fw-bold px-4 rounded-pill" onclick="sendVideoOtp(<?php echo $activeVideo['id']; ?>)">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Send OTP to Registered Mobile
                                    </button>
                                </div>
                                
                                <div id="otp-verify-block" style="display: none; max-width: 300px; margin: 0 auto;">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="video_otp_input" placeholder="Enter OTP" maxlength="6">
                                        <button class="btn btn-success" type="button" onclick="verifyVideoOtp(<?php echo $activeVideo['id']; ?>)">Unlock</button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small id="otp_message" class="text-info"></small>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mt-4 border-bottom-0" id="videoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark border-bottom-0" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab" aria-controls="desc" aria-selected="true">Description</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark border-bottom-0" id="resources-tab" data-bs-toggle="tab" data-bs-target="#resources" type="button" role="tab" aria-controls="resources" aria-selected="false">Resources</button>
                    </li>
                </ul>
                <div class="tab-content bg-white p-4 border rounded-bottom-4 rounded-end-4 shadow-sm mb-4" id="videoTabsContent">
                    <div class="tab-pane fade show active" id="desc" role="tabpanel" aria-labelledby="desc-tab">
                        <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($activeVideo['title']); ?></h5>
                        <?php if (!empty($activeVideo['description'])): ?>
                            <p class="text-muted" style="white-space: pre-wrap;"><?php echo htmlspecialchars($activeVideo['description']); ?></p>
                        <?php else: ?>
                            <p class="text-muted fs-7">No description provided for this lesson.</p>
                        <?php endif; ?>
                        
                        <hr class="my-4">
                        <p class="text-muted m-0 fs-7">You are watching: Lesson <?php 
                            $lessonIndex = 1;
                            foreach ($allVideos as $idx => $v) {
                                if ($v['id'] === $activeVideo['id']) {
                                    $lessonIndex = $idx + 1;
                                    break;
                                }
                            }
                            echo $lessonIndex . " of " . count($allVideos);
                        ?></p>
                    </div>
                    <div class="tab-pane fade" id="resources" role="tabpanel" aria-labelledby="resources-tab">
                        <?php if (!empty($activeVideo['document_url']) && $hasAccess && !($enrollmentStatus === 'Pending' && $activeVideoIndex >= 2)): ?>
                            <h5 class="fw-bold mb-3">Lesson Resources</h5>
                            <p class="text-muted fs-7 mb-4">Download the supplementary materials for this lesson below.</p>
                            <a href="<?php echo htmlspecialchars(SITE_URL . '/' . $activeVideo['document_url']); ?>" download class="btn btn-outline-primary rounded-pill fw-bold px-4">
                                <i class="fa-solid fa-download me-2"></i>Download Document
                            </a>
                        <?php elseif (empty($activeVideo['document_url'])): ?>
                            <p class="text-muted fs-7 m-0">No resources available for this lesson.</p>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 fs-8 m-0"><i class="fa-solid fa-lock me-2"></i>Please unlock the video to access the resources.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Side: Syllabus navigation -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-ul text-primary me-2"></i>Course Syllabus</h5>
                    <div class="syllabus-list">
                        <?php foreach ($syllabus as $item): 
                            $section = $item['section'];
                            $videos = $item['videos'];
                        ?>
                            <div class="bg-light p-3 fw-semibold text-dark fs-7 border-bottom border-top">
                                <?php echo htmlspecialchars($section['title']); ?>
                            </div>
                            
                            <?php foreach ($videos as $video): 
                                $isActive = ($video['id'] === $activeVideo['id']);
                            ?>
                                <a href="course_play.php?slug=<?php echo $course['slug']; ?>&video_id=<?php echo $video['id']; ?>" 
                                   class="syllabus-item <?php echo $isActive ? 'active' : ''; ?> text-decoration-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <i class="fa-regular fa-circle-play <?php echo $isActive ? 'text-primary' : 'text-muted'; ?>"></i>
                                        <span class="fs-7 fw-medium text-dark <?php echo $isActive ? 'fw-bold text-primary' : ''; ?>">
                                            <?php echo htmlspecialchars($video['title']); ?>
                                        </span>
                                    </div>
                                    <i class="fa-solid fa-chevron-right text-muted fs-8"></i>
                                </a>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
