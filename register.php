<?php
// User Registration Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    set_flash_message('info', 'You are already logged in.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::register();
}

$csrfToken = generate_csrf_token();
$pageTitle = 'Create account';
require_once __DIR__ . '/views/layout/header.php';
?>

<main class="lp-body-scope studio-auth-layout">
    <div class="studio-auth-layout__context" aria-hidden="true">
        <div class="studio-auth-layout__context-inner">
            <span class="studio-kicker studio-kicker--light">Join CAVA</span>
            <h1>Create one account for your learning workspace.</h1>
            <p>Register with your contact details, then use OTP verification whenever you return.</p>
        </div>
    </div>
    <section class="studio-auth-layout__form" aria-labelledby="register-title">
        <div class="lp-auth-card">
            <a href="<?php echo SITE_URL; ?>/index.php" class="studio-auth-brand" aria-label="CAVA Studio home">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" width="54" height="54">
                <span>CAVA Studio</span>
            </a>
            <div class="mb-4">
                <p class="studio-kicker mb-2">Create account</p>
                <h2 class="mb-2" id="register-title">Set up your student profile</h2>
                <p class="text-muted mb-0">Use contact details that you can access for future OTP sign-ins.</p>
            </div>

            <form action="register.php" method="POST" class="needs-validation">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <div class="mb-3">
                    <label for="full_name" class="lp-form-label">Full name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
                        <input type="text" class="form-control lp-form-control border-start-0" id="full_name" name="full_name" placeholder="Your full name" autocomplete="name" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="lp-form-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-regular fa-envelope" aria-hidden="true"></i></span>
                        <input type="email" class="form-control lp-form-control border-start-0" id="email" name="email" placeholder="name@example.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="mobile_number" class="lp-form-label">Mobile number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa-solid fa-mobile-screen" aria-hidden="true"></i></span>
                        <input type="tel" class="form-control lp-form-control border-start-0" id="mobile_number" name="mobile_number" placeholder="9876543210" autocomplete="tel" required>
                    </div>
                </div>
                <button type="submit" class="lp-btn-primary w-100">Create account <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                <p class="text-center text-muted small mt-4 mb-0">Already registered? <a href="login.php" class="fw-bold">Sign in</a></p>
            </form>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
