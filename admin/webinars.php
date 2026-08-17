<?php
// Admin Webinars CRUD
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? 'list');
$id = trim($_GET['id'] ?? '');

// Form Actions Handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: webinars.php");
        exit;
    }
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $price = floatval($_POST['price'] ?? 0.00);
    $status = $_POST['status'] ?? 'Active';
    $joinUrl = trim($_POST['join_url'] ?? '');
    
    if (empty($title) || empty($date) || empty($time)) {
        set_flash_message('danger', 'Title, date, and time are required.');
        header("Location: webinars.php?action=" . $action . (!empty($id) ? "&id=$id" : ""));
        exit;
    }
    
    // Server-side validation for date
    $currentDate = date('Y-m-d');
    if ($date < $currentDate) {
        set_flash_message('danger', 'Webinar scheduled date cannot be in the past.');
        header("Location: webinars.php?action=" . $action . (!empty($id) ? "&id=$id" : ""));
        exit;
    }
    
    // Thumbnail file upload processing
    $thumbnailName = null;
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['thumbnail']['tmp_name'];
        $fileName = $_FILES['thumbnail']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);
        
        if (in_array($fileExtension, $allowedExtensions) && in_array($detectedMime, $allowedMimeTypes)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = BASE_PATH . '/uploads/';
            
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            
            $destPath = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $thumbnailName = $newFileName;
            }
        }
    }

    try {
        if ($action === 'add') {
            $sql = "INSERT INTO webinars (id, title, thumbnail, description, date, time, price, status, join_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->execute([generate_uuid(), $title, $thumbnailName, $description, $date, $time, $price, $status, $joinUrl]);
            set_flash_message('success', 'Webinar created successfully!');
        } elseif ($action === 'edit' && !empty($id)) {
            $oldWebinar = DB::fetch("SELECT title, thumbnail, description, date, time, price, status, join_url FROM webinars WHERE id = ?", [$id]);
            
            $updates = [];
            $params = [];
            
            if ($title !== $oldWebinar['title']) { $updates[] = "title = ?"; $params[] = $title; }
            if ($description !== $oldWebinar['description']) { $updates[] = "description = ?"; $params[] = $description; }
            if ($date !== $oldWebinar['date']) { $updates[] = "date = ?"; $params[] = $date; }
            if ($time !== $oldWebinar['time']) { $updates[] = "time = ?"; $params[] = $time; }
            if (floatval($price) !== floatval($oldWebinar['price'])) { $updates[] = "price = ?"; $params[] = $price; }
            if ($status !== $oldWebinar['status']) { $updates[] = "status = ?"; $params[] = $status; }
            if ($joinUrl !== $oldWebinar['join_url']) { $updates[] = "join_url = ?"; $params[] = $joinUrl; }
            
            if ($thumbnailName) {
                if ($oldWebinar['thumbnail'] && file_exists(BASE_PATH . '/uploads/' . $oldWebinar['thumbnail'])) {
                    @unlink(BASE_PATH . '/uploads/' . $oldWebinar['thumbnail']);
                }
                $updates[] = "thumbnail = ?";
                $params[] = $thumbnailName;
            }

            if (!empty($updates)) {
                $params[] = $id;
                $sql = "UPDATE webinars SET " . implode(', ', $updates) . " WHERE id = ?";
                $stmt = DB::getConnection()->prepare($sql);
                $stmt->execute($params);
            }
            set_flash_message('success', 'Webinar details updated successfully!');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database error: ' . $e->getMessage());
    }
    
    header("Location: webinars.php");
    exit;
}

// Handle Delete Webinar
if ($action === 'delete' && !empty($id)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid or unauthorized request.');
        header("Location: webinars.php");
        exit;
    }
    try {
        DB::query("DELETE FROM webinars WHERE id = ?", [$id]);
        set_flash_message('success', 'Webinar deleted successfully.');
    } catch (Exception $e) {
        set_flash_message('danger', 'Database error: ' . $e->getMessage());
    }
    header("Location: webinars.php");
    exit;
}

