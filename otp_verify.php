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
                    <div class="mb-4 text-center">
                        <label class="form-label fw-semibold mb-3">One‑Time Password</label>
                        <div class="d-flex justify-content-center gap-2 otp-input-group" dir="ltr">
                            <?php for($i = 0; $i < 6; $i++): ?>
                                <input type="text" inputmode="numeric" class="form-control text-center fs-4 fw-bold otp-input bg-light border-0 shadow-sm" maxlength="1" required style="width: 50px; height: 60px; border-radius: 8px;">
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="otp" id="final_otp" required>
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
<script src="assets/js/otp.js"></script>
<?php
require_once __DIR__ . '/views/layout/footer.php';
?>
