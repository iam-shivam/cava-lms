<?php
// Admin Courses CRUD
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? 'list');
$id = trim($_GET['id'] ?? '');

// Form Submission (Add or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
  $csrf = $_POST['csrf_token'] ?? '';
  if (!verify_csrf_token($csrf)) {
    set_flash_message('danger', 'CSRF verification failed.');
    header("Location: courses.php");
    exit;
  }
  
  $categoryId = trim($_POST['category_id'] ?? '');
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $price = floatval($_POST['price'] ?? 0.00);
  $courseDuration = intval($_POST['course_duration'] ?? 0);
  $allowPartialPayment = isset($_POST['allow_partial_payment']) ? 1 : 0;
  $minInstallment = floatval($_POST['min_installment'] ?? 0.00);
  $status = $_POST['status'] ?? 'Published';
  
  // Simple validation
  if (empty($categoryId) || empty($title) || empty($description)) {
    set_flash_message('danger', 'All fields are required.');
    header("Location: courses.php?action=" . $action . (!empty($id) ? "&id=$id" : ""));
    exit;
  }
  
  $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
  
  // File Upload Handling
  $thumbnailName = null;
  if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['thumbnail']['tmp_name'];
    $fileName = $_FILES['thumbnail']['name'];
    $fileSize = $_FILES['thumbnail']['size'];
    $fileType = $_FILES['thumbnail']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    // Validate MIME type and extension
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detectedMime = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);
    
    if (in_array($fileExtension, $allowedExtensions) && in_array($detectedMime, $allowedMimeTypes)) {
      // Generate unique name
      $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
      $uploadFileDir = BASE_PATH . '/uploads/';
      
      // Create uploads directory if not exists
      if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
      }
      
      $destPath = $uploadFileDir . $newFileName;
      if (move_uploaded_file($fileTmpPath, $destPath)) {
        $thumbnailName = $newFileName;
      } else {
        set_flash_message('danger', 'There was an error moving the uploaded thumbnail file.');
      }
    } else {
      set_flash_message('danger', 'Upload failed. Allowed formats: JPG, PNG, GIF.');
      header("Location: courses.php?action=" . $action . (!empty($id) ? "&id=$id" : ""));
      exit;
    }
  }
  
  try {
    if ($action === 'add') {
      // Check if slug exists
      $exists = DB::fetch("SELECT id FROM courses WHERE slug = ?", [$slug]);
      if ($exists) {
        $slug .= '-' . time();
      }
      
      $sql = "INSERT INTO courses (id, category_id, title, slug, thumbnail, description, price, course_duration, allow_partial_payment, min_installment, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
      $stmt = DB::getConnection()->prepare($sql);
      $stmt->execute([generate_uuid(), $categoryId, $title, $slug, $thumbnailName, $description, $price, $courseDuration, $allowPartialPayment, $minInstallment, $status]);
      set_flash_message('success', 'Course created successfully!');
    } elseif ($action === 'edit' && !empty($id)) {
      // Fetch only required fields for comparison
      $oldCourse = DB::fetch("SELECT title, category_id, description, price, course_duration, allow_partial_payment, min_installment, status, thumbnail FROM courses WHERE id = ?", [$id]);

      $updates = [];
      $params = [];
      
      // Compare and build update query only for modified fields
      if ($title !== $oldCourse['title']) {
          $updates[] = "title = ?";
          $params[] = $title;
          
          // Only generate a new slug if the title actually changed
          $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title));
          $exists = DB::fetch("SELECT id FROM courses WHERE slug = ? AND id != ?", [$slug, $id]);
          if ($exists) {
              $slug .= '-' . time();
          }
          $updates[] = "slug = ?";
          $params[] = $slug;
      }
      if ($categoryId !== $oldCourse['category_id']) { $updates[] = "category_id = ?"; $params[] = $categoryId; }
      if ($description !== $oldCourse['description']) { $updates[] = "description = ?"; $params[] = $description; }
      if (floatval($price) !== floatval($oldCourse['price'])) { $updates[] = "price = ?"; $params[] = $price; }
      if (intval($courseDuration) !== intval($oldCourse['course_duration'])) { $updates[] = "course_duration = ?"; $params[] = $courseDuration; }
      if (intval($allowPartialPayment) !== intval($oldCourse['allow_partial_payment'])) { $updates[] = "allow_partial_payment = ?"; $params[] = $allowPartialPayment; }
      if (floatval($minInstallment) !== floatval($oldCourse['min_installment'])) { $updates[] = "min_installment = ?"; $params[] = $minInstallment; }
      if ($status !== $oldCourse['status']) { $updates[] = "status = ?"; $params[] = $status; }

      // If new thumbnail uploaded, remove old one if exists
      if ($thumbnailName) {
        if ($oldCourse['thumbnail'] && file_exists(BASE_PATH . '/uploads/' . $oldCourse['thumbnail'])) {
          unlink(BASE_PATH . '/uploads/' . $oldCourse['thumbnail']);
        }
        $updates[] = "thumbnail = ?";
        $params[] = $thumbnailName;
      }
      
      if (!empty($updates)) {
        $params[] = $id;
        $sql = "UPDATE courses SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->execute($params);
      }
      set_flash_message('success', 'Course updated successfully!');
    }
  } catch (Exception $e) {
    set_flash_message('danger', 'Database error: ' . $e->getMessage());
  }
  
  header("Location: courses.php");
  exit;
}

