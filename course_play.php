<?php
// Course Video Player Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/VideoOTP.php';
require_once __DIR__ . '/services/BunnyStreamService.php';
require_once __DIR__ . '/helpers/SecurityHelper.php';

// 1. Require Login
if (!isset($_SESSION['user_id'])) {
    set_flash_message('warning', 'Please login to access your courses.');
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$currentUser = DB::fetch("SELECT email, full_name FROM users WHERE id = ?", [$userId]);
$userEmail = $currentUser['email'] ?? ($_SESSION['user_email'] ?? 'student@cava.com');
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

if ($enrollmentStatus === 'Pending') {
    set_flash_message('warning', 'Please pay the remaining balance to access the course content.');
    header("Location: course.php?slug=" . urlencode($slug));
    exit;
}

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

// Fetch completed video IDs for progress calculation and checkmarks
$completedVideoIds = [];
try {
    $rows = DB::fetchAll("SELECT video_id FROM user_video_progress WHERE user_id = ? AND course_id = ? AND status = 'completed'", [$userId, $courseId]);
    $completedVideoIds = array_column($rows, 'video_id');
} catch (Exception $e) {
    // Fail silently
}
$totalVideos = count($allVideos);
$completedCount = count($completedVideoIds);
$progressPercent = ($totalVideos > 0) ? min(100, round(($completedCount / $totalVideos) * 100)) : 0;

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
$videoId = trim($_GET['video_id'] ?? '');
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

// Fetch active video documents
$activeVideoDocuments = DB::fetchAll("SELECT * FROM video_documents WHERE video_id = ? ORDER BY created_at ASC", [$activeVideo['id']]);

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
                        <?php 
                        $videoWatermarkSvg = 'data:image/svg+xml;utf8,' . rawurlencode('
                        <svg xmlns="http://www.w3.org/2000/svg" width="360" height="230">
                            <text x="50%" y="30%" fill="rgba(255, 255, 255, 0.16)" font-size="15" font-family="sans-serif" font-weight="bold" text-anchor="middle" transform="rotate(-30, 180, 69)">' . htmlspecialchars($userEmail) . '</text>
                            <text x="50%" y="80%" fill="rgba(255, 255, 255, 0.12)" font-size="20" font-family="sans-serif" font-weight="900" text-anchor="middle" transform="rotate(-40, 180, 184)">' . htmlspecialchars($userEmail) . '</text>
                        </svg>');
                        ?>
                        <?php if (($activeVideo['video_provider'] ?? '') === 'bunny' && !empty($activeVideo['bunny_video_id'])): 
                            $bunnyService = new BunnyStreamService();
                            $bunnyEmbedUrl = $bunnyService->getPlaybackUrl($activeVideo['bunny_video_id']);
                        ?>
                            <div style="position: relative; padding-top: 56.25%; width: 100%; min-height: 450px; background: #000; overflow: hidden;">
                                <iframe 
                                    id="bunnyPlayerIframe"
                                    src="<?php echo htmlspecialchars($bunnyEmbedUrl); ?>?autoplay=false" 
                                    loading="lazy" 
                                    style="border: none; position: absolute; top: 0; left: 0; height: 100%; width: 100%;" 
                                    allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;" 
                                    allowfullscreen="true">
                                </iframe>
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 10; background-image: url('<?php echo $videoWatermarkSvg; ?>'); background-repeat: repeat;"></div>
                            </div>
                        <?php elseif (!empty($activeVideo['video_url'])): ?>
                            <div style="position: relative; width: 100%; height: 100%; min-height: 450px; background: #000; overflow: hidden;">
                                <video controls controlsList="nodownload" style="width: 100%; height: 100%; min-height: 450px; background: #000;">
                                    <source src="video_stream.php?id=<?php echo $activeVideo['id']; ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; z-index: 10; background-image: url('<?php echo $videoWatermarkSvg; ?>'); background-repeat: repeat;"></div>
                            </div>
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
                                    <button class="btn btn-warning fw-bold px-4 rounded-pill" onclick="sendVideoOtp('<?php echo $activeVideo['id']; ?>')">
                                        <i class="fa-solid fa-paper-plane me-2"></i>Send OTP to Registered Email
                                    </button>
                                </div>
                                
                                <div id="otp-verify-block" style="display: none; max-width: 300px; margin: 0 auto;">
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="video_otp_input" placeholder="Enter OTP" maxlength="6">
                                        <button class="btn btn-success" type="button" onclick="verifyVideoOtp('<?php echo $activeVideo['id']; ?>')">Unlock</button>
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
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                            <h5 class="fw-bold m-0"><?php echo htmlspecialchars($activeVideo['title']); ?></h5>
                            <?php if ($hasAccess): ?>
                                <div id="progress-toggle-btn">
                                    <?php if (in_array($activeVideo['id'], $completedVideoIds)): ?>
                                        <button class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="toggleProgress('<?php echo $activeVideo['id']; ?>', '<?php echo $courseId; ?>', 'uncomplete')">
                                            <i class="fa-solid fa-circle-check text-success me-1"></i> Completed (Undo)
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-primary rounded-pill btn-sm px-3 text-white" onclick="toggleProgress('<?php echo $activeVideo['id']; ?>', '<?php echo $courseId; ?>', 'complete')">
                                            <i class="fa-regular fa-circle me-1"></i> Mark as Completed
                                        </button>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
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
                        <?php if ($hasAccess): ?>
                            <?php if (!empty($activeVideoDocuments)): ?>
                                <h5 class="fw-bold mb-3">Lesson Resources</h5>
                                <p class="text-muted fs-7 mb-4">View supplementary materials for this lesson below in the secure in-page viewer.</p>
                                <div class="list-group">
                                    <?php foreach ($activeVideoDocuments as $doc): ?>
                                        <button type="button" 
                                                onclick="openSecureDocumentViewer('<?php echo urlencode($doc['id']); ?>', '<?php echo htmlspecialchars(addslashes($doc['title'] . '.' . $doc['file_type'])); ?>', '<?php echo strtolower($doc['file_type']); ?>')" 
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 mb-2 border py-3 px-3">
                                            <div class="d-flex align-items-center">
                                                <?php 
                                                    $icon = 'fa-file';
                                                    $textClass = 'text-secondary';
                                                    if ($doc['file_type'] === 'pdf') { $icon = 'fa-file-pdf'; $textClass = 'text-danger'; }
                                                    elseif (in_array($doc['file_type'], ['doc','docx'])) { $icon = 'fa-file-word'; $textClass = 'text-primary'; }
                                                    elseif (in_array($doc['file_type'], ['xls','xlsx'])) { $icon = 'fa-file-excel'; $textClass = 'text-success'; }
                                                    elseif (in_array($doc['file_type'], ['ppt','pptx'])) { $icon = 'fa-file-powerpoint'; $textClass = 'text-warning'; }
                                                    elseif (in_array($doc['file_type'], ['zip'])) { $icon = 'fa-file-zipper'; $textClass = 'text-muted'; }
                                                ?>
                                                <i class="fa-solid <?php echo $icon; ?> <?php echo $textClass; ?> fs-4 me-3"></i>
                                                <div class="text-start">
                                                    <h6 class="mb-0 fw-semibold text-dark"><?php echo htmlspecialchars($doc['title'] . '.' . $doc['file_type']); ?></h6>
                                                    <small class="text-muted"><?php echo number_format($doc['file_size'] / 1024 / 1024, 2); ?> MB</small>
                                                </div>
                                            </div>
                                            <span class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium"><i class="fa-solid fa-eye me-1"></i> View Resource</span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted fs-7 m-0">No resources available for this lesson.</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 fs-8 m-0"><i class="fa-solid fa-lock me-2"></i>Please unlock the video to access the resources.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Side: Syllabus navigation -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fs-7 fw-bold text-dark">Your Progress</span>
                        <span class="fs-7 fw-bold text-primary"><?php echo $progressPercent; ?>%</span>
                    </div>
                    <div class="progress mb-2" style="height: 8px; border-radius: 4px;">
                        <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: <?php echo $progressPercent; ?>%;" aria-valuenow="<?php echo $progressPercent; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted fs-8"><?php echo $completedCount; ?> of <?php echo $totalVideos; ?> lessons completed</small>
                </div>

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
                                $isCompleted = in_array($video['id'], $completedVideoIds);
                            ?>
                                <a href="course_play.php?slug=<?php echo $course['slug']; ?>&video_id=<?php echo $video['id']; ?>" 
                                   class="syllabus-item <?php echo $isActive ? 'active' : ''; ?> text-decoration-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($isCompleted): ?>
                                            <i class="fa-solid fa-circle-check text-success"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-circle-play <?php echo $isActive ? 'text-primary' : 'text-muted'; ?>"></i>
                                        <?php endif; ?>
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


<script>
function toggleProgress(videoId, courseId, action) {
    const btnContainer = document.getElementById('progress-toggle-btn');
    if (!btnContainer) return;
    
    // Disable interaction
    const btn = btnContainer.querySelector('button');
    if (btn) btn.disabled = true;

    const formData = new FormData();
    formData.append('video_id', videoId);
    formData.append('course_id', courseId);
    formData.append('action', action);

    fetch('api/track_progress.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to refresh progress calculation and UI states
            window.location.reload();
        } else {
            alert(data.message || 'Error updating progress.');
            if (btn) btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error tracking progress:', error);
        if (btn) btn.disabled = false;
    });
}

// Auto-track video completion on ended event
function handleVideoEnded() {
    console.log('Video ended. Automatically marking as completed.');
    const videoId = "<?php echo $activeVideo['id']; ?>";
    const courseId = "<?php echo $courseId; ?>";
    
    // Only auto-complete if not already completed
    const isAlreadyCompleted = <?php echo in_array($activeVideo['id'], $completedVideoIds) ? 'true' : 'false'; ?>;
    if (!isAlreadyCompleted) {
        const formData = new FormData();
        formData.append('video_id', videoId);
        formData.append('course_id', courseId);
        formData.append('action', 'complete');

        fetch('api/track_progress.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Auto-track error:', error);
        });
    }
}

