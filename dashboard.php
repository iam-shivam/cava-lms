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

// Handle Profile Updates & Avatar Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        set_flash_message('danger', 'CSRF verification failed.');
        header("Location: dashboard.php?tab=profile");
        exit;
    }

    if ($_POST['action'] === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $mobile   = trim($_POST['mobile_number'] ?? '');

        if (empty($fullName) || empty($mobile)) {
            set_flash_message('danger', 'All profile fields are required.');
        } else {
            User::updateProfile($userId, $fullName, $mobile);
            $_SESSION['user_name'] = $fullName;
            set_flash_message('success', 'Profile updated successfully.');
        }

        // Handle avatar upload (on same form submission)
        if (!empty($_FILES['profile_picture']['name'])) {
            $file    = $_FILES['profile_picture'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024; // 2 MB

            if (!in_array($file['type'], $allowed)) {
                set_flash_message('danger', 'Profile picture must be JPG, PNG, GIF or WebP.');
            } elseif ($file['size'] > $maxSize) {
                set_flash_message('danger', 'Profile picture must be under 2 MB.');
            } else {
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . substr($userId, 0, 8) . '_' . time() . '.' . $ext;
                $destDir  = BASE_PATH . '/uploads/avatars/';
                if (!is_dir($destDir)) mkdir($destDir, 0775, true);

                if (move_uploaded_file($file['tmp_name'], $destDir . $filename)) {
                    // Delete old avatar if it exists
                    if (!empty($user['profile_picture'])) {
                        $oldFile = $destDir . $user['profile_picture'];
                        if (file_exists($oldFile)) @unlink($oldFile);
                    }
                    User::updateAvatar($userId, $filename);
                    $_SESSION['user_avatar'] = $filename;
                    set_flash_message('success', 'Profile picture updated!');
                } else {
                    set_flash_message('danger', 'Failed to upload profile picture.');
                }
            }
        }
    }

    header("Location: dashboard.php?tab=profile");
    exit;
}

// Fetch user data
$purchasedCourses    = Course::getEnrolledCourses($userId);
$registeredWebinars  = Webinar::getRegisteredWebinars($userId);
$myQueries           = Query::getByUser($userId);
$myPayments          = Payment::getPaymentsByUser($userId);

// Summary counts for stat cards
$totalPaid = array_sum(array_column(
    array_filter($myPayments, fn($p) => $p['status'] === 'Success'),
    'amount'
));

// User initials helper
$nameParts = explode(' ', trim($user['full_name']));
$initials  = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

// Avatar URL helper
$avatarUrl = '';
if (!empty($user['profile_picture'])) {
    $avatarPath = BASE_PATH . '/uploads/avatars/' . $user['profile_picture'];
    if (file_exists($avatarPath)) {
        $avatarUrl = SITE_URL . '/uploads/avatars/' . $user['profile_picture'];
    }
}

