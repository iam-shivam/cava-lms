<?php
// CAVA Studio shared public/student header.
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/db.php';

$siteTitle = get_setting('site_title', 'CAVA LMS Portal');
$siteDescription = 'CAVA LMS Portal — learn immigration, career, and skills courses online at your own pace.';
$metaTitle = isset($pageTitle) ? $pageTitle . ' | ' . $siteTitle : $siteTitle;
$metaDescription = $pageDescription ?? $siteDescription;
$metaImage = $pageImage ?? SITE_URL . '/assets/images/og-default.png';
$canonicalUrl = SITE_URL . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$isUserLoggedIn = isset($_SESSION['user_id']);
$userName = $isUserLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
$coursePages = ['courses.php', 'course.php', 'course_play.php'];
$authPages = ['login.php', 'register.php', 'otp_verify.php'];
$studentPages = ['dashboard.php', 'course_play.php', 'document_viewer.php', 'payment_process.php'];
$pageSlug = preg_replace('/[^a-z0-9-]/', '-', strtolower(pathinfo($currentPage, PATHINFO_FILENAME)));
$bodyClasses = ['cava-studio', 'studio-page-' . $pageSlug];
$legacyPageClassMap = [
    'index.php' => 'page-index',
    'courses.php' => 'page-courses',
    'course.php' => 'page-course-detail',
    'webinars.php' => 'page-webinars',
    'events.php' => 'page-events',
    'support.php' => 'page-support',
    'dashboard.php' => 'page-dashboard',
];
if (isset($legacyPageClassMap[$currentPage])) {
    $bodyClasses[] = $legacyPageClassMap[$currentPage];
}
if (in_array($currentPage, $authPages, true)) {
    $bodyClasses[] = 'studio-area-auth';
    $bodyClasses[] = 'page-auth';
}
if (in_array($currentPage, $studentPages, true)) {
    $bodyClasses[] = 'studio-area-student';
}
if (!empty($bodyClass)) {
    $bodyClasses[] = preg_replace('/[^a-zA-Z0-9 _-]/', '', $bodyClass);
}

$navAvatarUrl = '';
$navInitials = '?';
if ($isUserLoggedIn) {
    if (!empty($_SESSION['user_avatar'])) {
        $navAvatarFile = BASE_PATH . '/uploads/avatars/' . $_SESSION['user_avatar'];
        if (file_exists($navAvatarFile)) {
            $navAvatarUrl = SITE_URL . '/uploads/avatars/' . rawurlencode($_SESSION['user_avatar']);
        }
    }
    if (!$navAvatarUrl && empty($_SESSION['user_avatar_checked'])) {
        try {
            $navUser = DB::fetch('SELECT profile_picture FROM users WHERE id = ?', [$_SESSION['user_id']]);
            if ($navUser && !empty($navUser['profile_picture'])) {
                $navAvatarFile = BASE_PATH . '/uploads/avatars/' . $navUser['profile_picture'];
                if (file_exists($navAvatarFile)) {
                    $navAvatarUrl = SITE_URL . '/uploads/avatars/' . rawurlencode($navUser['profile_picture']);
                    $_SESSION['user_avatar'] = $navUser['profile_picture'];
                }
            }
            $_SESSION['user_avatar_checked'] = true;
        } catch (Exception $e) {
            // Navigation remains usable when optional avatar lookup fails.
        }
    }
    $navNameParts = preg_split('/\s+/', trim($userName));
    $navInitials = strtoupper(substr($navNameParts[0] ?? 'U', 0, 1) . (isset($navNameParts[1]) ? substr($navNameParts[1], 0, 1) : ''));
}

