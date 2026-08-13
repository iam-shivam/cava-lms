<?php
// Admin Edit Video Lesson
require_once __DIR__ . '/admin_header.php';

$courseId = trim($_GET['course_id'] ?? '');
$id = trim($_GET['id'] ?? '');

$course = DB::fetch("SELECT id FROM courses WHERE id = ?", [$courseId]);
$video = DB::fetch("SELECT id, section_id, title, description, video_url, document_url, thumbnail, video_source, video_provider, bunny_video_id, sort_order FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);

if (!$course || !$video) {
    set_flash_message('danger', 'Video or Course not found.');
    header("Location: courses.php");
    exit;
}

$sections = DB::fetchAll("SELECT * FROM course_sections WHERE course_id = ? ORDER BY sort_order ASC, created_at ASC", [$courseId]);

$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = trim($_REQUEST['csrf_token'] ?? '');
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: video_edit.php?course_id=$courseId&id=$id");
        exit;
    }

    // Check if it's a delete doc action
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    if ($action === 'delete_doc') {
        $docIdToDelete = $_POST['doc_id'] ?? $_GET['doc_id'] ?? '';
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
        header("Location: video_edit.php?course_id=$courseId&id=$id");
        exit;
    } else {
        $existingTitle = DB::fetch("SELECT id FROM course_videos WHERE title = ? AND course_id = ? AND id != ?", [$title, $courseId, $id]);
        if ($existingTitle) {
            set_flash_message('danger', 'A video lesson with this title already exists in the course.');
        } else {
            $videoUrl = $video['video_url'];
        $documentUrl = $video['document_url'];

        $videoDir = BASE_PATH . '/uploads/videos/';
        $docDir = BASE_PATH . '/uploads/documents/';
        if (!is_dir($videoDir))
            mkdir($videoDir, 0755, true);
        if (!is_dir($docDir))
            mkdir($docDir, 0755, true);

        // Process new video upload (optional)
        if (!empty($_FILES['video_file']['name'])) {
            $videoExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            $videoName = 'vid_' . time() . '_' . uniqid() . '.' . $videoExt;
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoDir . $videoName)) {
                // Delete old video if local
                if ((($video['video_provider'] ?? '') === 'local' || ($video['video_source'] ?? '') === 'local') && !empty($video['video_url']) && file_exists(BASE_PATH . '/' . $video['video_url'])) {
                    @unlink(BASE_PATH . '/' . $video['video_url']);
                }
                $videoUrl = 'uploads/videos/' . $videoName;
            }
        }

        // Process new lesson thumbnail upload (optional)
        $thumbDir = BASE_PATH . '/uploads/thumbnails/';
        if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
        $newLessonThumbnail = null;
        $thumbnailUpdated = false;
        if (isset($_FILES['lesson_thumbnail']) && $_FILES['lesson_thumbnail']['error'] === UPLOAD_ERR_OK) {
            $thumbTmpPath = $_FILES['lesson_thumbnail']['tmp_name'];
            $thumbExt = strtolower(pathinfo($_FILES['lesson_thumbnail']['name'], PATHINFO_EXTENSION));
            $allowedThumbExts = ['jpg', 'jpeg', 'png', 'gif'];
            $allowedThumbMimes = ['image/jpeg', 'image/png', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMime = finfo_file($finfo, $thumbTmpPath);
            finfo_close($finfo);
            if (in_array($thumbExt, $allowedThumbExts) && in_array($detectedMime, $allowedThumbMimes)) {
                $newThumbName = 'thumb_' . time() . '_' . uniqid() . '.' . $thumbExt;
                if (move_uploaded_file($thumbTmpPath, $thumbDir . $newThumbName)) {
                    // Delete old thumbnail if it exists
                    if (!empty($video['thumbnail']) && file_exists(BASE_PATH . '/' . $video['thumbnail'])) {
                        @unlink(BASE_PATH . '/' . $video['thumbnail']);
                    }
                    $newLessonThumbnail = 'uploads/thumbnails/' . $newThumbName;
                    $thumbnailUpdated = true;
                }
            } else {
                set_flash_message('danger', 'Thumbnail upload failed. Allowed formats: JPG, PNG, GIF.');
                header("Location: video_edit.php?course_id=$courseId&id=$id");
                exit;
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

        if ($sectionId !== $video['section_id']) {
            $updates[] = "section_id = ?";
            $params[] = $sectionId;
        }
        if ($title !== $video['title']) {
            $updates[] = "title = ?";
            $params[] = $title;
        }
        if ($description !== $video['description']) {
            $updates[] = "description = ?";
            $params[] = $description;
        }
        if ($videoUrl !== $video['video_url']) {
            $updates[] = "video_url = ?";
            $params[] = $videoUrl;
            $updates[] = "video_provider = ?";
            $params[] = 'local';
            $updates[] = "bunny_video_id = ?";
            $params[] = null;
        }
        if ($documentUrl !== $video['document_url']) {
            $updates[] = "document_url = ?";
            $params[] = $documentUrl;
        }
        if ($thumbnailUpdated) {
            $updates[] = "thumbnail = ?";
            $params[] = $newLessonThumbnail;
        }
        if ($order !== intval($video['sort_order'])) {
            $updates[] = "sort_order = ?";
            $params[] = $order;
        }

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
        }
        
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

<div class="row justify-content-center">
    <div class="col-12 col-md-10 col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <form id="edit_video_form" action="video_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $id; ?>"
                method="POST" enctype="multipart/form-data">
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
                    <input type="text" class="form-control" id="video_title" name="video_title"
                        value="<?php echo htmlspecialchars($video['title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Lesson Thumbnail</label>
                    <?php if (!empty($video['thumbnail']) && file_exists(BASE_PATH . '/' . $video['thumbnail'])): ?>
                        <div class="mb-2">
                            <span class="d-block fs-8 text-muted mb-1">Current Thumbnail:</span>
                            <img src="<?php echo SITE_URL . '/' . htmlspecialchars($video['thumbnail']); ?>" alt="Current thumbnail" class="img-fluid rounded-3 border" style="max-width:200px;max-height:120px;object-fit:cover;" onerror="this.src='https://placehold.co/200x120/6f42c1/ffffff?text=No+Thumbnail'">
                        </div>
                    <?php endif; ?>
                    <label for="lesson_thumbnail" class="form-label fw-semibold mt-1"><?php echo !empty($video['thumbnail']) ? 'Replace Thumbnail (Optional)' : 'Upload Thumbnail (Optional)'; ?></label>
                    <input type="file" class="form-control" id="lesson_thumbnail" name="lesson_thumbnail" accept="image/*">
                    <div class="form-text">Recommended: 16:9 ratio. Max 2MB. Formats: JPG, PNG, GIF.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Current Video</label>
                    <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light border rounded">
                        <i class="fa-solid fa-video text-muted"></i>
                        <?php if (($video['video_provider'] ?? '') === 'bunny'): ?>
                            <span class="fs-8 text-dark text-truncate" style="max-width: 300px;"><strong>[Bunny
                                    Stream]</strong> <?php echo htmlspecialchars($video['bunny_video_id'] ?? ''); ?></span>
                        <?php else: ?>
                            <span class="fs-8 text-dark text-truncate" style="max-width: 300px;"><strong>[Local
                                    File]</strong> <?php echo htmlspecialchars($video['video_url'] ?? ''); ?></span>
                        <?php endif; ?>
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
                                    <a href="video_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $id; ?>&action=delete_doc&doc_id=<?php echo urlencode($doc['id']); ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="confirmAction(event, 'Are you sure you want to delete this document?', this.href)">
                                        <i class="fa-solid fa-trash"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted fs-8 mb-3">No documents attached.</div>
                    <?php endif; ?>
                    <label for="document_files" class="form-label fw-semibold mt-2">Add More Documents
                        (Optional)</label>
                    <input type="file" class="form-control" id="document_files" name="document_files[]" multiple
                        accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                    <div class="form-text">You can select multiple files at once. Supported formats: PDF, DOC, PPT, XLS,
                        ZIP.</div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Video Description</label>
                    <textarea class="form-control" id="description" name="description"
                        rows="5"><?php echo htmlspecialchars($video['description'] ?? ''); ?></textarea>
                </div>

                <div class="d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Update Video Lesson</button>
                    <button type="button" class="btn btn-secondary w-100 rounded-pill py-2" id="upload_bunny_btn"><i
                            class="fa-solid fa-cloud-arrow-up me-1"></i>Replace with Bunny Stream</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bunny Upload Progress Modal -->
<div class="modal fade" id="bunnyUploadModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="bunnyUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="bunnyUploadModalLabel">Bunny Stream Integration</h5>
            </div>
            <div class="modal-body text-center py-4">
                <div id="bunny-spinner" class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"
                    role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="fw-bold mb-2 text-dark" id="bunny-status-title">Preparing upload...</h6>
                <p class="text-muted fs-7 mb-3" id="bunny-status-desc">Please do not close this window or navigate away.
                </p>
                <div class="progress rounded-pill mb-2" style="height: 10px;">
                    <div id="bunny-progress-bar"
                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                        style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-primary fw-semibold" id="bunny-percentage">0%</small>
                <div id="bunny-error-block" class="alert alert-danger py-2 fs-8 mt-3 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill d-none" id="bunny-close-btn"
                    data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bunnyBtn = document.getElementById('upload_bunny_btn');
        if (bunnyBtn) {
            bunnyBtn.addEventListener('click', function (e) {
                e.preventDefault();

                const form = document.getElementById('edit_video_form');
                const sectionSelect = form.querySelector('#section_id');
                const titleInput = form.querySelector('#video_title');
                const videoFileInput = form.querySelector('#video_file');

                if (!sectionSelect.value) {
                    alert('Please select a section.');
                    sectionSelect.focus();
                    return;
                }
                if (!titleInput.value.trim()) {
                    alert('Please enter a lesson title.');
                    titleInput.focus();
                    return;
                }
                if (videoFileInput.files.length === 0) {
                    alert('Please select a video file to replace on Bunny Stream.');
                    videoFileInput.focus();
                    return;
                }

                const modalEl = document.getElementById('bunnyUploadModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                const statusTitle = document.getElementById('bunny-status-title');
                const statusDesc = document.getElementById('bunny-status-desc');
                const progressBar = document.getElementById('bunny-progress-bar');
                const percentageEl = document.getElementById('bunny-percentage');
                const spinner = document.getElementById('bunny-spinner');
                const closeBtn = document.getElementById('bunny-close-btn');
                const errorBlock = document.getElementById('bunny-error-block');

                statusTitle.innerText = "Uploading to server...";
                statusDesc.innerText = "Your video file is being transferred to the server.";
                progressBar.style.width = "0%";
                progressBar.className = "progress-bar progress-bar-striped progress-bar-animated bg-primary";
                percentageEl.innerText = "0%";
                errorBlock.classList.add('d-none');
                closeBtn.classList.add('d-none');
                spinner.classList.remove('d-none');

                const formData = new FormData(form);
                formData.append('course_id', '<?php echo $courseId; ?>');
                formData.append('video_id', '<?php echo $id; ?>');
                formData.append('is_edit', '1');

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '../api/bunny_upload.php', true);

                xhr.upload.onprogress = function (evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        progressBar.style.width = percentComplete + '%';
                        percentageEl.innerText = percentComplete + '%';

                        if (percentComplete === 100) {
                            statusTitle.innerText = "Uploading to Bunny Stream...";
                            statusDesc.innerText = "The server is securely transferring the video to Bunny Stream.";
                            progressBar.className = "progress-bar progress-bar-striped progress-bar-animated bg-success";
                        }
                    }
                };

                xhr.onload = function () {
                    let data;
                    try {
                        data = JSON.parse(xhr.responseText);
                    } catch (ex) {
                        data = { success: false, message: "Invalid server response: " + xhr.responseText };
                    }

                    if (xhr.status === 200 && data.success) {
                        statusTitle.innerText = "Upload Complete!";
                        statusDesc.innerText = "Your video lesson was successfully replaced. Redirecting...";
                        progressBar.style.width = "100%";
                        percentageEl.innerText = "100%";
                        spinner.classList.add('d-none');

                        setTimeout(function () {
                            window.location.href = "videos.php?course_id=<?php echo $courseId; ?>";
                        }, 1500);
                    } else {
                        statusTitle.innerText = "Upload Failed";
                        statusDesc.innerText = "An error occurred during upload.";
                        spinner.classList.add('d-none');
                        progressBar.className = "progress-bar bg-danger";
                        errorBlock.innerText = data.message || "An unexpected error occurred.";
                        errorBlock.classList.remove('d-none');
                        closeBtn.classList.remove('d-none');
                    }
                };

                xhr.onerror = function () {
                    statusTitle.innerText = "Upload Failed";
                    statusDesc.innerText = "Network communication error.";
                    spinner.classList.add('d-none');
                    progressBar.className = "progress-bar bg-danger";
                    errorBlock.innerText = "Check your connection or file size limitations.";
                    errorBlock.classList.remove('d-none');
                    closeBtn.classList.remove('d-none');
                };

                xhr.send(formData);
            });
        }
    });
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>