$csrfToken = generate_csrf_token();
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="dashboard-wrapper">
    <div class="container">

        <!-- Welcome Banner -->
        <div class="dash-welcome-banner mb-4 animate-fade-in-up">
            <div class="d-flex align-items-center gap-4">
                <?php if ($avatarUrl): ?>
                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" class="dash-avatar">
                <?php else: ?>
                    <div class="dash-avatar-initials"><?php echo $initials; ?></div>
                <?php endif; ?>
                <div>
                    <h2 class="fw-bold mb-1">Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
                    <p>Track your enrolled courses, webinars, and support queries from here.</p>
                </div>
                <div class="ms-auto d-none d-md-block">
                    <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background:rgba(255,255,255,0.2); color:#fff; font-size:0.8rem;">
                        <i class="fa-solid fa-circle-check me-1 text-success"></i> Account Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="dash-stat-card animate-fade-in-up">
                    <div class="dash-stat-icon purple"><i class="fa-solid fa-book-open"></i></div>
                    <div>
                        <div class="dash-stat-value"><?php echo count($purchasedCourses); ?></div>
                        <div class="dash-stat-label">Enrolled Courses</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dash-stat-card animate-fade-in-up">
                    <div class="dash-stat-icon blue"><i class="fa-solid fa-video"></i></div>
                    <div>
                        <div class="dash-stat-value"><?php echo count($registeredWebinars); ?></div>
                        <div class="dash-stat-label">Webinars</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dash-stat-card animate-fade-in-up">
                    <div class="dash-stat-icon amber"><i class="fa-solid fa-circle-question"></i></div>
                    <div>
                        <div class="dash-stat-value"><?php echo count($myQueries); ?></div>
                        <div class="dash-stat-label">Queries</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="dash-stat-card animate-fade-in-up">
                    <div class="dash-stat-icon green"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                    <div>
                        <div class="dash-stat-value">₹<?php echo number_format($totalPaid, 0); ?></div>
                        <div class="dash-stat-label">Total Spent</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <div class="dashboard-sidebar">
                    <!-- User Identity -->
                    <div class="dash-sidebar-user">
                        <?php if ($avatarUrl): ?>
                            <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Avatar" class="dash-sidebar-avatar">
                        <?php else: ?>
                            <div class="dash-sidebar-avatar-initials"><?php echo $initials; ?></div>
                        <?php endif; ?>
                        <div class="overflow-hidden">
                            <div class="dash-sidebar-name text-truncate"><?php echo htmlspecialchars($user['full_name']); ?></div>
                            <div class="dash-sidebar-role">Student</div>
                        </div>
                    </div>

                    <div class="dash-section-label">Workspace</div>
                    <a href="dashboard.php?tab=courses" class="dashboard-menu-link <?php echo $tab === 'courses' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-book-open"></i> My Courses
                        <span class="menu-badge"><?php echo count($purchasedCourses); ?></span>
                    </a>
                    <a href="dashboard.php?tab=webinars" class="dashboard-menu-link <?php echo $tab === 'webinars' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-video"></i> My Webinars
                        <span class="menu-badge"><?php echo count($registeredWebinars); ?></span>
                    </a>
                    <a href="dashboard.php?tab=queries" class="dashboard-menu-link <?php echo $tab === 'queries' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-circle-question"></i> Query History
                        <span class="menu-badge"><?php echo count($myQueries); ?></span>
                    </a>
                    <a href="dashboard.php?tab=payments" class="dashboard-menu-link <?php echo $tab === 'payments' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-credit-card"></i> Order History
                        <span class="menu-badge"><?php echo count($myPayments); ?></span>
                    </a>

                    <div class="dash-section-label">Account</div>
                    <a href="dashboard.php?tab=profile" class="dashboard-menu-link <?php echo $tab === 'profile' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-id-card"></i> My Profile
                    </a>
                    <!-- Sign Out removed from sidebar — use the top-right navbar dropdown instead -->
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-lg-9">

                <!-- 1. Enrolled Courses -->
                <?php if ($tab === 'courses'): ?>
                    <div class="dash-content-card">
                        <div class="d-flex align-items-center mb-4">
                            <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>My Enrolled Courses</h4>
                            <?php if (!empty($purchasedCourses)): ?>
                                <a href="courses.php" class="btn btn-outline-primary btn-sm rounded-pill ms-auto px-3">Browse More</a>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($purchasedCourses)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-book-open-reader fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-3">You haven't enrolled in any courses yet.</p>
                                <a href="courses.php" class="btn btn-primary rounded-pill px-4">Browse Courses</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($purchasedCourses as $course):
                                $thumbnailUrl = 'https://placehold.co/160x120/6f42c1/ffffff?text=Course';
                                if ($course['thumbnail']) {
                                    $thumbnailUrl = (file_exists(BASE_PATH . '/uploads/' . $course['thumbnail']))
                                        ? SITE_URL . '/uploads/' . $course['thumbnail']
                                        : SITE_URL . '/assets/images/' . $course['thumbnail'];
                                }

                                $isExpired      = $course['expiry_date'] && strtotime($course['expiry_date']) < time();
                                $effectiveStatus = $course['enrollment_status'] ?? 'Active';
                                if ($isExpired) $effectiveStatus = 'Expired';

                                $courseVideosCount    = intval(DB::fetch("SELECT COUNT(id) as count FROM course_videos WHERE course_id = ?", [$course['id']])['count']);
                                $courseCompletedCount = intval(DB::fetch("SELECT COUNT(id) as count FROM user_video_progress WHERE user_id = ? AND course_id = ? AND status = 'completed'", [$userId, $course['id']])['count']);
                                $courseProgressPercent = ($courseVideosCount > 0) ? min(100, round(($courseCompletedCount / $courseVideosCount) * 100)) : 0;
                            ?>
                            <div class="dash-course-row">
                                <img src="<?php echo $thumbnailUrl; ?>" alt="thumbnail" class="dash-course-thumb"
                                     onerror="this.src='https://placehold.co/160x120/6f42c1/ffffff?text=Course'">
                                <div class="dash-course-body">
                                    <div class="dash-course-title"><?php echo htmlspecialchars($course['title']); ?></div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="fs-8 text-muted">Progress</span>
                                        <span class="fs-8 fw-bold text-primary"><?php echo $courseProgressPercent; ?>%</span>
                                    </div>
                                    <div class="progress" style="height:5px; border-radius:3px;">
                                        <div class="progress-bar bg-success" style="width:<?php echo $courseProgressPercent; ?>%;"></div>
                                    </div>
                                    <?php if ($effectiveStatus === 'Pending'): ?>
                                        <small class="text-warning fw-semibold d-block mt-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Partially Paid — <a href="course.php?slug=<?php echo $course['slug']; ?>">Pay Balance</a></small>
                                    <?php elseif ($effectiveStatus === 'Expired'): ?>
                                        <small class="text-danger fw-semibold d-block mt-1"><i class="fa-solid fa-lock me-1"></i>Access Expired — <a href="course.php?slug=<?php echo $course['slug']; ?>">Renew</a></small>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-shrink-0">
                                    <?php if ($effectiveStatus === 'Expired'): ?>
                                        <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled><i class="fa-solid fa-lock me-1"></i>Locked</button>
                                    <?php else: ?>
                                        <a href="course_play.php?slug=<?php echo $course['slug']; ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                            <i class="fa-solid fa-circle-play me-1"></i>
                                            <?php echo $courseProgressPercent > 0 ? 'Continue' : 'Start'; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- 2. Enrolled Webinars -->
                <?php if ($tab === 'webinars'): ?>
                    <div class="dash-content-card">
                        <div class="d-flex align-items-center mb-4">
                            <h4 class="fw-bold text-dark mb-0"><i class="fa-solid fa-video text-primary me-2"></i>My Registered Webinars</h4>
                            <?php if (!empty($registeredWebinars)): ?>
                                <a href="webinars.php" class="btn btn-outline-primary btn-sm rounded-pill ms-auto px-3">Browse More</a>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($registeredWebinars)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-ticket-simple fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-3">No upcoming live webinar registrations found.</p>
                                <a href="webinars.php" class="btn btn-primary rounded-pill px-4">Browse Webinars</a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($registeredWebinars as $webinar):
                                    $webDate = date('d M, Y', strtotime($webinar['date']));
                                    $webTime = date('h:i A', strtotime($webinar['time']));
                                    $isPastWebinar = strtotime($webinar['date'] . ' ' . $webinar['time']) < time();
                                ?>
                                <div class="col-md-6">
                                    <div class="border rounded-3 p-3 h-100 d-flex flex-column" style="border-color:#f3f4f6!important;">
                                        <?php if ($isPastWebinar): ?>
                                            <span class="badge bg-secondary-light text-secondary rounded-pill px-3 py-1 fw-semibold align-self-start mb-2">
                                                <i class="fa-solid fa-video-slash me-1"></i> Closed
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-light text-success rounded-pill px-3 py-1 fw-semibold align-self-start mb-2">
                                                <i class="fa-solid fa-circle-check me-1"></i> Registered
                                            </span>
                                        <?php endif; ?>
                                        <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($webinar['title']); ?></h6>
                                        <p class="text-muted small mb-0 flex-grow-1"><?php echo htmlspecialchars(mb_strimwidth($webinar['description'], 0, 100, '...')); ?></p>
                                        <div class="mt-3 pt-2 border-top d-flex justify-content-between text-muted small">
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
                    <div class="dash-content-card">
                        <h4 class="fw-bold text-dark mb-4"><i class="fa-solid fa-circle-question text-primary me-2"></i>My Query History</h4>

                        <?php if (empty($myQueries)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-comments fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">You haven't submitted any queries yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Query Message</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($myQueries as $query): ?>
                                        <tr>
                                            <td style="max-width:400px; white-space:normal; word-wrap:break-word;">
                                                <div class="fw-medium text-dark"><?php echo htmlspecialchars($query['query_message']); ?></div>
                                                <?php if ($query['resolved_at']): ?>
                                                    <small class="text-success d-block mt-1">Resolved: <?php echo date('d M, Y', strtotime($query['resolved_at'])); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M, Y', strtotime($query['created_at'])); ?></td>
                                            <td>
                                                <?php if ($query['status'] === 'Resolved'): ?>
                                                    <span class="badge bg-success rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Resolved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark rounded-pill"><i class="fa-regular fa-clock me-1"></i>Pending</span>
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
                    <div class="dash-content-card">
                        <h4 class="fw-bold text-dark mb-4"><i class="fa-solid fa-credit-card text-primary me-2"></i>Order & Transaction Logs</h4>

                        <?php if (empty($myPayments)): ?>
                            <div class="text-center py-5">
                                <i class="fa-solid fa-receipt fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">No transactions found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item</th>
                                            <th>Type</th>
                                            <th>Payment</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($myPayments as $payment): ?>
                                        <tr>
                                            <td class="fw-semibold text-dark"><?php echo htmlspecialchars($payment['item_title'] ?? 'Purchase'); ?></td>
                                            <td><span class="badge bg-secondary rounded-pill"><?php echo ucfirst($payment['item_type']); ?></span></td>
                                            <td>
                                                <span class="badge <?php echo (isset($payment['payment_type']) && $payment['payment_type'] === 'Full') ? 'bg-success' : 'bg-info text-dark'; ?> rounded-pill">
                                                    <?php echo $payment['payment_type'] ?? 'Full'; ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-primary">₹<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php echo $payment['status'] === 'Success' ? 'bg-success' : ($payment['status'] === 'Pending' ? 'bg-warning text-dark' : 'bg-danger'); ?> rounded-pill">
                                                    <?php echo $payment['status']; ?>
                                                </span>
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
                    <div class="dash-content-card">
                        <h4 class="fw-bold text-dark mb-4"><i class="fa-solid fa-user-gear text-primary me-2"></i>My Profile Settings</h4>

                        <form action="dashboard.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <input type="hidden" name="action" value="update_profile">

                            <!-- Avatar Upload -->
                            <div class="text-center mb-4">
                                <div class="avatar-upload-area">
                                    <?php if ($avatarUrl): ?>
                                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Profile Picture" id="avatar-preview">
                                    <?php else: ?>
                                        <div class="avatar-big-initials" id="avatar-initials-preview"><?php echo $initials; ?></div>
                                    <?php endif; ?>
                                    <label for="profile_picture" class="avatar-upload-btn" title="Change photo">
                                        <i class="fa-solid fa-camera"></i>
                                    </label>
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="d-none" onchange="previewAvatar(this)">
                                </div>
                                <small class="text-muted d-block">Click the camera icon to change your photo (max 2 MB)</small>
                            </div>

                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Email Address</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                        <small class="text-muted">Registered email cannot be modified.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label fw-semibold">Full Name</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name"
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="mobile_number" class="form-label fw-semibold">Mobile Number</label>
                                        <input type="tel" class="form-control" id="mobile_number" name="mobile_number"
                                               value="<?php echo htmlspecialchars($user['mobile_number']); ?>" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 mt-2">
                                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Profile
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

            </div><!-- /col-lg-9 -->
        </div><!-- /row -->
    </div><!-- /container -->
</div><!-- /dashboard-wrapper -->

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var preview = document.getElementById('avatar-preview');
            var initials = document.getElementById('avatar-initials-preview');
            if (!preview) {
                // Create an img element and replace initials div
                preview = document.createElement('img');
                preview.id = 'avatar-preview';
                preview.alt = 'Profile Picture';
                if (initials) initials.replaceWith(preview);
            }
            preview.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
