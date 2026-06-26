<?php
// API endpoint for Video OTP security

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/models/Course.php';
require_once dirname(__DIR__) . '/models/VideoOTP.php';
require_once dirname(__DIR__) . '/helpers/OTPHelper.php';
require_once dirname(__DIR__) . '/helpers/EmailHelper.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'];
$action = trim($_POST['action'] ?? '');
$videoId = intval($_POST['video_id'] ?? 0);

if ($videoId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID.']);
    exit;
}

// Check if user is enrolled in the course this video belongs to
$video = DB::fetch("SELECT v.*, c.id as course_id FROM course_videos v JOIN courses c ON v.course_id = c.id WHERE v.id = ?", [$videoId]);
if (!$video) {
    echo json_encode(['success' => false, 'message' => 'Video not found.']);
    exit;
}

if (!Course::isUserEnrolled($userId, $video['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'You do not have access to this course.']);
    exit;
}

if ($action === 'send') {
    $otp = OTPHelper::generateOTP();
    
    // Check master OTP logic
    if (defined('MASTER_OTP') && MASTER_OTP !== '') {
        $otp = MASTER_OTP;
    }
    
    OTPHelper::storeOTP($userEmail, $otp, 5); // 5 minutes expiry for OTP itself
    
    // Simulate sending to mobile by sending an email
    try {
        $userName = $_SESSION['user_name'] ?? 'Student';
        $subject = "Video Access OTP";
        $body = "<h3>Hi $userName,</h3><p>Your OTP to unlock the video '<strong>" . htmlspecialchars($video['title']) . "</strong>' is: <strong>$otp</strong></p><p>It will expire in 5 minutes.</p>";
        EmailHelper::sendEmail($userEmail, $userName, $subject, $body);
    } catch (Exception $e) {
        // Silently continue
    }
    
    echo json_encode(['success' => true, 'message' => 'OTP sent to registered mobile/email.']);
    exit;
} elseif ($action === 'verify') {
    $otp = trim($_POST['otp'] ?? '');
    if (empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Please enter OTP.']);
        exit;
    }
    
    if (OTPHelper::verifyOTP($userEmail, $otp) || (defined('MASTER_OTP') && MASTER_OTP !== '' && $otp === MASTER_OTP)) {
        $duration = 1440; // 24 hours fallback, but essentially active until another video is clicked
        
        VideoOTP::createSession($userId, $videoId, $duration);
        
        echo json_encode(['success' => true, 'message' => 'Video unlocked successfully!']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    exit;
}
