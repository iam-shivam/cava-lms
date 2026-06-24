<?php
// Authentication Controller

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Admin.php';
require_once dirname(__DIR__) . '/helpers/OTPHelper.php';


class AuthController {


    public static function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            set_flash_message('danger', 'CSRF verification failed.');
            header("Location: " . SITE_URL . "/register.php");
            exit;
        }
        
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobileNumber = trim($_POST['mobile_number'] ?? '');
        // Generate random secure password since auth is OTP-only
        $password = bin2hex(random_bytes(16));
        
        // Simple validations
        if (empty($fullName) || empty($email) || empty($mobileNumber)) {
            set_flash_message('danger', 'All fields are required.');
            header("Location: " . SITE_URL . "/register.php");
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash_message('danger', 'Invalid email address.');
            header("Location: " . SITE_URL . "/register.php");
            exit;
        }
        
        if (User::emailExists($email)) {
            set_flash_message('danger', 'Email address is already registered.');
            header("Location: " . SITE_URL . "/register.php");
            exit;
        }
        
        try {
            $created = User::create($fullName, $email, $mobileNumber, $password);
            if ($created) {
                // Send email notification (we will integrate PHPMailer later, but can add email logs entry)
                self::logRegistrationEmail($email, $fullName);
                
                set_flash_message('success', 'Registration successful! You can now log in.');
                header("Location: " . SITE_URL . "/login.php");
                exit;
            } else {
                set_flash_message('danger', 'Registration failed. Please try again.');
            }
        } catch (Exception $e) {
            set_flash_message('danger', 'An error occurred: ' . $e->getMessage());
        }
        
        header("Location: " . SITE_URL . "/register.php");
        exit;
    }
    
    public static function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            set_flash_message('danger', 'CSRF verification failed.');
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            set_flash_message('danger', 'Email and password are required.');
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
        
        $user = User::findByEmail($email);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['status'] === 'Suspended') {
                set_flash_message('danger', 'Your account has been suspended. Please contact support.');
                header("Location: " . SITE_URL . "/login.php");
                exit;
            }
            
            // Set User Sessions
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            set_flash_message('success', 'Logged in successfully. Welcome back!');
            header("Location: " . SITE_URL . "/dashboard.php");
            exit;
        } else {
            set_flash_message('danger', 'Invalid email or password.');
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
    }
    
    // Existing adminLogin method remains unchanged
    public static function adminLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            set_flash_message('danger', 'CSRF verification failed.');
            header("Location: " . SITE_URL . "/admin/login.php");
            exit;
        }
        
        $emailOrUsername = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($emailOrUsername) || empty($password)) {
            set_flash_message('danger', 'Username/Email and password are required.');
            header("Location: " . SITE_URL . "/admin/login.php");
            exit;
        }
        
        $admin = Admin::findByEmailOrUsername($emailOrUsername);
        
        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Set Admin Sessions
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            
            set_flash_message('success', 'Admin login successful!');
            header("Location: " . SITE_URL . "/admin/dashboard.php");
            exit;
        } else {
            set_flash_message('danger', 'Invalid credentials.');
            header("Location: " . SITE_URL . "/admin/login.php");
            exit;
        }
    }







    
    // OTP login: request OTP
    public static function requestOTP() {
        // Expect 'identifier' POST field (email or mobile)
        $identifier = trim($_POST['identifier'] ?? '');
        if (empty($identifier)) {
            set_flash_message('danger', 'Please provide an email or mobile number.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        // Try to find user by email or mobile number
        $user = User::findByIdentifier($identifier);
        if (!$user) {
            set_flash_message('danger', 'No account found for the provided identifier.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        
        if ($user['status'] === 'Suspended') {
            set_flash_message('danger', 'Your account has been suspended. Please contact support.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        
        // Generate OTP and store under primary email
        $otp = OTPHelper::generateOTP();
        OTPHelper::storeOTP($user['email'], $otp);
        
        // Send OTP via email
        $subject = 'Your OTP Code';
        $body = "Your OTP code is: <b>{$otp}</b>. It expires in 10 minutes.";
        
        require_once dirname(__DIR__) . '/helpers/EmailHelper.php';
        EmailHelper::sendEmail($user['email'], $user['full_name'], $subject, $body);
        
        // Store primary email in session for later verification
        $_SESSION['otp_email'] = $user['email'];
        
        // Mask the email for safety/feedback
        $emailParts = explode('@', $user['email']);
        $maskedEmail = substr($emailParts[0], 0, 2) . str_repeat('*', max(0, strlen($emailParts[0]) - 2)) . '@' . $emailParts[1];
        
        set_flash_message('success', "OTP has been sent to your registered email: {$maskedEmail}.");
        header('Location: ' . SITE_URL . '/otp_verify.php');
        exit;
    }

    // OTP login: verify OTP
    public static function verifyOTP() {
        $otp = trim($_POST['otp'] ?? '');
        $email = $_SESSION['otp_email'] ?? '';
        if (empty($otp) || empty($email)) {
            set_flash_message('danger', 'Invalid OTP submission or session expired.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        // Master OTP bypass for testing
        if (defined('MASTER_OTP') && MASTER_OTP !== '' && $otp === MASTER_OTP) {
            $valid = true;
        } else {
            $valid = OTPHelper::verifyOTP($email, $otp);
        }
        if ($valid) {
            // Load user and set session
            $user = User::findByEmail($email);
            if ($user) {
                unset($_SESSION['otp_email']);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                set_flash_message('success', 'Logged in successfully.');
                header('Location: ' . SITE_URL . '/dashboard.php');
                exit;
            }
        }
        set_flash_message('danger', 'Invalid or expired OTP.');
        header('Location: ' . SITE_URL . '/otp_verify.php');
        exit;
    }

    public static function logout() {
        // Unset sessions
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        
        set_flash_message('success', 'Logged out successfully.');
        header("Location: " . SITE_URL . "/login.php");
        exit;
    }
    
    public static function adminLogout() {
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_email']);
        
        set_flash_message('success', 'Admin logged out successfully.');
        header("Location: " . SITE_URL . "/admin/login.php");
        exit;
    }
    
    // Helpers
    private static function logRegistrationEmail($email, $name) {
        require_once dirname(__DIR__) . '/helpers/EmailHelper.php';
        $subject = "Welcome to CAVA LMS!";
        $body = "<h3>Hi " . htmlspecialchars($name) . ",</h3><p>Thank you for registering with CAVA LMS Portal. Explore our courses today!</p>";
        EmailHelper::sendEmail($email, $name, $subject, $body);
    }
}
