<?php
// Layout Header Component
require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(dirname(__DIR__)) . '/config/db.php';

// Fetch dynamic settings from database if possible
$siteTitle = get_setting('site_title', 'CAVA LMS Portal');
$siteDescription = 'CAVA LMS Portal – Learn immigration, career, and skills courses online at your own pace. Expert-led video courses, live webinars, and more.';

// Per-page SEO — pages can set $pageTitle, $pageDescription, $pageImage before including this header
$metaTitle       = isset($pageTitle)       ? htmlspecialchars($pageTitle) . ' | ' . htmlspecialchars($siteTitle) : htmlspecialchars($siteTitle);
$metaDescription = isset($pageDescription) ? htmlspecialchars($pageDescription) : htmlspecialchars($siteDescription);
$metaImage       = isset($pageImage)       ? $pageImage : SITE_URL . '/assets/images/og-default.png';
$canonicalUrl    = SITE_URL . strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$isUserLoggedIn = isset($_SESSION['user_id']);
$isAdminLoggedIn = isset($_SESSION['admin_id']);
$userName = $isUserLoggedIn ? ($_SESSION['user_name'] ?? 'User') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $metaTitle; ?></title>

    <!-- Resource Preconnect Hints for Maximum Loading Performance -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo $metaDescription; ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">

    <!-- Open Graph (Facebook, WhatsApp, LinkedIn) -->
    <meta property="og:type"        content="website">
    <meta property="og:title"       content="<?php echo $metaTitle; ?>">
    <meta property="og:description" content="<?php echo $metaDescription; ?>">
    <meta property="og:image"       content="<?php echo htmlspecialchars($metaImage); ?>">
    <meta property="og:url"         content="<?php echo htmlspecialchars($canonicalUrl); ?>">
    <meta property="og:site_name"   content="<?php echo htmlspecialchars($siteTitle); ?>">

    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:title"       content="<?php echo $metaTitle; ?>">
    <meta name="twitter:description" content="<?php echo $metaDescription; ?>">
    <meta name="twitter:image"       content="<?php echo htmlspecialchars($metaImage); ?>">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Style Sheet -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/jpeg" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">

    <!-- Premium Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Premium Design System CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/landing-premium.css" rel="stylesheet">
