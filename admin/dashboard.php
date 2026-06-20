<?php
// Admin Dashboard
require_once __DIR__ . '/admin_header.php';

// Fetch Statistics
$totalUsers = DB::fetch("SELECT COUNT(id) as count FROM users")['count'];
$totalCourses = DB::fetch("SELECT COUNT(id) as count FROM courses")['count'];
$totalWebinars = DB::fetch("SELECT COUNT(id) as count FROM webinars")['count'];

$revenueRow = DB::fetch("SELECT SUM(amount) as total FROM payments WHERE status = 'Success'");
$totalRevenue = $revenueRow['total'] ?? 0.00;

$totalPayments = DB::fetch("SELECT COUNT(id) as count FROM payments WHERE status = 'Success'")['count'];

// Recent Registrations
$recentUsers = DB::fetchAll("SELECT * FROM users ORDER BY id DESC LIMIT 5");

// Recent Payments
$recentPayments = DB::fetchAll("
    SELECT p.*, u.full_name as user_name 
    FROM payments p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.id DESC LIMIT 5
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800 fw-bold">Admin Dashboard Overview</h1>
</div>

<!-- Stats Row -->
<div class="row mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-primary border-4 shadow-sm h-100 py-2 bg-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">₹<?php echo number_format($totalRevenue, 2); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-indian-rupee-sign fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Total Success Transactions -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-success border-4 shadow-sm h-100 py-2 bg-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Success Payments</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalPayments; ?> Sales</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-regular fa-credit-card fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Users -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-info border-4 shadow-sm h-100 py-2 bg-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Registered Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalUsers; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-users fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Courses -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-start border-warning border-4 shadow-sm h-100 py-2 bg-white">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Active Courses</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $totalCourses; ?> / <?php echo $totalWebinars; ?> Webinars</div>
                    </div>
                    <div class="col-auto">
                        <i class="fa-solid fa-book-open fa-2x text-muted"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Users Table -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fa-solid fa-user-plus me-2"></i>Recent Registered Users</h6>
                <a href="users.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentUsers)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No users registered yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo date('d M, Y', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 rounded-4 bg-white">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success fw-bold"><i class="fa-solid fa-wallet me-2"></i>Recent Payments Log</h6>
                <a href="payments.php" class="btn btn-outline-success btn-sm rounded-pill px-3">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentPayments)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No payments recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recentPayments as $pay): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($pay['user_name']); ?></td>
                                        <td class="fw-bold">₹<?php echo number_format($pay['amount'], 2); ?></td>
                                        <td>
                                            <?php if ($pay['status'] === 'Success'): ?>
                                                <span class="badge bg-success">Success</span>
                                            <?php elseif ($pay['status'] === 'Pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M, Y', strtotime($pay['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
