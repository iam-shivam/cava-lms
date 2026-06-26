<?php
// Course Video Player Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';

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

// 2. Access Control check: Is enrolled?
$isEnrolled = Course::isUserEnrolled($userId, $courseId);
if (!$isEnrolled) {
    set_flash_message('danger', 'You do not have access to this course. Please purchase it first.');
    header("Location: " . SITE_URL . "/course.php?slug=" . $course['slug']);
    exit;
}

$syllabus = Course::getSyllabus($courseId);
$totalLessons = Course::countLessons($courseId);

// Get list of all videos in a flat array for easy navigation/indexing
$allVideos = [];
foreach ($syllabus as $item) {
    foreach ($item['videos'] as $video) {
        $allVideos[] = $video;
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

if ($videoId > 0) {
    foreach ($allVideos as $video) {
        if ($video['id'] === $videoId) {
            $activeVideo = $video;
            break;
        }
    }
}

// Fallback to first video
if (!$activeVideo) {
    $activeVideo = $allVideos[0];
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
                <div class="video-player-container mb-3 border border-dark rounded-4">
                    <?php if (!empty($activeVideo['video_url'])): ?>
                        <iframe 
                            src="<?php echo htmlspecialchars($activeVideo['video_url']); ?>" 
                            title="<?php echo htmlspecialchars($activeVideo['title']); ?>" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen 
                            style="width: 100%; height: 100%;">
                        </iframe>
                    <?php else: ?>
                        <div class="d-flex align-items-center justify-content-center text-white h-100 bg-dark">
                            <div><i class="fa-solid fa-video-slash fs-1 mb-2 d-block text-center"></i>Video URL is not set.</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card border-0 p-4 shadow-sm rounded-4 bg-white mt-4">
                    <h4 class="fw-bold mb-3"><?php echo htmlspecialchars($activeVideo['title']); ?></h4>
                    <p class="text-muted m-0">You are watching: Lesson <?php 
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
