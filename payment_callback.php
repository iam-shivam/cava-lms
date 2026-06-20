<?php
// Payment Callback Handler Page
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/models/Payment.php';
require_once __DIR__ . '/models/Course.php';
require_once __DIR__ . '/models/Webinar.php';
require_once __DIR__ . '/controllers/PaymentController.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/login.php");
    exit;
}

$razorpayOrderId = trim($_GET['razorpay_order_id'] ?? '');
$razorpayPaymentId = trim($_GET['razorpay_payment_id'] ?? '');
$razorpaySignature = trim($_GET['razorpay_signature'] ?? '');
$error = trim($_GET['error'] ?? '');

if (empty($razorpayOrderId)) {
    set_flash_message('danger', 'Invalid callback parameters.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

// 1. Check if checkout failed on client side
if ($error === 'payment_failed') {
    Payment::updatePaymentStatus($razorpayOrderId, null, null, 'Failed');
    set_flash_message('danger', 'Payment failed or cancelled. Please try again.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

// 2. Fetch payment details from DB log
$payment = Payment::getByOrderId($razorpayOrderId);
if (!$payment) {
    set_flash_message('danger', 'Payment record not found.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}

// 3. Verify Payment signature on backend
$verified = PaymentController::verifyPayment($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);

if ($verified) {
    set_flash_message('success', 'Thank you! Payment completed and order processed successfully.');
    
    // Redirect based on item type
    if ($payment['item_type'] === 'course') {
        $course = Course::getById($payment['item_id']);
        if ($course) {
            header("Location: " . SITE_URL . "/course_play.php?slug=" . $course['slug']);
            exit;
        }
    } elseif ($payment['item_type'] === 'webinar') {
        header("Location: " . SITE_URL . "/dashboard.php?tab=webinars");
        exit;
    }
    
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
} else {
    set_flash_message('danger', 'Payment verification failed. Please contact support.');
    header("Location: " . SITE_URL . "/dashboard.php");
    exit;
}
