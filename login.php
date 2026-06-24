<?php
// User Login Page (OTP Request)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::requestOTP();
}

$csrfToken = generate_csrf_token();
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="custom-card border-0 shadow-lg p-4 p-md-5 animate-fade-in-up">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle mb-3"
                        style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h2 class="fw-bold">Welcome Back</h2>
                    <p class="text-muted">Enter your registered email or mobile to log in via OTP</p>
                </div>

                <form action="login.php" method="POST" class="needs-validation">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                    <div class="mb-4">
                        <label for="identifier" class="form-label fw-semibold">Email or Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i
                                    class="fa-regular fa-envelope text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 ps-0" id="identifier"
                                name="identifier" placeholder="Enter your email or phone number" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 mb-3">Send OTP</button>

                    <div class="text-center mt-3">
                        <p class="mb-0 text-muted">Don't have an account? <a href="register.php"
                                class="fw-semibold text-primary">Register here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>