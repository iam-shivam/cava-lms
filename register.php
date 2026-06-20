<?php
// User Registration Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::register();
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
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h2 class="fw-bold">Create Account</h2>
                    <p class="text-muted">Start learning today on CAVA LMS</p>
                </div>
                
                <form action="register.php" method="POST" class="needs-validation">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="mb-3">
                        <label for="full_name" class="form-label fw-semibold">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-user text-muted"></i></span>
                            <input type="text" class="form-control bg-light border-start-0 ps-0" id="full_name" name="full_name" placeholder="John Doe" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-regular fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control bg-light border-start-0 ps-0" id="email" name="email" placeholder="john@example.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="mobile_number" class="form-label fw-semibold">Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-mobile-screen text-muted"></i></span>
                            <input type="tel" class="form-control bg-light border-start-0 ps-0" id="mobile_number" name="mobile_number" placeholder="9876543210" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                            <input type="password" class="form-control bg-light border-start-0 ps-0" id="password" name="password" placeholder="Min. 6 characters" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 mb-3">Create Account</button>
                    
                    <div class="text-center mt-3">
                        <p class="mb-0 text-muted">Already have an account? <a href="login.php" class="fw-semibold text-primary">Login here</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layout/footer.php'; ?>
