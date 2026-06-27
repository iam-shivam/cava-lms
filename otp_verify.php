<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Handle POST submission for OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::verifyOTP();
}

$csrfToken = generate_csrf_token();
require_once __DIR__ . '/views/layout/header.php';
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="custom-card border-0 shadow-lg p-4 p-md-5 animate-fade-in-up">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light text-primary rounded-circle mb-3" style="width: 60px; height: 60px; font-size: 24px;">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h2 class="fw-bold">Enter OTP</h2>
                    <p class="text-muted">Check your email (or SMS) for the code and enter it below.</p>
                </div>
                <form method="POST" class="needs-validation">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <div class="mb-3">
                        <label for="otp" class="form-label fw-semibold">One‑Time Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 ps-0" id="otp" name="otp" placeholder="123456" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 py-3 mb-3">Verify OTP</button>
                    <div class="text-center mt-3">
                        <p class="mb-0 text-muted"><a href="resend_otp.php" class="fw-semibold text-primary">Resend OTP / Change identifier</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php
require_once __DIR__ . '/views/layout/footer.php';
?>