// Handle Delete Course
if ($action === 'delete' && !empty($id)) {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash_message('danger', 'Invalid or unauthorized request.');
    header("Location: courses.php");
    exit;
  }
  try {
    // Fetch course details
    $course = DB::fetch("SELECT thumbnail FROM courses WHERE id = ?", [$id]);
    if ($course) {
      // Delete associated sections & videos (cascaded in DB, but remove thumbnail if any)
      if ($course['thumbnail'] && file_exists(BASE_PATH . '/uploads/' . $course['thumbnail'])) {
        unlink(BASE_PATH . '/uploads/' . $course['thumbnail']);
      }
      DB::query("DELETE FROM courses WHERE id = ?", [$id]);
      set_flash_message('success', 'Course and all its assets deleted successfully!');
    }
  } catch (Exception $e) {
    set_flash_message('danger', 'Database error: ' . $e->getMessage());
  }
  header("Location: courses.php");
  exit;
}

// Fetch Courses list
$courses = DB::fetchAll("
  SELECT c.*, cat.name as category_name,
      (SELECT COUNT(id) FROM course_videos WHERE course_id = c.id) as video_count 
  FROM courses c 
  JOIN categories cat ON c.category_id = cat.id 
  ORDER BY c.created_at DESC
");

$categories = DB::fetchAll("SELECT * FROM categories ORDER BY name ASC");
$csrfToken = generate_csrf_token();
?>

<!-- List View -->
<?php if ($action === 'list'): ?>
  <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h5 class="fw-bold text-dark m-0">Courses Management</h5>
      <a href="courses.php?action=add" class="btn btn-primary rounded-pill px-4">
        <i class="fa-solid fa-plus me-1"></i> Add Course
      </a>
    </div>
    
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr>
            <th class="text-center" style="width: 80px;">Thumbnail</th>
            <th class="text-center">Course Title</th>
            <th class="text-center">Category</th>
            <th class="text-center">Price</th>
            <th class="text-center">Lessons</th>
            <th class="text-center">Status</th>
            <th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($courses)): ?>
            <tr><td colspan="7" class="text-center text-muted">No courses created yet.</td></tr>
          <?php else: ?>
            <?php foreach ($courses as $c): 
              $thumbnailUrl = 'https://placehold.co/80x50/6f42c1/ffffff?text=LMS';
              if ($c['thumbnail']) {
                if (file_exists(BASE_PATH . '/uploads/' . $c['thumbnail'])) {
                  $thumbnailUrl = SITE_URL . '/uploads/' . $c['thumbnail'];
                } else {
                  $thumbnailUrl = SITE_URL . '/assets/images/' . $c['thumbnail'];
                }
              }
            ?>
              <tr>
                <td class="text-center">
                  <img src="<?php echo $thumbnailUrl; ?>" alt="thumbnail" class="img-fluid rounded-3 border" style="width: 70px; height: 45px; object-fit: cover;" onerror="this.src='https://placehold.co/80x50/6f42c1/ffffff?text=LMS'">
                </td>
                <td class="text-center">
                  <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['title']); ?></div>
                  <span class="text-muted fs-8">slug: <?php echo htmlspecialchars($c['slug']); ?></span>
                </td>
                <td class="text-center"><span class="badge bg-secondary"><?php echo htmlspecialchars($c['category_name']); ?></span></td>
                <td class="text-center fw-bold text-primary">₹<?php echo number_format($c['price'], 2); ?></td>
                <td class="text-center"><span class="badge bg-primary-light text-primary"><?php echo $c['video_count']; ?> Lessons</span></td>
                <td class="text-center">
                  <span class="badge <?php echo $c['status'] == 'Published' ? 'bg-success' : 'bg-warning'; ?>">
                    <?php echo $c['status']; ?>
                  </span>
                </td>
<td class="text-center">
  <div class="d-flex align-items-center justify-content-center gap-2">
    <a href="videos.php?course_id=<?php echo $c['id']; ?>" class="btn btn-outline-success btn-sm text-nowrap" title="Manage Lessons / Syllabus">
      <i class="fa-solid fa-list-check me-1"></i> Syllabus
    </a>
    <a href="courses.php?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-outline-primary btn-sm" style="width: 32px; height: 32px; padding: 0; display: inline-flex; justify-content: center; align-items: center;"   title="Edit Course details">
      <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="courses.php?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-outline-danger btn-sm" style="width: 32px; height: 32px; padding: 0; display: inline-flex; justify-content: center; align-items: center;"   onclick="confirmAction(event, 'Are you sure you want to delete this course and all its video lectures?', this.href);" title="Delete">
      <i class="fa-solid fa-trash-can"></i>
    </a>
  </div>
