<?php
// Layout Header Component
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/db.php';

// Fetch dynamic settings from database if possible
$siteTitle = 'CAVA LMS Portal';
try {
    $titleSetting = DB::fetch("SELECT setting_value FROM settings WHERE setting_key = 'site_title'");
    if ($titleSetting) {
        $siteTitle = $titleSetting['setting_value'];
    }
} catch (Exception $e) {
    // Fail silently if DB not seeded yet
}

$isUserLoggedIn = isset($_SESSION['user_id']);
$isAdminLoggedIn = isset($_SESSION['admin_id']);
$userName = $isUserLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($siteTitle); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>/index.php">
                <i class="fa-solid fa-graduation-cap me-2"></i>CAVA LMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/webinars.php">Webinars</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/support.php">Support</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-3">

                    <?php if ($isUserLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary dropdown-toggle d-flex align-items-center gap-2" type="button" id="userMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-user-circle fs-5"></i>
                                <span>Hi, <?php echo htmlspecialchars($userName); ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userMenuButton">
                                <li>
                                    <a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>/dashboard.php">
                                        <i class="fa-solid fa-gauge me-2 text-primary"></i>My Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>/dashboard.php?tab=profile">
                                        <i class="fa-solid fa-id-card me-2 text-primary"></i>My Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item py-2 text-danger" href="<?php echo SITE_URL; ?>/logout.php">
                                        <i class="fa-solid fa-sign-out-alt me-2"></i>Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-outline-primary btn-sm px-3">Login</a>
                        <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-primary btn-sm px-3">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mt-3">
        <?php display_flash_message(); ?>
    </div>
