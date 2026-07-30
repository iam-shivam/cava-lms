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
require_once __DIR__ . '/views/layout/header.php';
?>

<div class="lp-body-scope">
    <section class="py-5 min-vh-75 d-flex align-items-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="lp-auth-card">
                        <div class="text-center mb-4">
                            <div class="lp-auth-icon">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-1">Welcome Back</h2>
                            <p class="text-muted fs-7">Enter your registered email or mobile number to receive a secure OTP</p>
                        </div>

                        <form action="login.php" method="POST" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

                            <div class="mb-4">
                                <label for="identifier" class="lp-form-label">Email or Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border border-end-0 text-primary ps-3" style="border-radius: 10px 0 0 10px;">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <input type="text" class="form-control lp-form-control border-start-0 ps-2" id="identifier"
                                        name="identifier" placeholder="e.g. aman@example.com or 9876543210" required style="border-radius: 0 10px 10px 0 !important;">
                                </div>
                            </div>

                            <button type="submit" class="lp-btn-primary w-100 py-3 mb-3 text-center" style="border-radius: 100px;">
                                Send OTP <i class="fa-solid fa-paper-plane ms-2"></i>
                            </button>

                            <div class="text-center mt-3">
                                <p class="mb-0 text-muted fs-7">Don't have an account? <a href="register.php" class="fw-bold text-primary">Register here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>