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

<div class="container" style="margin-top: 10%;">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4 animate-fade-in-up bg-white">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary-light text-primary rounded-circle mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h2 class="fw-bold">Admin Portal</h2>
                    <p class="text-muted">Enter credentials to log in</p>
                </div>
                
                <?php display_flash_message(); ?>
                
                <form action="login.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-user text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 ps-0" id="username" name="username" placeholder="admin" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-key text-muted"></i></span>
                            <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    
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
</body>
</html>
