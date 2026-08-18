<?php
// Admin Users List and Suspension Toggle
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? '');
$id = trim($_GET['id'] ?? '');

// Status Toggle processing
if ($action === 'toggle_status' && !empty($id)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('danger', 'Invalid or unauthorized request.');
        header("Location: users.php");
        exit;
    }
    try {
        $user = DB::fetch("SELECT status FROM users WHERE id = ?", [$id]);
        if ($user) {
            $newStatus = ($user['status'] === 'Active') ? 'Suspended' : 'Active';
            $stmt = DB::getConnection()->prepare("UPDATE users SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $id]);
            
            // If user is suspended, we can destroy their active sessions, but for simplicity, the AuthController check covers this at login.
            set_flash_message('success', 'User status changed to ' . $newStatus);
        }
    } catch (Exception $e) {
        set_flash_message('danger', 'Database Error: ' . $e->getMessage());
    }
    header("Location: users.php");
    exit;
}

// Pagination settings
$perPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
if (!in_array($perPage, [10, 20, 50, 100])) $perPage = 10;
$currentPage = isset($_GET['pg']) ? max(1, intval($_GET['pg'])) : 1;

$totalRow = DB::fetch("SELECT COUNT(*) as total FROM users");
$totalUsers = intval($totalRow['total']);
$totalPages = max(1, ceil($totalUsers / $perPage));
if ($currentPage > $totalPages) $currentPage = $totalPages;
$offset = ($currentPage - 1) * $perPage;

// Fetch Users list with pagination
$users = DB::fetchAll("
    SELECT u.*, 
           (SELECT COUNT(id) FROM enrollments WHERE user_id = u.id) as enrollment_count,
           (SELECT COUNT(id) FROM webinar_registrations WHERE user_id = u.id) as webinar_count
    FROM users u 
    ORDER BY u.created_at DESC
    LIMIT $perPage OFFSET $offset
");
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Users Account Management</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Mobile Number</th>
                    <th>Course Enrollments</th>
                    <th>Webinar Registrations</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="8" class="text-center text-muted">No users registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $u): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($u['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['mobile_number']); ?></td>
                            <td><span class="badge bg-primary-light text-primary"><?php echo $u['enrollment_count']; ?> Courses</span></td>
                            <td><span class="badge bg-secondary-light text-secondary"><?php echo $u['webinar_count']; ?> Webinars</span></td>
                            <td>
                                <span class="badge <?php echo $u['status'] === 'Active' ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $u['status']; ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="users.php?action=toggle_status&id=<?php echo $u['id']; ?>" 
                                   class="btn btn-sm <?php echo $u['status'] === 'Active' ? 'btn-outline-danger' : 'btn-outline-success'; ?>" 
                                   onclick="confirmAction(event, 'Are you sure you want to <?php echo $u['status'] === 'Active' ? 'Suspend' : 'Activate'; ?> this user account?', this.href)">
                                    <i class="fa-solid <?php echo $u['status'] === 'Active' ? 'fa-user-slash' : 'fa-user-check'; ?> me-1"></i>
                                    <?php echo $u['status'] === 'Active' ? 'Suspend' : 'Activate'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Controls -->
    <?php if ($totalUsers > 0): ?>
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div class="text-muted small">
        Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $perPage, $totalUsers); ?> of <?php echo $totalUsers; ?> entries
      </div>
      <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
          <label class="text-muted small mb-0">Show</label>
          <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='users.php?per_page='+this.value+'&pg=1'">
            <?php foreach ([10, 20, 50, 100] as $opt): ?>
              <option value="<?php echo $opt; ?>" <?php echo $perPage == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted small">entries</span>
        </div>

        <nav aria-label="Users pagination">
          <ul class="pagination mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link" href="users.php?pg=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">&laquo;</a>
            </li>
            <?php
            $startP = max(1, $currentPage - 2);
            $endP = min($totalPages, $currentPage + 2);
            if ($startP > 1): ?>
              <li class="page-item"><a class="page-link" href="users.php?pg=1&per_page=<?php echo $perPage; ?>">1</a></li>
              <?php if ($startP > 2): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
            <?php endif; ?>
            <?php for ($p = $startP; $p <= $endP; $p++): ?>
              <li class="page-item <?php echo $p == $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="users.php?pg=<?php echo $p; ?>&per_page=<?php echo $perPage; ?>"><?php echo $p; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($endP < $totalPages): ?>
              <?php if ($endP < $totalPages - 1): ?><li class="page-item disabled"><span class="page-link">&hellip;</span></li><?php endif; ?>
              <li class="page-item"><a class="page-link" href="users.php?pg=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>"><?php echo $totalPages; ?></a></li>
            <?php endif; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
              <a class="page-link" href="users.php?pg=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">&raquo;</a>
            </li>
          </ul>
        </nav>

      </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