function studio_nav_active(array $pages, string $currentPage): string {
    return in_array($currentPage, $pages, true) ? ' cava-nav-link--active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#103428">
    <title><?php echo htmlspecialchars($metaTitle); ?></title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($metaImage); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($siteTitle); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($metaTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($metaImage); ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Legacy assets remain in place for a one-step rollback. -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css?v=2.2" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/landing-premium.css?v=2.2" rel="stylesheet">
    <!-- CAVA Studio presentation layer. Keep this order: tokens, components, area. -->
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-tokens.css?v=2.0" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-components.css?v=2.0" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-public.css?v=2.2" rel="stylesheet">
    <?php if (in_array($currentPage, $studentPages, true)): ?>
        <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-student.css?v=2.0" rel="stylesheet">
    <?php endif; ?>

    <link rel="icon" type="image/jpeg" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">
</head>
<body class="<?php echo htmlspecialchars(implode(' ', $bodyClasses)); ?>">
    <a class="studio-skip-link" href="#studio-main-content">Skip to main content</a>

    <header class="cava-nav-header" id="cava-nav-header" role="banner">
        <div class="cava-nav-inner">
            <a class="cava-nav-logo-link" href="<?php echo SITE_URL; ?>/index.php" aria-label="CAVA Studio home">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" class="cava-nav-logo-img">
                <span class="cava-nav-brand-wrap">
                    <span class="cava-nav-brand">CAVA Studio</span>
                    <span class="cava-nav-brand-note">Learn with direction</span>
                </span>
            </a>

            <nav class="cava-nav-center" aria-label="Primary navigation">
                <ul class="cava-nav-links" role="list">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="cava-nav-link<?php echo studio_nav_active(['index.php'], $currentPage); ?>"<?php echo $currentPage === 'index.php' ? ' aria-current="page"' : ''; ?>>Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/courses.php" class="cava-nav-link<?php echo studio_nav_active($coursePages, $currentPage); ?>"<?php echo in_array($currentPage, $coursePages, true) ? ' aria-current="page"' : ''; ?>>Courses</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/webinars.php" class="cava-nav-link<?php echo studio_nav_active(['webinars.php'], $currentPage); ?>"<?php echo $currentPage === 'webinars.php' ? ' aria-current="page"' : ''; ?>>Webinars</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/events.php" class="cava-nav-link<?php echo studio_nav_active(['events.php'], $currentPage); ?>"<?php echo $currentPage === 'events.php' ? ' aria-current="page"' : ''; ?>>Events</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/support.php" class="cava-nav-link<?php echo studio_nav_active(['support.php'], $currentPage); ?>"<?php echo $currentPage === 'support.php' ? ' aria-current="page"' : ''; ?>>Support</a></li>
                </ul>
            </nav>

            <div class="cava-nav-right">
                <?php if ($isUserLoggedIn): ?>
                    <div class="dropdown cava-user-dropdown">
                        <button class="cava-user-btn" type="button" id="cavaUserMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                            <?php if ($navAvatarUrl): ?>
                                <img src="<?php echo htmlspecialchars($navAvatarUrl); ?>" alt="" class="cava-user-avatar-img">
                            <?php else: ?>
                                <span class="cava-user-avatar-initials" aria-hidden="true"><?php echo htmlspecialchars($navInitials); ?></span>
                            <?php endif; ?>
                            <span class="cava-user-name"><?php echo htmlspecialchars($userName); ?></span>
                            <i class="fa-solid fa-chevron-down cava-user-chevron" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end cava-user-dropdown-menu" aria-labelledby="cavaUserMenuBtn">
                            <li class="cava-dropdown-header">
                                <span class="cava-dropdown-username"><?php echo htmlspecialchars($userName); ?></span>
                                <span class="cava-dropdown-role">Student account</span>
                            </li>
                            <li><hr class="cava-dropdown-divider"></li>
                            <li><a class="cava-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard.php"><i class="fa-solid fa-table-columns" aria-hidden="true"></i> Dashboard</a></li>
                            <li><a class="cava-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard.php?tab=profile"><i class="fa-regular fa-user" aria-hidden="true"></i> Profile</a></li>
                            <li><hr class="cava-dropdown-divider"></li>
                            <li><a class="cava-dropdown-item cava-dropdown-item--danger" href="<?php echo SITE_URL; ?>/logout.php"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="cava-nav-login-btn">Student login <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                <?php endif; ?>

                <button class="cava-nav-hamburger" id="cavaNavHamburger" type="button" aria-label="Open navigation menu" aria-expanded="false" aria-controls="cavaMobileDrawer">
                    <span class="cava-hamburger-bar"></span>
                    <span class="cava-hamburger-bar"></span>
                    <span class="cava-hamburger-bar"></span>
                </button>
            </div>
        </div>
    </header>

    <div class="cava-mobile-drawer" id="cavaMobileDrawer" role="dialog" aria-modal="false" aria-label="Mobile navigation" hidden>
        <div class="cava-mobile-drawer-inner">
            <div class="cava-mobile-drawer-header">
                <a class="cava-nav-logo-link" href="<?php echo SITE_URL; ?>/index.php" aria-label="CAVA Studio home">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" class="cava-nav-logo-img">
                    <span class="cava-nav-brand-wrap"><span class="cava-nav-brand">CAVA Studio</span><span class="cava-nav-brand-note">Learn with direction</span></span>
                </a>
                <button class="cava-mobile-close" id="cavaMobileClose" type="button" aria-label="Close navigation menu"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <nav aria-label="Mobile navigation">
                <ul class="cava-mobile-nav-links" role="list">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="cava-mobile-nav-link<?php echo $currentPage === 'index.php' ? ' cava-mobile-nav-link--active' : ''; ?>"><i class="fa-solid fa-house" aria-hidden="true"></i>Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/courses.php" class="cava-mobile-nav-link<?php echo in_array($currentPage, $coursePages, true) ? ' cava-mobile-nav-link--active' : ''; ?>"><i class="fa-solid fa-book-open" aria-hidden="true"></i>Courses</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/webinars.php" class="cava-mobile-nav-link<?php echo $currentPage === 'webinars.php' ? ' cava-mobile-nav-link--active' : ''; ?>"><i class="fa-solid fa-video" aria-hidden="true"></i>Webinars</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/events.php" class="cava-mobile-nav-link<?php echo $currentPage === 'events.php' ? ' cava-mobile-nav-link--active' : ''; ?>"><i class="fa-regular fa-calendar" aria-hidden="true"></i>Events</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/support.php" class="cava-mobile-nav-link<?php echo $currentPage === 'support.php' ? ' cava-mobile-nav-link--active' : ''; ?>"><i class="fa-regular fa-circle-question" aria-hidden="true"></i>Support</a></li>
                </ul>
            </nav>
            <div class="cava-mobile-drawer-footer">
                <?php if ($isUserLoggedIn): ?>
                    <div class="cava-mobile-user-info">
                        <?php if ($navAvatarUrl): ?>
                            <img src="<?php echo htmlspecialchars($navAvatarUrl); ?>" alt="" class="cava-mobile-user-avatar-img">
                        <?php else: ?>
                            <span class="cava-mobile-user-avatar-initials"><?php echo htmlspecialchars($navInitials); ?></span>
                        <?php endif; ?>
                        <div><div class="cava-mobile-user-name"><?php echo htmlspecialchars($userName); ?></div><div class="cava-mobile-user-role">Student account</div></div>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="cava-mobile-cta-btn">Open dashboard</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="cava-mobile-logout-link"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Logout</a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="cava-mobile-cta-btn">Student login</a>
                    <p class="cava-mobile-register-hint">New to CAVA? <a href="<?php echo SITE_URL; ?>/register.php">Create an account</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="cava-mobile-overlay" id="cavaMobileOverlay" aria-hidden="true"></div>

    <div id="studio-main-content" tabindex="-1">
        <?php display_flash_message(); ?>