</script>
<script src="https://assets.mediadelivery.net/playerjs/player-0.1.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // For local HTML5 video
    const video = document.querySelector('video');
    if (video) {
        video.addEventListener('ended', handleVideoEnded);
    }
    
    // For Bunny Stream Player via Player.js API
    const iframe = document.getElementById('bunnyPlayerIframe');
    if (iframe) {
        try {
            const player = new playerjs.Player(iframe);
            player.on('ready', function() {
                console.log('PlayerJS: Bunny Player is ready');
            });
            player.on('ended', function() {
                console.log('PlayerJS: Video ended event received');
                handleVideoEnded();
            });
        } catch(e) {
            console.error('PlayerJS initialization error:', e);
        }
    }
});
</script>

<!-- Secure Document Viewer Modal (No Download / Anti-Piracy Protections) -->
<div class="modal fade" id="secureDocModal" tabindex="-1" aria-labelledby="secureDocModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 92vw; height: 88vh;">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-dark text-white h-100">
            <div class="modal-header border-secondary py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-success fs-5"></i>
                    <h6 class="modal-title fw-bold text-white mb-0 text-truncate" id="secureDocTitle" style="max-width: 350px;">Document Viewer</h6>
                    <span class="badge bg-secondary text-light fs-8 ms-2"><i class="fa-solid fa-lock me-1"></i>Protected View</span>
                </div>
                
                <!-- Toolbar Controls -->
                <div class="d-flex align-items-center gap-2" id="docViewerControls">
                    <button type="button" class="btn btn-sm btn-outline-light d-none" id="btnPrevPage" title="Previous Page"><i class="fa-solid fa-chevron-left"></i></button>
                    <span class="fs-8 text-light fw-medium d-none" id="docPageCounter"><span id="docPageNum">1</span> / <span id="docPageCount">--</span></span>
                    <button type="button" class="btn btn-sm btn-outline-light d-none" id="btnNextPage" title="Next Page"><i class="fa-solid fa-chevron-right"></i></button>
                    <div class="vr bg-secondary mx-1 d-none" id="docToolbarVR" style="height:20px;"></div>
                    <button type="button" class="btn btn-sm btn-outline-light d-none" id="btnZoomOut" title="Zoom Out"><i class="fa-solid fa-magnifying-glass-minus"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-light d-none" id="btnZoomIn" title="Zoom In"><i class="fa-solid fa-magnifying-glass-plus"></i></button>
                    <button type="button" class="btn-close btn-close-white ms-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            
            <div class="modal-body p-0 d-flex justify-content-center align-items-start position-relative overflow-auto bg-dark" id="docCanvasContainer" style="user-select: none; -webkit-user-select: none;">
                <div id="docLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <p class="text-light fs-7">Loading secure document...</p>
                </div>
                <div id="docErrorAlert" class="alert alert-danger m-4 d-none"></div>
                <canvas id="docCanvas" class="shadow-lg my-3 rounded d-none" style="pointer-events: none;"></canvas>
                <div id="docTextContainer" class="p-4 m-3 bg-white text-dark rounded shadow-lg d-none w-100 position-relative" style="user-select: none; -webkit-user-select: none; max-width: 900px; min-height: 500px; overflow-y: auto;"></div>
            </div>
        </div>
    </div>