</head>
<body>

    <!-- =========================================================================
         Premium 3-Zone Navigation Header
         ========================================================================= -->
    <header class="cava-nav-header" id="cava-nav-header" role="banner">
        <div class="cava-nav-inner">
            <!-- Zone 1: Logo -->
            <a class="cava-nav-logo-link" href="<?php echo SITE_URL; ?>/index.php" aria-label="CAVA LMS Home">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="CAVA LMS Logo" class="cava-nav-logo-img">
                <span class="cava-nav-brand">CAVA LMS</span>
            </a>

            <!-- Zone 2: Center Navigation (Desktop) -->
            <nav class="cava-nav-center" aria-label="Main navigation">
                <ul class="cava-nav-links" role="list">
                    <li>
                        <a href="<?php echo SITE_URL; ?>/index.php"
                           class="cava-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? ' cava-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'aria-current="page"' : ''; ?>>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/courses.php"
                           class="cava-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'courses.php') ? ' cava-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'courses.php') ? 'aria-current="page"' : ''; ?>>
                            Courses
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/events.php"
                           class="cava-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'events.php') ? ' cava-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'events.php') ? 'aria-current="page"' : ''; ?>>
                            Events
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/support.php"
                           class="cava-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'support.php') ? ' cava-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'support.php') ? 'aria-current="page"' : ''; ?>>
                            Support
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Zone 3: Right Actions -->
            <div class="cava-nav-right">
                <?php if ($isUserLoggedIn): ?>
                    <?php
                    // Resolve profile picture for navbar
                    $navAvatarUrl = '';
                    if (!empty($_SESSION['user_avatar'])) {
                        $navAvatarFile = BASE_PATH . '/uploads/avatars/' . $_SESSION['user_avatar'];
                        if (file_exists($navAvatarFile)) {
                            $navAvatarUrl = SITE_URL . '/uploads/avatars/' . $_SESSION['user_avatar'];
                        }
                    }
                    if (!$navAvatarUrl && empty($_SESSION['user_avatar_checked'])) {
                        try {
                            $navUser = DB::fetch("SELECT profile_picture FROM users WHERE id = ?", [$_SESSION['user_id']]);
                            if ($navUser && !empty($navUser['profile_picture'])) {
                                $navAvatarFile = BASE_PATH . '/uploads/avatars/' . $navUser['profile_picture'];
                                if (file_exists($navAvatarFile)) {
                                    $navAvatarUrl = SITE_URL . '/uploads/avatars/' . $navUser['profile_picture'];
                                    $_SESSION['user_avatar'] = $navUser['profile_picture'];
                                }
                            }
                            $_SESSION['user_avatar_checked'] = true;
                        } catch (Exception $e) {}
                    }
                    $navNameParts = explode(' ', trim($userName));
                    $navInitials  = strtoupper(substr($navNameParts[0], 0, 1) . (isset($navNameParts[1]) ? substr($navNameParts[1], 0, 1) : ''));
                    ?>
                    <div class="dropdown cava-user-dropdown">
                        <button class="cava-user-btn" type="button" id="cavaUserMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                            <?php if ($navAvatarUrl): ?>
                                <img src="<?php echo htmlspecialchars($navAvatarUrl); ?>" alt="<?php echo htmlspecialchars($userName); ?> avatar" class="cava-user-avatar-img">
                            <?php else: ?>
                                <span class="cava-user-avatar-initials" aria-hidden="true"><?php echo $navInitials; ?></span>
                            <?php endif; ?>
                            <span class="cava-user-name">Hi, <?php echo htmlspecialchars($userName); ?></span>
                            <i class="fa-solid fa-chevron-down cava-user-chevron" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end cava-user-dropdown-menu" aria-labelledby="cavaUserMenuBtn">
                            <li class="cava-dropdown-header">
                                <span class="cava-dropdown-username"><?php echo htmlspecialchars($userName); ?></span>
                                <span class="cava-dropdown-role">Student Account</span>
                            </li>
                            <li><hr class="cava-dropdown-divider"></li>
                            <li>
                                <a class="cava-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard.php">
                                    <i class="fa-solid fa-chart-pie" aria-hidden="true"></i>
                                    My Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="cava-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard.php?tab=profile">
                                    <i class="fa-solid fa-user-gear" aria-hidden="true"></i>
                                    My Profile
                                </a>
                            </li>
                            <li><hr class="cava-dropdown-divider"></li>
                            <li>
                                <a class="cava-dropdown-item cava-dropdown-item--danger" href="<?php echo SITE_URL; ?>/logout.php">
                                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="cava-nav-login-btn">Login</a>
                <?php endif; ?>

                <!-- Mobile Hamburger -->
                <button class="cava-nav-hamburger" id="cavaNavHamburger"
                        aria-label="Open navigation menu"
                        aria-expanded="false"
                        aria-controls="cavaMobileDrawer">
                    <span class="cava-hamburger-bar"></span>
                    <span class="cava-hamburger-bar"></span>
                    <span class="cava-hamburger-bar"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div class="cava-mobile-drawer" id="cavaMobileDrawer" role="dialog" aria-modal="false" aria-label="Mobile navigation" hidden>
        <div class="cava-mobile-drawer-inner">
            <div class="cava-mobile-drawer-header">
                <a class="cava-nav-logo-link" href="<?php echo SITE_URL; ?>/index.php" aria-label="CAVA LMS Home">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="CAVA LMS Logo" class="cava-nav-logo-img">
                    <span class="cava-nav-brand">CAVA LMS</span>
                </a>
                <button class="cava-mobile-close" id="cavaMobileClose" aria-label="Close navigation menu">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>

            <nav aria-label="Mobile navigation">
                <ul class="cava-mobile-nav-links" role="list">
                    <li>
                        <a href="<?php echo SITE_URL; ?>/index.php"
                           class="cava-mobile-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? ' cava-mobile-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'aria-current="page"' : ''; ?>>
                            <i class="fa-solid fa-house" aria-hidden="true"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/courses.php"
                           class="cava-mobile-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'courses.php') ? ' cava-mobile-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'courses.php') ? 'aria-current="page"' : ''; ?>>
                            <i class="fa-solid fa-book-open" aria-hidden="true"></i> Courses
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/events.php"
                           class="cava-mobile-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'events.php') ? ' cava-mobile-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'events.php') ? 'aria-current="page"' : ''; ?>>
                            <i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Events
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo SITE_URL; ?>/support.php"
                           class="cava-mobile-nav-link<?php echo (basename($_SERVER['PHP_SELF']) === 'support.php') ? ' cava-mobile-nav-link--active' : ''; ?>"
                           <?php echo (basename($_SERVER['PHP_SELF']) === 'support.php') ? 'aria-current="page"' : ''; ?>>
                            <i class="fa-solid fa-headset" aria-hidden="true"></i> Support
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="cava-mobile-drawer-footer">
                <?php if ($isUserLoggedIn): ?>
                    <div class="cava-mobile-user-info">
                        <?php if ($navAvatarUrl ?? ''): ?>
                            <img src="<?php echo htmlspecialchars($navAvatarUrl); ?>" alt="Avatar" class="cava-mobile-user-avatar-img">
                        <?php else: ?>
                            <span class="cava-mobile-user-avatar-initials"><?php echo $navInitials ?? '?'; ?></span>
                        <?php endif; ?>
                        <div>
                            <div class="cava-mobile-user-name"><?php echo htmlspecialchars($userName); ?></div>
                            <div class="cava-mobile-user-role">Student Account</div>
                        </div>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="cava-mobile-cta-btn">My Dashboard</a>
                    <a href="<?php echo SITE_URL; ?>/logout.php" class="cava-mobile-logout-link">
                        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="<?php echo SITE_URL; ?>/login.php" class="cava-mobile-cta-btn">Login to CAVA</a>
                    <p class="cava-mobile-register-hint">
                        Don't have an account?
                        <a href="<?php echo SITE_URL; ?>/register.php">Create Account</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Mobile Overlay Backdrop -->
    <div class="cava-mobile-overlay" id="cavaMobileOverlay" aria-hidden="true"></div>

    <?php display_flash_message(); ?>