</td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<!-- Add or Edit Form View -->
<?php if (in_array($action, ['add', 'edit'])): 
  $editCourse = null;
  if ($action === 'edit' && !empty($id)) {
    // Fetch only the required fields needed by the frontend form
    $editCourse = DB::fetch("SELECT id, title, description, category_id, price, course_duration, allow_partial_payment, min_installment, status, thumbnail FROM courses WHERE id = ?", [$id]);
  }
?>
  <div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
    <h5 class="fw-bold text-primary mb-4">
      <?php echo $action === 'edit' ? 'Edit Course Details' : 'Create New Course'; ?>
    </h5>
    
    <form action="courses.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
      
      <div class="row">
        <div class="col-md-8">
          <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Course Title</label>
            <input type="text" class="form-control" id="title" name="title" 
                value="<?php echo $editCourse ? htmlspecialchars($editCourse['title']) : ''; ?>" placeholder="e.g. FSW Point System Tutorial" required>
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Course Description</label>
            <textarea class="form-control" id="description" name="description" rows="8" placeholder="Enter structured course description..." required><?php echo $editCourse ? htmlspecialchars($editCourse['description']) : ''; ?></textarea>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="mb-3">
            <label for="category_id" class="form-label fw-semibold">Course Category</label>
            <select class="form-select" id="category_id" name="category_id" required>
              <option value="">Select Category</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" 
                  <?php echo ($editCourse && $editCourse['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($cat['name']); ?> (<?php echo htmlspecialchars($cat['slug']); ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="price" class="form-label fw-semibold">Course Price (INR)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" 
                value="<?php echo $editCourse ? htmlspecialchars($editCourse['price']) : '0.00'; ?>" required>
          </div>
          
          <div class="mb-3">
            <label for="course_duration" class="form-label fw-semibold">Course Duration (Months)</label>
            <select class="form-select" id="course_duration" name="course_duration">
              <option value="0" <?php echo ($editCourse && $editCourse['course_duration'] == 0) ? 'selected' : ''; ?>>Lifetime (Unlimited)</option>
              <option value="3" <?php echo ($editCourse && $editCourse['course_duration'] == 3) ? 'selected' : ''; ?>>3 Months</option>
              <option value="6" <?php echo ($editCourse && $editCourse['course_duration'] == 6) ? 'selected' : ''; ?>>6 Months</option>
              <option value="12" <?php echo ($editCourse && $editCourse['course_duration'] == 12) ? 'selected' : ''; ?>>12 Months</option>
              <option value="24" <?php echo ($editCourse && $editCourse['course_duration'] == 24) ? 'selected' : ''; ?>>24 Months</option>
            </select>
          </div>

          <div class="mb-3 form-check form-switch">
            <input class="form-check-input" type="checkbox" id="allow_partial_payment" name="allow_partial_payment" value="1" <?php echo ($editCourse && $editCourse['allow_partial_payment']) ? 'checked' : ''; ?>>
            <label class="form-check-label fw-semibold" for="allow_partial_payment">Allow Partial Payments</label>
          </div>

          <div class="mb-3" id="min_installment_container" style="<?php echo ($editCourse && $editCourse['allow_partial_payment']) ? '' : 'display: none;'; ?>">
            <label for="min_installment" class="form-label fw-semibold">Min Installment Amount (INR)</label>
            <input type="number" step="0.01" min="0" class="form-control" id="min_installment" name="min_installment" value="<?php echo $editCourse ? htmlspecialchars($editCourse['min_installment']) : '0.00'; ?>">
          </div>
          


          <div class="mb-3">
            <label for="status" class="form-label fw-semibold">Publish Status</label>
            <select class="form-select" id="status" name="status" required>
              <option value="Published" <?php echo ($editCourse && $editCourse['status'] === 'Published') ? 'selected' : ''; ?>>Published</option>
              <option value="Draft" <?php echo ($editCourse && $editCourse['status'] === 'Draft') ? 'selected' : ''; ?>>Draft</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label for="thumbnail" class="form-label fw-semibold">Course Thumbnail Image</label>
            <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
            <span class="fs-8 text-muted mt-1 d-block">Recommended: 16:9 Aspect Ratio. Max 2MB.</span>
            
            <?php if ($editCourse && $editCourse['thumbnail']): ?>
              <div class="mt-3">
                <span class="d-block fs-8 text-muted mb-1">Current Thumbnail:</span>
                <img src="<?php echo (file_exists(BASE_PATH . '/uploads/' . $editCourse['thumbnail'])) ? SITE_URL . '/uploads/' . $editCourse['thumbnail'] : SITE_URL . '/assets/images/' . $editCourse['thumbnail']; ?>" 
                   alt="Current thumbnail" class="img-fluid rounded-3 border" style="max-width: 150px;" onerror="this.src='https://placehold.co/150x80/6f42c1/ffffff?text=No+Image'">
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="mt-3 text-end border-top pt-3">
        <a href="courses.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">Cancel</a>
        <button type="submit" class="btn btn-primary rounded-pill px-5">Save Course</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
