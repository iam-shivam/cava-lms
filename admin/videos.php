<?php
// Admin Course Sections & Videos Manager
require_once __DIR__ . '/admin_header.php';

$courseId = trim($_GET['course_id'] ?? '');
$course = DB::fetch("SELECT id, title FROM courses WHERE id = ?", [$courseId]);

if (!$course) {
    set_flash_message('danger', 'Course not found.');
    header("Location: courses.php");
    exit;
}

$csrfToken = generate_csrf_token();
$action = trim($_GET['action'] ?? 'list');
$id = trim($_GET['id'] ?? '');

// Form Actions Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: videos.php?course_id=$courseId");
        exit;
    }
    
    // Handle delete actions
    if ($action === 'delete_section' && !empty($id)) {
        try {
            DB::query("DELETE FROM course_sections WHERE id = ? AND course_id = ?", [$id, $courseId]);
            set_flash_message('success', 'Section and all its videos deleted successfully!');
        } catch (Exception $e) {
            set_flash_message('danger', 'Database Error: ' . $e->getMessage());
        }
        header("Location: videos.php?course_id=$courseId");
        exit;
    }
    
    if ($action === 'delete_video' && !empty($id)) {
        try {
            $vid = DB::fetch("SELECT video_url, document_url FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);
            if ($vid) {
                if ($vid['video_url'] && file_exists(BASE_PATH . '/' . $vid['video_url'])) {
                    unlink(BASE_PATH . '/' . $vid['video_url']);
                }
                
                // Delete multiple documents
                $docs = DB::fetchAll("SELECT file_path FROM video_documents WHERE video_id = ?", [$id]);
                foreach ($docs as $d) {
                    if (!empty($d['file_path']) && file_exists(BASE_PATH . '/' . $d['file_path'])) {
                        unlink(BASE_PATH . '/' . $d['file_path']);
                    }
                }
                
                DB::query("DELETE FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);
                set_flash_message('success', 'Video lesson and associated resources deleted successfully!');
            }
        } catch (Exception $e) {
            set_flash_message('danger', 'Database Error: ' . $e->getMessage());
        }
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
                $existing = DB::fetch("SELECT id FROM course_sections WHERE title = ? AND course_id = ?", [$title, $courseId]);
                if ($existing) {
                    set_flash_message('danger', 'A section with this title already exists in the course.');
                } else {
                    $stmt = DB::getConnection()->prepare("INSERT INTO course_sections (id, course_id, title, sort_order) VALUES (?, ?, ?, ?)");
                    $stmt->execute([generate_uuid(), $courseId, $title, $order]);
                    set_flash_message('success', 'Section created successfully!');
                }
            }
        } elseif ($formType === 'edit_section_inline') {
            $sectionId = trim($_POST['section_id'] ?? '');
            $title = trim($_POST['section_title'] ?? '');
            if (!empty($sectionId) && !empty($title)) {
                $oldSection = DB::fetch("SELECT title FROM course_sections WHERE id = ? AND course_id = ?", [$sectionId, $courseId]);
                if ($oldSection && $oldSection['title'] !== $title) {
                    $existing = DB::fetch("SELECT id FROM course_sections WHERE title = ? AND course_id = ?", [$title, $courseId]);
                    if ($existing) {
                        set_flash_message('danger', 'A section with this title already exists in the course.');
                    } else {
                        $stmt = DB::getConnection()->prepare("UPDATE course_sections SET title = ? WHERE id = ? AND course_id = ?");
                        $stmt->execute([$title, $sectionId, $courseId]);
                        set_flash_message('success', 'Section name updated!');
                    }
                } else {
                    set_flash_message('success', 'No changes made to section name.');
                }
            }
        } elseif ($formType === 'add_video') {
            $sectionId = trim($_POST['section_id'] ?? '');
            $title = trim($_POST['video_title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $order = intval($_POST['sort_order'] ?? 0);
            
            if (empty($sectionId) || empty($title) || empty($_FILES['video_file']['name'])) {
                set_flash_message('danger', 'Please complete all required fields and select a video.');
            } else {
                $existing = DB::fetch("SELECT id FROM course_videos WHERE title = ? AND course_id = ?", [$title, $courseId]);
                if ($existing) {
                    set_flash_message('danger', 'A video lesson with this title already exists in the course.');
                } else {
                    $videoUrl = '';
                    $documentUrl = null;
                    $lessonThumbnailName = null;
                
                // Ensure upload directories exist
                $videoDir = BASE_PATH . '/uploads/videos/';
                $docDir = BASE_PATH . '/uploads/documents/';
                $thumbDir = BASE_PATH . '/uploads/thumbnails/';
                if (!is_dir($videoDir)) mkdir($videoDir, 0755, true);
                if (!is_dir($docDir)) mkdir($docDir, 0755, true);
                if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

                // Process Lesson Thumbnail Upload (optional)
                if (isset($_FILES['lesson_thumbnail']) && $_FILES['lesson_thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $thumbTmpPath = $_FILES['lesson_thumbnail']['tmp_name'];
                    $thumbFileName = $_FILES['lesson_thumbnail']['name'];
                    $thumbExt = strtolower(pathinfo($thumbFileName, PATHINFO_EXTENSION));
                    $allowedThumbExts = ['jpg', 'jpeg', 'png', 'gif'];
                    $allowedThumbMimes = ['image/jpeg', 'image/png', 'image/gif'];
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $detectedMime = finfo_file($finfo, $thumbTmpPath);
                    finfo_close($finfo);
                    if (in_array($thumbExt, $allowedThumbExts) && in_array($detectedMime, $allowedThumbMimes)) {
                        $newThumbName = 'thumb_' . time() . '_' . uniqid() . '.' . $thumbExt;
                        if (move_uploaded_file($thumbTmpPath, $thumbDir . $newThumbName)) {
                            $lessonThumbnailName = 'uploads/thumbnails/' . $newThumbName;
                        }
                    } else {
                        set_flash_message('danger', 'Thumbnail upload failed. Allowed formats: JPG, PNG, GIF.');
                        header("Location: videos.php?course_id=$courseId");
                        exit;
                    }
                }
                
                // Process Video Upload
                $videoExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                $videoName = 'vid_' . time() . '_' . uniqid() . '.' . $videoExt;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoDir . $videoName)) {
                    $videoUrl = 'uploads/videos/' . $videoName;
                } else {
                    throw new Exception("Failed to upload video.");
                }
                
                // We insert the video first to get the video UUID
                $videoId = generate_uuid();
                
                $stmt = DB::getConnection()->prepare("INSERT INTO course_videos (id, section_id, course_id, title, thumbnail, description, video_url, video_source, document_url, video_access_duration, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, 'local', NULL, 0, ?)");
                $stmt->execute([$videoId, $sectionId, $courseId, $title, $lessonThumbnailName, $description, $videoUrl, $order]);
                
                // Process Multiple Document Uploads
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
                                $stmtDoc->execute([generate_uuid(), $videoId, $docTitleOrig, $docPath, $docExt, $docSize]);
                            }
                        }
                    }
                }
                
                set_flash_message('success', 'Video lesson and resources added successfully!');
                }
            }
        }
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
                            <h2 class="accordion-header position-relative" id="<?php echo $headingId; ?>">
                                <!-- View Mode -->
                                <div id="view_sec_<?php echo $sec['id']; ?>" class="d-flex w-100 position-relative">
                                    <button class="accordion-button collapsed bg-light fw-bold py-3 w-100" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false">
                                        <i class="fa-solid fa-folder me-2 text-warning"></i>
                                        <span id="text_sec_<?php echo $sec['id']; ?>"><?php echo htmlspecialchars($sec['title']); ?></span>
                                        <small class="text-muted ms-2 fs-8 pe-5 me-5">(Sort: <?php echo $sec['sort_order']; ?>)</small>
                                    </button>
                                    <!-- View Actions Overlay (Outside the button) -->
                                    <div class="position-absolute top-50 translate-middle-y" style="right: 50px; z-index: 10;">
                                        <button type="button" 
                                           class="btn btn-outline-primary btn-sm border-0" 
                                           title="Edit Section"
                                           onclick="toggleSectionEdit('<?php echo $sec['id']; ?>', event);">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <a href="videos.php?course_id=<?php echo $courseId; ?>&action=delete_section&id=<?php echo $sec['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm border-0" 
                                           onclick="confirmAction(event, 'Are you sure you want to delete this section and all its lesson videos?', this.href);"
                                           title="Delete Section">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- Edit Mode -->
                                <div id="edit_sec_<?php echo $sec['id']; ?>" class="d-none bg-light fw-bold py-3 px-4 align-items-center gap-2 w-100">
                                    <i class="fa-solid fa-folder me-2 text-warning"></i>
                                    <input type="text" class="form-control form-control-sm w-auto flex-grow-1" id="input_sec_<?php echo $sec['id']; ?>" value="<?php echo htmlspecialchars($sec['title']); ?>" onkeydown="if(event.key === 'Enter') { event.preventDefault(); saveSection('<?php echo $sec['id']; ?>', event); }">
                                    <button type="button" class="btn btn-sm btn-success py-1 px-2" onclick="saveSection('<?php echo $sec['id']; ?>', event);" title="Save">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary py-1 px-2" onclick="toggleSectionEdit('<?php echo $sec['id']; ?>', event);" title="Cancel">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </h2>
                            <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse">
                                <div class="accordion-body bg-white p-0">
                                    <div class="list-group list-group-flush">
                                        <?php if (empty($videos)): ?>
                                            <div class="p-3 text-center text-muted fs-8">No video lessons in this section.</div>
                                        <?php else: ?>
                                            <?php foreach ($videos as $video): ?>
                                                <div class="list-group-item d-flex align-items-center justify-content-between py-3 px-4 border-0 border-bottom">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <?php if (!empty($video['thumbnail']) && file_exists(BASE_PATH . '/' . $video['thumbnail'])): ?>
                                                            <img src="<?php echo SITE_URL . '/' . htmlspecialchars($video['thumbnail']); ?>" alt="Thumbnail" class="rounded" style="width:48px;height:32px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-block';">
                                                            <i class="fa-regular fa-circle-play text-primary" style="display:none;"></i>
                                                        <?php else: ?>
                                                            <i class="fa-regular fa-circle-play text-primary"></i>
                                                        <?php endif; ?>
                                                        <div>
                                                            <span class="fw-medium text-dark d-block"><?php echo htmlspecialchars($video['title']); ?></span>
                                                            <span class="text-muted fs-8 d-block text-truncate" style="max-width: 300px;">
                                                                <?php if (($video['video_provider'] ?? 'local') === 'bunny'): ?>
                                                                    <strong>[Bunny Stream]</strong> <?php echo htmlspecialchars($video['bunny_video_id'] ?? ''); ?>
                                                                <?php else: ?>
                                                                    <strong>[Local File]</strong> <?php echo htmlspecialchars($video['video_url'] ?? ''); ?>
                                                                <?php endif; ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <a href="video_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $video['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm border-0" 
                                                           title="Edit Lesson">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <a href="videos.php?course_id=<?php echo $courseId; ?>&action=delete_video&id=<?php echo $video['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm border-0" 
                                                           onclick="confirmAction(event, 'Delete this lesson video?', this.href);"
                                                           title="Delete Lesson">
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
            <form id="add_video_form" action="videos.php?course_id=<?php echo $courseId; ?>" method="POST" enctype="multipart/form-data">
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
                    <label for="lesson_thumbnail" class="form-label fw-semibold">Lesson Thumbnail <span class="text-muted fw-normal">(Optional)</span></label>
                    <input type="file" class="form-control" id="lesson_thumbnail" name="lesson_thumbnail" accept="image/*">
                    <div class="form-text">Recommended: 16:9 ratio. Max 2MB. Formats: JPG, PNG, GIF.</div>
                </div>

                <div class="mb-3">
                    <label for="video_file" class="form-label fw-semibold">Upload Video File (MP4/WebM/Ogg)</label>
                    <input type="file" class="form-control" id="video_file" name="video_file" accept="video/*" required>
                </div>

                <div class="mb-3">
                    <label for="document_files" class="form-label fw-semibold">Upload Resource Documents (Optional)</label>
                    <input type="file" class="form-control" id="document_files" name="document_files[]" multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip">
                    <div class="form-text">You can select multiple files at once. Supported formats: PDF, DOC, PPT, XLS, ZIP.</div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Video Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of this video..."></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="sort_order_vid" class="form-label fw-semibold">Sort Order</label>
                    <input type="number" class="form-control" id="sort_order_vid" name="sort_order" value="1">
                </div>
                
                <div class="d-flex flex-column gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Add Video Lesson</button>
                    <button type="button" class="btn btn-secondary w-100 rounded-pill py-2" id="upload_bunny_btn"><i class="fa-solid fa-cloud-arrow-up me-1"></i>Upload to Bunny Stream</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden Form for Inline Editing -->
