<?php
// Admin Course Sections & Videos Manager
require_once __DIR__ . '/admin_header.php';

$courseId = intval($_GET['course_id'] ?? 0);
$course = DB::fetch("SELECT * FROM courses WHERE id = ?", [$courseId]);

if (!$course) {
    set_flash_message('danger', 'Course not found.');
    header("Location: courses.php");
    exit;
}

$csrfToken = generate_csrf_token();
$action = trim($_GET['action'] ?? 'list');
$id = intval($_GET['id'] ?? 0);

// Form Actions Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: videos.php?course_id=$courseId");
        exit;
    }
    
    $formType = $_POST['form_type'] ?? '';
    
    try {
        if ($formType === 'add_section') {
            $title = trim($_POST['section_title'] ?? '');
            $order = intval($_POST['sort_order'] ?? 0);
            if (empty($title)) {
                set_flash_message('danger', 'Section title cannot be empty.');
            } else {
                $stmt = DB::getConnection()->prepare("INSERT INTO course_sections (course_id, title, sort_order) VALUES (?, ?, ?)");
                $stmt->execute([$courseId, $title, $order]);
                set_flash_message('success', 'Section created successfully!');
            }
        } elseif ($formType === 'add_video') {
            $sectionId = intval($_POST['section_id'] ?? 0);
            $title = trim($_POST['video_title'] ?? '');
            $url = trim($_POST['video_url'] ?? '');
            $source = $_POST['video_source'] ?? 'youtube';
            $order = intval($_POST['sort_order'] ?? 0);
            
            if ($sectionId <= 0 || empty($title) || empty($url)) {
                set_flash_message('danger', 'Please complete all lesson video fields.');
            } else {
                $stmt = DB::getConnection()->prepare("INSERT INTO course_videos (section_id, course_id, title, video_url, video_source, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$sectionId, $courseId, $title, $url, $source, $order]);
                set_flash_message('success', 'Video lesson added successfully!');
            }
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    
    header("Location: videos.php?course_id=$courseId");
    exit;
}

// Delete Handlers
if ($action === 'delete_section' && $id > 0) {
    try {
        DB::query("DELETE FROM course_sections WHERE id = ? AND course_id = ?", [$id, $courseId]);
        set_flash_message('success', 'Section and all its videos deleted successfully!');
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    header("Location: videos.php?course_id=$courseId");
    exit;
}

if ($action === 'delete_video' && $id > 0) {
    try {
        DB::query("DELETE FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);
        set_flash_message('success', 'Video lesson deleted successfully!');
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    header("Location: videos.php?course_id=$courseId");
    exit;
}

// Fetch Sections
$sections = DB::fetchAll("SELECT * FROM course_sections WHERE course_id = ? ORDER BY sort_order ASC, id ASC", [$courseId]);

// Fetch Videos grouped by Section
$syllabus = [];
foreach ($sections as $sec) {
    $videos = DB::fetchAll("SELECT * FROM course_videos WHERE section_id = ? ORDER BY sort_order ASC, id ASC", [$sec['id']]);
    $syllabus[] = [
        'section' => $sec,
        'videos' => $videos
    ];
}
?>

<div class="mb-4">
    <a href="courses.php" class="text-decoration-none text-muted mb-2 d-inline-block">
        <i class="fa-solid fa-arrow-left"></i> Back to Courses
    </a>
    <h1 class="h3 fw-bold text-dark">Syllabus Builder: <span class="text-primary"><?php echo htmlspecialchars($course['title']); ?></span></h1>
</div>

<div class="row">
    <!-- Manage Content / Syllabus List -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <h5 class="fw-bold text-dark mb-4"><i class="fa-solid fa-folder-tree text-primary me-2"></i>Syllabus Outline</h5>
            
            <?php if (empty($syllabus)): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-list-check fs-1 text-muted mb-3 d-block"></i>
                    <p class="text-muted">No sections or videos have been created yet. Build them using the panels on the right.</p>
                </div>
            <?php else: ?>
                <div class="accordion" id="adminSyllabus">
                    <?php foreach ($syllabus as $index => $item): 
                        $sec = $item['section'];
                        $videos = $item['videos'];
                        $collapseId = "collapseSec_" . $sec['id'];
                        $headingId = "headingSec_" . $sec['id'];
                    ?>
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded-4 overflow-hidden">
                            <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                <div class="accordion-button bg-light fw-bold py-3 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="true">
                                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                        <span>
                                            <i class="fa-solid fa-folder me-2 text-warning"></i>
                                            <?php echo htmlspecialchars($sec['title']); ?>
                                            <small class="text-muted ms-2 fs-8">(Sort: <?php echo $sec['sort_order']; ?>)</small>
                                        </span>
                                        <div>
                                            <a href="videos.php?course_id=<?php echo $courseId; ?>&action=delete_section&id=<?php echo $sec['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger py-1 px-2 border-0" 
                                               onclick="return confirm('Are you sure you want to delete this section and all its lesson videos?');"
                                               title="Delete Section">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </h2>
                            <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse show" data-bs-parent="#adminSyllabus">
                                <div class="accordion-body bg-white p-0">
                                    <div class="list-group list-group-flush">
                                        <?php if (empty($videos)): ?>
                                            <div class="p-3 text-center text-muted fs-8">No video lessons in this section.</div>
                                        <?php else: ?>
                                            <?php foreach ($videos as $video): ?>
                                                <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-4 border-0 border-bottom">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <i class="fa-regular fa-circle-play text-primary"></i>
                                                        <div>
                                                            <span class="fw-medium text-dark d-block"><?php echo htmlspecialchars($video['title']); ?></span>
                                                            <span class="text-muted fs-8 d-block text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($video['video_url']); ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-secondary-light text-secondary fs-8"><?php echo htmlspecialchars($video['video_source']); ?></span>
                                                        <a href="videos.php?course_id=<?php echo $courseId; ?>&action=delete_video&id=<?php echo $video['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm border-0" 
                                                           onclick="return confirm('Delete this lesson video?');">
                                                            <i class="fa-solid fa-trash-can"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Section & Add Video Forms -->
    <div class="col-lg-5">
        <!-- Add Section Form -->
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4 mb-4">
            <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-folder-plus me-2"></i>Add Course Section</h5>
            <form action="videos.php?course_id=<?php echo $courseId; ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="form_type" value="add_section">
                
                <div class="mb-3">
                    <label for="section_title" class="form-label fw-semibold">Section Title</label>
                    <input type="text" class="form-control" id="section_title" name="section_title" placeholder="e.g. Section 1: Introduction" required>
                </div>
                
                <div class="mb-3">
                    <label for="sort_order" class="form-label fw-semibold">Sort Order</label>
                    <input type="number" class="form-control" id="sort_order" name="sort_order" value="1">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Create Section</button>
            </form>
        </div>
        
        <!-- Add Video Form -->
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-video-camera me-2"></i>Add Video Lesson</h5>
            <form action="videos.php?course_id=<?php echo $courseId; ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="form_type" value="add_video">
                
                <div class="mb-3">
                    <label for="section_id" class="form-label fw-semibold">Select Section</label>
                    <select class="form-select" id="section_id" name="section_id" required>
                        <option value="">Select Section</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?php echo $sec['id']; ?>"><?php echo htmlspecialchars($sec['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="video_title" class="form-label fw-semibold">Lesson Title</label>
                    <input type="text" class="form-control" id="video_title" name="video_title" placeholder="e.g. What is ECA Evaluation?" required>
                </div>
                
                <div class="mb-3">
                    <label for="video_source" class="form-label fw-semibold">Video Source</label>
                    <select class="form-select" id="video_source" name="video_source" required>
                        <option value="youtube">YouTube Embed Link</option>
                        <option value="vimeo">Vimeo Embed Link</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="video_url" class="form-label fw-semibold">Embed Video URL</label>
                    <input type="url" class="form-control" id="video_url" name="video_url" placeholder="e.g. https://www.youtube.com/embed/XXXXXX" required>
                    <small class="text-muted fs-8 mt-1 d-block">Make sure to use the <b>embed</b> format URL.</small>
                </div>
                
                <div class="mb-3">
                    <label for="sort_order_vid" class="form-label fw-semibold">Sort Order</label>
                    <input type="number" class="form-control" id="sort_order_vid" name="sort_order" value="1">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Add Video Lesson</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
