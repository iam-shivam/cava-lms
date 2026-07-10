<?php
// Admin Edit Video Lesson
require_once __DIR__ . '/admin_header.php';

$courseId = trim($_GET['course_id'] ?? '');
$id = trim($_GET['id'] ?? '');

$course = DB::fetch("SELECT id FROM courses WHERE id = ?", [$courseId]);
$video = DB::fetch("SELECT id, section_id, title, description, video_url, document_url, sort_order FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);

if (!$course || !$video) {
    set_flash_message('danger', 'Video or Course not found.');
    header("Location: courses.php");
    exit;
}

$sections = DB::fetchAll("SELECT * FROM course_sections WHERE course_id = ? ORDER BY sort_order ASC", [$courseId]);

$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = trim($_REQUEST['csrf_token'] ?? '');
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: video_edit.php?course_id=$courseId&id=$id");
        exit;
    }
    
    // Check if it's a delete doc action
    $action = $_POST['action'] ?? '';
    if ($action === 'delete_doc') {
        $docIdToDelete = $_POST['doc_id'] ?? '';
        if ($docIdToDelete) {
            $docToDelete = DB::fetch("SELECT file_path FROM video_documents WHERE id = ? AND video_id = ?", [$docIdToDelete, $id]);
            if ($docToDelete) {
                if (!empty($docToDelete['file_path']) && file_exists(BASE_PATH . '/' . $docToDelete['file_path'])) {
                    @unlink(BASE_PATH . '/' . $docToDelete['file_path']);
                }
                DB::query("DELETE FROM video_documents WHERE id = ?", [$docIdToDelete]);
                set_flash_message('success', 'Document deleted successfully.');
            }
        }
        header("Location: video_edit.php?course_id=$courseId&id=$id");
        exit;
    }
    
    $sectionId = trim($_POST['section_id'] ?? '');
    $title = trim($_POST['video_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $order = intval($_POST['sort_order'] ?? 0);
    
    if (empty($sectionId) || empty($title)) {
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
        
        // Process new multiple document uploads (optional)
        if (!empty($_FILES['document_files']['name'][0])) {
            $docCount = count($_FILES['document_files']['name']);
            for ($i = 0; $i < $docCount; $i++) {
                if ($_FILES['document_files']['error'][$i] === UPLOAD_ERR_OK) {
                    $docTitleOrig = pathinfo($_FILES['document_files']['name'][$i], PATHINFO_FILENAME);
                    $docExt = strtolower(pathinfo($_FILES['document_files']['name'][$i], PATHINFO_EXTENSION));
                    $docSize = $_FILES['document_files']['size'][$i];
                    $docName = 'doc_' . time() . '_' . uniqid() . '.' . $docExt;
                    $tmpName = $_FILES['document_files']['tmp_name'][$i];
                    
                    if (move_uploaded_file($tmpName, $docDir . $docName)) {
                        $docPath = 'uploads/documents/' . $docName;
                        $stmtDoc = DB::getConnection()->prepare("INSERT INTO video_documents (id, video_id, title, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmtDoc->execute([generate_uuid(), $id, $docTitleOrig, $docPath, $docExt, $docSize]);
                    }
                }
            }
        }
        
        $updates = [];
        $params = [];
        
        if ($sectionId !== $video['section_id']) { $updates[] = "section_id = ?"; $params[] = $sectionId; }
        if ($title !== $video['title']) { $updates[] = "title = ?"; $params[] = $title; }
        if ($description !== $video['description']) { $updates[] = "description = ?"; $params[] = $description; }
        if ($videoUrl !== $video['video_url']) { $updates[] = "video_url = ?"; $params[] = $videoUrl; }
        if ($documentUrl !== $video['document_url']) { $updates[] = "document_url = ?"; $params[] = $documentUrl; }
        if ($order !== intval($video['sort_order'])) { $updates[] = "sort_order = ?"; $params[] = $order; }
        
        if (!empty($updates)) {
            $params[] = $id;
            $stmt = DB::getConnection()->prepare("UPDATE course_videos SET " . implode(', ', $updates) . " WHERE id = ?");
            if (!$stmt->execute($params)) {
                set_flash_message('danger', 'Failed to update video.');
                header("Location: video_edit.php?course_id=$courseId&id=$id");
                exit;
            }
        }
        
        set_flash_message('success', 'Video lesson updated successfully!');
        header("Location: videos.php?course_id=$courseId");
        exit;
    }
}

$videoDocuments = DB::fetchAll("SELECT * FROM video_documents WHERE video_id = ? ORDER BY created_at ASC", [$id]);
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
                    <label class="form-label fw-semibold">Current Documents</label>
                    <?php if (!empty($videoDocuments)): ?>
                        <div class="list-group mb-3">
                            <?php foreach ($videoDocuments as $doc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                    <div class="text-truncate" style="max-width:300px;">
                                        <i class="fa-solid fa-file text-secondary me-2"></i>
                                        <?php echo htmlspecialchars($doc['title'] . '.' . $doc['file_type']); ?>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDocument('<?php echo $doc['id']; ?>')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted fs-8 mb-3">No documents attached.</div>
                    <?php endif; ?>
                    <label for="document_files" class="form-label fw-semibold mt-2">Add More Documents (Optional)</label>
                    <input type="file" class="form-control" id="document_files" name="document_files[]" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                    <div class="form-text">You can select multiple files at once. Supported formats: PDF, DOC, PPT, XLS, ZIP.</div>
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

<form id="deleteDocForm" action="video_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $id; ?>" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="action" value="delete_doc">
    <input type="hidden" name="doc_id" id="deleteDocId">
</form>

<script>
function deleteDocument(docId) {
    if (confirm('Are you sure you want to delete this document?')) {
        document.getElementById('deleteDocId').value = docId;
        document.getElementById('deleteDocForm').submit();
    }
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
