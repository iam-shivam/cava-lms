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
$videoId = trim($_POST['video_id'] ?? '');

if (empty($videoId)) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID.']);
    exit;
}

// Check if user is enrolled in the course this video belongs to
$video = DB::fetch("SELECT v.*, c.id as course_id FROM course_videos v JOIN courses c ON v.course_id = c.id WHERE v.id = ?", [$videoId]);
if (!$video) {
    echo json_encode(['success' => false, 'message' => 'Video not found.']);
    exit;
}

// Check enrollment status and active date range
$enrollment = DB::fetch("SELECT status, expiry_date FROM enrollments WHERE user_id = ? AND course_id = ?", [$userId, $video['course_id']]);
if (!$enrollment) {
    echo json_encode(['success' => false, 'message' => 'You do not have access to this course.']);
    exit;
}

if ($enrollment['expiry_date'] && strtotime($enrollment['expiry_date']) < time()) {
    echo json_encode(['success' => false, 'message' => 'Your course access has expired.']);
    exit;
}

if ($enrollment['status'] === 'Pending') {
    // If pending, check if video is one of the first two preview videos
    $syllabus = Course::getSyllabus($video['course_id']);
    $allVideos = [];
    foreach ($syllabus as $sec) {
        if (!empty($sec['videos'])) {
            foreach ($sec['videos'] as $v) {
                $allVideos[] = $v;
            }
        }
    }
    $videoIndex = -1;
    foreach ($allVideos as $index => $v) {
        if (trim($v['id']) === $videoId) {
            $videoIndex = $index;
            break;
        }
    }
    if ($videoIndex >= 2 || $videoIndex === -1) {
        echo json_encode(['success' => false, 'message' => 'Please pay the remaining balance to unlock the rest of the course videos.']);
        exit;
    }
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
        EmailHelper::sendTemplateEmail($userEmail, $userName, 'video_otp', [
            'user_name' => $userName,
            'video_title' => $video['title'],
            'otp' => $otp
        ]);
    } catch (Exception $e) {
        // Silently continue
    }
    
    $maskedEmail = substr($userEmail, 0, 2) . str_repeat('*', max(0, strlen(explode('@', $userEmail)[0]) - 2)) . '@' . explode('@', $userEmail)[1];
    echo json_encode(['success' => true, 'message' => "OTP sent to registered email: {$maskedEmail}"]);
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
