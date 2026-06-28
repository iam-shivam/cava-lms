<?php
// Admin Events CRUD
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? 'list');
$id = intval($_GET['id'] ?? 0);

// Form processing (Add/Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: events.php");
        exit;
    }
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';
    
    if (empty($title) || empty($date)) {
        set_flash_message('danger', 'Title and date are required.');
        header("Location: events.php?action=" . $action . ($id > 0 ? "&id=$id" : ""));
        exit;
    }
    
    // Server-side validation for date
    $currentDate = date('Y-m-d');
    if ($date < $currentDate) {
        set_flash_message('danger', 'Event date cannot be in the past.');
        header("Location: events.php?action=" . $action . ($id > 0 ? "&id=$id" : ""));
        exit;
    }
    
    // File upload logic
    $imageName = null;
    if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['event_image']['tmp_name'];
        $fileName = $_FILES['event_image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
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
                $imageName = $newFileName;
            } else {
                set_flash_message('danger', 'Error moving the uploaded event image file.');
            }
        } else {
            set_flash_message('danger', 'Upload failed. Allowed formats: JPG, PNG, GIF.');
            header("Location: events.php?action=" . $action . ($id > 0 ? "&id=$id" : ""));
            exit;
        }
    }
    
    try {
        if ($action === 'add') {
            $sql = "INSERT INTO events (title, description, date, event_image) VALUES (?, ?, ?, ?)";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->execute([$title, $description, $date, $imageName]);
            set_flash_message('success', 'Event created successfully!');
        } elseif ($action === 'edit' && $id > 0) {
            if ($imageName) {
                // Delete old image
                $oldImage = DB::fetch("SELECT event_image FROM events WHERE id = ?", [$id])['event_image'];
                if ($oldImage && file_exists(BASE_PATH . '/uploads/' . $oldImage)) {
                    unlink(BASE_PATH . '/uploads/' . $oldImage);
                }
                
                $sql = "UPDATE events SET title = ?, description = ?, date = ?, event_image = ? WHERE id = ?";
                $stmt = DB::getConnection()->prepare($sql);
                $stmt->execute([$title, $description, $date, $imageName, $id]);
            } else {
                $sql = "UPDATE events SET title = ?, description = ?, date = ? WHERE id = ?";
                $stmt = DB::getConnection()->prepare($sql);
                $stmt->execute([$title, $description, $date, $id]);
            }
            set_flash_message('success', 'Event updated successfully!');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database error: ' . $e->getMessage());
    }
    
    header("Location: events.php");
    exit;
}

// Delete Event
if ($action === 'delete' && $id > 0) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid or unauthorized request.');
        header("Location: events.php");
        exit;
    }
    try {
        $event = DB::fetch("SELECT event_image FROM events WHERE id = ?", [$id]);
        if ($event) {
            if ($event['event_image'] && file_exists(BASE_PATH . '/uploads/' . $event['event_image'])) {
                unlink(BASE_PATH . '/uploads/' . $event['event_image']);
            }
            DB::query("DELETE FROM events WHERE id = ?", [$id]);
            set_flash_message('success', 'Event deleted successfully.');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database error: ' . $e->getMessage());
    }
    header("Location: events.php");
    exit;
}

// Fetch Events
$events = DB::fetchAll("SELECT * FROM events ORDER BY date ASC");
$csrfToken = generate_csrf_token();
?>

<?php if ($action === 'list'): ?>
    <div class="card shadow-sm border-0 rounded-4 bg-white p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold text-dark m-0">Events Management</h5>
            <a href="events.php?action=add" class="btn btn-primary rounded-pill px-4">
                <i class="fa-solid fa-plus me-1"></i> Add Event
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">Image</th>
                        <th>Event Title</th>
                        <th>Scheduled Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="5" class="text-center text-muted">No events listed yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $ev): 
                            $imageUrl = 'https://placehold.co/80x50/6f42c1/ffffff?text=Event';
                            if ($ev['event_image']) {
                                if (file_exists(BASE_PATH . '/uploads/' . $ev['event_image'])) {
                                    $imageUrl = SITE_URL . '/uploads/' . $ev['event_image'];
                                } else {
                                    $imageUrl = SITE_URL . '/assets/images/' . $ev['event_image'];
                                }
                            }
                        ?>
                            <tr>
                                <td>
                                    <img src="<?php echo $imageUrl; ?>" alt="event" class="img-fluid rounded-3 border" style="width: 70px; height: 45px; object-fit: cover;" onerror="this.src='https://placehold.co/80x50/6f42c1/ffffff?text=Event'">
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($ev['title']); ?></div>
                                    <span class="text-muted fs-8 d-block text-truncate" style="max-width: 400px;"><?php echo htmlspecialchars($ev['description'] ?? ''); ?></span>
                                </td>
                                <td>
                                    <span class="fw-medium text-dark"><i class="fa-regular fa-calendar me-1"></i><?php echo date('d M, Y', strtotime($ev['date'])); ?></span>
                                </td>
                                <td>
                                    <?php if (strtotime($ev['date']) < strtotime(date('Y-m-d'))): ?>
                                        <span class="badge bg-secondary">Closed</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="events.php?action=edit&id=<?php echo $ev['id']; ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <a href="events.php?action=delete&id=<?php echo $ev['id']; ?>" 
                                       class="btn btn-outline-danger btn-sm" 
                                       onclick="confirmAction(event, 'Delete this event?', this.href);" 
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
    </div>
<?php endif; ?>

<?php if (in_array($action, ['add', 'edit'])): 
    $editEvent = null;
    if ($action === 'edit' && $id > 0) {
        $editEvent = DB::fetch("SELECT * FROM events WHERE id = ?", [$id]);
    }
?>
    <div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
        <h5 class="fw-bold text-primary mb-4">
            <?php echo $action === 'edit' ? 'Edit Event Details' : 'Create New Event'; ?>
        </h5>
        
        <form action="events.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?php echo $editEvent ? htmlspecialchars($editEvent['title']) : ''; ?>" placeholder="e.g. Canada Student Visa Counselling Fair" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Event Description</label>
                        <textarea class="form-control" id="description" name="description" rows="6" placeholder="Provide general outline and schedules..." required><?php echo $editEvent ? htmlspecialchars($editEvent['description']) : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="date" class="form-label fw-semibold">Event Date</label>
                        <input type="date" class="form-control" id="date" name="date" 
                               value="<?php echo $editEvent ? $editEvent['date'] : ''; ?>" 
                               min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="event_image" class="form-label fw-semibold">Event Banner/Image</label>
                        <input type="file" class="form-control" id="event_image" name="event_image" accept="image/*">
                        <span class="fs-8 text-muted mt-1 d-block font-medium">JPG, PNG, GIF formats only. Max 2MB.</span>
                        
                        <?php if ($editEvent && $editEvent['event_image']): ?>
                            <div class="mt-3">
                                <span class="d-block fs-8 text-muted mb-1">Current Banner:</span>
                                <img src="<?php echo (file_exists(BASE_PATH . '/uploads/' . $editEvent['event_image'])) ? SITE_URL . '/uploads/' . $editEvent['event_image'] : SITE_URL . '/assets/images/' . $editEvent['event_image']; ?>" 
                                     alt="Current event banner" class="img-fluid rounded-3 border" style="max-width: 150px;" onerror="this.src='https://placehold.co/150x80/6f42c1/ffffff?text=No+Image'">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mt-3 text-end border-top pt-3">
                <a href="events.php" class="btn btn-outline-secondary rounded-pill px-4 me-2">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-5">Save Event</button>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
