<?php
// API endpoint for Course/Video Progress tracking

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/models/Course.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit;
}

$userId = $_SESSION['user_id'];
$videoId = trim($_POST['video_id'] ?? '');
$courseId = trim($_POST['course_id'] ?? '');
$action = trim($_POST['action'] ?? 'complete'); // complete or uncomplete

if (empty($videoId) || empty($courseId)) {
    echo json_encode(['success' => false, 'message' => 'Video ID and Course ID are required.']);
    exit;
}

try {
    // Check enrollment
    $isEnrolled = Course::isUserEnrolled($userId, $courseId);
    if (!$isEnrolled) {
        echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course.']);
        exit;
    }

    // Verify video belongs to the course
    $video = DB::fetch("SELECT id FROM course_videos WHERE id = ? AND course_id = ?", [$videoId, $courseId]);
    if (!$video) {
        echo json_encode(['success' => false, 'message' => 'Video not found in this course.']);
        exit;
    }

    if ($action === 'complete') {
        // Mark as completed
        $id = generate_uuid();
        $sql = "INSERT INTO user_video_progress (id, user_id, video_id, course_id, status) 
                VALUES (?, ?, ?, ?, 'completed') 
                ON DUPLICATE KEY UPDATE status = 'completed'";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->execute([$id, $userId, $videoId, $courseId]);
        
        echo json_encode(['success' => true, 'message' => 'Lesson marked as completed.']);
    } else {
        // Mark as incomplete / remove record
        $sql = "DELETE FROM user_video_progress WHERE user_id = ? AND video_id = ? AND course_id = ?";
        $stmt = DB::getConnection()->prepare($sql);
        $stmt->execute([$userId, $videoId, $courseId]);
        
        echo json_encode(['success' => true, 'message' => 'Lesson marked as incomplete.']);
    }
    exit;

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>
