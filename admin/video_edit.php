<?php
// Admin Edit Video Lesson
require_once __DIR__ . '/admin_header.php';

$courseId = intval($_GET['course_id'] ?? 0);
$id = intval($_GET['id'] ?? 0);

$course = DB::fetch("SELECT * FROM courses WHERE id = ?", [$courseId]);
$video = DB::fetch("SELECT * FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);

if (!$course || !$video) {
    set_flash_message('danger', 'Video or Course not found.');
    header("Location: courses.php");
    exit;
}

$sections = DB::fetchAll("SELECT * FROM course_sections WHERE course_id = ? ORDER BY sort_order ASC", [$courseId]);

$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: video_edit.php?course_id=$courseId&id=$id");
        exit;
    }
    
    $sectionId = intval($_POST['section_id'] ?? 0);
    $title = trim($_POST['video_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $order = intval($_POST['sort_order'] ?? 0);
    
    if ($sectionId <= 0 || empty($title)) {
        set_flash_message('danger', 'Please complete all required fields.');
    } else {
        $videoUrl = $video['video_url'];
        $documentUrl = $video['document_url'];
        
        $videoDir = BASE_PATH . '/uploads/videos/';
        $docDir = BASE_PATH . '/uploads/documents/';
        if (!is_dir($videoDir)) mkdir($videoDir, 0755, true);
        if (!is_dir($docDir)) mkdir($docDir, 0755, true);
        
        // Process new video upload (optional)
        if (!empty($_FILES['video_file']['name'])) {
            $videoExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            $videoName = 'vid_' . time() . '_' . uniqid() . '.' . $videoExt;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoDir . $videoName)) {
                // Delete old video if local
                if ($video['video_source'] === 'local' && !empty($videoUrl) && file_exists(BASE_PATH . '/' . $videoUrl)) {
                    @unlink(BASE_PATH . '/' . $videoUrl);
                }
                $videoUrl = 'uploads/videos/' . $videoName;
            }
        }
        
        // Process new document upload (optional)
        if (!empty($_FILES['document_file']['name'])) {
            $docExt = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
            $docName = 'doc_' . time() . '_' . uniqid() . '.' . $docExt;
            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $docDir . $docName)) {
                // Delete old document
                if (!empty($documentUrl) && file_exists(BASE_PATH . '/' . $documentUrl)) {
                    @unlink(BASE_PATH . '/' . $documentUrl);
                }
                $documentUrl = 'uploads/documents/' . $docName;
            }
        }
        
        $stmt = DB::getConnection()->prepare("UPDATE course_videos SET section_id = ?, title = ?, description = ?, video_url = ?, document_url = ?, sort_order = ? WHERE id = ?");
        if ($stmt->execute([$sectionId, $title, $description, $videoUrl, $documentUrl, $order, $id])) {
            set_flash_message('success', 'Video lesson updated successfully!');
            header("Location: videos.php?course_id=$courseId");
            exit;
        } else {
            set_flash_message('danger', 'Failed to update video.');
        }
    }
}
?>

<div class="mb-4">
    <a href="videos.php?course_id=<?php echo $courseId; ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
        <i class="fa-solid fa-arrow-left"></i> Back to Syllabus
    </a>
    <h1 class="h3 fw-bold text-dark">Edit Video Lesson</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <form action="video_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="mb-3">
                    <label for="section_id" class="form-label fw-semibold">Select Section</label>
                    <select class="form-select" id="section_id" name="section_id" required>
                        <option value="">Select Section</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?php echo $sec['id']; ?>" <?php echo ($sec['id'] == $video['section_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sec['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="video_title" class="form-label fw-semibold">Lesson Title</label>
                    <input type="text" class="form-control" id="video_title" name="video_title" value="<?php echo htmlspecialchars($video['title']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Video</label>
                    <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light border rounded">
                        <i class="fa-solid fa-video text-muted"></i>
                        <span class="fs-8 text-dark text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($video['video_url']); ?></span>
                    </div>
                    <label for="video_file" class="form-label fw-semibold mt-2">Replace Video File (Optional)</label>
                    <input type="file" class="form-control" id="video_file" name="video_file" accept="video/*">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Document</label>
                    <?php if (!empty($video['document_url'])): ?>
                        <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light border rounded">
                            <i class="fa-solid fa-file-pdf text-danger"></i>
                            <span class="fs-8 text-dark text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($video['document_url']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-muted fs-8 mb-2">No document attached.</div>
                    <?php endif; ?>
                    <label for="document_file" class="form-label fw-semibold mt-2">Replace Document (Optional)</label>
                    <input type="file" class="form-control" id="document_file" name="document_file" accept=".pdf,.doc,.docx">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Video Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5"><?php echo htmlspecialchars($video['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="sort_order_vid" class="form-label fw-semibold">Sort Order</label>
                    <input type="number" class="form-control" id="sort_order_vid" name="sort_order" value="<?php echo htmlspecialchars($video['sort_order']); ?>">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Update Video Lesson</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
