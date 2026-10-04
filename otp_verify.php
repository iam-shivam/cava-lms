<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Handle POST submission for OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::verifyOTP();
}

$csrfToken = generate_csrf_token();
$pageTitle = 'Verify OTP';
require_once __DIR__ . '/views/layout/header.php';
?>

<main class="lp-body-scope studio-auth-layout">
    <div class="studio-auth-layout__context" aria-hidden="true">
        <div class="studio-auth-layout__context-inner">
            <span class="studio-kicker studio-kicker--light">Verification</span>
            <h1>One quick check before you continue.</h1>
            <p>The one-time code confirms access to the contact details connected to your student account.</p>
        </div>
    </div>
    <section class="studio-auth-layout__form" aria-labelledby="otp-title">
        <div class="lp-auth-card">
            <a href="<?php echo SITE_URL; ?>/index.php" class="studio-auth-brand" aria-label="CAVA Studio home">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpeg" alt="" width="54" height="54">
                <span>CAVA Studio</span>
            </a>
            <div class="mb-4">
                <p class="studio-kicker mb-2">Enter your code</p>
                <h2 class="mb-2" id="otp-title">Verify your sign-in</h2>
                <p class="text-muted mb-0">Enter the six-digit OTP sent to your registered contact.</p>
            </div>

            <form method="POST" class="needs-validation">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <fieldset class="mb-4 text-center">
                    <legend class="lp-form-label mb-3">One-time password</legend>
                    <div class="d-flex justify-content-center align-items-center flex-nowrap gap-1 gap-sm-2 otp-input-group" dir="ltr">
                        <?php for ($i = 0; $i < 6; $i++): ?>
                            <input type="text" inputmode="numeric" pattern="[0-9]*" class="form-control otp-input" maxlength="1" autocomplete="one-time-code" aria-label="OTP digit <?php echo $i + 1; ?>" required>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="otp" id="final_otp" required>
                </fieldset>
                <button type="submit" class="lp-btn-primary w-100">Verify and continue <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                <p class="text-center text-muted small mt-4 mb-0">Didn't receive the code? <a href="resend_otp.php" class="fw-bold">Resend OTP or change contact</a></p>
            </form>
        </div>
    </section>
</main>

<script src="assets/js/otp.js"></script>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
