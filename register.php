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
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <h2 class="fw-bold text-dark mb-1">Create Account</h2>
                            <p class="text-muted fs-7">Start your learning journey today on CAVA LMS</p>
                        </div>
                        
                        <form action="register.php" method="POST" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            
                            <div class="mb-3">
                                <label for="full_name" class="lp-form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border border-end-0 text-primary ps-3" style="border-radius: 10px 0 0 10px;">
                                        <i class="fa-regular fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control lp-form-control border-start-0 ps-2" id="full_name" name="full_name" placeholder="John Doe" required style="border-radius: 0 10px 10px 0 !important;">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="lp-form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border border-end-0 text-primary ps-3" style="border-radius: 10px 0 0 10px;">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control lp-form-control border-start-0 ps-2" id="email" name="email" placeholder="john@example.com" required style="border-radius: 0 10px 10px 0 !important;">
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="mobile_number" class="lp-form-label">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border border-end-0 text-primary ps-3" style="border-radius: 10px 0 0 10px;">
                                        <i class="fa-solid fa-mobile-screen"></i>
                                    </span>
                                    <input type="tel" class="form-control lp-form-control border-start-0 ps-2" id="mobile_number" name="mobile_number" placeholder="9876543210" required style="border-radius: 0 10px 10px 0 !important;">
                                </div>
                            </div>
                            
                            <button type="submit" class="lp-btn-primary w-100 py-3 mb-3 text-center" style="border-radius: 100px;">
                                Create Account <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                            
                            <div class="text-center mt-3">
                                <p class="mb-0 text-muted fs-7">Already have an account? <a href="login.php" class="fw-bold text-primary">Login here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
