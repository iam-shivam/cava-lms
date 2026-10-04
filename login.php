<?php
// User Login Page (OTP Request)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    set_flash_message('info', 'You are already logged in.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::requestOTP();
}

$csrfToken = generate_csrf_token();
$pageTitle = 'Student login';
require_once __DIR__ . '/views/layout/header.php';
?>

<main class="lp-body-scope studio-auth-layout">
    <div class="studio-auth-layout__context" aria-hidden="true">
        <div class="studio-auth-layout__context-inner">
            <span class="studio-kicker studio-kicker--light">Student access</span>
            <h1>Pick up where your learning left off.</h1>
            <p>Your dashboard, enrolled courses, lesson progress, and resources are available after OTP verification.</p>
        </div>
    </div>
    <section class="studio-auth-layout__form" aria-labelledby="login-title">
        <div class="lp-auth-card">
            <a href="<?php echo SITE_URL; ?>/index.php" class="studio-auth-brand" aria-label="CAVA Studio home">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" width="54" height="54">
                <span>CAVA Studio</span>
            </a>
            <div class="mb-4">
                <p class="studio-kicker mb-2">Welcome back</p>
                <h2 class="mb-2" id="login-title">Sign in with a secure OTP</h2>
                <p class="text-muted mb-0">Use the email address or mobile number registered with your account.</p>
            </div>

            <form action="login.php" method="POST" class="needs-validation">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <div class="mb-4">
                    <label for="identifier" class="lp-form-label">Email or mobile number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-regular fa-envelope" aria-hidden="true"></i></span>
                        <input type="text" class="form-control lp-form-control border-start-0" id="identifier" name="identifier" placeholder="name@example.com or 9876543210" autocomplete="username" required>
                    </div>
                </div>
                <button type="submit" class="lp-btn-primary w-100">Send OTP <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                <p class="text-center text-muted small mt-4 mb-0">New to CAVA? <a href="register.php" class="fw-bold">Create an account</a></p>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
