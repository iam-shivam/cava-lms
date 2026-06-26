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
        } elseif ($formType === 'edit_section_inline') {
            $sectionId = intval($_POST['section_id'] ?? 0);
            $title = trim($_POST['section_title'] ?? '');
            if ($sectionId > 0 && !empty($title)) {
                $stmt = DB::getConnection()->prepare("UPDATE course_sections SET title = ? WHERE id = ? AND course_id = ?");
                $stmt->execute([$title, $sectionId, $courseId]);
                set_flash_message('success', 'Section name updated!');
            }
        } elseif ($formType === 'add_video') {
            $sectionId = intval($_POST['section_id'] ?? 0);
            $title = trim($_POST['video_title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $order = intval($_POST['sort_order'] ?? 0);
            
            if ($sectionId <= 0 || empty($title) || empty($_FILES['video_file']['name'])) {
                set_flash_message('danger', 'Please complete all required fields and select a video.');
            } else {
                $videoUrl = '';
                $documentUrl = null;
                
                // Ensure upload directories exist
                $videoDir = BASE_PATH . '/uploads/videos/';
                $docDir = BASE_PATH . '/uploads/documents/';
                if (!is_dir($videoDir)) mkdir($videoDir, 0755, true);
                if (!is_dir($docDir)) mkdir($docDir, 0755, true);
                
                // Process Video Upload
                $videoExt = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
                $videoName = 'vid_' . time() . '_' . uniqid() . '.' . $videoExt;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $videoDir . $videoName)) {
                    $videoUrl = 'uploads/videos/' . $videoName;
                } else {
                    throw new Exception("Failed to upload video.");
                }
                
                // Process Document Upload (if any)
                if (!empty($_FILES['document_file']['name'])) {
                    $docExt = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
                    $docName = 'doc_' . time() . '_' . uniqid() . '.' . $docExt;
                    if (move_uploaded_file($_FILES['document_file']['tmp_name'], $docDir . $docName)) {
                        $documentUrl = 'uploads/documents/' . $docName;
                    }
                }
                
                $stmt = DB::getConnection()->prepare("INSERT INTO course_videos (section_id, course_id, title, description, video_url, video_source, document_url, video_access_duration, sort_order) VALUES (?, ?, ?, ?, ?, 'local', ?, 0, ?)");
                $stmt->execute([$sectionId, $courseId, $title, $description, $videoUrl, $documentUrl, $order]);
                set_flash_message('success', 'Video lesson and resources added successfully!');
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
        $vid = DB::fetch("SELECT video_url, document_url FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);
        if ($vid) {
            if ($vid['video_url'] && file_exists(BASE_PATH . '/' . $vid['video_url'])) {
                unlink(BASE_PATH . '/' . $vid['video_url']);
            }
            if (!empty($vid['document_url']) && file_exists(BASE_PATH . '/' . $vid['document_url'])) {
                unlink(BASE_PATH . '/' . $vid['document_url']);
            }
            DB::query("DELETE FROM course_videos WHERE id = ? AND course_id = ?", [$id, $courseId]);
            set_flash_message('success', 'Video lesson deleted successfully!');
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
                            <h2 class="accordion-header" id="<?php echo $headingId; ?>">
                                <div class="accordion-button bg-light fw-bold py-3 d-flex align-items-center justify-content-between" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="true">
                                    <div class="w-100 me-3 position-relative">
                                        <!-- View Mode -->
                                        <div id="view_sec_<?php echo $sec['id']; ?>" class="d-flex align-items-center justify-content-between w-100">
                                            <span>
                                                <i class="fa-solid fa-folder me-2 text-warning"></i>
                                                <span id="text_sec_<?php echo $sec['id']; ?>"><?php echo htmlspecialchars($sec['title']); ?></span>
                                                <small class="text-muted ms-2 fs-8">(Sort: <?php echo $sec['sort_order']; ?>)</small>
                                            </span>
                                            <div>
                                                <button type="button" 
                                                   class="btn btn-sm btn-outline-primary py-1 px-2 border-0" 
                                                   title="Edit Section"
                                                   onclick="toggleSectionEdit(<?php echo $sec['id']; ?>, event)">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <a href="videos.php?course_id=<?php echo $courseId; ?>&action=delete_section&id=<?php echo $sec['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger py-1 px-2 border-0" 
                                                   onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this section and all its lesson videos?');"
                                                   title="Delete Section">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </div>
                                        
                                        <!-- Edit Mode -->
                                        <div id="edit_sec_<?php echo $sec['id']; ?>" class="d-none align-items-center gap-2 w-100" onclick="event.stopPropagation();">
                                            <i class="fa-solid fa-folder me-2 text-warning"></i>
                                            <input type="text" class="form-control form-control-sm w-auto flex-grow-1" id="input_sec_<?php echo $sec['id']; ?>" value="<?php echo htmlspecialchars($sec['title']); ?>" onkeydown="if(event.key === 'Enter') saveSection(<?php echo $sec['id']; ?>, event);">
                                            <button type="button" class="btn btn-sm btn-success py-1 px-2" onclick="saveSection(<?php echo $sec['id']; ?>, event)" title="Save">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary py-1 px-2" onclick="toggleSectionEdit(<?php echo $sec['id']; ?>, event)" title="Cancel">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
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
                                                        <a href="video_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $video['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm border-0" 
                                                           title="Edit Lesson">
                                                            <i class="fa-solid fa-pen-to-square"></i>
                                                        </a>
                                                        <a href="videos.php?course_id=<?php echo $courseId; ?>&action=delete_video&id=<?php echo $video['id']; ?>" 
                                                           class="btn btn-outline-danger btn-sm border-0" 
                                                           onclick="return confirm('Delete this lesson video?');"
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
            <form action="videos.php?course_id=<?php echo $courseId; ?>" method="POST" enctype="multipart/form-data">
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
                    <label for="video_file" class="form-label fw-semibold">Upload Video File (MP4/WebM/Ogg)</label>
                    <input type="file" class="form-control" id="video_file" name="video_file" accept="video/*" required>
                </div>

                <div class="mb-3">
                    <label for="document_file" class="form-label fw-semibold">Upload Resource Document (Optional)</label>
                    <input type="file" class="form-control" id="document_file" name="document_file" accept=".pdf,.doc,.docx">
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Video Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of this video..."></textarea>
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

<!-- Hidden Form for Inline Editing -->
<form id="edit_section_form" action="videos.php?course_id=<?php echo $courseId; ?>" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="form_type" value="edit_section_inline">
    <input type="hidden" name="section_id" id="edit_section_id">
    <input type="hidden" name="section_title" id="edit_section_title">
</form>

<script>
function toggleSectionEdit(id, event) {
    if (event) event.stopPropagation(); // Prevents accordion from toggling
    
    let viewDiv = document.getElementById('view_sec_' + id);
    let editDiv = document.getElementById('edit_sec_' + id);
    let inputEl = document.getElementById('input_sec_' + id);
    
    if (viewDiv.classList.contains('d-none')) {
        // Switch to View Mode
        viewDiv.classList.remove('d-none');
        viewDiv.classList.add('d-flex');
        
        editDiv.classList.remove('d-flex');
        editDiv.classList.add('d-none');
        
        // Revert input value back to original text
        inputEl.value = document.getElementById('text_sec_' + id).innerText;
    } else {
        // Switch to Edit Mode
        viewDiv.classList.remove('d-flex');
        viewDiv.classList.add('d-none');
        
        editDiv.classList.remove('d-none');
        editDiv.classList.add('d-flex');
        
        inputEl.focus();
    }
}

function saveSection(id, event) {
    if (event) event.stopPropagation();
    let newTitle = document.getElementById('input_sec_' + id).value;
    if (newTitle.trim() !== "") {
        document.getElementById('edit_section_id').value = id;
        document.getElementById('edit_section_title').value = newTitle;
        document.getElementById('edit_section_form').submit();
    }
}
</script>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
