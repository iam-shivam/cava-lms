<?php
// Admin Webinars CRUD
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? 'list');
$id = intval($_GET['id'] ?? 0);

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
    
    if (empty($title) || empty($date) || empty($time)) {
        set_flash_message('danger', 'Title, date, and time are required.');
        header("Location: webinars.php?action=" . $action . ($id > 0 ? "&id=$id" : ""));
        exit;
    }
    
    // Server-side validation for date
    $currentDate = date('Y-m-d');
    if ($date < $currentDate) {
        set_flash_message('danger', 'Webinar scheduled date cannot be in the past.');
        header("Location: webinars.php?action=" . $action . ($id > 0 ? "&id=$id" : ""));
        exit;
    }
    
    try {
        if ($action === 'add') {
            $sql = "INSERT INTO webinars (title, description, date, time, price, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->execute([$title, $description, $date, $time, $price, $status]);
            set_flash_message('success', 'Webinar created successfully!');
        } elseif ($action === 'edit' && $id > 0) {
            $sql = "UPDATE webinars SET title = ?, description = ?, date = ?, time = ?, price = ?, status = ? WHERE id = ?";
            $stmt = DB::getConnection()->prepare($sql);
            $stmt->execute([$title, $description, $date, $time, $price, $status, $id]);
            set_flash_message('success', 'Webinar details updated successfully!');
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database error: ' . $e->getMessage());
    }
    
    header("Location: webinars.php");
    exit;
}

// Handle Delete Webinar
if ($action === 'delete' && $id > 0) {
    try {
        DB::query("DELETE FROM webinars WHERE id = ?", [$id]);
        set_flash_message('success', 'Webinar deleted successfully.');
    } catch (Exception $e) {
        set_flash_message('danger', 'Database error: ' . $e->getMessage());
    }
    header("Location: webinars.php");
    exit;
}

// Fetch Webinars
$webinars = DB::fetchAll("
    SELECT w.*, 
           (SELECT COUNT(id) FROM webinar_registrations WHERE webinar_id = w.id) as registration_count 
    FROM webinars w 
    ORDER BY w.date ASC, w.time ASC
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
                        <th>Webinar Title</th>
                        <th>Date & Time</th>
                        <th>Price</th>
                        <th>Registrations</th>
                        <th>Status</th>
                        <th>Export</th>
<th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($webinars)): ?>
                        <tr><td colspan="6" class="text-center text-muted">No webinars created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($webinars as $w): ?>
                            <tr>
                                <td class="fw-semibold text-dark"><?php echo htmlspecialchars($w['title']); ?></td>
                                <td>
                                    <span class="d-block"><i class="fa-regular fa-calendar me-1"></i><?php echo date('d M, Y', strtotime($w['date'])); ?></span>
                                    <span class="d-block text-muted fs-8"><i class="fa-regular fa-clock me-1"></i><?php echo date('h:i A', strtotime($w['time'])); ?></span>
                                </td>
                                <td class="fw-bold text-primary">₹<?php echo number_format($w['price'], 2); ?></td>
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
    <a href="webinars.php?action=edit&id=<?php echo $w['id']; ?>" class="btn btn-outline-primary btn-sm me-1" title="Edit">
        <i class="fa-solid fa-pen-to-square"></i>
    </a>
    <a href="webinars.php?action=delete&id=<?php echo $w['id']; ?>" 
        class="btn btn-outline-danger btn-sm" 
        onclick="return confirm('Are you sure you want to delete this webinar?');" 
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
    $editWebinar = null;
    if ($action === 'edit' && $id > 0) {
        $editWebinar = DB::fetch("SELECT * FROM webinars WHERE id = ?", [$id]);
    }
?>
    <div class="card shadow-sm border-0 rounded-4 bg-white p-4 p-md-5">
        <h5 class="fw-bold text-primary mb-4">
            <?php echo $action === 'edit' ? 'Edit Webinar Details' : 'Create New Webinar'; ?>
        </h5>
        
        <form action="webinars.php?action=<?php echo $action; ?>&id=<?php echo $id; ?>" method="POST">
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
                    
                    <div class="mb-3">
                        <label for="price" class="form-label fw-semibold">Ticket Price (INR)</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" 
                               value="<?php echo $editWebinar ? htmlspecialchars($editWebinar['price']) : '0.00'; ?>" required>
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