<form id="edit_section_form" action="videos.php?course_id=<?php echo $courseId; ?>" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="form_type" value="edit_section_inline">
    <input type="hidden" name="section_id" id="edit_section_id">
    <input type="hidden" name="section_title" id="edit_section_title">
</form>



<!-- Bunny Upload Progress Modal -->
<div class="modal fade" id="bunnyUploadModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="bunnyUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg bg-white">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="bunnyUploadModalLabel">Bunny Stream Integration</h5>
            </div>
            <div class="modal-body text-center py-4">
                <div id="bunny-spinner" class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="fw-bold mb-2 text-dark" id="bunny-status-title">Preparing upload...</h6>
                <p class="text-muted fs-7 mb-3" id="bunny-status-desc">Please do not close this window or navigate away.</p>
                <div class="progress rounded-pill mb-2" style="height: 10px;">
                    <div id="bunny-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-primary fw-semibold" id="bunny-percentage">0%</small>
                <div id="bunny-error-block" class="alert alert-danger py-2 fs-8 mt-3 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-center">
                <button type="button" class="btn btn-secondary btn-sm px-4 rounded-pill d-none" id="bunny-close-btn" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bunnyBtn = document.getElementById('upload_bunny_btn');
    if (bunnyBtn) {
        bunnyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = document.getElementById('add_video_form');
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
                alert('Please select a video file to upload to Bunny Stream.');
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
            formData.append('is_edit', '0');
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '../api/bunny_upload.php', true);
            
            xhr.upload.onprogress = function(evt) {
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
            
            xhr.onload = function() {
                let data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch(ex) {
                    data = { success: false, message: "Invalid server response: " + xhr.responseText };
                }
                
                if (xhr.status === 200 && data.success) {
                    statusTitle.innerText = "Upload Complete!";
                    statusDesc.innerText = "Your video lesson was successfully uploaded. Redirecting...";
                    progressBar.style.width = "100%";
                    percentageEl.innerText = "100%";
                    spinner.classList.add('d-none');
                    
                    setTimeout(function() {
                        window.location.reload();
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
            
            xhr.onerror = function() {
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
