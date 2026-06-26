<?php
// User Dashboard Controller & View
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/models/Query.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Payment.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    set_flash_message('warning', 'Please login to access your dashboard.');
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$user = User::findById($userId);

if (!$user) {
    session_destroy();
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$tab = trim($_GET['tab'] ?? 'courses');

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: dashboard.php?tab=profile");
        exit;
    }
    
    if ($_POST['action'] === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile_number'] ?? '');
        
        if (empty($fullName) || empty($mobile)) {
            set_flash_message('danger', 'All profile fields are required.');
        } else {
            User::updateProfile($userId, $fullName, $mobile);
            $_SESSION['user_name'] = $fullName; // update session
            set_flash_message('success', 'Profile updated successfully.');
        }
    }
    header("Location: dashboard.php?tab=profile");
    exit;
}

// Fetch user data
$purchasedCourses = Course::getEnrolledCourses($userId);
$registeredWebinars = Webinar::getRegisteredWebinars($userId);
$myQueries = Query::getByUser($userId);
$myPayments = Payment::getPaymentsByUser($userId);

$csrfToken = generate_csrf_token();
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="dashboard-wrapper">
    <div class="container">
        <!-- Welcome Banner -->
        <div class="card border-0 p-4 shadow-sm bg-white rounded-4 mb-4 animate-fade-in-up">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-1">Welcome back, <span class="text-primary"><?php echo htmlspecialchars($user['full_name']); ?></span>!</h2>
                    <p class="text-muted m-0">Ready to build your career modules? Track your lectures and webinar times below.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <span class="badge bg-primary-light text-primary px-3 py-2 rounded-pill fw-semibold">
                        <i class="fa-solid fa-circle-check text-success me-1"></i> Account: Active
                    </span>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="dashboard-sidebar">
                    <h6 class="text-uppercase text-muted fw-bold mb-3 fs-8 px-2">Workspace</h6>
                    <a href="dashboard.php?tab=courses" class="dashboard-menu-link <?php echo $tab === 'courses' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-book-open"></i> My Courses
                    </a>
                    <a href="dashboard.php?tab=webinars" class="dashboard-menu-link <?php echo $tab === 'webinars' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-video"></i> My Webinars
                    </a>
                    <a href="dashboard.php?tab=queries" class="dashboard-menu-link <?php echo $tab === 'queries' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-circle-question"></i> Query History
                    </a>
                    <a href="dashboard.php?tab=payments" class="dashboard-menu-link <?php echo $tab === 'payments' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-credit-card"></i> Order History
                    </a>
                    
                    <h6 class="text-uppercase text-muted fw-bold my-3 fs-8 px-2">Account</h6>
                    <a href="dashboard.php?tab=profile" class="dashboard-menu-link <?php echo $tab === 'profile' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-id-card"></i> My Profile
                    </a>
                    <a href="logout.php" class="dashboard-menu-link text-danger">
                        <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                    </a>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-9">
                <!-- 1. Enrolled Courses -->
                <?php if ($tab === 'courses'): ?>
                    <div class="card border-0 shadow-sm bg-white p-4 rounded-4 mb-4">
                        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>My Enrolled Courses</h4>
                        
                        <?php if (empty($purchasedCourses)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-book-open-reader fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-3">You haven't enrolled in any courses yet.</p>
                                <a href="index.php#featured-courses" class="btn btn-primary rounded-pill px-4">Browse Courses</a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($purchasedCourses as $course): 
                                    $thumbnailUrl = 'https://placehold.co/600x340/6f42c1/ffffff?text=Course+Thumbnail';
                                    if ($course['thumbnail']) {
                                        $thumbnailUrl = (file_exists(BASE_PATH . '/uploads/' . $course['thumbnail'])) ? SITE_URL . '/uploads/' . $course['thumbnail'] : SITE_URL . '/assets/images/' . $course['thumbnail'];
                                    }
                                    
                                    $isExpired = false;
                                    if ($course['expiry_date'] && strtotime($course['expiry_date']) < time()) {
                                        $isExpired = true;
                                    }
                                    $effectiveStatus = $course['enrollment_status'] ?? 'Active';
                                    if ($isExpired) $effectiveStatus = 'Expired';
                                ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="custom-card border">
                                            <div class="card-img-wrapper">
                                                <img src="<?php echo $thumbnailUrl; ?>" alt="course thumbnail" onerror="this.src='https://placehold.co/600x340/6f42c1/ffffff?text=Course+Thumbnail'">
                                            </div>
                                            <div class="card-content">
                                                <h5 class="fw-bold mb-3"><?php echo htmlspecialchars($course['title']); ?></h5>
                                                <?php if ($effectiveStatus === 'Pending'): ?>
                                                    <div class="alert alert-warning py-2 fs-8 mb-3">Partially Paid. <a href="course.php?slug=<?php echo $course['slug']; ?>">Pay Balance</a></div>
                                                    <a href="course_play.php?slug=<?php echo $course['slug']; ?>" class="btn btn-warning w-100 rounded-pill mt-auto">
                                                        <i class="fa-solid fa-circle-play me-2"></i>Start Learning (Preview)
                                                    </a>
                                                <?php elseif ($effectiveStatus === 'Expired'): ?>
                                                    <div class="alert alert-danger py-2 fs-8 mb-3">Access Expired. <a href="course.php?slug=<?php echo $course['slug']; ?>">Renew</a></div>
                                                    <button class="btn btn-secondary w-100 rounded-pill mt-auto" disabled><i class="fa-solid fa-lock me-2"></i>Locked</button>
                                                <?php else: ?>
                                                    <a href="course_play.php?slug=<?php echo $course['slug']; ?>" class="btn btn-primary w-100 rounded-pill mt-auto">
                                                        <i class="fa-solid fa-circle-play me-2"></i>Start Learning
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 2. Enrolled Webinars -->
                <?php if ($tab === 'webinars'): ?>
                    <div class="card border-0 shadow-sm bg-white p-4 rounded-4 mb-4">
                        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-video text-primary me-2"></i>My Registered Webinars</h4>
                        
                        <?php if (empty($registeredWebinars)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-ticket-simple fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-3">No upcoming live webinar registrations found.</p>
                                <a href="index.php#upcoming-webinars" class="btn btn-primary rounded-pill px-4">Browse Webinars</a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($registeredWebinars as $webinar): 
                                    $webDate = date('d M, Y', strtotime($webinar['date']));
                                    $webTime = date('h:i A', strtotime($webinar['time']));
                                    $webinarTimestamp = strtotime($webinar['date'] . ' ' . $webinar['time']);
                                    $isPastWebinar = $webinarTimestamp < time();
                                ?>
                                    <div class="col-md-6 mb-4">
                                        <div class="card border-0 shadow-sm p-4 bg-light rounded-4 h-100">
                                            <?php if ($isPastWebinar): ?>
                                                <span class="badge bg-secondary-light text-secondary px-3 py-2 rounded-pill fw-semibold mb-3 align-self-start">
                                                    <i class="fa-solid fa-video-slash me-1"></i> Closed
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success-light text-success px-3 py-2 rounded-pill fw-semibold mb-3 align-self-start">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Registered
                                                </span>
                                            <?php endif; ?>
                                            <h5 class="fw-bold text-dark mb-2"><?php echo htmlspecialchars($webinar['title']); ?></h5>
                                            <p class="text-muted fs-7 mb-4"><?php echo htmlspecialchars($webinar['description']); ?></p>
                                            
                                            <div class="mt-auto border-top pt-3 d-flex justify-content-between text-muted fs-8">
                                                <span><i class="fa-regular fa-calendar me-1"></i><?php echo $webDate; ?></span>
                                                <span><i class="fa-regular fa-clock me-1"></i><?php echo $webTime; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 3. Query History -->
                <?php if ($tab === 'queries'): ?>
                    <div class="card border-0 shadow-sm bg-white p-4 rounded-4 mb-4">
                        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-circle-question text-primary me-2"></i>My Queries History</h4>
                        
                        <?php if (empty($myQueries)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-message-slash fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted">You haven't submitted any queries yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Query Message</th>
                                            <th>Date Submitted</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($myQueries as $query): ?>
                                            <tr>
                                                <td style="max-width: 400px; white-space: normal; word-wrap: break-word;">
                                                    <div class="fw-medium text-dark"><?php echo htmlspecialchars($query['query_message']); ?></div>
                                                    <?php if ($query['resolved_at']): ?>
                                                        <small class="text-success d-block mt-1">Resolved on: <?php echo date('d M, Y', strtotime($query['resolved_at'])); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('d M, Y', strtotime($query['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($query['status'] === 'Resolved'): ?>
                                                        <span class="badge bg-success"><i class="fa-solid fa-circle-check me-1"></i> Resolved</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark"><i class="fa-regular fa-clock me-1"></i> Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 4. Order History -->
                <?php if ($tab === 'payments'): ?>
                    <div class="card border-0 shadow-sm bg-white p-4 rounded-4 mb-4">
                        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-credit-card text-primary me-2"></i>Order & Transaction Logs</h4>
                        
                        <?php if (empty($myPayments)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-credit-card-slash fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted">No transactions found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Receipt Name</th>
                                            <th>Item Type</th>
                                            <th>Payment</th>
                                            <th>Amount</th>
                                            <th>Order ID</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($myPayments as $payment): ?>
                                            <tr>
                                                <td class="fw-bold text-dark"><?php echo htmlspecialchars($payment['item_title'] ?? 'Purchase'); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo ucfirst($payment['item_type']); ?></span></td>
                                                <td>
                                                    <span class="badge <?php echo (isset($payment['payment_type']) && $payment['payment_type'] === 'Full') ? 'bg-success' : 'bg-info text-dark'; ?>">
                                                        <?php echo $payment['payment_type'] ?? 'Full'; ?>
                                                    </span>
                                                </td>
                                                <td class="fw-bold text-primary">₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td><code><?php echo htmlspecialchars($payment['razorpay_order_id']); ?></code></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo $payment['status'] === 'Success' ? 'bg-success' : ($payment['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger'); 
                                                    ?>"><?php echo $payment['status']; ?></span>
                                                </td>
                                                <td><?php echo date('d M, Y', strtotime($payment['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 5. Profile Settings -->
                <?php if ($tab === 'profile'): ?>
                    <div class="card border-0 shadow-sm bg-white p-4 rounded-4 mb-4">
                        <h4 class="fw-bold mb-4 text-dark"><i class="fa-solid fa-user-gear text-primary me-2"></i>My Profile Settings</h4>
                        
                        <div class="row justify-content-center">
                            <!-- Update Profile Form -->
                            <div class="col-md-8 mb-4">
                                <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">Update Personal Details</h6>
                                <form action="dashboard.php" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="update_profile">
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <small class="text-muted fs-8">Registered email cannot be modified.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label fw-semibold">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mobile_number" class="form-label fw-semibold">Mobile Number</label>
                                        <input type="tel" class="form-control" id="mobile_number" name="mobile_number" value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 mt-2">Save Profile Details</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
