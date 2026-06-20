<?php
// Admin Panel Header
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';

// Access Control check
if (!isset($_SESSION['admin_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$adminUsername = $_SESSION['admin_username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAVA LMS - Admin Panel</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            transition: var(--transition);
            border-radius: 8px;
            margin: 4px 12px;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            color: #fff;
            background-color: var(--primary);
            text-decoration: none;
        }
        .main-content {
            background-color: var(--bg-gray);
            min-height: 100vh;
            padding: 30px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0 bg-dark min-vh-100 position-sticky top-0" style="z-index: 1000; height: 100vh; overflow-y: auto;">
            <div class="text-center py-4 border-bottom border-secondary mb-3">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="text-white fs-4 fw-bold text-decoration-none">
                    <i class="fa-solid fa-graduation-cap me-2 text-primary"></i>CAVA LMS
                </a>
                <span class="badge bg-secondary mt-2">Admin Portal</span>
            </div>
            
            <nav class="nav flex-column">
                <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-folder-open"></i> Categories
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/courses.php" class="admin-nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['courses.php', 'videos.php']) ? 'active' : ''; ?>">
                    <i class="fa-solid fa-book-open"></i> Courses & Videos
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/webinars.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'webinars.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-video"></i> Webinars
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/events.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-calendar-days"></i> Events
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/users.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-users"></i> Users
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/enrollments.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'enrollments.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-user-check"></i> Enrollments
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/payments.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-credit-card"></i> Payments
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/queries.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'queries.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-circle-question"></i> Queries
                </a>
                <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="admin-nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fa-solid fa-gears"></i> Settings
                </a>
                <hr class="text-secondary mx-3">
                <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="admin-nav-link text-danger">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </nav>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 px-0">
            <!-- Navbar top -->
            <nav class="navbar navbar-expand navbar-light bg-white border-bottom py-3 px-4 sticky-top shadow-sm" style="top: 0; z-index: 999;">
                <div class="container-fluid">
                    <span class="navbar-text fw-semibold text-dark">
                        Welcome back, <strong class="text-primary"><?php echo htmlspecialchars($adminUsername); ?></strong>
                    </span>
                    <div class="navbar-nav ms-auto">
                        <a href="<?php echo SITE_URL; ?>/index.php" target="_blank" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                            <i class="fa-solid fa-globe me-1"></i> Visit Website
                        </a>
                    </div>
                </div>
            </nav>
            
            <div class="main-content">
                <?php display_flash_message(); ?>
