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
    <title>CAVA LMS - Admin Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php display_flash_message(); ?>

    <div class="container" style="margin-top: 10%;">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4 animate-fade-in-up bg-white">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-light text-primary rounded-circle mb-3"
                            style="width: 60px; height: 60px; font-size: 24px;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <h2 class="fw-bold">Admin Portal</h2>
                        <p class="text-muted">Enter credentials to log in</p>
                    </div>

                    <form action="login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold">Username or Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="fa-regular fa-user text-muted"></i></span>
                                <input type="text" class="form-control bg-light border-start-0 ps-0" id="username"
                                    name="username" placeholder="admin" required>
                            </div>
                        </div>

                        <style>
                            /* Hide native Edge/IE password reveal icon to prevent duplicates */
                            input[type="password"]::-ms-reveal {
                                display: none;
                            }
                            /* Animated Eye Icon */
                            .animated-eye {
                                transition: transform 0.2s ease-in-out, opacity 0.2s ease-in-out;
                            }
                            .animated-eye.eye-click {
                                transform: scale(0.5);
                                opacity: 0;
                            }
                        </style>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group position-relative">
                                <span class="input-group-text bg-light border-end-0"><i
                                        class="fa-solid fa-key text-muted"></i></span>
                                <input type="password" class="form-control bg-light border-start-0 ps-0" id="password"
                                    name="password" placeholder="••••••••" autocomplete="current-password" required style="padding-right: 40px;">
                                <span class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer; z-index: 10;" onclick="togglePasswordVisibility()">
                                    <i class="fa-solid fa-eye-slash animated-eye" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                        </div>

                        <script>
                            function togglePasswordVisibility() {
                                const passwordInput = document.getElementById('password');
                                const toggleIcon = document.getElementById('togglePasswordIcon');
                                
                                // Add animation class
                                toggleIcon.classList.add('eye-click');
                                
                                setTimeout(() => {
                                    if (passwordInput.type === 'password') {
                                        passwordInput.type = 'text';
                                        toggleIcon.classList.remove('fa-eye-slash');
                                        toggleIcon.classList.add('fa-eye');
                                    } else {
                                        passwordInput.type = 'password';
                                        toggleIcon.classList.remove('fa-eye');
                                        toggleIcon.classList.add('fa-eye-slash');
                                    }
                                    // Remove animation class to trigger fade back in
                                    toggleIcon.classList.remove('eye-click');
                                }, 200); // Wait for the scale down to complete
                            }
                        </script>

                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3">Access Dashboard</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="<?php echo SITE_URL; ?>/index.php" class="text-decoration-none text-muted fs-7">
                            <i class="fa-solid fa-arrow-left"></i> Return to Site
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>

</html>