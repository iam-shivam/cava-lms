<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/helpers/RateLimiter.php';
require_once __DIR__ . '/helpers/OTPHelper.php';
require_once __DIR__ . '/helpers/EmailHelper.php';
require_once __DIR__ . '/helpers/SMSHelper.php';

// Ensure identifier and email are stored in session from previous OTP request
$identifier = $_SESSION['otp_identifier'] ?? '';
$email = $_SESSION['otp_email'] ?? '';

if (empty($identifier) || empty($email)) {
    // No context – redirect back to login
    set_flash_message('danger', 'Session expired. Please request a new OTP.');
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

$ip = RateLimiter::getIP();
$reason = '';
// Allow resend only if rate‑limit permits a new OTP request
if (!RateLimiter::canRequestOTP($identifier, $ip, $reason)) {
    set_flash_message('danger', $reason);
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Generate new OTP and store it (keyed by primary email)
$otp = OTPHelper::generateOTP();
OTPHelper::storeOTP($email, $otp);
RateLimiter::logOTPRequest($identifier, $ip);

// Send the OTP via appropriate channel (email or SMS)
$isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;
if ($isEmail) {
    $subject = 'Your OTP Code';
    $body = "Your OTP code is: <b>{$otp}</b>. It expires in 5 minutes.";
    EmailHelper::sendEmail($email, $identifier, $subject, $body);
    $masked = substr($email, 0, 2) . str_repeat('*', max(0, strlen(explode('@', $email)[0]) - 2)) . '@' . explode('@', $email)[1];
    set_flash_message('success', "OTP has been resent to your email: {$masked}.");
} else {
    $message = "Your OTP code is: {$otp}. It expires in 5 minutes.";
    SMSHelper::sendSMS($identifier, $message);
    $maskedMobile = str_repeat('*', max(0, strlen($identifier) - 4)) . substr($identifier, -4);
    set_flash_message('success', "OTP has been resent to your mobile: {$maskedMobile}.");
}

// Return to the verification page
header('Location: ' . SITE_URL . '/otp_verify.php');
exit;
?>