</div>

<!-- PDF.js library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<!-- Mammoth.js library for DOCX rendering -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
<script>
let currentPdfDoc = null;
let currentDocPage = 1;
let currentScale = 1.2;
let pageIsRendering = false;
let pageNumPending = null;
let currentFileType = 'pdf';
let currentLoadedImage = null;
const watermarkText = <?php echo json_encode($userEmail); ?>;

function getMultiLayerSvgWatermark(email) {
    const svgStr = `
        <svg xmlns="http://www.w3.org/2000/svg" width="380" height="240">
            <text x="50%" y="28%" fill="rgba(100, 100, 100, 0.18)" font-size="15" font-family="sans-serif" font-weight="bold" text-anchor="middle" transform="rotate(-30, 190, 67)">${email}</text>
            <text x="50%" y="78%" fill="rgba(70, 70, 70, 0.14)" font-size="22" font-family="sans-serif" font-weight="900" text-anchor="middle" transform="rotate(-42, 190, 187)">${email}</text>
        </svg>
    `;
    return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svgStr);
}

function drawWatermarkOnCanvas(canvas, ctx) {
    ctx.save();
    const text = watermarkText;
    const width = canvas.width;
    const height = canvas.height;
    const diag = Math.sqrt(width * width + height * height);

    // Layer 1: Dense grid, angle -30 deg, font 22px
    ctx.save();
    ctx.font = "bold 22px sans-serif";
    ctx.fillStyle = "rgba(110, 110, 110, 0.20)";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.rotate(-Math.PI / 6);

    const stepX1 = 260;
    const stepY1 = 150;
    for (let y = -diag; y < diag * 2; y += stepY1) {
        for (let x = -diag; x < diag * 2; x += stepX1) {
            ctx.fillText(text, x, y);
        }
    }
    ctx.restore();

    // Layer 2: Off-grid large text, angle -42 deg, font 36px
    ctx.save();
    ctx.font = "900 36px sans-serif";
    ctx.fillStyle = "rgba(80, 80, 80, 0.15)";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.rotate(-Math.PI / 4.2);

    const stepX2 = 380;
    const stepY2 = 230;
    for (let y = -diag + 60; y < diag * 2; y += stepY2) {
        for (let x = -diag + 80; x < diag * 2; x += stepX2) {
            ctx.fillText(text, x, y);
        }
    }
    ctx.restore();

    // Layer 3: Micro detail grid, angle -18 deg, font 15px
    ctx.save();
    ctx.font = "600 15px sans-serif";
    ctx.fillStyle = "rgba(130, 130, 130, 0.13)";
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.rotate(-Math.PI / 10);

    const stepX3 = 210;
    const stepY3 = 110;
    for (let y = -diag + 30; y < diag * 2; y += stepY3) {
        for (let x = -diag + 40; x < diag * 2; x += stepX3) {
            ctx.fillText(text, x, y);
        }
    }
    ctx.restore();

    ctx.restore();
}

