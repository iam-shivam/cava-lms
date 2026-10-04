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
$currentAdminPage = basename($_SERVER['PHP_SELF']);
$adminPageTitles = [
    'dashboard.php' => 'Dashboard',
    'categories.php' => 'Categories',
    'courses.php' => 'Courses',
    'videos.php' => 'Course Videos',
    'video_edit.php' => 'Edit Video',
    'section_edit.php' => 'Edit Course Section',
    'webinars.php' => 'Webinars',
    'events.php' => 'Events',
    'users.php' => 'Users',
    'enrollments.php' => 'Enrollments',
    'payments.php' => 'Payments',
    'queries.php' => 'Support Queries',
    'email_templates.php' => 'Email Templates',
    'email_template_add.php' => 'Add Email Template',
    'email_template_edit.php' => 'Edit Email Template',
    'settings.php' => 'Settings',
];
$adminPageTitle = $adminPageTitles[$currentAdminPage] ?? 'Admin Panel';
$adminInitial = strtoupper(substr(trim((string) $adminUsername), 0, 1));
if ($adminInitial === '') {
    $adminInitial = 'A';
}

$isDashboardPage = $currentAdminPage === 'dashboard.php';
$isCategoryPage = $currentAdminPage === 'categories.php';
$isCoursePage = in_array($currentAdminPage, ['courses.php', 'videos.php', 'video_edit.php', 'section_edit.php'], true);
$isWebinarPage = $currentAdminPage === 'webinars.php';
$isEventPage = $currentAdminPage === 'events.php';
$isUserPage = $currentAdminPage === 'users.php';
$isEnrollmentPage = $currentAdminPage === 'enrollments.php';
$isPaymentPage = $currentAdminPage === 'payments.php';
$isQueryPage = $currentAdminPage === 'queries.php';
$isEmailPage = in_array($currentAdminPage, ['email_templates.php', 'email_template_add.php', 'email_template_edit.php'], true);
$isSettingsPage = $currentAdminPage === 'settings.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($adminPageTitle); ?> | CAVA Studio Admin</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- CAVA Studio typography -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="icon" type="image/jpeg" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">

    <!-- Legacy base remains first for a safe rollback; Studio layers override it. -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-tokens.css?v=2.0" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-components.css?v=2.0" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-admin.css?v=2.0" rel="stylesheet">
</head>
<body class="cava-studio-admin">
    <a class="studio-skip-link" href="#admin-main-content">Skip to main content</a>

    <div class="studio-admin-shell">
        <aside class="offcanvas-lg offcanvas-start studio-admin-sidebar" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-body">
                <div class="studio-admin-sidebar__brand">
                    <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="studio-admin-brand" id="adminSidebarLabel">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" class="studio-admin-brand__logo">
                        <span class="studio-admin-brand__copy">
                            <span class="studio-admin-brand__name">CAVA Studio</span>
                            <span class="studio-admin-brand__label">Admin workspace</span>
                        </span>
                    </a>
                    <button type="button" class="studio-admin-sidebar__close" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close admin navigation">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="studio-admin-profile">
                    <span class="studio-admin-profile__avatar" aria-hidden="true"><?php echo htmlspecialchars($adminInitial); ?></span>
                    <span class="studio-admin-profile__copy">
                        <span class="studio-admin-profile__name"><?php echo htmlspecialchars($adminUsername); ?></span>
                        <span class="studio-admin-profile__role">Administrator</span>
                    </span>
                </div>

                <nav class="studio-admin-nav" aria-label="Admin navigation">
                    <span class="studio-admin-nav__label">Overview</span>
                    <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="admin-nav-link<?php echo $isDashboardPage ? ' active' : ''; ?>"<?php echo $isDashboardPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-gauge-high" aria-hidden="true"></i><span>Dashboard</span>
                    </a>

                    <span class="studio-admin-nav__label">Learning content</span>
                    <a href="<?php echo SITE_URL; ?>/admin/categories.php" class="admin-nav-link<?php echo $isCategoryPage ? ' active' : ''; ?>"<?php echo $isCategoryPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-folder-open" aria-hidden="true"></i><span>Categories</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/courses.php" class="admin-nav-link<?php echo $isCoursePage ? ' active' : ''; ?>"<?php echo $isCoursePage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-book-open" aria-hidden="true"></i><span>Courses &amp; Videos</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/webinars.php" class="admin-nav-link<?php echo $isWebinarPage ? ' active' : ''; ?>"<?php echo $isWebinarPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-video" aria-hidden="true"></i><span>Webinars</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/events.php" class="admin-nav-link<?php echo $isEventPage ? ' active' : ''; ?>"<?php echo $isEventPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-calendar-days" aria-hidden="true"></i><span>Events</span>
                    </a>

                    <span class="studio-admin-nav__label">Operations</span>
                    <a href="<?php echo SITE_URL; ?>/admin/users.php" class="admin-nav-link<?php echo $isUserPage ? ' active' : ''; ?>"<?php echo $isUserPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-users" aria-hidden="true"></i><span>Users</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/enrollments.php" class="admin-nav-link<?php echo $isEnrollmentPage ? ' active' : ''; ?>"<?php echo $isEnrollmentPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-user-check" aria-hidden="true"></i><span>Enrollments</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/payments.php" class="admin-nav-link<?php echo $isPaymentPage ? ' active' : ''; ?>"<?php echo $isPaymentPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-credit-card" aria-hidden="true"></i><span>Payments</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/queries.php" class="admin-nav-link<?php echo $isQueryPage ? ' active' : ''; ?>"<?php echo $isQueryPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-circle-question" aria-hidden="true"></i><span>Queries</span>
                    </a>

                    <span class="studio-admin-nav__label">System</span>
                    <a href="<?php echo SITE_URL; ?>/admin/email_templates.php" class="admin-nav-link<?php echo $isEmailPage ? ' active' : ''; ?>"<?php echo $isEmailPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i><span>Email Templates</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="admin-nav-link<?php echo $isSettingsPage ? ' active' : ''; ?>"<?php echo $isSettingsPage ? ' aria-current="page"' : ''; ?>>
                        <i class="fa-solid fa-gears" aria-hidden="true"></i><span>Settings</span>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="admin-nav-link admin-nav-link--logout">
                        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Logout</span>
                    </a>
                </nav>
            </div>
        </aside>

        <div class="studio-admin-frame">
            <header class="studio-admin-topbar">
                <div class="studio-admin-topbar__leading">
                    <button type="button" class="studio-admin-sidebar-toggle" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open admin navigation">
                        <i class="fa-solid fa-bars" aria-hidden="true"></i>
                    </button>
                    <div class="studio-admin-topbar__copy">
                        <span class="studio-admin-topbar__eyebrow">CAVA Studio / Admin</span>
                        <h1 class="studio-admin-topbar__title"><?php echo htmlspecialchars($adminPageTitle); ?></h1>
                    </div>
                </div>

                <div class="studio-admin-topbar__actions">
                    <div class="studio-admin-topbar__user" aria-label="Signed in administrator">
                        <span class="studio-admin-topbar__avatar" aria-hidden="true"><?php echo htmlspecialchars($adminInitial); ?></span>
                        <span class="small fw-semibold"><?php echo htmlspecialchars($adminUsername); ?></span>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/index.php" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>Visit website</span>
                    </a>
                </div>
            </header>

            <main class="main-content studio-admin-main" id="admin-main-content" tabindex="-1">
                <?php display_flash_message(); ?>
