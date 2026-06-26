<?php
// Enable Output Buffering
ob_start();

// CAVA LMS Configuration File

// Error reporting (for development)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set PHP default timezone
date_default_timezone_set('Asia/Kolkata');

// Load environment variables if .env exists
if (file_exists(dirname(__DIR__) . '/.env')) {
    if (!class_exists('Dotenv\Dotenv')) {
        // Attempt to load Composer autoloader for vlucas/phpdotenv
        if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
            require_once dirname(__DIR__) . '/vendor/autoload.php';
        }
    }
    if (class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
    }
}

// Base Paths
define('BASE_PATH', dirname(__DIR__));
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost/lms');

// Session setup
if (session_status() === PHP_SESSION_NONE) {
    // Add security cookies flags
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    // Secure flag can be enabled on HTTPS: ini_set('session.cookie_secure', 1);
    session_start();
}

// Database Credentials
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'cava_lms');

// Razorpay Credentials (Test Mode by default)
define('RAZORPAY_KEY_ID', $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_vK68kH1v9q6tWp');
define('RAZORPAY_KEY_SECRET', $_ENV['RAZORPAY_KEY_SECRET'] ?? 'y8XN8XGjGkI5C1L69PjXwX1T');

// SMTP / PHPMailer Credentials
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 2525);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? 'your_mailtrap_user_id');
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? 'your_mailtrap_password');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls'); // 'tls' or 'ssl'
define('SMTP_FROM_EMAIL', $_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@cavalms.com');
define('SMTP_FROM_NAME', $_ENV['SMTP_FROM_NAME'] ?? 'CAVA LMS Portal');

// Google OAuth credentials (set via .env)

define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? SITE_URL . '/google_callback.php');

// Autoload composer dependencies
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}
// Master OTP for testing
define('MASTER_OTP', $_ENV['MASTER_OTP'] ?? '');
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

// Helper: Flash message setting/getting (enhanced with slide-in)
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
        $icon = '';
        switch ($flash['type']) {
            case 'success':
                $icon = '<i class="fa-solid fa-check-circle me-2"></i>';
                break;
            case 'danger':
                $icon = '<i class="fa-solid fa-exclamation-circle me-2"></i>';
                break;
            case 'warning':
                $icon = '<i class="fa-solid fa-triangle-exclamation me-2"></i>';
                break;
            case 'info':
                $icon = '<i class="fa-solid fa-info-circle me-2"></i>';
                break;
        }
        // Toast container (if not already present, we add a wrapper)
        echo '<div class="position-fixed top-0 end-0 p-3" style="z-index: 1055;">
                <div id="flashToast" class="toast align-items-center text-bg-' . htmlspecialchars($flash['type']) . ' border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ' . $icon . htmlspecialchars($flash['message']) . '
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
              </div>';
        // Inline script to trigger toast display
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
                var toastEl = document.getElementById("flashToast");
                if (toastEl) {
                    var toast = new bootstrap.Toast(toastEl, {delay: 5000});
                    toast.show();
                }
            });</script>';
    }
}

// Require DB class so database functions are available
require_once __DIR__ . '/db.php';

// Inactivity Session Timeout & Single-Session Enforcement
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['admin_id']);
if ($isLoggedIn) {
    // 1. Session Inactivity Timeout (15 minutes = 900 seconds)
    $timeout_duration = 900; 
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
        $isAdminSession = isset($_SESSION['admin_id']);
        
        // Clear database session ID to allow future logins
        try {
            if (isset($_SESSION['user_id'])) {
                DB::query("UPDATE users SET session_id = NULL WHERE id = ?", [$_SESSION['user_id']]);
            } elseif (isset($_SESSION['admin_id'])) {
                DB::query("UPDATE admins SET session_id = NULL WHERE id = ?", [$_SESSION['admin_id']]);
            }
        } catch (Exception $e) {
            // Ignore DB errors during timeout cleanup
        }
        
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        session_start();
        set_flash_message('warning', 'Your session has expired due to inactivity. Please log in again.');
        if ($isAdminSession) {
            header("Location: " . SITE_URL . "/admin/login.php");
        } else {
            header("Location: " . SITE_URL . "/login.php");
        }
        exit;
    }
    $_SESSION['last_activity'] = time();

    // 2. Single-Session Enforcement (Logout from other devices)
    $currentSessionId = session_id();
    try {
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $dbSession = DB::fetch("SELECT session_id FROM users WHERE id = ?", [$userId]);
            if ($dbSession) {
                if ($dbSession['session_id'] === null) {
                    DB::query("UPDATE users SET session_id = ? WHERE id = ?", [$currentSessionId, $userId]);
                } elseif ($dbSession['session_id'] !== $currentSessionId) {
                    // Logged out from other device
                    $_SESSION = [];
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    session_destroy();
                    session_start();
                    set_flash_message('danger', 'You have been logged out because your account was logged in from another device/browser.');
                    header("Location: " . SITE_URL . "/login.php");
                    exit;
                }
            }
        } elseif (isset($_SESSION['admin_id'])) {
            $adminId = $_SESSION['admin_id'];
            $dbSession = DB::fetch("SELECT session_id FROM admins WHERE id = ?", [$adminId]);
            if ($dbSession) {
                if ($dbSession['session_id'] === null) {
                    DB::query("UPDATE admins SET session_id = ? WHERE id = ?", [$currentSessionId, $adminId]);
                } elseif ($dbSession['session_id'] !== $currentSessionId) {
                    // Logged out from other device
                    $_SESSION = [];
                    if (ini_get("session.use_cookies")) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000,
                            $params["path"], $params["domain"],
                            $params["secure"], $params["httponly"]
                        );
                    }
                    session_destroy();
                    session_start();
                    set_flash_message('danger', 'You have been logged out because your account was logged in from another device/browser.');
                    header("Location: " . SITE_URL . "/admin/login.php");
                    exit;
                }
            }
        }
    } catch (Exception $e) {
        // Ignore DB connection errors during session checks to prevent site-wide crashes
    }
}
?>
