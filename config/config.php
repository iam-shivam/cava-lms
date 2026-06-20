<?php
// Enable Output Buffering
ob_start();

// CAVA LMS Configuration File

// Error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Base Paths
define('BASE_PATH', dirname(__DIR__));
define('SITE_URL', 'http://localhost/lms');

// Session setup
if (session_status() === PHP_SESSION_NONE) {
    // Add security cookies flags
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Secure flag can be enabled on HTTPS: ini_set('session.cookie_secure', 1);
    session_start();
}

// Database Credentials
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'cava_lms');

// Razorpay Credentials (Test Mode by default)
define('RAZORPAY_KEY_ID', 'rzp_test_vK68kH1v9q6tWp'); // Sample Test Key ID
define('RAZORPAY_KEY_SECRET', 'y8XN8XGjGkI5C1L69PjXwX1T'); // Sample Test Key Secret

// SMTP / PHPMailer Credentials (using Mailtrap or local SMTP as default)
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', 'your_mailtrap_user_id'); // Placeholder
define('SMTP_PASS', 'your_mailtrap_password'); // Placeholder
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', 'no-reply@cavalms.com');
define('SMTP_FROM_NAME', 'CAVA LMS Portal');

// Autoload composer dependencies
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Helper: Check CSRF Token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Helper: Generate CSRF Token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Helper: Flash message setting/getting
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

function display_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert alert-' . htmlspecialchars($flash['type']) . ' alert-dismissible fade show" role="alert">' .
             htmlspecialchars($flash['message']) .
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' .
             '</div>';
    }
}
