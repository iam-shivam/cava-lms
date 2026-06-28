<?php
// Admin Users List and Suspension Toggle
require_once __DIR__ . '/admin_header.php';

$action = trim($_GET['action'] ?? '');
$id = intval($_GET['id'] ?? 0);

// Status Toggle processing
if ($action === 'toggle_status' && $id > 0) {
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

// Fetch Users list
$users = DB::fetchAll("
    SELECT u.*, 
           (SELECT COUNT(id) FROM enrollments WHERE user_id = u.id) as enrollment_count,
           (SELECT COUNT(id) FROM webinar_registrations WHERE user_id = u.id) as webinar_count
    FROM users u 
    ORDER BY u.id DESC
");
?>

<div class="card shadow-sm border-0 rounded-4 bg-white p-4">
    <h5 class="fw-bold text-dark mb-4">Users Account Management</h5>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
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
                    <tr><td colspan="7" class="text-center text-muted">No users registered yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
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
</div>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
