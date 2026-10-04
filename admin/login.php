<?php
// Admin Login Page
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/controllers/AuthController.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: " . SITE_URL . "/admin/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::adminLogin();
}

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>CAVA LMS - Admin Login</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link rel="icon" type="image/jpeg" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/logo.jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Legacy base remains first for a safe rollback; Studio layers override it. -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-tokens.css?v=2.0" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-components.css?v=2.0" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/cava-studio-admin.css?v=2.0" rel="stylesheet">
</head>
<body class="cava-studio-auth studio-admin-login">
    <a class="studio-skip-link" href="#admin-login-form">Skip to sign in</a>
    <?php display_flash_message(); ?>

    <main class="studio-admin-auth-shell">
        <section class="studio-admin-auth-aside" aria-labelledby="adminWelcomeTitle">
            <a href="<?php echo SITE_URL; ?>/index.php" class="studio-admin-auth-brand" aria-label="Return to CAVA LMS home">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="">
                <span>
                    <strong>CAVA Studio</strong>
                    <small>Admin workspace</small>
                </span>
            </a>

            <div class="studio-admin-auth-message">
                <span class="studio-admin-auth-message__kicker">Administration</span>
                <h1 id="adminWelcomeTitle">Welcome back.</h1>
                <p>Manage courses, learners, payments, events, and support from one focused workspace.</p>
            </div>

            <p class="studio-admin-auth-note mb-0">Authorized CAVA LMS administrators only.</p>
        </section>

        <section class="studio-admin-auth-main" aria-labelledby="adminLoginTitle">
            <div class="card studio-admin-login-card">
                <span class="studio-admin-login-card__mark" aria-hidden="true">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <h2 id="adminLoginTitle">Admin sign in</h2>
                <p class="studio-admin-login-card__intro">Use your administrator credentials to continue.</p>

                <form action="login.php" method="POST" id="admin-login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username or email</label>
                        <div class="input-group">
                            <span class="input-group-text" aria-hidden="true">
                                <i class="fa-regular fa-user"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Enter your username or email" autocomplete="username" required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group position-relative studio-admin-password-field">
                            <span class="input-group-text" aria-hidden="true">
                                <i class="fa-solid fa-key"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="studio-password-toggle" onclick="togglePasswordVisibility()" aria-label="Show password" aria-pressed="false">
                                <i class="fa-solid fa-eye-slash animated-eye" id="togglePasswordIcon" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        Access dashboard <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <div class="studio-admin-login-card__footer">
                    <a href="<?php echo SITE_URL; ?>/index.php">
                        <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Return to public site
                    </a>
                </div>
            </div>
        </section>
    </main>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            const toggleButton = toggleIcon.closest('button');
            const showPassword = passwordInput.type === 'password';

            toggleIcon.classList.add('eye-click');
            window.setTimeout(() => {
                passwordInput.type = showPassword ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye-slash', !showPassword);
                toggleIcon.classList.toggle('fa-eye', showPassword);
                toggleIcon.classList.remove('eye-click');
                toggleButton.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
                toggleButton.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
            }, 180);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