// Pagination settings
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM webinars");
$totalWebinars = intval($totalRow['total']);
$totalPages = max(1, ceil($totalWebinars / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

// Fetch Webinars with pagination
$webinars = DB::fetchAll("
    SELECT w.*, 
           (SELECT COUNT(id) FROM webinar_registrations WHERE webinar_id = w.id) as registration_count 
    FROM webinars w 
    ORDER BY w.date ASC, w.time ASC
    LIMIT $perPage OFFSET $offset
");

$csrfToken = generate_csrf_token();
?>

<?php if ($action === 'list'): ?>
    <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold text-dark m-0">Webinars Management</h5>
            <a href="webinars.php?action=add" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-plus me-1"></i> Create Webinar
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Webinar Title</th>
                        <th>Date & Time</th>
                        <th>Registrations</th>
                        <th>Status</th>
                        <th>Export</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($webinars)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No webinars created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($webinars as $index => $w): ?>
                            <tr>
                                <td class="text-muted fw-semibold"><?php echo $offset + $index + 1; ?></td>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($w['title']); ?></td>
                                <td>
                                    <span class="d-block"><i class="fa-regular fa-calendar me-1"></i><?php echo date('d M, Y', strtotime($w['date'])); ?></span>
                                    <span class="d-block text-muted fs-8"><i class="fa-regular fa-clock me-1"></i><?php echo date('h:i A', strtotime($w['time'])); ?></span>
                                </td>
                                <td><span class="badge bg-primary-light text-primary"><?php echo $w['registration_count']; ?> Registered</span></td>
                                <td>
                                    <?php 
                                    $webinarTimestamp = strtotime($w['date'] . ' ' . $w['time']);
                                    $displayStatus = $w['status'];
                                    if ($w['status'] === 'Active' && $webinarTimestamp < time()) {
                                        $displayStatus = 'Closed';
                                    }
                                    ?>
                                    <span class="badge <?php 
                                        echo $displayStatus == 'Active' ? 'bg-success' : (($displayStatus == 'Closed' || $displayStatus == 'Completed') ? 'bg-secondary' : 'bg-danger'); 
                                    ?>"><?php echo $displayStatus; ?></span>
                                </td>
                                <td>
    <a href="export_registrants.php?webinar_id=<?php echo $w['id']; ?>&format=csv" class="btn btn-outline-success btn-sm" title="Export Registrants CSV">
        <i class="fa-solid fa-file-csv"></i>
    </a>
</td>
<td class="text-end">
    <a href="webinars.php?action=edit&id=<?php echo $w['id']; ?>" class="btn btn-outline-primary btn-sm" style="width: 32px; height: 32px; padding: 0; display: inline-flex; justify-content: center; align-items: center;"  title="Edit">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="webinars.php?action=delete&id=<?php echo $w['id']; ?>" 
        class="btn btn-outline-danger btn-sm"
        style="width: 32px; height: 32px; padding: 0; display: inline-flex; justify-content: center; align-items: center;"
        onclick="confirmAction(event, 'Are you sure you want to delete this webinar?', this.href);"
        title="Delete">
        <i class="fa-solid fa-trash-can"></i>
    </a>
</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalWebinars > 0): ?>
        <div class="d-flex justify-content-between align-items-center mt-3">
          <div class="text-muted small">
            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalWebinars); ?> of <?php echo $totalWebinars; ?> entries
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
              <label class="text-muted small mb-0">Show</label>
              <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='webinars.php?per_page='+this.value+'&pg=1'">
                <?php foreach ([10, 20, 50, 100] as $opt): ?>
                  <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                <?php endforeach; ?>
              </select>
              <span class="text-muted small">entries</span>
            </div>

            <nav aria-label="Webinars pagination">
              <ul class="pagination mb-0">
                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                  <a class="page-link" href="webinars.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">&laquo;</a>
                </li>
                <?php
                $startP = max(1, $currentPage - 2);
                $endP = min($totalPages, $currentPage + 2);
                if ($startP > 1): ?>
                  <li class="page-item"><a class="page-link" href="webinars.php?pg=1&per_page=<?php echo $perPage; ?>">1</a></li>
                  <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                <?php endif; ?>
                <?php for ($p = $startP; $p <= $endP; $p++): ?>
                  <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="webinars.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
                  </li>
                <?php endfor; ?>
                <?php if ($endP < $totalPages): ?>
                  <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
                  <li class="page-item"><a class="page-link" href="webinars.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>"><?php echo $totalPages; ?></a></li>
                <?php endif; ?>
                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                  <a class="page-link" href="webinars.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">&raquo;</a>
                </li>
              </ul>
            </nav>

          </div>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (in_array($action, ['add', 'edit'])): 
    $editWebinar = null;
    if ($action === 'edit' && !empty($id)) {
        $editWebinar = DB::fetch("SELECT id, title, thumbnail, description, date, time, price, status, join_url FROM webinars WHERE id = ?", [$id]);
    }
?>
    <div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
        <h5 class="fw-bold text-primary mb-4">
            <?php echo $action === 'edit' ? 'Edit Webinar Details' : 'Create New Webinar'; ?>
        </h5>
        
        <form action="webinars.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Webinar Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo $editWebinar ? htmlspecialchars($editWebinar['title']) : ''; ?>" placeholder="e.g. Live Q&A Session Canada CRS Calculator" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Webinar Description</label>
                        <textarea class="form-control" id="description" name="description" rows="6" placeholder="Enter brief overview about what live webinar covers..." required><?php echo $editWebinar ? htmlspecialchars($editWebinar['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="join_url" class="form-label fw-semibold">Join Webinar URL (Optional)</label>
                        <input type="url" class="form-control" id="join_url" name="join_url" value="<?php echo $editWebinar ? htmlspecialchars($editWebinar['join_url']) : ''; ?>" placeholder="https://zoom.us/j/...">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="date" class="form-label fw-semibold">Scheduled Date</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo $editWebinar ? $editWebinar['date'] : ''; ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="time" class="form-label fw-semibold">Scheduled Time</label>
                        <input type="time" class="form-control" id="time" name="time" 
                               value="<?php echo $editWebinar ? $editWebinar['time'] : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3 d-none">
                        <label for="price" class="form-label fw-semibold">Ticket Price (INR)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" 
                               value="<?php echo $editWebinar ? htmlspecialchars($editWebinar['price']) : '0.00'; ?>">
                    </div>

                    <div class="mb-3">
                        <label for="thumbnail" class="form-label fw-semibold">Webinar Banner/Thumbnail</label>
                        <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                        <span class="fs-8 text-muted mt-1 d-block font-medium">JPG, PNG, GIF, WEBP formats only.</span>
                        
                        <?php if ($editWebinar && !empty($editWebinar['thumbnail'])): ?>
                            <div class="mt-2">
                                <span class="d-block fs-8 text-muted mb-1">Current Banner:</span>
                                <img src="<?php echo (file_exists(BASE_PATH . '/uploads/' . $editWebinar['thumbnail'])) ? SITE_URL . '/uploads/' . $editWebinar['thumbnail'] : SITE_URL . '/assets/images/' . $editWebinar['thumbnail']; ?>" 
                                     alt="Current webinar thumbnail" class="img-fluid rounded-3 border" style="max-width: 150px; max-height: 90px; object-fit: cover;" onerror="this.src='https://placehold.co/150x90/6f42c1/ffffff?text=No+Thumbnail'">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Webinar Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="Active" <?php echo ($editWebinar && $editWebinar['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                            <option value="Completed" <?php echo ($editWebinar && $editWebinar['status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="Cancelled" <?php echo ($editWebinar && $editWebinar['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-end border-top pt-3">
                <a href="webinars.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5">Save Webinar</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
