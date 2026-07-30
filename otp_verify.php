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

<div class="lp-body-scope">
    <section class="py-5 min-vh-75 d-flex align-items-center bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="lp-auth-card">
                        <div class="text-center mb-4">
                            <div class="lp-auth-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-1">Enter OTP Code</h2>
                            <p class="text-muted fs-7">Check your registered email or phone for the 6-digit verification code</p>
                        </div>

                        <form method="POST" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="mb-4 text-center">
                                <label class="lp-form-label mb-3">One-Time Security Password</label>
                                <div class="d-flex justify-content-center gap-2 otp-input-group" dir="ltr">
                                    <?php for($i = 0; $i < 6; $i++): ?>
                                        <input type="text" inputmode="numeric" class="form-control text-center fs-4 fw-bold otp-input bg-light border shadow-sm" maxlength="1" required style="width: 50px; height: 60px; border-radius: 12px; border-color: rgba(226,232,240,0.9) !important;">
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="otp" id="final_otp" required>
                            </div>

                            <button type="submit" class="lp-btn-primary w-100 py-3 mb-3 text-center" style="border-radius: 100px;">
                                Verify & Continue <i class="fa-solid fa-circle-check ms-2"></i>
                            </button>

                            <div class="text-center mt-3">
                                <p class="mb-0 text-muted fs-7">Didn't receive the code? <a href="resend_otp.php" class="fw-bold text-primary">Resend OTP / Change Contact</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="assets/js/otp.js"></script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
