<?php
// Admin Edit Course Section
require_once __DIR__ . '/admin_header.php';

$courseId = trim($_GET['course_id'] ?? '');
$id = trim($_GET['id'] ?? '');

$course = DB::fetch("SELECT id FROM courses WHERE id = ?", [$courseId]);
$section = DB::fetch("SELECT id, title, sort_order FROM course_sections WHERE id = ? AND course_id = ?", [$id, $courseId]);

if (!$course || !$section) {
    set_flash_message('danger', 'Section or Course not found.');
    header("Location: courses.php");
    exit;
}

$csrfToken = generate_csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: section_edit.php?course_id=$courseId&id=$id");
        exit;
    }
    
    $title = trim($_POST['section_title'] ?? '');
    
    if (empty($title)) {
        set_flash_message('danger', 'Section title cannot be empty.');
        header("Location: section_edit.php?course_id=$courseId&id=$id");
        exit;
    } else {
        if ($title !== $section['title']) {
            $stmt = DB::getConnection()->prepare("UPDATE course_sections SET title = ? WHERE id = ?");
            if (!$stmt->execute([$title, $id])) {
                set_flash_message('danger', 'Failed to update section.');
                header("Location: section_edit.php?course_id=$courseId&id=$id");
                exit;
            }
        }
        
        set_flash_message('success', 'Section updated successfully!');
        header("Location: videos.php?course_id=$courseId");
        exit;
    }
}
?>

<div class="mb-4">
    <a href="videos.php?course_id=<?php echo $courseId; ?>" class="text-decoration-none text-muted mb-2 d-inline-block">
        <i class="fa-solid fa-arrow-left"></i> Back to Syllabus
    </a>
    <h1 class="h3 fw-bold text-dark">Edit Course Section</h1>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
            <form action="section_edit.php?course_id=<?php echo $courseId; ?>&id=<?php echo $id; ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="mb-3">
                    <label for="section_title" class="form-label fw-semibold">Section Title</label>
                    <input type="text" class="form-control" id="section_title" name="section_title" value="<?php echo htmlspecialchars($section['title']); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Update Section</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