function renderDocPage(num) {
    if (!currentPdfDoc) return;
    pageIsRendering = true;
    currentPdfDoc.getPage(num).then(page => {
        const canvas = document.getElementById('docCanvas');
        const ctx = canvas.getContext('2d');
        const viewport = page.getViewport({ scale: currentScale });
        
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        const renderCtx = {
            canvasContext: ctx,
            viewport: viewport
        };

        page.render(renderCtx).promise.then(() => {
            pageIsRendering = false;
            drawWatermarkOnCanvas(canvas, ctx);

            if (pageNumPending !== null) {
                renderDocPage(pageNumPending);
                pageNumPending = null;
            }
        });

        document.getElementById('docPageNum').textContent = num;
    });
}

function renderImageCanvas() {
    if (!currentLoadedImage) return;
    const canvas = document.getElementById('docCanvas');
    const ctx = canvas.getContext('2d');
    canvas.width = currentLoadedImage.width * currentScale;
    canvas.height = currentLoadedImage.height * currentScale;
    ctx.drawImage(currentLoadedImage, 0, 0, canvas.width, canvas.height);
    drawWatermarkOnCanvas(canvas, ctx);
}

function queueRenderPage(num) {
    if (currentFileType === 'pdf') {
        if (pageIsRendering) {
            pageNumPending = num;
        } else {
            renderDocPage(num);
        }
    } else if (['jpg','jpeg','png','gif','webp'].includes(currentFileType)) {
        renderImageCanvas();
    }
}

