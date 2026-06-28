<?php
// Authentication Controller

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/Admin.php';
require_once dirname(__DIR__) . '/helpers/OTPHelper.php';
require_once dirname(__DIR__) . '/helpers/RateLimiter.php';


class AuthController {


    public static function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $ip = RateLimiter::getIP();
        $reason = '';
        if (!RateLimiter::canRegister($ip, $reason)) {
            set_flash_message('danger', $reason);
            header("Location: " . SITE_URL . "/register.php");
            exit;
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
        
        if (User::mobileExists($mobileNumber)) {
            set_flash_message('danger', 'Mobile number is already registered.');
            header("Location: " . SITE_URL . "/register.php");
            exit;
        }
        
        try {
            $created = User::create($fullName, $email, $mobileNumber, $password);
            if ($created) {
                // Log successful registration
                RateLimiter::logRegistration($ip);

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

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = RateLimiter::getIP();
        $reason = '';

        if (!RateLimiter::canLogin($email, $ip, $reason)) {
            set_flash_message('danger', $reason);
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
        
        $csrf = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($csrf)) {
            set_flash_message('danger', 'CSRF verification failed.');
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
        
        if (empty($email) || empty($password)) {
            set_flash_message('danger', 'Email and password are required.');
            header("Location: " . SITE_URL . "/login.php");
            exit;
        }
        
        $user = User::findByEmail($email);
        $success = ($user && password_verify($password, $user['password_hash']));
        RateLimiter::logLogin($email, $ip, $success);
        
        if ($success) {
            if ($user['status'] === 'Suspended') {
                set_flash_message('danger', 'Your account has been suspended. Please contact support.');
                header("Location: " . SITE_URL . "/login.php");
                exit;
            }
            
            // Set User Sessions
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            
            // Single Session Enforcement: Update session_id in Database
            $sessionId = session_id();
            DB::query("UPDATE users SET session_id = ? WHERE id = ?", [$sessionId, $user['id']]);
            
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
            
            // Single Session Enforcement: Update session_id in Database
            $sessionId = session_id();
            DB::query("UPDATE admins SET session_id = ? WHERE id = ?", [$sessionId, $admin['id']]);
            
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
        $ip = RateLimiter::getIP();
        
        // Expect 'identifier' POST field (email or mobile)
        $identifier = trim($_POST['identifier'] ?? '');
        if (empty($identifier)) {
            set_flash_message('danger', 'Please provide an email or mobile number.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }

        // Check if allowed to request OTP
        $reason = '';
        if (!RateLimiter::canRequestOTP($identifier, $ip, $reason)) {
            set_flash_message('danger', $reason);
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        
        // Try to find user by email or mobile number
        $user = User::findByIdentifier($identifier);
        if (!$user) {
            set_flash_message('danger', 'No account found. Please <a href="' . SITE_URL . '/register.php" class="text-white fw-bold text-decoration-underline">Register now</a>.', true);
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
        // Store OTP keyed by primary email (defaults to 5 minutes expiry)
        OTPHelper::storeOTP($user['email'], $otp);

        // Log OTP request
        RateLimiter::logOTPRequest($identifier, $ip);

        // Determine delivery method
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
        if ($isEmail) {
            // Send OTP via email
            $subject = 'Your OTP Code';
            $body = "Your OTP code is: <b>{$otp}</b>. It expires in 5 minutes.";
            require_once dirname(__DIR__) . '/helpers/EmailHelper.php';
            EmailHelper::sendEmail($user['email'], $user['full_name'], $subject, $body);
            $masked = substr($user['email'], 0, 2) . str_repeat('*', max(0, strlen(explode('@', $user['email'])[0]) - 2)) . '@' . explode('@', $user['email'])[1];
            set_flash_message('success', "OTP has been sent to your registered email: {$masked}.");
        } else {
            // Send OTP via SMS (mobile number)
            $message = "Your OTP code is: {$otp}. It expires in 5 minutes.";
            require_once dirname(__DIR__) . '/helpers/SMSHelper.php';
            SMSHelper::sendSMS($user['mobile_number'], $message);
            // Mask mobile number for display (show last 4 digits)
            $maskedMobile = str_repeat('*', max(0, strlen($user['mobile_number']) - 4)) . substr($user['mobile_number'], -4);
            set_flash_message('success', "OTP has been sent to your registered mobile: {$maskedMobile}.");
        }

        // Store primary email and identifier in session for later verification
        $_SESSION['otp_email'] = $user['email'];
        $_SESSION['otp_identifier'] = $identifier;
        header('Location: ' . SITE_URL . '/otp_verify.php');
        exit;
    }

    // OTP login: verify OTP
    public static function verifyOTP() {
        $ip = RateLimiter::getIP();
        
        $otp = trim($_POST['otp'] ?? '');
        $email = $_SESSION['otp_email'] ?? '';
        $identifier = $_SESSION['otp_identifier'] ?? $email;
        
        if (empty($otp) || empty($email)) {
            set_flash_message('danger', 'Invalid OTP submission or session expired.');
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }

        // Check if verify is locked
        $reason = '';
        if (!RateLimiter::canVerifyOTP($identifier, $ip, $reason)) {
            set_flash_message('danger', $reason);
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        
        // Master OTP bypass for testing
        if (defined('MASTER_OTP') && MASTER_OTP !== '' && $otp === MASTER_OTP) {
            $valid = true;
        } else {
            $valid = OTPHelper::verifyOTP($email, $otp);
        }

        // Log OTP verification attempt
        RateLimiter::logOTPVerify($identifier, $ip, $valid);
        
        if ($valid) {
            // Load user and set session
            $user = User::findByEmail($email);
            if ($user) {
                unset($_SESSION['otp_email']);
                unset($_SESSION['otp_identifier']);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Single Session Enforcement: Update session_id in Database
                $sessionId = session_id();
                DB::query("UPDATE users SET session_id = ? WHERE id = ?", [$sessionId, $user['id']]);
                
                set_flash_message('success', 'Logged in successfully.');
                header('Location: ' . SITE_URL . '/dashboard.php');
                exit;
            }
        }

        // Check if locked after this failure
        $lockReason = '';
        if (!RateLimiter::canVerifyOTP($identifier, $ip, $lockReason)) {
            set_flash_message('danger', $lockReason);
            header('Location: ' . SITE_URL . '/login.php');
            exit;
        }
        
        set_flash_message('danger', 'Invalid or expired OTP.');
        header('Location: ' . SITE_URL . '/otp_verify.php');
        exit;
    }

    public static function logout() {
        if (isset($_SESSION['user_id'])) {
            try {
                DB::query("UPDATE users SET session_id = NULL WHERE id = ?", [$_SESSION['user_id']]);
            } catch (Exception $e) {
                // Ignore DB errors during logout
            }
        }
        
        // Unset sessions
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['last_activity']);
        
        set_flash_message('success', 'Logged out successfully.');
        header("Location: " . SITE_URL . "/login.php");
        exit;
    }
    
    public static function adminLogout() {
        if (isset($_SESSION['admin_id'])) {
            try {
                DB::query("UPDATE admins SET session_id = NULL WHERE id = ?", [$_SESSION['admin_id']]);
            } catch (Exception $e) {
                // Ignore DB errors during logout
            }
        }
        
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        unset($_SESSION['admin_email']);
        unset($_SESSION['last_activity']);
        
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