function setToolbarState(type) {
    const isPdf = (type === 'pdf');
    const isImage = ['jpg','jpeg','png','gif','webp'].includes(type);
    
    document.getElementById('btnPrevPage').classList.toggle('d-none', !isPdf);
    document.getElementById('docPageCounter').classList.toggle('d-none', !isPdf);
    document.getElementById('btnNextPage').classList.toggle('d-none', !isPdf);
    document.getElementById('docToolbarVR').classList.toggle('d-none', !(isPdf || isImage));
    document.getElementById('btnZoomOut').classList.toggle('d-none', !(isPdf || isImage));
    document.getElementById('btnZoomIn').classList.toggle('d-none', !(isPdf || isImage));
}

function openSecureDocumentViewer(docId, title, fileType) {
    const modalEl = document.getElementById('secureDocModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    document.getElementById('secureDocTitle').innerText = title;
    const spinner = document.getElementById('docLoadingSpinner');
    const errorAlert = document.getElementById('docErrorAlert');
    const canvas = document.getElementById('docCanvas');
    const textContainer = document.getElementById('docTextContainer');
    
    spinner.classList.remove('d-none');
    errorAlert.classList.add('d-none');
    canvas.classList.add('d-none');
    textContainer.classList.add('d-none');
    textContainer.innerHTML = '';
    
    modal.show();
    
    currentFileType = fileType ? fileType.toLowerCase() : 'pdf';
    setToolbarState(currentFileType);
    
    const docUrl = 'serve_document.php?id=' + encodeURIComponent(docId);
    currentDocPage = 1;
    currentScale = 1.0;
    currentPdfDoc = null;
    currentLoadedImage = null;

    // 1. PDF File Handling
    if (currentFileType === 'pdf') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        const loadingTask = pdfjsLib.getDocument({
            url: docUrl,
            httpHeaders: { 'X-Viewer-Auth': 'true' }
        });
        
        loadingTask.promise.then(pdf => {
            currentPdfDoc = pdf;
            document.getElementById('docPageCount').textContent = pdf.numPages;
            spinner.classList.add('d-none');
            canvas.classList.remove('d-none');
            currentScale = 1.2;
            renderDocPage(currentDocPage);
        }).catch(err => {
            spinner.classList.add('d-none');
            errorAlert.innerText = 'Unable to load PDF document in secure viewer. Access denied or file error.';
            errorAlert.classList.remove('d-none');
        });
    }
    // 2. DOCX / DOC Handling via Mammoth.js
    else if (['docx', 'doc'].includes(currentFileType)) {
        fetch(docUrl, { headers: { 'X-Viewer-Auth': 'true' } })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.arrayBuffer();
            })
            .then(arrayBuffer => mammoth.convertToHtml({ arrayBuffer: arrayBuffer }))
            .then(result => {
                spinner.classList.add('d-none');
                textContainer.innerHTML = `
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;z-index:10;background-image:url('${getMultiLayerSvgWatermark(watermarkText)}');background-repeat:repeat;"></div>
                    <div class="p-3 fs-6 lh-base position-relative" style="z-index:1;">${result.value}</div>
                `;
                textContainer.classList.remove('d-none');
            })
            .catch(err => {
                spinner.classList.add('d-none');
                errorAlert.innerText = 'Unable to load Word document. File may be corrupted or permission denied.';
                errorAlert.classList.remove('d-none');
            });
    }
    // 3. Image File Handling (JPG, PNG, GIF, WEBP)
    else if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(currentFileType)) {
        fetch(docUrl, { headers: { 'X-Viewer-Auth': 'true' } })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.blob();
            })
            .then(blob => {
                const img = new Image();
                const objectUrl = URL.createObjectURL(blob);
                img.onload = function() {
                    currentLoadedImage = img;
                    spinner.classList.add('d-none');
                    canvas.classList.remove('d-none');
                    renderImageCanvas();
                    URL.revokeObjectURL(objectUrl);
                };
                img.src = objectUrl;
            })
            .catch(err => {
                spinner.classList.add('d-none');
                errorAlert.innerText = 'Unable to load image in secure viewer.';
                errorAlert.classList.remove('d-none');
            });
    }
    // 4. Text / Code File Handling (TXT, MD, CSV, JSON)
    else if (['txt', 'md', 'csv', 'json', 'log'].includes(currentFileType)) {
        fetch(docUrl, { headers: { 'X-Viewer-Auth': 'true' } })
            .then(res => res.text())
            .then(text => {
                spinner.classList.add('d-none');
                const safeText = text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                textContainer.innerHTML = `
                    <div style="position:absolute;top:0;left:0;right:0;bottom:0;pointer-events:none;z-index:10;background-image:url('${getMultiLayerSvgWatermark(watermarkText)}');background-repeat:repeat;"></div>
                    <pre class="p-3 fs-7 text-dark m-0 position-relative" style="z-index:1;white-space:pre-wrap;font-family:monospace;">${safeText}</pre>
                `;
                textContainer.classList.remove('d-none');
            })
            .catch(err => {
                spinner.classList.add('d-none');
                errorAlert.innerText = 'Unable to load text document.';
                errorAlert.classList.remove('d-none');
            });
    }
    // 5. Fallback for other file types (XLSX, PPTX, ZIP)
    else {
        spinner.classList.add('d-none');
        textContainer.innerHTML = `
            <div class="text-center py-5">
                <i class="fa-solid fa-file-shield text-primary mb-3" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold text-dark mb-2">Protected ${currentFileType.toUpperCase()} Resource</h5>
                <p class="text-muted fs-7 mb-0" style="max-width: 500px; margin: 0 auto;">
                    Direct downloading of files is disabled to protect course content. For optimal viewing, please convert PPTX/XLSX resources to PDF or DOCX format before uploading.
                </p>
            </div>
        `;
        textContainer.classList.remove('d-none');
    }
}

// Viewer Control Handlers
document.getElementById('btnPrevPage').addEventListener('click', () => {
    if (!currentPdfDoc || currentDocPage <= 1) return;
    currentDocPage--;
    queueRenderPage(currentDocPage);
});

document.getElementById('btnNextPage').addEventListener('click', () => {
    if (!currentPdfDoc || currentDocPage >= currentPdfDoc.numPages) return;
    currentDocPage++;
    queueRenderPage(currentDocPage);
});

document.getElementById('btnZoomIn').addEventListener('click', () => {
    currentScale += 0.2;
    queueRenderPage(currentDocPage);
});

document.getElementById('btnZoomOut').addEventListener('click', () => {
    if (currentScale <= 0.4) return;
    currentScale -= 0.2;
    queueRenderPage(currentDocPage);
});

</script>

<?php echo SecurityHelper::renderAntiPiracyScript(); ?>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
